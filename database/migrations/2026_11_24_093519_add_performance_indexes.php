<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes for the filters, counts, and joins used by the busiest routes.
     *
     * @return array<int, array{table: string, columns: array<int, string>, name: string}>
     */
    private function indexes(): array
    {
        return [
            ['table' => 'users', 'columns' => ['role', 'status'], 'name' => 'users_role_status_index'],
            ['table' => 'users', 'columns' => ['verification_status'], 'name' => 'users_verification_status_index'],

            ['table' => 'business_units', 'columns' => ['department_id', 'status'], 'name' => 'business_units_department_status_index'],
            ['table' => 'business_units', 'columns' => ['status', 'registration_start', 'registration_deadline'], 'name' => 'business_units_registration_window_index'],
            ['table' => 'business_units', 'columns' => ['batch'], 'name' => 'business_units_batch_index'],

            ['table' => 'applications', 'columns' => ['participant_id', 'business_unit_id'], 'name' => 'applications_participant_unit_index'],
            ['table' => 'applications', 'columns' => ['business_unit_id', 'batch', 'status'], 'name' => 'applications_quota_index'],
            ['table' => 'applications', 'columns' => ['mentor_id', 'status'], 'name' => 'applications_mentor_status_index'],
            ['table' => 'applications', 'columns' => ['status'], 'name' => 'applications_status_index'],

            ['table' => 'programs', 'columns' => ['participant_id', 'archived_at'], 'name' => 'programs_participant_archived_index'],
            ['table' => 'programs', 'columns' => ['mentor_id', 'status'], 'name' => 'programs_mentor_status_index'],
            ['table' => 'programs', 'columns' => ['status'], 'name' => 'programs_status_index'],

            ['table' => 'logbooks', 'columns' => ['participant_id', 'status'], 'name' => 'logbooks_participant_status_index'],
            ['table' => 'logbooks', 'columns' => ['program_id', 'status'], 'name' => 'logbooks_program_status_index'],
            ['table' => 'logbooks', 'columns' => ['date'], 'name' => 'logbooks_date_index'],

            ['table' => 'outputs', 'columns' => ['participant_id', 'status'], 'name' => 'outputs_participant_status_index'],
            ['table' => 'outputs', 'columns' => ['program_id', 'status'], 'name' => 'outputs_program_status_index'],
            ['table' => 'mentor_sessions', 'columns' => ['participant_id'], 'name' => 'mentor_sessions_participant_index'],
            ['table' => 'timelines', 'columns' => ['program_id', 'week'], 'name' => 'timelines_program_week_index'],
            ['table' => 'evaluations', 'columns' => ['program_id'], 'name' => 'evaluations_program_index'],
            ['table' => 'collaboration_pipelines', 'columns' => ['program_id'], 'name' => 'collaboration_pipelines_program_index'],

            ['table' => 'notifications', 'columns' => ['notifiable_type', 'notifiable_id', 'read_at'], 'name' => 'notifications_notifiable_read_index'],
            ['table' => 'notifications', 'columns' => ['read_at'], 'name' => 'notifications_read_at_index'],

            ['table' => 'news', 'columns' => ['status', 'published_at'], 'name' => 'news_status_published_index'],
            ['table' => 'certificates', 'columns' => ['status', 'issued_at'], 'name' => 'certificates_status_issued_index'],
        ];
    }

    public function up(): void
    {
        foreach ($this->indexes() as $index) {
            if (! Schema::hasTable($index['table']) || Schema::hasIndex($index['table'], $index['name'])) {
                continue;
            }

            $columns = array_values(array_filter(
                $index['columns'],
                fn (string $column): bool => Schema::hasColumn($index['table'], $column),
            ));

            if ($columns === []) {
                continue;
            }

            Schema::table($index['table'], function (Blueprint $table) use ($columns, $index): void {
                $table->index($columns, $index['name']);
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes()) as $index) {
            if (! Schema::hasTable($index['table']) || ! Schema::hasIndex($index['table'], $index['name'])) {
                continue;
            }

            Schema::table($index['table'], function (Blueprint $table) use ($index): void {
                $table->dropIndex($index['name']);
            });
        }
    }
};
