<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel del historial de mantenimientos (RF-31 / RF-33 / RF-34).
 * Recibe los mantenimientos ya filtrados, así coincide con la vista previa y el PDF.
 */
class MantenimientosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $mantenimientos)
    {
    }

    public function collection(): Collection
    {
        return $this->mantenimientos;
    }

    public function title(): string
    {
        return 'Mantenimientos';
    }

    public function headings(): array
    {
        return [
            'Fecha del servicio',
            'Número económico',
            'Placas',
            'Vehículo',
            'Categoría',
            'Descripción',
            'Kilometraje',
            'Monto',
            'Próximo mantenimiento (fecha)',
            'Próximo mantenimiento (km)',
        ];
    }

    public function map($mantenimiento): array
    {
        $vehiculo = $mantenimiento->vehiculo;

        return [
            $mantenimiento->fecha_mantenimiento?->format('d/m/Y'),
            $vehiculo?->numero_economico,
            $vehiculo?->placas,
            trim(($vehiculo?->marca ?? '') . ' ' . ($vehiculo?->modelo ?? '') . ' ' . ($vehiculo?->anio ?? '')),
            $mantenimiento->categoria_mantenimiento,
            $mantenimiento->descripcion_mantenimiento,
            $mantenimiento->kilometraje_mantenimiento,
            (float) $mantenimiento->monto_mantenimiento,
            $mantenimiento->proximo_mantenimiento_fecha?->format('d/m/Y'),
            $mantenimiento->proximo_mantenimiento_km,
        ];
    }

    public function styles(Worksheet $hoja): array
    {
        // Encabezados en negritas
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}