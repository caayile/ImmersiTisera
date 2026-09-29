<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'program_id', 'participant_id', 'title', 'type', 'description', 'file_path', 'link',
    'hasil_file_path', 'laporan_link',
    'department_id', 'business_unit_id', 'year',
    'is_main_output', 'is_final_report', 'mentor_feedback', 'status',
])]

class ProgramOutput extends Model
{
    protected $table = 'outputs';

    protected function casts(): array
    {
        return [
            'is_main_output' => 'boolean',
            'is_final_report' => 'boolean',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /**
     * External URL as entered by the participant, normalized with a scheme.
     */
    public function linkUrl(): ?string
    {
        return self::normalizeUrl($this->link);
    }

    public function laporanLinkUrl(): ?string
    {
        return self::normalizeUrl($this->laporan_link);
    }

    public function hasilFileUrl(): ?string
    {
        return $this->hasil_file_path ? Storage::disk('public')->url($this->hasil_file_path) : null;
    }

    public function laporanFileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    private static function normalizeUrl(mixed $value): ?string
    {
        $link = trim((string) $value);

        if ($link === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $link)) {
            $link = 'https://'.$link;
        }

        return $link;
    }
}
