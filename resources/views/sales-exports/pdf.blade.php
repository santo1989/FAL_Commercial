<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Exports</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #f4f4f4;
        }
    </style>
</head>

<body>
    <h2>Sales Exports</h2>
    <table>
        <thead>
            <tr>
                <th>Contract No</th>
                <th>Buyer</th>
                <th>Shipment Date</th>
                <th>Invoice No</th>
                <th>Export Bill No</th>
                <th>Amount (USD)</th>
                <th>Realized Value</th>
                <th>Qty (PCS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($exports as $export)
                <tr>
                    <td>{{ optional($export->salesContract)->sales_contract_no }}</td>
                    <td>{{ optional($export->salesContract)->buyer_name }}</td>
                    <td>{{ $export->shipment_date ? \Carbon\Carbon::parse($export->shipment_date)->format('d-M-Y') : '' }}
                    </td>
                    <td>{{ $export->invoice_no }}</td>
                    <td>{{ $export->export_bill_no }}</td>
                    <td>{{ number_format($export->amount_usd, 2) }}</td>
                    <td>{{ number_format($export->realized_value, 2) }}</td>
                    <td>{{ number_format($export->g_qty_pcs) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
