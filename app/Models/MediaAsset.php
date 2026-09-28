<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable(['id', 'mime_type', 'content'])]
class MediaAsset extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $hidden = ['content'];

    public static function encodeBinaryContent(string $content, string $driver): string
    {
        return $driver === 'pgsql' ? '\\x'.bin2hex($content) : $content;
    }

    public static function decodeBinaryContent(mixed $content, string $driver): string
    {
        if (is_resource($content)) {
            $content = stream_get_contents($content);
        }

        if (! is_string($content)) {
            return '';
        }

        if ($driver !== 'pgsql' || ! str_starts_with($content, '\\x')) {
            return $content;
        }

        return hex2bin(substr($content, 2)) ?: '';
    }

    public function binaryContent(): string
    {
        $content = $this->getRawOriginal('content');

        return self::decodeBinaryContent($content, DB::connection($this->getConnectionName())->getDriverName());
    }
}
