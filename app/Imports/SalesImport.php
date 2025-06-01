<?php

namespace App\Imports;

use App\Models\SalesImport as SalesImportModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $normalized = $this->normalizeKeys($row);

        return new SalesImportModel([
            'btb_lc_no' => $normalized['btb_lc_no'] ?? null,
            'date' => $this->parseDate($normalized['date'] ?? null),
            'description' => $normalized['description'] ?? null,
            'fabric_value' => $this->parseDecimal($normalized['fabric_value'] ?? 0),
            'accessories_value' => $this->parseDecimal($normalized['accessories_value'] ?? 0),
            'fabric_qty_kg' => $this->parseDecimal($normalized['fabric_qty_in_kgs'] ?? 0),
            'accessories_qty' => $this->parseDecimal($normalized['accessories_qty'] ?? 0),
            'print_emb_qty' => $this->parseDecimal($normalized['printing_embroidery_qty'] ?? 0),
            'print_emb_value' => $this->parseDecimal($normalized['printing_embroidery_value'] ?? 0),
        ]);
    }

    private function normalizeKeys(array $row)
    {
        $keyMap = [
            'btb_lc_no' => 'btb_lc_no',
            'btb_lc_no.' => 'btb_lc_no',
            'btb_lc' => 'btb_lc_no',
            'lc_no' => 'btb_lc_no',

            'date' => 'date',
            'import_date' => 'date',
            'transaction_date' => 'date',

            'description' => 'description',
            'desc' => 'description',
            'details' => 'description',

            'fabric_value' => 'fabric_value',
            'fabric_amount' => 'fabric_value',
            'fabric_cost' => 'fabric_value',

            'accessories_value' => 'accessories_value',
            'accessories_amount' => 'accessories_value',
            'acc_value' => 'accessories_value',

            'fabric_qty_in_kgs' => 'fabric_qty_in_kgs',
            'fabric_quantity' => 'fabric_qty_in_kgs',
            'fabric_qty' => 'fabric_qty_in_kgs',
            'fabric_kg' => 'fabric_qty_in_kgs',

            'accessories_qty' => 'accessories_qty',
            'accessories_quantity' => 'accessories_qty',
            'acc_qty' => 'accessories_qty',

            'printing_embroidery_qty' => 'printing_embroidery_qty',
            'print_emb_qty' => 'printing_embroidery_qty',
            'printing_qty' => 'printing_embroidery_qty',

            'printing_embroidery_value' => 'printing_embroidery_value',
            'print_emb_value' => 'printing_embroidery_value',
            'printing_value' => 'printing_embroidery_value',
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

        $cleaned = str_replace(['$', ',', ' '], '', $value);
        return is_numeric($cleaned) ? floatval($cleaned) : 0.0;
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            // Handle Excel numeric dates
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                    ->addDays(intval($value) - 2);
            }
            // Handle timestamp format
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d H:i:s', $value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}