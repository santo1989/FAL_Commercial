<x-backend.layouts.master>
    <x-slot name="pageTitle">BTB LCs PDF</x-slot>

    <div class="container-fluid p-4">
        <h3>BTB / LC Records</h3>
        <table class="table table-bordered table-sm" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Contract No</th>
                    <th>BTB/LC No</th>
                    <th>Import ID</th>
                    <th>Date</th>
                    <th>Bank</th>
                    <th>Aceptence Date</th>
                    <th>Aceptence Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ optional($r->contract)->sales_contract_no }}</td>
                        <td>{{ $r->btb_lc_no }}</td>
                        <td>{{ $r->import_id }}</td>
                        <td>{{ $r->date ? $r->date->format('Y-m-d') : '' }}</td>
                        <td>{{ $r->bank_name }}</td>
                        <td>{{ $r->aceptence_date ? $r->aceptence_date->format('Y-m-d') : '' }}</td>
                        <td style="text-align:right;">{{ number_format($r->aceptence_value ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-backend.layouts.master>
