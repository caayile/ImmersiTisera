<?php

use App\Support\NotificationLink;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('notifications')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $data = json_decode($row->data, true);

                    if (! is_array($data) || ! array_key_exists('url', $data)) {
                        continue;
                    }

                    $normalized = NotificationLink::normalize($data['url']);

                    if ($normalized === $data['url']) {
                        continue;
                    }

                    $data['url'] = $normalized;

                    DB::table('notifications')
                        ->where('id', $row->id)
                        ->update(['data' => json_encode($data)]);
                }
            });
    }

    public function down(): void
    {
        // Data normalization is not reversible.
    }
};
