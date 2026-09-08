<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DepartmentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_department_with_image(): void
    {
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/departments', [
                'name' => 'Unit Bisnis Foto',
                'subtitle' => 'Nama Lengkap Baru',
                'description' => 'Deskripsi unit bisnis baru.',
                'area' => 'Area baru',
                'image' => UploadedFile::fake()->image('unit.jpg', 800, 500),
            ])
            ->assertRedirect();

        $department = Department::where('slug', 'unit-bisnis-foto')->firstOrFail();
        $this->assertSame('Nama Lengkap Baru', $department->subtitle);
        $this->assertNotNull($department->image_path);
        Storage::disk('public')->assertExists($department->image_path);

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
        Storage::disk('public')->assertExists($department->image_path);

        $this->actingAs($admin)
            ->delete(route('admin.departments.destroy', $department))
            ->assertRedirect();

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        Storage::disk('public')->assertMissing($department->image_path);
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
        Storage::fake('public');
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $tspm = Department::where('slug', 'tspm')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/business-units', [
                'department_id' => $tspm->id,
                'name' => 'Departemen Foto Baru',
                'description' => 'Deskripsi departemen baru.',
                'image' => UploadedFile::fake()->image('dept.jpg', 800, 500),
            ])
            ->assertRedirect();

        $unit = $tspm->businessUnits()->where('name', 'Departemen Foto Baru')->firstOrFail();
        $this->assertNotNull($unit->image_path);
        Storage::disk('public')->assertExists($unit->image_path);

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
        Storage::disk('public')->assertExists($unit->image_path);
        $this->assertSame('Deskripsi diperbarui.', $unit->description);

        $this->actingAs($admin)
            ->delete(route('admin.units.destroy', $unit))
            ->assertRedirect();

        $this->assertDatabaseMissing('business_units', ['id' => $unit->id]);
        Storage::disk('public')->assertMissing($unit->image_path);
    }
}