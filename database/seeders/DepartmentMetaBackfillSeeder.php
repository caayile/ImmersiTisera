<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentMetaBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $subtitles = [
            'tspm' => 'Tiga Serangkai Pustaka Mandiri',
            'tsic' => 'Tiga Serangkai Innovation Center',
            'wjl' => 'Wangsa Jastra Lestari',
        ];

        foreach ($subtitles as $slug => $subtitle) {
            Department::where('slug', $slug)->update(['subtitle' => $subtitle]);
        }

        $defaultImages = [
            'tspm' => 'images/hero/campus.jpg',
            'tsic' => 'images/hero/campus.jpg',
            'k33' => 'images/hero/k33.jpg',
            'wjl' => 'images/hero/k33.jpg',
            'assalam-hypermarket' => 'images/hero/assalaam.jpg',
            'sd-al-firdaus' => 'images/hero/al-firdaus.jpg',
            'puspa-holistic-integrative-care' => 'images/hero/campus.jpg',
        ];

        foreach ($defaultImages as $slug => $path) {
            Department::where('slug', $slug)->whereNull('image_path')->update(['image_path' => $path]);
        }

        foreach (Department::all() as $department) {
            BusinessUnit::where('department_id', $department->id)
                ->whereNull('image_path')
                ->update(['image_path' => $department->image_path]);
        }
    }
}