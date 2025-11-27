<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportePagosExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    public function __construct(
        protected Carbon $start,
        protected Carbon $end
    ) {}

    public function headings(): array
    {
        return ['Fecha Pago', 'Cliente', 'Barbero', 'Servicio', 'Método', 'Monto'];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_00 // Columna Monto → formato número con 2 decimales
        ];
    }

    public function collection()
    {
        // Usamos SQL como string aquí
        $fechaPagoSql = "COALESCE(p.pagado_at, p.created_at)";

        return DB::table('pagos as p')
            ->join('citas as c', 'c.id', '=', 'p.cita_id')
            ->join('clientes as cl', 'cl.id', '=', 'c.cliente_id')
            ->join('servicios as s', 's.id', '=', 'c.servicio_id')
            ->leftJoin('barberos as b', 'b.id', '=', 'c.barbero_id')
            ->whereRaw('LOWER(TRIM(p.estado)) = ?', ['pagado'])
            ->whereRaw("$fechaPagoSql BETWEEN ? AND ?", [$this->start, $this->end])
            ->orderByRaw("$fechaPagoSql ASC")
            ->selectRaw("
                DATE_FORMAT($fechaPagoSql, '%Y-%m-%d') as fecha_pago,
                cl.nombre as cliente,
                IFNULL(b.nombre,'—') as barbero,
                s.nombre as servicio,
                p.metodo,
                p.monto
            ")
            ->get();
    }
}
