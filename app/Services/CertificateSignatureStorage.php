<?php

namespace App\Services;

use App\Models\Certificate;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CertificateSignatureStorage
{
    private const MAX_BYTES = 1048576;

    public function store(?UploadedFile $file, ?string $dataUrl, ?string $existingPath = null): string
    {
        $path = $file
            ? $this->storeUploadedFile($file)
            : $this->storeDataUrl((string) $dataUrl);

        if ($existingPath && ! str_starts_with($existingPath, 'data:image/')) {
            Storage::disk('local')->delete($existingPath);
        }

        return $path;
    }

    public function toDataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'data:image/')) {
            return $path;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return null;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($disk->get($path));

        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($path));
    }

    /**
     * Prefer the signature frozen on the certificate. A missing file or the
     * 1-pixel seed placeholder falls back to the mentor's current signature.
     */
    public function forCertificate(?string $stored, ?string $current): ?string
    {
        if (! $this->isPlaceholder($stored)) {
            $resolved = $this->forDisplay($stored);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return $this->forDisplay($current);
    }

    /**
     * Crop empty margins and thicken the stroke so a saved signature
     * fills the certificate and report signature slots.
     */
    public function forDisplay(?string $value): ?string
    {
        $uri = $this->toDataUri($value);

        if ($uri === null || $this->isPlaceholder($uri)) {
            return $uri;
        }

        $binary = $this->binary($uri);

        if ($binary === null) {
            return $uri;
        }

        return $this->emphasizeInk($binary) ?? $uri;
    }

    public function replacePlaceholderSnapshots(int $mentorId, string $path): void
    {
        Certificate::query()
            ->whereHas('program', fn ($query) => $query->where('mentor_id', $mentorId))
            ->get()
            ->each(function (Certificate $certificate) use ($path): void {
                $stored = $certificate->mentor_signature_path ?: $certificate->mentor_signature;

                if (! $this->isPlaceholder($stored) && $this->toDataUri($stored) !== null) {
                    return;
                }

                $attributes = Schema::hasColumn('certificates', 'mentor_signature_path')
                    ? ['mentor_signature' => null, 'mentor_signature_path' => $path]
                    : ['mentor_signature' => $path];

                $certificate->update($attributes);
            });
    }

    public function isPlaceholder(?string $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $binary = $this->binary($value);

        if ($binary === null) {
            return false;
        }

        $size = @getimagesizefromstring($binary);

        return is_array($size) && ($size[0] ?? 0) <= 2 && ($size[1] ?? 0) <= 2;
    }

    private function storeUploadedFile(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $validTypes = [
            'png' => ['image/png'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'webp' => ['image/webp'],
        ];

        if (
            $file->getSize() > self::MAX_BYTES
            || ! isset($validTypes[$extension])
            || ! in_array($mime, $validTypes[$extension], true)
            || ! @getimagesize($file->getRealPath())
        ) {
            throw ValidationException::withMessages([
                'certificate_signature_file' => 'Unggah PNG, JPG, atau WebP yang valid dengan ukuran maksimal 1 MB.',
            ]);
        }

        $path = $file->store('certificates/signatures', 'local');

        if (! $path) {
            throw new RuntimeException('Tanda tangan sertifikat gagal disimpan.');
        }

        return $path;
    }

    private function storeDataUrl(string $dataUrl): string
    {
        if (! preg_match('/\Adata:image\/(png|jpeg|webp);base64,([A-Za-z0-9+\/=\r\n]+)\z/i', $dataUrl, $matches)) {
            throw ValidationException::withMessages([
                'certificate_signature_data' => 'Gambar tanda tangan harus berupa PNG, JPG, atau WebP yang valid.',
            ]);
        }

        $contents = base64_decode($matches[2], true);

        if ($contents === false || strlen($contents) > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'certificate_signature_data' => 'Ukuran tanda tangan maksimal 1 MB.',
            ]);
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        $extensions = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];

        if (! isset($extensions[$mime]) || ! @getimagesizefromstring($contents)) {
            throw ValidationException::withMessages([
                'certificate_signature_data' => 'Gambar tanda tangan tidak valid.',
            ]);
        }

        $path = 'certificates/signatures/'.Str::uuid().'.'.$extensions[$mime];

        if (! Storage::disk('local')->put($path, $contents)) {
            throw new RuntimeException('Tanda tangan sertifikat gagal disimpan.');
        }

        return $path;
    }

    private function binary(string $value): ?string
    {
        if (str_starts_with($value, 'data:image/')) {
            if (! preg_match('/base64,([A-Za-z0-9+\/=\r\n]+)/', $value, $matches)) {
                return null;
            }

            $decoded = base64_decode($matches[1], true);

            return $decoded === false ? null : $decoded;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($value)) {
            return null;
        }

        return $disk->get($value);
    }

    private function emphasizeInk(string $binary): ?string
    {
        $source = @imagecreatefromstring($binary);

        if ($source === false) {
            return null;
        }

        $bounds = $this->inkBounds($source);

        if ($bounds === null) {
            imagedestroy($source);

            return null;
        }

        [$left, $top, $right, $bottom] = $bounds;
        $radius = 2;
        $width = $right - $left + 1 + ($radius * 2);
        $height = $bottom - $top + 1 + ($radius * 2);
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $clear = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width - 1, $height - 1, $clear);

        /** @var array<string, int> $colors */
        $colors = [];

        for ($y = $top; $y <= $bottom; $y++) {
            for ($x = $left; $x <= $right; $x++) {
                $ink = $this->inkAt($source, $x, $y);

                if ($ink === null) {
                    continue;
                }

                $key = $ink['red'].','.$ink['green'].','.$ink['blue'];
                $colors[$key] ??= imagecolorallocatealpha($canvas, $ink['red'], $ink['green'], $ink['blue'], 0);
                $paint = $colors[$key];
                $originX = $x - $left + $radius;
                $originY = $y - $top + $radius;

                for ($offsetY = -$radius; $offsetY <= $radius; $offsetY++) {
                    for ($offsetX = -$radius; $offsetX <= $radius; $offsetX++) {
                        if (($offsetX * $offsetX) + ($offsetY * $offsetY) > $radius * $radius) {
                            continue;
                        }

                        imagesetpixel($canvas, $originX + $offsetX, $originY + $offsetY, $paint);
                    }
                }
            }
        }

        imagedestroy($source);

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        imagedestroy($canvas);

        if (! is_string($png) || $png === '') {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int}|null
     */
    private function inkBounds(\GdImage $image): ?array
    {
        $left = imagesx($image);
        $top = imagesy($image);
        $right = -1;
        $bottom = -1;

        for ($y = 0; $y < imagesy($image); $y++) {
            for ($x = 0; $x < imagesx($image); $x++) {
                if ($this->inkAt($image, $x, $y) === null) {
                    continue;
                }

                $left = min($left, $x);
                $top = min($top, $y);
                $right = max($right, $x);
                $bottom = max($bottom, $y);
            }
        }

        if ($right < $left || $bottom < $top) {
            return null;
        }

        return [$left, $top, $right, $bottom];
    }

    /**
     * @return array{red: int, green: int, blue: int, alpha: int}|null
     */
    private function inkAt(\GdImage $image, int $x, int $y): ?array
    {
        $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));

        if ($rgba['alpha'] > 40 || ($rgba['red'] > 245 && $rgba['green'] > 245 && $rgba['blue'] > 245)) {
            return null;
        }

        return $rgba;
    }
}
