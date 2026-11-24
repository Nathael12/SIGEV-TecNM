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
 * Excel del historial de asignaciones (RF-32 / RF-33 / RF-34).
 * Recibe las asignaciones ya filtradas, así coincide con la vista previa y el PDF.
 */
class AsignacionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $asignaciones)
    {
    }

    public function collection(): Collection
    {
        return $this->asignaciones;
    }

    public function title(): string
    {
        return 'Asignaciones';
    }

    public function headings(): array
    {
        return [
            'Número económico',
            'Placas',
            'Solicitante',
            'Fecha de salida',
            'Fecha de regreso',
            'Destino',
            'Motivo de uso',
            'Km inicial',
            'Km final',
            'Km recorridos',
            'Estado',
        ];
    }

    public function map($asignacion): array
    {
        $solicitante = trim(
            ($asignacion->solicitante?->nombre_usuario ?? '') . ' ' .
            ($asignacion->solicitante?->apellido_paterno ?? '') . ' ' .
            ($asignacion->solicitante?->apellido_materno ?? '')
        );

        return [
            $asignacion->vehiculo?->numero_economico,
            $asignacion->vehiculo?->placas,
            $solicitante,
            $asignacion->fecha_salida->format('d/m/Y h:i A'),
            $asignacion->fecha_regreso?->format('d/m/Y h:i A'),
            $asignacion->destino_vehiculo,
            $asignacion->motivo_uso,
            $asignacion->kilometraje_inicial,
            $asignacion->kilometraje_final,
            $asignacion->kilometros_recorridos,
            $asignacion->estado_asignacion,
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