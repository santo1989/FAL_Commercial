<x-backend.layouts.master>

    <x-slot name="pageTitle">
      Sales Export Records
    </x-slot>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="text-center mb-0">
                <i class="fas fa-file-import"></i>  Export Records
            </h3>
            <div class="float-left">
                <!--back button-->
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back </a>
            </div>
            @can('Export-CURD')
            <div class="float-right">
                
                <a href="{{ route('sales-exports.create') }}" class="btn btn-success btn-md rounded-pill mb-2">
                    <i class="fas fa-plus"></i> New Export
                </a>
                
                 <!-- Export template file download -->
             <a href="{{ route('excel.export-template') }}"
             class="btn btn-primary btn-md rounded-pill mb-2">
             <i class="fas fa-download me-2"></i> Download Export Template </a>
            
            </div>
             @endcan
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
        <!--- Include any flash messages or notifications or errors here -->
            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
    @include('partials.search-form')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-success text-white">
                        <tr>
                            <th>Sales Contract No.</th>
                            <th>Shipment Date</th>
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
                            <td>{{ $export->salesContract->sales_contract_no }}</td>
                            <td>
                                @if ($export->shipment_date)
                                {{ \Carbon\Carbon::parse($export->shipment_date)->format('d-M-Y') }}
                                @else
                                N/A
                                @endif
                            </td>
                            <td>{{ $export->invoice_no }}</td>
                            <td>{{ $export->export_bill_no }}</td>
                            <td>${{ number_format($export->amount_usd, 2) }}</td>
                            <td>${{ number_format($export->realized_value, 2) }}</td>
                            <td>{{ number_format($export->g_qty_pcs) }}</td>
                            <td>{{ $export->date_of_realized }}</td>
                            <td>
                                @can('Export-CURD')
                                @include('partials.actions', [
                                    'editRoute' => route('sales-exports.edit', $export->id),
                                    'deleteRoute' => route('sales-exports.destroy', $export->id)
                                ])
                                @endcan
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
 