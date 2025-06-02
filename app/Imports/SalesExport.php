<?php

namespace App\Imports;

use App\Models\SalesExport as SalesExportModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class SalesExport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $normalized = $this->normalizeKeys($row);

        return new SalesExportModel([
            'shipment_date' => $this->parseDate($normalized['shipment_date'] ?? null),
            'invoice_no'         => $normalized['invoice_no'] ?? null,
            'export_bill_no'     => $normalized['export_bill_no'] ?? null,
            'amount_usd'         => $this->parseDecimal($normalized['amount'] ?? 0),
            'realized_value'     => $this->parseDecimal($normalized['realized_value'] ?? 0),
            'g_qty_pcs'          => intval($normalized['quantity'] ?? 0),
            'date_of_realized'   => $this->parseDate($normalized['realized_date'] ?? null),
            'due_amount_usd'     => $this->parseDecimal($normalized['due_amount'] ?? 0),
        ]);
    }

    private function normalizeKeys(array $row)
    {
        $keyMap = [
            // Amount
            'amount_usd' => 'amount',
            'amount' => 'amount',
            'export_amount' => 'amount',
            'amount_(usd)' => 'amount',

            // Realized value
            'realized_value' => 'realized_value',
            'realized_amount' => 'realized_value',
            'amount_realized' => 'realized_value',

            // Quantity
            'quantity_pcs' => 'quantity',
            'quantity' => 'quantity',
            'qty_pcs' => 'quantity',
            'qty' => 'quantity',
            'g_qty_pcs' => 'quantity',

            // Date
            // Add these date mappings:
            'realized_date' => 'date_of_realised',
            'date_of_realized' => 'date_of_realised',
            'realization_date' => 'date_of_realised',
            'realized' => 'date_of_realised',

            // Due amount
            'due_amount_usd' => 'due_amount',
            'due_amount' => 'due_amount',
            'balance_due' => 'due_amount',

            // Invoice/Bill
            'invoice_no' => 'invoice_no',
            'invoice' => 'invoice_no',
            'export_bill_no' => 'export_bill_no',
            'bill_no' => 'export_bill_no',

            // shipment_date
            'shipment_date' => 'shipment_date',
            'shipment' => 'shipment_date',
            
        ];

        $normalized = [];
        foreach ($row as $key => $value) {
            $cleanKey = strtolower(preg_replace('/[^a-z0-9]/i', '_', $key));
            $cleanKey = preg_replace('/_+/', '_', $cleanKey);
            $cleanKey = trim($cleanKey, '_');

            if (array_key_exists($cleanKey, $keyMap)) {
                $cleanKey = $keyMap[$cleanKey];
            }

            $normalized[$cleanKey] = $value;
        }
        return $normalized;
    }

    private function parseDecimal($value)
    {
        if (is_numeric($value)) return floatval($value);

        // Handle currency strings
        $cleaned = str_replace(['$', ',', ' '], '', $value);
        return is_numeric($cleaned) ? floatval($cleaned) : 0.0;
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            // Handle Excel timestamp format
            if (is_numeric($value)) {
                return Carbon::createFromTimestamp(($value - 25569) * 86400);
            }
            // Handle string formats
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}