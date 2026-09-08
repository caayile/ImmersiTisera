<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentAhAlFirdausPuspaSeeder extends Seeder
{
    public function run(): void
    {
        $ahDepartments = [
            ['Store Operation', 'Operasi toko dan layanan pelanggan Assalaam Hypermarket.', 'Store operations', ['Manajemen', 'Pemasaran']],
            ['Trading', 'Pengadaan dan perdagangan produk Assalaam Hypermarket.', 'Trading & merchandising', ['Manajemen']],
            ['Finance & Accounting', 'Keuangan dan akuntansi Assalaam Hypermarket.', 'Finance and accounting', ['Akuntansi', 'Manajemen']],
            ['IT', 'Teknologi informasi dan sistem digital Assalaam Hypermarket.', 'Information technology', ['Informatika', 'Sistem Informasi']],
            ['Buyer', 'Pembelian dan pengelolaan supplier Assalaam Hypermarket.', 'Buying & procurement', ['Manajemen']],
            ['Marketing & PD', 'Pemasaran dan product development Assalaam Hypermarket.', 'Marketing & product development', ['Manajemen', 'Ilmu Komunikasi']],
            ['HR & GA', 'Sumber daya manusia dan general affair Assalaam Hypermarket.', 'People & general affairs', ['Manajemen', 'Psikologi']],
        ];

        $assalam = Department::updateOrCreate(
            ['slug' => 'assalam-hypermarket'],
            [
                'name' => 'Assalam Hypermarket',
                'description' => 'Unit bisnis retail dengan departemen operasional, perdagangan, keuangan, teknologi, pemasaran, dan SDM.',
                'function' => 'Retail operations and corporate services',
                'area' => 'Assalaam Hypermarket (AH)',
                'status' => 'active',
            ],
        );

        foreach ($ahDepartments as [$name, $description, $function, $programs]) {
            $this->unit($assalam, $name, $description, $function, $programs);
        }

        $this->departmentWithDirectPlacement(
            name: 'SD Al-Firdaus',
            slug: 'sd-al-firdaus',
            description: 'Mitra immersion untuk pendidikan dasar; direkomendasikan bagi prodi PGSD.',
            function: 'Pendidikan dasar',
            area: 'Pendidikan',
            programs: ['PGSD'],
        );

        $this->departmentWithDirectPlacement(
            name: 'Puspa Holistic Integrative Care',
            slug: 'puspa-holistic-integrative-care',
            description: 'Mitra immersion bidang pengembangan holistik; direkomendasikan bagi prodi Psikologi.',
            function: 'Pengembangan holistik & kesejahteraan',
            area: 'Psikologi',
            programs: ['Psikologi'],
        );
    }

    private function departmentWithDirectPlacement(
        string $name,
        string $slug,
        string $description,
        string $function,
        string $area,
        array $programs,
    ): void {
        $department = Department::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'function' => $function,
                'area' => $area,
                'status' => 'active',
            ],
        );

        // Penempatan default internal agar pengajuan/program tetap jalan,
        // tanpa menampilkan unit bisnis terpisah di UI publik.
        BusinessUnit::updateOrCreate(
            [
                'department_id' => $department->id,
                'name' => $name,
            ],
            [
                'description' => $description,
                'function' => $function,
                'work_done' => "Kegiatan operasional {$name}.",
                'example_activities' => 'Observasi, penugasan, riset terapan, dan diskusi mentoring 30 menit.',
                'requirements' => 'Kompetensi relevan dan komitmen 8 minggu.',
                'relevant_programs' => $programs,
                'period' => '8 weeks / ±60 days',
                'status' => 'open',
            ],
        );
    }

    private function unit(Department $department, string $name, string $description, string $function, array $programs): void
    {
        BusinessUnit::updateOrCreate(
            ['department_id' => $department->id, 'name' => $name],
            [
                'description' => $description,
                'function' => $function,
                'work_done' => "Kegiatan departemen {$name} pada unit bisnis {$department->name}.",
                'example_activities' => 'Observasi, penugasan, riset terapan, dan diskusi mentoring 30 menit.',
                'requirements' => 'Kompetensi relevan dan komitmen 8 minggu.',
                'relevant_programs' => $programs,
                'period' => '8 weeks / ±60 days',
                'status' => 'open',
            ],
        );
    }
}
