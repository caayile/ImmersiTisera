<?php

namespace App\Console\Commands;

use App\Models\MentorSession;
use App\Models\ProgramOutput;
use App\Models\Timeline;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Throwable;

#[Signature('files:secure-uploads {--apply : Copy files to private storage and remove public copies} ')]
#[Description('Move participant reports and evidence from public to private storage')]
class SecureUploadedFiles extends Command
{
    /** @var array{planned: int, migrated: int, private: int, failed: int} */
    private array $summary = [
        'planned' => 0,
        'migrated' => 0,
        'private' => 0,
        'failed' => 0,
    ];

    public function handle(): int
    {
        $this->summary = [
            'planned' => 0,
            'migrated' => 0,
            'private' => 0,
            'failed' => 0,
        ];
        $apply = (bool) $this->option('apply');

        $this->processQuery(
            ProgramOutput::query()->select(['id', 'file_path', 'hasil_file_path']),
            ['file_path', 'hasil_file_path'],
            $apply,
        );
        $this->processQuery(
            Timeline::query()->select(['id', 'attachment_path']),
            ['attachment_path'],
            $apply,
        );
        $this->processQuery(
            MentorSession::query()->select(['id', 'attachment_path']),
            ['attachment_path'],
            $apply,
        );

        if (! $apply) {
            $this->warn('Dry run only. Review the counts, back up storage, then rerun with --apply.');
        }

        $this->table(['Planned', 'Migrated', 'Already private', 'Failed'], [[
            $this->summary['planned'],
            $this->summary['migrated'],
            $this->summary['private'],
            $this->summary['failed'],
        ]]);

        return $this->summary['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function processQuery(Builder $query, array $columns, bool $apply): void
    {
        $query->chunkById(100, function (Collection $records) use ($columns, $apply): void {
            foreach ($records as $record) {
                foreach ($columns as $column) {
                    $path = $record->{$column};

                    if (! is_string($path) || $path === '') {
                        continue;
                    }

                    $this->migratePath($path, $apply);
                }
            }
        });
    }

    private function migratePath(string $path, bool $apply): void
    {
        if (
            ! str_starts_with($path, 'outputs/')
            && ! str_starts_with($path, 'timeline-reports/')
            && ! str_starts_with($path, 'mentoring-docs/')
        ) {
            return;
        }

        $public = Storage::disk('public');
        $private = Storage::disk('local');

        if (! $public->exists($path)) {
            if ($private->exists($path)) {
                $this->summary['private']++;

                return;
            }

            $this->summary['failed']++;

            return;
        }

        if (! $apply) {
            $this->summary['planned']++;

            return;
        }

        try {
            $contents = $public->get($path);

            if (! is_string($contents) || ! $private->put($path, $contents)) {
                $this->summary['failed']++;

                return;
            }

            $privateContents = $private->get($path);
            if (! is_string($privateContents) || ! hash_equals(hash('sha256', $contents), hash('sha256', $privateContents))) {
                $this->summary['failed']++;

                return;
            }

            if (! $public->delete($path)) {
                $this->summary['failed']++;

                return;
            }

            $this->summary['migrated']++;
        } catch (Throwable) {
            $this->summary['failed']++;
        }
    }
}
