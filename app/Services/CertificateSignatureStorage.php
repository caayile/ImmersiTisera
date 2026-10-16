<?php

namespace App\Services;

use finfo;
use Illuminate\Http\UploadedFile;
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
}
