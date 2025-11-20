<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Import Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
<h3>Sales Import Report</h3>
<table>
    <thead>
    <tr>
        <th>Sl</th>
        <th>Bank Name</th>
        <th>BTB LC No</th>
        <th>LC Date</th>
        <th>LC Value</th>
        <th>Supplier Name</th>
        <th>Commercial Invoice No</th>
        <th>Invoice Value</th>
        <th>Acceptance Date</th>
        <th>Tenor (days)</th>
        <th>Maturity Date</th>
        <th>Extension New Maturity</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $i => $row)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row->bank_name ?? optional($row->import)->bank_name ?? optional($row->contract)->bank_name ?? '' }}</td>
            <td>{{ $row->btb_lc_no ?? optional($row->import)->btb_lc_no ?? '' }}</td>
            <td>{{ optional($row->date ?? optional($row->import)->date)->format('Y-m-d') ?? '' }}</td>
            <td>{{ number_format($row->aceptence_value ?? optional($row->import)->aceptence_value ?? 0, 2) }}</td>
            <td>{{ optional($row->import)->description ?? optional($row->contract)->buyer_name ?? '' }}</td>
            <td>{{ optional($row->import)->data_1 ?? '' }}</td>
            <td>{{ number_format((optional($row->import)->fabric_value ?? 0) + (optional($row->import)->accessories_value ?? 0) + (optional($row->import)->print_emb_value ?? 0), 2) }}</td>
            <td>{{ optional($row->aceptence_date)->format('Y-m-d') ?? '' }}</td>
            <td>{{ $row->tenor_days ?? '' }}</td>
            <td>{{ optional($row->mature_date)->format('Y-m-d') ?? '' }}</td>
            <td></td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
