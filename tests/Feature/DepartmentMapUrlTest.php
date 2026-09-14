<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\User;
use Tests\TestCase;

class DepartmentMapUrlTest extends TestCase
{
    public function test_place_pin_coordinates_win_over_camera_position(): void
    {
        $url = 'https://www.google.com/maps/place/Tiga+Serangkai/@-7.500000,110.800000,17z/data=!3m1!4b1!4m6!3m5!1s0x2e7a31:0xabc!8m2!3d-7.565432!4d110.821098';

        $coordinates = Department::mapCoordinates($url);

        $this->assertSame(['lat' => '-7.565432', 'lng' => '110.821098'], $coordinates);
        $this->assertSame(
            'https://maps.google.com/maps?q=-7.565432,110.821098&hl=id&z=17&output=embed',
            Department::embedMapUrl($url)
        );
        $this->assertSame($url, Department::openMapUrl($url));
    }

    public function test_place_path_coordinates_are_used_for_the_pin(): void
    {
        $url = 'https://www.google.com/maps/place/-7.5589405,110.7634012/@-7.500000,110.800000,17z';

        $this->assertSame(['lat' => '-7.5589405', 'lng' => '110.7634012'], Department::mapCoordinates($url));
        $this->assertSame($url, Department::openMapUrl($url));
        $this->assertSame(
            'https://maps.google.com/maps?q=-7.5589405,110.7634012&hl=id&z=17&output=embed',
            Department::embedMapUrl($url)
        );
    }

    public function test_embed_url_opens_the_same_pin_in_google_maps(): void
    {
        $url = 'https://maps.google.com/maps?q=-7.5589405,110.7634012&z=17&output=embed';

        $this->assertSame(
            'https://www.google.com/maps/search/?api=1&query=-7.5589405,110.7634012',
            Department::openMapUrl($url)
        );
        $this->assertSame(
            'https://maps.google.com/maps?q=-7.5589405,110.7634012&hl=id&z=17&output=embed',
            Department::embedMapUrl($url)
        );
    }

    public function test_unit_page_fills_the_card_image_and_opens_the_same_map_pin(): void
    {
        $shareUrl = 'https://www.google.com/maps/place/Imersi/@-7.500000,110.800000,17z/data=!3d-7.565432!4d110.821098';
        $department = Department::create([
            'name' => 'Peta Presisi',
            'slug' => 'peta-presisi',
            'area' => 'Solo',
            'status' => 'active',
            'map_url' => $shareUrl,
        ]);
        $unit = BusinessUnit::create([
            'department_id' => $department->id,
            'name' => 'Unit Peta',
            'status' => 'open',
        ]);

        $this->get(route('units.show', $unit))
            ->assertOk()
            ->assertSee('object-cover', false)
            ->assertSee('q=-7.565432,110.821098', false)
            ->assertSee($shareUrl, false)
            ->assertDontSee('q=-7.500000,110.800000', false)
            ->assertSee('Buka di Google Maps');
    }

    public function test_department_page_fills_unit_photos_and_does_not_show_a_map(): void
    {
        $this->seed();

        $department = Department::where('slug', 'k33')->firstOrFail();

        $this->get(route('departments.show', $department))
            ->assertOk()
            ->assertSee('object-cover', false)
            ->assertSee('h-52 w-full', false)
            ->assertDontSee('Lokasi Magang');
    }

    public function test_departments_index_fills_unit_images_to_the_card(): void
    {
        $this->seed();

        $this->get('/departments')
            ->assertOk()
            ->assertSee('object-cover', false);
    }

    public function test_admin_keeps_the_original_google_maps_share_link(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $shareUrl = 'https://www.google.com/maps/place/Tiga+Serangkai/@-7.500000,110.800000,17z/data=!3d-7.565432!4d110.821098';

        $this->actingAs($admin)
            ->post('/admin/departments', [
                'name' => 'Unit Peta Asli',
                'map_url' => $shareUrl,
            ])
            ->assertRedirect();

        $department = Department::where('slug', 'unit-peta-asli')->firstOrFail();

        $this->assertSame($shareUrl, $department->map_url);
        $this->assertSame(
            'https://maps.google.com/maps?q=-7.565432,110.821098&hl=id&z=17&output=embed',
            $department->mapEmbedSrc()
        );
    }
}
