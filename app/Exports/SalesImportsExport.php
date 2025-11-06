<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesImportsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $imports;

    public function __construct(Collection $imports)
    {
        $this->imports = $imports;
    }

    public function collection()
    {
        return $this->imports->values();
    }

    public function headings(): array
    {
        return [
            'Contract No',
            'Buyer',
            'BTB LC No',
            'Date',
            'Description',
            'Fabric Value',
            'Accessories Value',
            'Fabric Qty (KG)',
            'Accessories Qty',
            'Print/Emb Value'
        ];
    }

    public function map($import): array
    {
        return [
            optional($import->salesContract)->sales_contract_no,
            optional($import->salesContract)->buyer_name,
            $import->btb_lc_no,
            $import->date ? \Carbon\Carbon::parse($import->date)->toDateString() : null,
            $import->description,
            $import->fabric_value !== null ? (float) $import->fabric_value : 0,
            $import->accessories_value !== null ? (float) $import->accessories_value : 0,
            $import->fabric_qty_kg !== null ? (float) $import->fabric_qty_kg : 0,
            $import->accessories_qty !== null ? (float) $import->accessories_qty : 0,
            $import->print_emb_value !== null ? (float) $import->print_emb_value : 0,
        ];
    }
}
