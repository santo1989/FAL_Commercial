<x-backend.layouts.master>

    <x-slot name="pageTitle">
      Sales Export Records
    </x-slot>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="float-left">Export Records</h1>
            <div class="float-right">
                <a href="{{ route('sales-exports.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> New Export
                </a>
                <a href="{{ route('export-template') }}" class="btn btn-primary">
                    <i class="fas fa-file-excel"></i> Download Template
                </a>
            </div>
        </div>
    </div>

    @include('partials.search-form')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-success text-white">
                        <tr>
                            <th>Invoice No.</th>
                            <th>Export Bill No.</th>
                            <th>Amount (USD)</th>
                            <th>Realized Value</th>
                            <th>Quantity (PCS)</th>
                            <th>Realized Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exports as $export)
                        <tr>
                            <td>{{ $export->invoice_no }}</td>
                            <td>{{ $export->export_bill_no }}</td>
                            <td>${{ number_format($export->amount_usd, 2) }}</td>
                            <td>${{ number_format($export->realized_value, 2) }}</td>
                            <td>{{ number_format($export->g_qty_pcs) }}</td>
                            <td>{{ $export->date_of_realized->format('d-M-Y') }}</td>
                            <td>
                                @include('partials.actions', [
                                    'editRoute' => route('sales-exports.edit', $export->id),
                                    'deleteRoute' => route('sales-exports.destroy', $export->id)
                                ])
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $exports->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
</x-backend.layouts.master>
 