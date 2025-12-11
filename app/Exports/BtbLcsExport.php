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
            'Sl',
            'Export Contract No',
            'BTBLC No',
            'BTBLC Date',
            'Import Invoice ID',
            'Product Type',
            'Name of BTBLC Beneficiery',
            'Due dt of Beneficiery',
            'Paid dt to Beneficiery',
            'Bank',
            'BTBLC Aceptence Date',
            'BTBLC Aceptence Value',
            'Aceptence Type',
            'Tenor Days',
            'BTBLC Maturity Date',
            'Repayment Date',
            'Repaid Amount',
            'BTB Closing Payable Amount',
            'Procurement Type'
        ];
    }

    public function map($row): array
    {
        static $counter = 0;
        $counter++;
        
        return [
            $counter, // Sl - Auto incremental
            optional($row->contract)->sales_contract_no, // Export Contract No
            $row->btb_lc_no, // BTBLC No
            (is_object($row->date) && method_exists($row->date, 'format')) ? $row->date->format('Y-m-d') : ($row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : null), // BTBLC Date
            $row->import_id, // Import Invoice ID
            $row->import_type, // Product Type
            optional($row->import)->description, // Name of BTBLC Beneficiery (from sales_imports.description)
            (is_object($row->mature_date) && method_exists($row->mature_date, 'format')) ? $row->mature_date->format('Y-m-d') : ($row->mature_date ? \Carbon\Carbon::parse($row->mature_date)->format('Y-m-d') : null), // Due dt of Beneficiery
            '', // Paid dt to Beneficiery (empty/not mapped)
            $row->bank_name, // Bank
            (is_object($row->aceptence_date) && method_exists($row->aceptence_date, 'format')) ? $row->aceptence_date->format('Y-m-d') : ($row->aceptence_date ? \Carbon\Carbon::parse($row->aceptence_date)->format('Y-m-d') : null), // BTBLC Aceptence Date
            $row->aceptence_value, // BTBLC Aceptence Value
            $row->aceptence_type, // Aceptence Type
            $row->tenor_days, // Tenor Days
            (is_object($row->mature_date) && method_exists($row->mature_date, 'format')) ? $row->mature_date->format('Y-m-d') : ($row->mature_date ? \Carbon\Carbon::parse($row->mature_date)->format('Y-m-d') : null), // BTBLC Maturity Date
            (is_object($row->repayment_date) && method_exists($row->repayment_date, 'format')) ? $row->repayment_date->format('Y-m-d') : ($row->repayment_date ? \Carbon\Carbon::parse($row->repayment_date)->format('Y-m-d') : null), // Repayment Date
            $row->repayment_value, // Repaid Amount
            $row->closing_balance, // BTB Closing Payable Amount
            $row->proclument_type, // Procurement Type
        ];
    }
}
