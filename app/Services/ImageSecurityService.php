<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ImageSecurityService
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * @return list<string>
     */
    public static function allowedMimes(): array
    {
        return self::ALLOWED_MIMES;
    }

    /**
     * Strictly inspects and validates an uploaded image file.
     * Prevents upload of binary executables (ELF, PE/EXE, Mach-O), shell scripts,
     * PHP webshells, or polyglot files disguised as images.
     *
     * @throws ValidationException
     */
    public static function validateImage(?UploadedFile $file, string $attribute = 'image'): void
    {
        if (! $file) {
            return;
        }

        // 1. Validasi ekstensi file
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension()));
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                $attribute => 'Format file tidak diizinkan. Hanya file gambar (.jpg, .jpeg, .png, .webp) yang diperbolehkan.',
            ]);
        }

        // 2. Validasi MIME type awal
        $mime = strtolower((string) ($file->getMimeType() ?: ''));
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                $attribute => 'Tipe konten file tidak sah. File terdeteksi bukan merupakan gambar murni.',
            ]);
        }

        // 3. Pastikan file ada di disk dan dapat dibaca
        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath) || ! is_readable($realPath)) {
            throw ValidationException::withMessages([
                $attribute => 'File tidak dapat diakses untuk analisis keamanan biner.',
            ]);
        }

        // 4. Analisis integritas fisik struktur gambar via getimagesize
        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false || empty($imageInfo[0]) || empty($imageInfo[1])) {
            throw ValidationException::withMessages([
                $attribute => 'Keamanan: File yang diunggah terdeteksi sebagai file biner/eksekusi berbahaya dan bukan gambar asli.',
            ]);
        }

        $detectedMime = strtolower((string) ($imageInfo['mime'] ?? ''));
        if (! in_array($detectedMime, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                $attribute => 'Struktur biner file tidak cocok dengan format gambar yang diizinkan.',
            ]);
        }

        // 5. Pemeriksaan Magic Bytes biner berbahaya (Executable & Script Signatures)
        $handle = @fopen($realPath, 'rb');
        if ($handle) {
            $magicHeader = fread($handle, 512) ?: '';
            fclose($handle);

            // Linux ELF executable binary
            if (str_starts_with($magicHeader, "\x7fELF")) {
                throw ValidationException::withMessages([
                    $attribute => 'Keamanan ditolak: File terdeteksi sebagai biner eksekutabel Linux (ELF).',
                ]);
            }

            // Windows PE / DOS executable binary (MZ)
            if (str_starts_with($magicHeader, 'MZ')) {
                throw ValidationException::withMessages([
                    $attribute => 'Keamanan ditolak: File terdeteksi sebagai biner eksekutabel Windows (PE/EXE/DLL).',
                ]);
            }

            // Java class binary (\xCA\xFE\xBA\xBE)
            if (str_starts_with($magicHeader, "\xCA\xFE\xBA\xBE")) {
                throw ValidationException::withMessages([
                    $attribute => 'Keamanan ditolak: File terdeteksi sebagai biner Java bytecode.',
                ]);
            }

            // Unix / Bash script shebang
            if (str_starts_with($magicHeader, '#!/')) {
                throw ValidationException::withMessages([
                    $attribute => 'Keamanan ditolak: File terdeteksi sebagai script eksekusi shell.',
                ]);
            }

            // Web shell / PHP injection scan
            $content = file_get_contents($realPath);
            if (is_string($content) && (
                preg_match('/<\?(?:php|=)/i', $content) ||
                preg_match('/<script\b[^>]*>/i', $content) ||
                preg_match('/\b(?:eval|passthru|shell_exec|system|base64_decode)\s*\(/i', $content)
            )) {
                throw ValidationException::withMessages([
                    $attribute => 'Keamanan ditolak: File terdeteksi mengandung script/kode eksekusi berbahaya.',
                ]);
            }
        }
    }
}
