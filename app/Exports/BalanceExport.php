<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BalanceExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    private $month;
    private $year;
    private $data;
    private $kpis;

    public function __construct($month, $year, $data, $kpis)
    {
        $this->month = $month;
        $this->year = $year;
        $this->data = $data;
        $this->kpis = $kpis;
    }

    public function collection()
    {
        // We return a custom collection that includes the KPIs at the top
        $rows = [];
        
        // 1. Brand Header
        $rows[] = ['EUNOIA COSMETICS - REPORTE DE BALANCE'];
        $rows[] = ['Periodo: ' . $this->month . ' / ' . $this->year];
        $rows[] = ['']; // Spacer

        // 2. KPI Summary
        $rows[] = ['RESUMEN EJECUTIVO'];
        $rows[] = ['Inversión Total', 'Ventas Totales', 'Ganancia Neta', 'ROI %'];
        $rows[] = [
            '$' . number_format($this->kpis['gastoMensual'], 2),
            '$' . number_format($this->kpis['ventasMensuales'], 2),
            '+$' . number_format($this->kpis['gananciaMensual'], 2),
            $this->kpis['roi'] . '%'
        ];
        $rows[] = ['']; // Spacer
        $rows[] = ['']; // Spacer

        // 3. Detailed Table Header
        $rows[] = ['DETALLE DE LOTES'];
        $rows[] = ['Producto', 'Categoría', 'Lote ID', 'Cant. Comprada', 'Costo USD', 'Costo Unit.', 'Unid. Vendidas', 'Recaudado USD', 'Ganancia USD', 'ROI %'];

        // 4. Data
        foreach ($this->data as $lote) {
            $ganancia = $lote->total_recaudado - $lote->cost_usd;
            $roi = $lote->cost_usd > 0 ? round(($ganancia / $lote->cost_usd) * 100, 1) : 0;

            $rows[] = [
                $lote->product->name ?? '',
                $lote->product->category ?? '',
                $lote->id,
                $lote->quantity,
                $lote->cost_usd,
                round($lote->cost_usd / max($lote->quantity, 1), 2),
                $lote->unidades_vendidas,
                $lote->total_recaudado,
                round($ganancia, 2),
                $roi . '%',
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return []; // We handle headings inside the collection for better design
    }

    public function styles(Worksheet $sheet)
    {
        // Main Title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFE88C8C'));
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Period
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF6B7280'));
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // KPI Section Title
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A4:J4');

        // KPI Headers
        $kpiHeaders = 'A5:D5';
        $sheet->getStyle($kpiHeaders)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle($kpiHeaders)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE88C8C');
        $sheet->getStyle($kpiHeaders)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // KPI Values
        $kpiValues = 'A6:D6';
        $sheet->getStyle($kpiValues)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle($kpiValues)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($kpiValues)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Detail Section Title
        $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A8:J8');

        // Table Header
        $tableHeader = 'A9:J9';
        $sheet->getStyle($tableHeader)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle($tableHeader)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE88C8C');
        $sheet->getStyle($tableHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Body - Zebra Stripes
        $rowCount = count($this->data) + 9;
        for ($i = 10; $i <= $rowCount; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:J{$i}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9FAFB');
            }
            $sheet->getStyle("A{$i}:J{$i}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE5E7EB');
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Format currency columns
                $currencyColumns = ['E', 'F', 'H', 'I'];
                foreach ($currencyColumns as $col) {
                    $sheet->getStyle("{$col}6:{$col}" . (count($this->data) + 9))
                          ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                }
            },
        ];
    }
}
