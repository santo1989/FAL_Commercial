<?php

// namespace App\Imports;

// use App\Models\SalesExport as SalesExportModel;
// use Carbon\Carbon;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class SalesExport implements ToModel, WithHeadingRow
// {
//     public function model(array $row)
//     {
//         // return new SalesExportModel([
//         //     'contract_id'        => $row['contract_id'],
//         //     'invoice_no'         => $row['invoice_no'],
//         //     'export_bill_no'    => $row['export_bill_no'],
//         //     'amount_usd'         => $row['amount_usd'],
//         //     'realized_value'    => $row['realized_value'],
//         //     'g_qty_pcs'          => $row['g_qty_pcs'],
//         //     'date_of_realized'   => $row['date_of_realized'],
//         //     'due_amount_usd'    => $row['due_amount_usd'],
//         // ]);

//         return new SalesExportModel([
//             'invoice_no'         => $row['invoice_no'] ?? null,
//             'export_bill_no'     => $row['export_bill_no'] ?? null,
//             'amount_usd'         => $row['amount_usd_of_export_goods'] ?? 0,
//             'realized_value'     => $row['amount_usd_realised'] ?? 0,
//             'g_qty_pcs'          => $row['g_qty_pcs'] ?? 0,
//             'date_of_realized'   => isset($row['date_of_realised']) ? Carbon::parse($row['date_of_realised']) : null,
//             'due_amount_usd'     => $row['due_amount_usd'] ?? 0,
//         ]);
//     }
// }

// app/Imports/SalesExport.php
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
        // Normalize keys to handle different header formats
        $normalizedRow = $this->normalizeKeys($row);

        return new SalesExportModel([
            'invoice_no'         => $normalizedRow['invoice_no'] ?? null,
            'export_bill_no'     => $normalizedRow['export_bill_no'] ?? null,
            'amount_usd'         => $normalizedRow['amount_usd_of_export_goods'] ?? 0,
            'realized_value'     => $normalizedRow['amount_usd_realised'] ?? 0,
            'g_qty_pcs'          => $normalizedRow['g_qty_pcs'] ?? 0,
            'date_of_realized'   => isset($normalizedRow['date_of_realised']) && $normalizedRow['date_of_realised']
                ? Carbon::parse($normalizedRow['date_of_realised'])
                : null,
            'due_amount_usd'     => $normalizedRow['due_amount_usd'] ?? 0,
        ]);
    }

    private function normalizeKeys(array $row)
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = Str::lower(str_replace([' ', '-', '(', ')', '$'], '_', $key));
            $normalizedKey = preg_replace('/_+/', '_', $normalizedKey);
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }
}