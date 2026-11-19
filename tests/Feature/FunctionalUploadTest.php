<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use App\Services\ImageSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FunctionalUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_security_accepts_every_allowed_format(): void
    {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            $file = UploadedFile::fake()->image("foto.{$extension}", 120, 120);

            ImageSecurityService::validateImage($file);
        }

        $this->assertTrue(true);
    }

    public function test_image_security_rejects_pdf_disguised_as_png(): void
    {
        $file = UploadedFile::fake()->createWithContent('laporan.png', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");

        $this->expectException(ValidationException::class);

        ImageSecurityService::validateImage($file);
    }

    public function test_image_security_rejects_text_file_disguised_as_image(): void
    {
        $file = UploadedFile::fake()->createWithContent('catatan.jpg', 'hanya teks biasa, bukan gambar');

        $this->expectException(ValidationException::class);

        ImageSecurityService::validateImage($file);
    }

    public function test_image_security_rejects_polyglot_png_containing_php_code(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nMkAAAAASUVORK5CYII=');
        $this->assertIsString($png);
        $this->assertNotFalse(getimagesizefromstring($png));

        $file = UploadedFile::fake()->createWithContent('foto.png', $png."\n<?php system(\$_GET['cmd']); ?>");

        $this->expectException(ValidationException::class);

        ImageSecurityService::validateImage($file);
    }

    public function test_admin_home_background_rejects_pdf_disguised_as_png(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mediaCount = MediaAsset::query()->count();

        $this->actingAs($admin)
            ->from(route('admin.settings'))
            ->post(route('admin.settings.home-hero'), [
                'background' => UploadedFile::fake()->createWithContent('laporan.png', "%PDF-1.4\n%%EOF"),
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors([
                'image' => 'Keamanan: File yang diunggah terdeteksi sebagai file biner/eksekusi berbahaya dan bukan gambar asli.',
            ]);

        $this->assertSame($mediaCount, MediaAsset::query()->count());
    }

    public function test_avatar_upload_rejects_polyglot_image_without_storing_media(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);
        $originalAvatar = $mentor->avatar;
        $mediaCount = MediaAsset::query()->count();

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nMkAAAAASUVORK5CYII=');
        $this->assertIsString($png);

        $this->actingAs($mentor)
            ->postJson('/api/profile', [
                '_method' => 'PUT',
                'business_unit' => 'Digital Business',
                'expertise' => ['Digital strategy'],
                'avatar' => UploadedFile::fake()->createWithContent('foto.png', $png.'<?php system($_GET["cmd"]); ?>'),
            ])
            ->assertUnprocessable();

        $this->assertSame($originalAvatar, $mentor->fresh()->avatar);
        $this->assertSame($mediaCount, MediaAsset::query()->count());
    }

    public function test_certificate_signature_rejects_pdf_disguised_as_png(): void
    {
        Storage::fake('local');
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $originalSignature = $mentor->mentor->certificate_signature;

        $this->actingAs($mentor)
            ->from(route('mentor.certificates.signature'))
            ->post(route('mentor.certificates.signature'), [
                'certificate_signature_file' => UploadedFile::fake()->createWithContent('tanda-tangan.png', "%PDF-1.4\n%%EOF"),
            ])
            ->assertSessionHasErrors('certificate_signature_file');

        $this->assertSame($originalSignature, $mentor->mentor->fresh()->certificate_signature);
    }

    public function test_media_route_uses_detected_image_mime_instead_of_database_value(): void
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nMkAAAAASUVORK5CYII=');
        $this->assertIsString($png);

        $media = MediaAsset::create([
            'id' => 'media-security-test',
            'mime_type' => 'text/html',
            'content' => MediaAsset::encodeBinaryContent($png, DB::connection()->getDriverName()),
        ]);

        $this->get(route('media.show', $media))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Disposition', 'inline');
    }

    public function test_media_route_refuses_poisoned_non_image_content(): void
    {
        $html = '<html><body><script>alert(1)</script></body></html>';

        $media = MediaAsset::create([
            'id' => 'media-poisoned-test',
            'mime_type' => 'image/png',
            'content' => MediaAsset::encodeBinaryContent($html, DB::connection()->getDriverName()),
        ]);

        $this->get(route('media.show', $media))->assertNotFound();
    }
}
