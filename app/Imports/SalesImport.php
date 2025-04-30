<?php

namespace App\Imports;
use App\Models\SalesImport as SalesImportModel;
use App\Models\SalesExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToModel, WithHeadingRow
{
    // public function model(array $row)
    // {
    //     return new SalesImportModel([
    //         'contract_id'        => $row['contract_id'],
    //         'btb_lc_no'          => $row['btb_lc_no'],
    //         'date'               => $row['date'],
    //         'description'        => $row['description'],
    //         'fabric_value'       => $row['fabric_value'],
    //         'accessories_value'  => $row['accessories_value'],
    //         'fabric_qty_kg'      => $row['fabric_qty_kg'],
    //         'accessories_qty'    => $row['accessories_qty'],
    //         'print_emb_qty'      => $row['print_emb_qty'],
    //         'print_emb_value'    => $row['print_emb_value'],
    //     ]);
    // }

    public function model(array $row)
    {
        return new SalesImportModel([
            'btb_lc_no' => $row['btb_lc_no'] ?? null,
            'date' => isset($row['date']) ? Carbon::parse($row['date']) : null,
            'description' => $row['description'] ?? null,
            'fabric_value' => $row['fabric_value'] ?? 0,
            'accessories_value' => $row['accessories_value'] ?? 0,
            'fabric_qty_kg' => $row['fabric_qty_in_kgs'] ?? 0,
            'accessories_qty' => $row['accessories_qty'] ?? 0,
            'print_emb_qty' => $row['printing_embroidery_qty'] ?? 0,
            'print_emb_value' => $row['printing_embroidery_value'] ?? 0,
        ]);
    }
}