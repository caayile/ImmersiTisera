<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DepartmentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_department_with_image(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $image = UploadedFile::fake()->image('unit.jpg', 800, 500);
        $imageContents = file_get_contents($image->getRealPath());

        $this->actingAs($admin)
            ->post('/admin/departments', [
                'name' => 'Unit Bisnis Foto',
                'subtitle' => 'Nama Lengkap Baru',
                'description' => 'Deskripsi unit bisnis baru.',
                'area' => 'Area baru',
                'image' => $image,
            ])
            ->assertRedirect();

        $department = Department::where('slug', 'unit-bisnis-foto')->firstOrFail();
        $this->assertSame('Nama Lengkap Baru', $department->subtitle);
        $this->assertNotNull($department->image_path);
        $this->assertStringStartsWith('database-media/', $department->image_path);
        $mediaId = substr($department->image_path, strlen('database-media/'));
        $media = MediaAsset::findOrFail($mediaId);
        $this->assertSame($imageContents, $media->binaryContent());
        $encodedImage = MediaAsset::encodeBinaryContent($imageContents, 'pgsql');
        $this->assertSame('\\x'.bin2hex($imageContents), $encodedImage);
        $this->assertSame($imageContents, MediaAsset::decodeBinaryContent($encodedImage, 'pgsql'));
        $binaryStream = fopen('php://memory', 'r+');
        fwrite($binaryStream, $encodedImage);
        rewind($binaryStream);
        $this->assertSame($imageContents, MediaAsset::decodeBinaryContent($binaryStream, 'pgsql'));
        fclose($binaryStream);
        $this->get(route('media.show', $mediaId))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg')
            ->assertContent($imageContents);

        $oldMediaId = $mediaId;
        $this->actingAs($admin)
            ->put(route('admin.departments.update', $department), [
                'name' => 'Unit Bisnis Foto Updated',
                'subtitle' => 'Nama Lengkap Diperbarui',
                'description' => 'Deskripsi diperbarui.',
                'area' => 'Area diperbarui',
                'status' => 'active',
                'image' => UploadedFile::fake()->image('unit-baru.jpg', 800, 500),
            ])
            ->assertRedirect();

        $department->refresh();
        $this->assertSame('Nama Lengkap Diperbarui', $department->subtitle);
        $this->assertSame('unit-bisnis-foto-updated', $department->slug);
        $this->assertNotSame($oldMediaId, substr($department->image_path, strlen('database-media/')));
        $this->assertDatabaseMissing('media_assets', ['id' => $oldMediaId]);
        $mediaId = substr($department->image_path, strlen('database-media/'));
        $this->assertModelExists(MediaAsset::findOrFail($mediaId));

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $department))
            ->assertRedirect();

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseMissing('media_assets', ['id' => $mediaId]);
    }

    public function test_admin_cannot_delete_department_that_has_business_units(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $department = Department::where('slug', 'tspm')->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $department))
            ->assertRedirect()
            ->assertSessionHasErrors('department');

        $this->assertDatabaseHas('departments', ['id' => $department->id]);
    }

    public function test_admin_can_upload_business_unit_image(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $tspm = Department::where('slug', 'tspm')->firstOrFail();
        $image = UploadedFile::fake()->image('dept.jpg', 800, 500);

        $this->actingAs($admin)
            ->post('/admin/business-units', [
                'department_id' => $tspm->id,
                'name' => 'Departemen Foto Baru',
                'description' => 'Deskripsi departemen baru.',
                'image' => $image,
            ])
            ->assertRedirect();

        $unit = $tspm->businessUnits()->where('name', 'Departemen Foto Baru')->firstOrFail();
        $this->assertNotNull($unit->image_path);
        $this->assertStringStartsWith('database-media/', $unit->image_path);
        $oldMediaId = substr($unit->image_path, strlen('database-media/'));
        $this->assertModelExists(MediaAsset::findOrFail($oldMediaId));

        $this->actingAs($admin)
            ->put(route('admin.units.update', $unit), [
                'department_id' => $tspm->id,
                'name' => 'Departemen Foto Baru',
                'description' => 'Deskripsi diperbarui.',
                'status' => 'open',
                'image' => UploadedFile::fake()->image('dept-baru.jpg', 800, 500),
            ])
            ->assertRedirect();

        $unit->refresh();
        $this->assertNotSame($oldMediaId, substr($unit->image_path, strlen('database-media/')));
        $this->assertDatabaseMissing('media_assets', ['id' => $oldMediaId]);
        $mediaId = substr($unit->image_path, strlen('database-media/'));
        $this->assertModelExists(MediaAsset::findOrFail($mediaId));
        $this->assertSame('Deskripsi diperbarui.', $unit->description);

        $this->actingAs($admin)
            ->delete(route('admin.units.destroy', $unit))
            ->assertRedirect();

        $this->assertDatabaseMissing('business_units', ['id' => $unit->id]);
        $this->assertDatabaseMissing('media_assets', ['id' => $mediaId]);
    }
}
