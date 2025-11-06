<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Sales Contracts</title>
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
    <h2>Sales Contracts</h2>
    <table>
        <thead>
            <tr>
                <th>Contract No</th>
                <th>Buyer</th>
                <th>Contract Value</th>
                <th>Qty (PCS)</th>
                <th>FOB</th>
                <th>Export Value</th>
                <th>Realization Value</th>
                <th>BTB Value</th>
                <th>BTB %</th>
                <th>First Shipment</th>
                <th>Last Shipment</th>
                <th>Expiry</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contracts as $contract)
                @php
                    $baseValue = $contract->sales_contract_value;
                    $baseQty = $contract->quantity_pcs;
                    $totalRevisedValue = $contract->Revised_value ?? 0;
                    $totalRevisedQty = $contract->Revised_qty_pcs ?? 0;
                    if (!empty($contract->revised_history)) {
                        foreach ($contract->revised_history as $history) {
                            $totalRevisedValue += $history['Revised_value'] ?? 0;
                            $totalRevisedQty += $history['Revised_qty_pcs'] ?? 0;
                        }
                    }
                    $sales_contract_value = $baseValue + $totalRevisedValue;
                    $quantity_pcs = $baseQty + $totalRevisedQty;
                    $fob = $quantity_pcs > 0 ? $sales_contract_value / $quantity_pcs : 0;
                    $exportValue = $contract->exports->sum('amount_usd');
                    $realizationValue = $contract->exports->sum('realized_value');
                    $btbValue =
                        ($contract->fabrics_value ?? 0) +
                        ($contract->accessories_value ?? 0) +
                        ($contract->print_emb_value ?? 0);
                    $btbPercentage = $exportValue > 0 ? ($btbValue / $exportValue) * 100 : 0;
                    $first_shipment = $contract->exports()->orderBy('shipment_date', 'asc')->value('shipment_date');
                    $last_shipment = $contract->exports()->orderBy('shipment_date', 'desc')->value('shipment_date');
                @endphp
                <tr>
                    <td>{{ $contract->sales_contract_no }}</td>
                    <td>{{ $contract->buyer_name }}</td>
                    <td>{{ number_format($sales_contract_value, 2) }}</td>
                    <td>{{ number_format($quantity_pcs) }}</td>
                    <td>{{ number_format($fob, 2) }}</td>
                    <td>{{ number_format($exportValue, 2) }}</td>
                    <td>{{ number_format($realizationValue, 2) }}</td>
                    <td>{{ number_format($btbValue, 2) }}</td>
                    <td>{{ number_format($btbPercentage, 2) }}%</td>
                    <td>{{ $first_shipment ? \Carbon\Carbon::parse($first_shipment)->format('d-M-Y') : '' }}</td>
                    <td>{{ $last_shipment ? \Carbon\Carbon::parse($last_shipment)->format('d-M-Y') : '' }}</td>
                    <td>{{ $contract->expiry_date ?? '' }}</td>
                    <td>{{ $contract->data_4 ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
