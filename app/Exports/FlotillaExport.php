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
 * Excel del listado de flotilla (RF-30 / RF-34).
 * Recibe los vehículos ya filtrados, así coincide con la vista previa y el PDF.
 */
class FlotillaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $vehiculos)
    {
    }

    public function collection(): Collection
    {
        return $this->vehiculos;
    }

    public function title(): string
    {
        return 'Flotilla';
    }

    public function headings(): array
    {
        return [
            'Número económico',
            'Placas',
            'Marca',
            'Modelo',
            'Año',
            'Tipo de vehículo',
            'Color',
            'Tipo de combustible',
            'Kilometraje',
            'Fecha de adquisición',
            'Estado',
        ];
    }

    public function map($vehiculo): array
    {
        return [
            $vehiculo->numero_economico,
            $vehiculo->placas,
            $vehiculo->marca,
            $vehiculo->modelo,
            $vehiculo->anio,
            $vehiculo->getAttribute('tipo vehiculo'),
            $vehiculo->color,
            $vehiculo->getAttribute('tipo combustible'),
            $vehiculo->kilometraje_vigente,
            $vehiculo->fecha_adquisicion?->format('d/m/Y'),
            $vehiculo->estado,
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