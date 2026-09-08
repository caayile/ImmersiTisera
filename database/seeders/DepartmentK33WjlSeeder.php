<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentK33WjlSeeder extends Seeder
{
    public function run(): void
    {
        $k33 = Department::updateOrCreate(
            ['slug' => 'k33'],
            [
                'name' => 'K33',
                'description' => 'Departemen operasional dan pendukung di lokasi K33 untuk program Industry Immersion.',
                'function' => 'Operations, people, finance, and IT services',
                'area' => 'K33',
                'image_path' => 'images/hero/k33.jpg',
                'status' => 'active',
            ],
        );

        $wjl = Department::updateOrCreate(
            ['slug' => 'wjl'],
            [
                'name' => 'WJL',
                'description' => 'Departemen produksi, pemasaran, dan administrasi di lokasi WJL untuk program Industry Immersion.',
                'function' => 'Production, marketing, finance, and people services',
                'area' => 'WJL',
                'subtitle' => 'Wangsa Jastra Lestari',
                'image_path' => 'images/hero/k33.jpg',
                'status' => 'active',
            ],
        );

        $k33Units = [
            ['Operation (Sales)', 'Sales operations', ['Manajemen']],
            ['Human Resources Development', 'People development', ['Manajemen', 'Psikologi']],
            ['General Affair & Industrial Relation', 'GA & industrial relations', ['Manajemen']],
            ['Finance Accounting', 'Finance and accounting', ['Sistem Informasi', 'Akuntansi', 'Manajemen']],
            ['IT', 'Information technology', ['Sistem Informasi', 'Informatika', 'Teknologi Informasi']],
        ];

        $wjlUnits = [
            ['Production', 'Production operations', ['Manajemen']],
            ['Marketing', 'Brand & campaign', ['Manajemen', 'Desain Komunikasi Visual']],
            ['Finance & Accounting', 'Finance and accounting', ['Manajemen', 'Sistem Informasi']],
            ['HR & GA', 'People & general affairs', ['Manajemen', 'Psikologi']],
        ];

        foreach ($k33Units as [$name, $function, $programs]) {
            $this->unit($k33, $name, $function, $programs);
        }

        foreach ($wjlUnits as [$name, $function, $programs]) {
            $this->unit($wjl, $name, $function, $programs);
        }
    }

    private function unit(Department $department, string $name, string $function, array $programs): void
    {
        BusinessUnit::updateOrCreate(
            [
                'department_id' => $department->id,
                'name' => $name,
            ],
            [
                'description' => "Departemen {$name} pada unit bisnis {$department->name} untuk immersion dosen TSU.",
                'function' => $function,
                'image_path' => $department->image_path,
                'work_done' => "Operasional harian {$name}, kolaborasi lintas tim, dan improvement berkelanjutan.",
                'example_activities' => 'Observasi, penugasan, riset terapan, dan diskusi mentoring 30 menit.',
                'requirements' => 'Kompetensi relevan dengan fungsi unit dan komitmen 8 minggu.',
                'relevant_programs' => $programs,
                'period' => '8 weeks / ±60 days',
                'status' => 'open',
            ],
        );
    }
}
