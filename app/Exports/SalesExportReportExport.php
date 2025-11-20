<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesExportReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows->values();
    }

    public function headings(): array
    {
        return [
            'Sl',
            'Company',
            'Export Invoice No',
            'Invoice Date',
            'Buyer',
            'Invoice Value',
            'LC No',
            'LC Date',
            'Shipment Date',
            'Container No',
        ];
    }

    public function map($row): array
    {
        // $row expected to be a SalesExport model with relations salesContract and possibly btbLc
        $contract = $row->salesContract ?? null;
        $btb = $row->btbLc ?? null;

        return [
            null,
            optional($contract)->company_name ?? null,
            $row->invoice_no ?? null,
            $row->invoice_date ? \Carbon\Carbon::parse($row->invoice_date)->toDateString() : null,
            optional($contract)->buyer_name ?? null,
            $row->invoice_value ?? null,
            optional($btb)->btb_lc_no ?? null,
            optional($btb)->date ? \Carbon\Carbon::parse(optional($btb)->date)->toDateString() : null,
            $row->shipment_date ? \Carbon\Carbon::parse($row->shipment_date)->toDateString() : null,
            $row->container_no ?? null,
        ];
    }
}
