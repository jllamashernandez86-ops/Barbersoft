<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteResumenExport implements FromArray, WithStyles
{
    public function __construct(private array $summary) {}

    public function array(): array
    {
        $s = $this->summary;

        $rows = [];
        $rows[] = ["Reporte: {$s['rango']['inicio']} al {$s['rango']['fin']}"];
        $rows[] = [];

        // Ingresos
        $rows[] = ['Ingresos del período'];
        $rows[] = ['Total', 'Transacciones', 'Promedio'];
        $rows[] = [
            (float) $s['ingresos']['total'],
            (int)   $s['ingresos']['transacciones'],
            (float) $s['ingresos']['promedio'],
        ];
        $rows[] = [];

        // Clientes
        $rows[] = ['Estadísticas de clientes'];
        $rows[] = ['Activos', 'Citas del período', 'Retención (%)'];
        $rows[] = [
            (int) $s['clientes']['activos'],
            (int) $s['clientes']['citas'],
            (int) $s['clientes']['retencion'],
        ];
        $rows[] = [];

        // Métodos de pago
        $rows[] = ['Métodos de pago'];
        $rows[] = ['Método', 'Pagos', 'Total'];
        foreach ($s['metodos'] as $m) {
            $rows[] = [$m['metodo'], (int)$m['pagos'], (float)$m['total']];
        }
        $rows[] = [];

        // Rendimiento por barbero
        $rows[] = ['Rendimiento por barbero'];
        $rows[] = ['Barbero', 'Citas'];
        foreach ($s['barberos'] as $b) {
            $rows[] = [$b['barbero'], (int)$b['citas']];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Negritas para títulos/secciones
        foreach ([1,3,6,9,12, (12 + count($this->summary['metodos']) + 1), (12 + count($this->summary['metodos']) + 3)] as $row) {
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        }
        // Encabezados de tablas en negrita
        foreach ([4,7,13, (12 + count($this->summary['metodos']) + 2)] as $row) {
            $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        }
        // Anchos básicos
        foreach (range('A','C') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        return [];
    }
}
