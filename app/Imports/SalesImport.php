<?php

namespace App\Imports;
use App\Models\SalesImport as SalesImportModel;
use App\Models\SalesExport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SalesImportModel([
            'contract_id'        => $row['contract_id'],
            'btb_lc_no'          => $row['btb_lc_no'],
            'date'               => $row['date'],
            'description'        => $row['description'],
            'fabric_value'       => $row['fabric_value'],
            'accessories_value'  => $row['accessories_value'],
            'fabric_qty_kg'      => $row['fabric_qty_kg'],
            'accessories_qty'    => $row['accessories_qty'],
            'print_emb_qty'      => $row['print_emb_qty'],
            'print_emb_value'    => $row['print_emb_value'],
        ]);
    }
}