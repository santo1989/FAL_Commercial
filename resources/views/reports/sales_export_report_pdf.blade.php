<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Export Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h3>Sales Export Report</h3>
<table>
    <thead>
    <tr>
        <th>Sl</th>
        <th>Company</th>
        <th>Export Invoice No</th>
        <th>Invoice Date</th>
        <th>Buyer</th>
        <th>Invoice Value</th>
        <th>LC No</th>
        <th>LC Date</th>
        <th>Shipment Date</th>
        <th>Container No</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ optional($row->salesContract)->company_name ?? '' }}</td>
            <td>{{ $row->invoice_no ?? '' }}</td>
            <td>{{ optional($row->invoice_date)->format('Y-m-d') ?? '' }}</td>
            <td>{{ optional($row->salesContract)->buyer_name ?? '' }}</td>
            <td>{{ number_format($row->invoice_value ?? $row->amount_usd ?? 0, 2) }}</td>
            <td>{{ optional($row->btbLc)->btb_lc_no ?? '' }}</td>
            <td>{{ optional(optional($row->btbLc)->date)->format('Y-m-d') ?? '' }}</td>
            <td>{{ optional($row->shipment_date)->format('Y-m-d') ?? '' }}</td>
            <td>{{ $row->container_no ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
