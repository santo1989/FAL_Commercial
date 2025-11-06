<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesExportsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $exports;

    public function __construct(Collection $exports)
    {
        $this->exports = $exports;
    }

    public function collection()
    {
        return $this->exports->values();
    }

    public function headings(): array
    {
        return [
            'Contract No',
            'Buyer',
            'Shipment Date',
            'Invoice No',
            'Export Bill No',
            'Amount USD',
            'Realized Value',
            'Quantity (PCS)',
            'Date of Realized',
            'Due Amount USD'
        ];
    }

    public function map($export): array
    {
        return [
            optional($export->salesContract)->sales_contract_no,
            optional($export->salesContract)->buyer_name,
            $export->shipment_date ? \Carbon\Carbon::parse($export->shipment_date)->toDateString() : null,
            $export->invoice_no,
            $export->export_bill_no,
            $export->amount_usd !== null ? (float) $export->amount_usd : 0,
            $export->realized_value !== null ? (float) $export->realized_value : 0,
            $export->g_qty_pcs !== null ? (int) $export->g_qty_pcs : 0,
            $export->date_of_realized ? \Carbon\Carbon::parse($export->date_of_realized)->toDateString() : null,
            $export->due_amount_usd !== null ? (float) $export->due_amount_usd : 0,
        ];
    }
}
