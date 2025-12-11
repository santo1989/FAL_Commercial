<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BtbLcReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $reportData;
    protected $months;

    public function __construct($reportData, $months)
    {
        $this->reportData = $reportData;
        $this->months = $months;
    }

    public function collection()
    {
        $rows = collect();
        $sl = 1;
        $grandTotal = 0;
        $monthlyGrandTotals = array_fill(0, count($this->months), 0);

        foreach ($this->reportData as $bankGroup => $categories) {
            $subTotal = 0;
            $monthlySubTotals = array_fill(0, count($this->months), 0);

            foreach ($categories as $category => $monthlyData) {
                $row = [$sl++, $category];
                $rowTotal = 0;

                foreach ($this->months as $idx => $month) {
                    $value = $monthlyData[$month] ?? 0;
                    $row[] = $value > 0 ? $value : '';
                    $monthlySubTotals[$idx] += $value;
                    $monthlyGrandTotals[$idx] += $value;
                    $rowTotal += $value;
                }

                $row[] = $rowTotal > 0 ? $rowTotal : '';
                $subTotal += $rowTotal;
                $rows->push($row);
            }

            // Add subtotal row
            $subTotalRow = [$sl++, "Sub Total - {$bankGroup}"];
            foreach ($monthlySubTotals as $value) {
                $subTotalRow[] = $value > 0 ? $value : '';
            }
            $subTotalRow[] = $subTotal > 0 ? $subTotal : '';
            $rows->push($subTotalRow);

            $grandTotal += $subTotal;
        }

        // Add grand total row
        $grandTotalRow = [$sl, 'Grand Total'];
        foreach ($monthlyGrandTotals as $value) {
            $grandTotalRow[] = $value > 0 ? $value : '';
        }
        $grandTotalRow[] = $grandTotal > 0 ? $grandTotal : '';
        $rows->push($grandTotalRow);

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['Sl', 'Bank Name'];
        foreach ($this->months as $month) {
            $headings[] = $month;
        }
        $headings[] = 'Total';

        return [
            ['Amounts in US$'],
            $headings
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge first row for title
        $lastColumn = chr(66 + count($this->months)); // B + number of months
        $sheet->mergeCells("A1:{$lastColumn}1");

        // Style title row
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Style header row
        $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        // Right-align numeric columns (months and total)
        $numericRange = 'C2:' . $lastColumn . ($sheet->getHighestRow());
        $sheet->getStyle($numericRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Bold subtotal and grand total rows
        $rowCount = $sheet->getHighestRow();
        for ($row = 3; $row <= $rowCount; $row++) {
            $cellValue = $sheet->getCell("B{$row}")->getValue();
            if (str_contains($cellValue, 'Sub Total') || str_contains($cellValue, 'Grand Total')) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => str_contains($cellValue, 'Grand Total') ? 'D3D3D3' : 'F0F0F0']
                    ]
                ]);
            }
        }

        // Auto-size columns
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function title(): string
    {
        return 'BTB LC Value Report';
    }
}
