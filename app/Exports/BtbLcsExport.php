<?php

namespace App\Exports;

use App\Models\BtbLc;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BtbLcsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = BtbLc::with(['contract', 'import']);

        if (!empty($this->filters['contract_id'])) {
            $query->where('contract_id', $this->filters['contract_id']);
        }

        if (!empty($this->filters['btb_lc_no'])) {
            $query->where('btb_lc_no', 'like', "%{$this->filters['btb_lc_no']}%");
        }

        if (!empty($this->filters['bank_name'])) {
            $query->where('bank_name', $this->filters['bank_name']);
        }

        if (!empty($this->filters['import_type'])) {
            $query->where('import_type', $this->filters['import_type']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Contract No', 'BTB/LC No', 'Import ID', 'Date', 'Bank', 'Aceptence Date', 'Aceptence Value', 'Aceptence Type', 'Tenor Days', 'Mature Date', 'Repayment Date', 'Repayment Value', 'Closing Balance', 'Procurement Type', 'Import Type'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            optional($row->contract)->sales_contract_no,
            $row->btb_lc_no,
            $row->import_id,
            // ensure date formatting works even if not Carbon instance
            (is_object($row->date) && method_exists($row->date, 'format')) ? $row->date->format('Y-m-d') : ($row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : null),
            $row->bank_name,
            (is_object($row->aceptence_date) && method_exists($row->aceptence_date, 'format')) ? $row->aceptence_date->format('Y-m-d') : ($row->aceptence_date ? \Carbon\Carbon::parse($row->aceptence_date)->format('Y-m-d') : null),
            $row->aceptence_value,
            $row->aceptence_type,
            $row->tenor_days,
            (is_object($row->mature_date) && method_exists($row->mature_date, 'format')) ? $row->mature_date->format('Y-m-d') : ($row->mature_date ? \Carbon\Carbon::parse($row->mature_date)->format('Y-m-d') : null),
            (is_object($row->repayment_date) && method_exists($row->repayment_date, 'format')) ? $row->repayment_date->format('Y-m-d') : ($row->repayment_date ? \Carbon\Carbon::parse($row->repayment_date)->format('Y-m-d') : null),
            $row->repayment_value,
            $row->closing_balance,
            $row->proclument_type,
            $row->import_type,
        ];
    }
}
