<?php

namespace App\Support;

class StudyPrograms
{
    /**
     * Katalog fakultas dan program studi resmi TSU untuk Imersi.
     *
     * @return array<string, list<string>>
     */
    public static function catalog(): array
    {
        return [
            'Fakultas Teknik' => [
                'Sistem Informasi',
                'Informatika',
                'Rekayasa Komputer',
            ],
            'Fakultas Humaniora' => [
                'Manajemen',
                'Psikologi',
                'PGSD',
            ],
            'Sekolah Vokasi' => [
                'Teknologi Informasi',
                'Sistem Informasi',
                'Desain Komunikasi Visual',
                'Desain Produksi Tekstil',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function faculties(): array
    {
        return array_keys(self::catalog());
    }

    /**
     * @return list<string>
     */
    public static function programsFor(string $faculty): array
    {
        return self::catalog()[$faculty] ?? [];
    }

    /**
     * @return list<string>
     */
    public static function allPrograms(): array
    {
        return collect(self::catalog())->flatten()->unique()->values()->all();
    }

    public static function isValid(string $faculty, string $program): bool
    {
        return in_array($program, self::programsFor($faculty), true);
    }

    /**
     * Target penempatan yang direkomendasikan untuk prodi tertentu.
     *
     * @return list<array{unit: string, departments: list<string>}>
     */
    public static function placementTargets(string $program): array
    {
        return match ($program) {
            'Desain Produksi Tekstil' => [
                ['unit' => 'Desain Seragam', 'departments' => ['K33', 'WJL']],
                ['unit' => 'Souvenir', 'departments' => ['K33', 'WJL']],
            ],
            'PGSD' => [
                ['unit' => 'SD Al-Firdaus', 'departments' => ['SD Al-Firdaus']],
            ],
            'Psikologi' => [
                ['unit' => 'Puspa Holistic', 'departments' => ['Puspa Holistic']],
            ],
            default => [],
        };
    }

    public static function facultyForProgram(string $program): ?string
    {
        foreach (self::catalog() as $faculty => $programs) {
            if (in_array($program, $programs, true)) {
                return $faculty;
            }
        }

        return null;
    }
}
