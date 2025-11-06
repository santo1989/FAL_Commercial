<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesContractsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $contracts;

    public function __construct(Collection $contracts)
    {
        $this->contracts = $contracts;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->contracts->values();
    }

    public function headings(): array
    {
        return [
            'Contract No',
            'Buyer',
            'Contract Value',
            'Quantity (PCS)',
            'FOB',
            'Export Value',
            'Realization Value',
            'BTB Value',
            'BTB %',
            'First Shipment',
            'Last Shipment',
            'Expiry Date',
            'Status',
        ];
    }

    /**
     * Map a single row for export
     */
    public function map($contract): array
    {
        // compute the same fields as in the index view
        $baseValue = $contract->sales_contract_value;
        $baseQty = $contract->quantity_pcs;

        $totalRevisedValue = $contract->Revised_value ?? 0;
        $totalRevisedQty = $contract->Revised_qty_pcs ?? 0;

        if (!empty($contract->revised_history)) {
            foreach ($contract->revised_history as $history) {
                $totalRevisedValue += $history['Revised_value'] ?? 0;
                $totalRevisedQty += $history['Revised_qty_pcs'] ?? 0;
            }
        }

        $sales_contract_value = $baseValue + $totalRevisedValue;
        $quantity_pcs = $baseQty + $totalRevisedQty;
        $fob = $quantity_pcs > 0 ? $sales_contract_value / $quantity_pcs : 0;

        $exportValue = $contract->exports->sum('amount_usd');
        $realizationValue = $contract->exports->sum('realized_value');
        $btbValue = ($contract->fabrics_value ?? 0) + ($contract->accessories_value ?? 0) + ($contract->print_emb_value ?? 0);
        $btbPercentage = $exportValue > 0 ? ($btbValue / $exportValue) * 100 : 0;

        // value() returns the raw shipment_date or null; avoid wrapping with optional() which returns an Optional object
        $first_shipment_date = $contract->exports()->orderBy('shipment_date', 'asc')->value('shipment_date');
        $last_shipment_date = $contract->exports()->orderBy('shipment_date', 'desc')->value('shipment_date');

        return [
            $contract->sales_contract_no,
            $contract->buyer_name,
            number_format($sales_contract_value, 2),
            number_format($quantity_pcs),
            number_format($fob, 2),
            number_format($exportValue, 2),
            number_format($realizationValue, 2),
            number_format($btbValue, 2),
            number_format($btbPercentage, 2) . '%',
            $first_shipment_date ? \Carbon\Carbon::parse($first_shipment_date)->toDateString() : null,
            $last_shipment_date ? \Carbon\Carbon::parse($last_shipment_date)->toDateString() : null,
            isset($contract->expiry_date) ? $contract->expiry_date : null,
            $contract->data_4 ?? null,
        ];
    }
}
