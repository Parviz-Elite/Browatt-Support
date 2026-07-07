<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomersExport implements FromQuery, ShouldAutoSize, WithChunkReading, WithColumnFormatting, WithHeadings, WithMapping, WithStrictNullComparison
{
    use Exportable;

    /**
     * @param array{search?: string|null, sort?: string|null, direction?: string|null} $filters
     */
    public function __construct(
        private readonly array $filters = [],
        private readonly bool $applyFilters = true,
    ) {}

    public function query(): Builder
    {
        $query = User::query()->role('customer');

        if ($this->applyFilters) {
            $search = trim((string) ($this->filters['search'] ?? ''));

            if ($search !== '') {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('mobile', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }
        }

        $sort = $this->applyFilters ? ($this->filters['sort'] ?? 'registered_at') : 'registered_at';
        $direction = $this->applyFilters ? ($this->filters['direction'] ?? 'desc') : 'desc';
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';

        match ($sort) {
            'id' => $query->orderBy('id', $direction),
            'mobile' => $query->orderBy('mobile', $direction)->orderBy('id', 'desc'),
            'name' => $query->orderBy('last_name', $direction)->orderBy('first_name', $direction)->orderBy('id', 'desc'),
            default => $query->orderBy('registered_at', $direction)->orderBy('id', 'desc'),
        };

        return $query;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'شناسه',
            'نام',
            'نام خانوادگی',
            'موبایل',
            'تاریخ ثبت نام',
            'تاریخ ایجاد',
        ];
    }

    /**
     * @param User $user
     * @return array<int, mixed>
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->first_name,
            $user->last_name,
            $user->mobile,
            $user->registered_at ? Date::dateTimeToExcel($user->registered_at) : null,
            $user->created_at ? Date::dateTimeToExcel($user->created_at) : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'F' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
