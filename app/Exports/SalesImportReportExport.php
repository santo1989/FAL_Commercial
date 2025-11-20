<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesImportReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Bank Name',
            'BTB LC No',
            'LC Date',
            'LC Value',
            'Supplier Name',
            'Commercial Invoice No',
            'Invoice Value',
            'Acceptance Date',
            'Tenor (days)',
            'Maturity Date',
            'Extension New Maturity'
        ];
    }

    public function map($row): array
    {
        // $row expected to be a model (BtbLc) with relations 'contract' and 'import'
        $import = $row->import ?? null;
        $contract = $row->contract ?? null;

        return [
            null, // Sl handled by consumer (we'll leave empty)
            $row->bank_name ?? optional($import)->bank_name ?? optional($contract)->bank_name ?? null,
            $row->btb_lc_no ?? optional($import)->btb_lc_no ?? null,
            $row->date ? \Carbon\Carbon::parse($row->date)->toDateString() : (optional($import)->date ? \Carbon\Carbon::parse($import->date)->toDateString() : null),
            $row->aceptence_value !== null ? (float) $row->aceptence_value : null,
            // Supplier name: try import->description then contract->buyer_name
            optional($import)->description ?? optional($contract)->buyer_name ?? null,
            // Commercial Invoice No: try import->data_1 or export-related fields (best effort)
            optional($import)->data_1 ?? null,
            // Invoice Value: try sum of fabric+accessories+print_emb
            (optional($import)->fabric_value ?? 0) + (optional($import)->accessories_value ?? 0) + (optional($import)->print_emb_value ?? 0),
            $row->aceptence_date ? \Carbon\Carbon::parse($row->aceptence_date)->toDateString() : null,
            $row->tenor_days ?? null,
            $row->mature_date ? \Carbon\Carbon::parse($row->mature_date)->toDateString() : null,
            // Extension New Maturity: leave blank (no clear source) — keep null so user can fill
            null,
        ];
    }
}
