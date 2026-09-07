<?php

namespace App\Support;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DepartmentFilters
{
    /**
     * @return array<string, string>
     */
    public static function sortOptions(): array
    {
        return [
            'relevan' => 'Paling relevan',
            'nama_az' => 'Nama A–Z',
            'nama_za' => 'Nama Z–A',
            'area' => 'Mitra / area',
            'unit_terbanyak' => 'Unit terbanyak',
        ];
    }

    /**
     * Bidang cepat untuk filter checkbox (IT, Finance, dll).
     *
     * @return array<string, list<string>>
     */
    public static function fields(): array
    {
        return [
            'IT' => ['IT', 'Information technology', 'Digital', 'Informatika', 'Sistem Informasi', 'Teknologi Informasi', 'E-Publishing'],
            'Finance' => ['Finance', 'Accounting', 'Keuangan', 'TAX', 'Akuntansi'],
            'HR & GA' => ['HR', 'Human Resource', 'People', 'GA', 'General Affair', 'Industrial Relation'],
            'Marketing' => ['Marketing', 'Buyer', 'Trading', 'Sales', 'Brand', 'Pemasaran'],
            'Operasi' => ['Operation', 'Store Operation', 'Production', 'SCM', 'Procurement', 'Operasi'],
            'Desain' => ['Desain', 'Souvenir', 'Seragam', 'DKV', 'Komunikasi Visual', 'Tekstil'],
            'Pendidikan' => ['Al-Firdaus', 'PGSD', 'Excellence', 'Learning', 'Publishing', 'Perpus', 'Pendidikan'],
            'Psikologi' => ['Puspa', 'Psikologi', 'Holistic'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function placementTypes(): array
    {
        return [
            'units' => 'Punya unit bisnis',
            'direct' => 'Penempatan langsung',
        ];
    }

    public static function availableAreas(): Collection
    {
        return Department::query()
            ->where('status', 'active')
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');
    }

    /**
     * @param  array{q?: string|null, sort?: string|null, area?: list<string>|string|null, field?: list<string>|string|null, prodi?: list<string>|string|null}  $filters
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $areas = self::normalizeList($filters['area'] ?? []);
        $fields = self::normalizeList($filters['field'] ?? []);
        $prodis = self::normalizeList($filters['prodi'] ?? []);
        $sort = (string) ($filters['sort'] ?? 'relevan');

        $query->when($q !== '', function (Builder $builder) use ($q) {
            $builder->where(function (Builder $inner) use ($q) {
                $inner->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('function', 'like', "%{$q}%")
                    ->orWhere('area', 'like', "%{$q}%")
                    ->orWhereHas('businessUnits', fn (Builder $units) => $units->where('name', 'like', "%{$q}%"));
            });
        });

        $query->when($areas !== [], fn (Builder $builder) => $builder->whereIn('area', $areas));

        $query->when($fields !== [], function (Builder $builder) use ($fields) {
            $builder->where(function (Builder $outer) use ($fields) {
                foreach ($fields as $field) {
                    $keywords = self::fields()[$field] ?? [$field];
                    $outer->orWhere(function (Builder $match) use ($keywords) {
                        foreach ($keywords as $keyword) {
                            $match->orWhere('name', 'like', "%{$keyword}%")
                                ->orWhere('function', 'like', "%{$keyword}%")
                                ->orWhere('description', 'like', "%{$keyword}%")
                                ->orWhereHas('businessUnits', function (Builder $units) use ($keyword) {
                                    $units->where('name', 'like', "%{$keyword}%")
                                        ->orWhere('function', 'like', "%{$keyword}%");
                                });
                        }
                    });
                }
            });
        });

        $query->when($prodis !== [], function (Builder $builder) use ($prodis) {
            $builder->whereHas('businessUnits', function (Builder $units) use ($prodis) {
                $units->where(function (Builder $match) use ($prodis) {
                    foreach ($prodis as $prodi) {
                        $match->orWhereJsonContains('relevant_programs', $prodi);
                    }
                });
            });
        });

        return match ($sort) {
            'nama_az' => $query->orderBy('name'),
            'nama_za' => $query->orderByDesc('name'),
            'area' => $query->orderBy('area')->orderBy('name'),
            'unit_terbanyak' => $query->orderByDesc('business_units_count')->orderBy('name'),
            default => $query->orderBy('area')->orderBy('name'),
        };
    }

    /**
     * @param  Collection<int, Department>  $departments
     * @param  list<string>  $placements
     * @return Collection<int, Department>
     */
    public static function filterPlacement(Collection $departments, array $placements): Collection
    {
        if ($placements === []) {
            return $departments;
        }

        return $departments->filter(function (Department $department) use ($placements) {
            $direct = $department->isDirectPlacement();

            return (in_array('direct', $placements, true) && $direct)
                || (in_array('units', $placements, true) && ! $direct);
        })->values();
    }

    /**
     * @param  list<string>|string|null  $value
     * @return list<string>
     */
    public static function normalizeList(array|string|null $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $items = is_array($value) ? $value : [$value];

        return collect($items)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
