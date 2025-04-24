<?php

namespace App\Imports;

use App\Models\SalesExport as SalesExportModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesExport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SalesExportModel([
            'contract_id'        => $row['contract_id'],
            'invoice_no'         => $row['invoice_no'],
            'export_bill_no'    => $row['export_bill_no'],
            'amount_usd'         => $row['amount_usd'],
            'realized_value'    => $row['realized_value'],
            'g_qty_pcs'          => $row['g_qty_pcs'],
            'date_of_realized'   => $row['date_of_realized'],
            'due_amount_usd'    => $row['due_amount_usd'],
        ]);
    }
}