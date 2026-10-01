<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    public function test_participant_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');
        $this->seed();
        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($participant)
            ->post('/participant/profile', [
                'name' => $participant->name,
                'phone' => $participant->phone,
                'nidn' => '00112233',
                'faculty' => 'Fakultas Teknik',
                'study_program' => 'Informatika',
                'expertise' => 'Machine Learning',
                'competency' => 'AI',
                'avatar' => UploadedFile::fake()->image('dosen.jpg'),
            ])
            ->assertRedirect();

        $avatarUrl = $participant->fresh()->avatar;
        $this->assertStringStartsWith('/storage/avatars/', $avatarUrl);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $avatarUrl));
    }

    public function test_mentor_can_upload_and_replace_a_profile_photo(): void
    {
        Storage::fake('public');
        $this->seed();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();

        $this->actingAs($mentor)
            ->post('/api/profile', [
                '_method' => 'PUT',
                'business_unit' => 'Digital Business',
                'industry_field' => 'TSPM',
                'expertise' => ['Digital strategy'],
                'avatar' => UploadedFile::fake()->image('mentor.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $firstAvatarUrl = $mentor->fresh()->avatar;
        $firstAvatarPath = str_replace('/storage/', '', $firstAvatarUrl);
        Storage::disk('public')->assertExists($firstAvatarPath);

        $this->actingAs($mentor)
            ->post('/api/profile', [
                '_method' => 'PUT',
                'business_unit' => 'Digital Business',
                'industry_field' => 'TSPM',
                'expertise' => ['Digital strategy'],
                'avatar' => UploadedFile::fake()->image('mentor-baru.png'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $secondAvatarUrl = $mentor->fresh()->avatar;
        $this->assertNotSame($firstAvatarUrl, $secondAvatarUrl);
        Storage::disk('public')->assertMissing($firstAvatarPath);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $secondAvatarUrl));
    }

    public function test_profile_photo_upload_rejects_non_image_files(): void
    {
        $this->seed();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();

        $this->actingAs($mentor)
            ->post('/api/profile', [
                '_method' => 'PUT',
                'business_unit' => 'Digital Business',
                'industry_field' => 'TSPM',
                'expertise' => ['Digital strategy'],
                'avatar' => UploadedFile::fake()->create('not-an-image.pdf', 100, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('avatar');
    }

    public function test_participant_profile_rejects_non_image_files(): void
    {
        $this->seed();
        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($participant)
            ->from('/participant/profile')
            ->post('/participant/profile', [
                'name' => $participant->name,
                'nidn' => '00112233',
                'faculty' => 'Fakultas Teknik',
                'study_program' => 'Informatika',
                'expertise' => 'Machine Learning',
                'competency' => 'AI',
                'avatar' => UploadedFile::fake()->create('not-an-image.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect('/participant/profile')
            ->assertSessionHasErrors('avatar');
    }
}
