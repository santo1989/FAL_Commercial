<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Imports</title>
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
    <h2>Sales Imports</h2>
    <table>
        <thead>
            <tr>
                <th>Contract No</th>
                <th>Buyer</th>
                <th>BTB LC No</th>
                <th>Date</th>
                <th>Description</th>
                <th>Fabric Value</th>
                <th>Accessories Value</th>
                <th>Fabric Qty (KG)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($imports as $import)
                <tr>
                    <td>{{ optional($import->salesContract)->sales_contract_no }}</td>
                    <td>{{ optional($import->salesContract)->buyer_name }}</td>
                    <td>{{ $import->btb_lc_no }}</td>
                    <td>{{ $import->date ? \Carbon\Carbon::parse($import->date)->format('d-M-Y') : '' }}</td>
                    <td>{{ $import->description }}</td>
                    <td>{{ number_format($import->fabric_value, 2) }}</td>
                    <td>{{ number_format($import->accessories_value, 2) }}</td>
                    <td>{{ number_format($import->fabric_qty_kg, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
