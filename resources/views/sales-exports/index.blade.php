<x-backend.layouts.master>

    <x-slot name="pageTitle">
        Sales Export Records
    </x-slot>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h3 class="text-center mb-0">
                    <i class="fas fa-file-import"></i> Export Records
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
                        <a href="{{ route('excel.export-template') }}" class="btn btn-primary btn-md rounded-pill mb-2">
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
        <!-- responsive search/filter header (same fields as sales-contracts) -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <div class="d-flex flex-row flex-wrap align-items-center justify-content-between gap-2">
                    <form action="{{ route('sales-exports.index') }}" method="GET" class="d-flex flex-row flex-wrap align-items-center flex-grow-1 gap-2">
                        <div>
                            @php
                                $buyers = \App\Models\SalesContract::select('buyer_id')->distinct()->get();
                                $buyers = \App\Models\Buyer::whereIn('id', $buyers)->get();
                            @endphp
                            <select name="buyer_id" class="form-control form-control-sm select2" aria-label="Filter by buyer">
                                <option value="">Buyer</option>
                                @foreach ($buyers as $buyer)
                                    <option value="{{ $buyer->id }}" {{ request('buyer_id') == $buyer->id ? 'selected' : '' }}>{{ $buyer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            @php
                                $Sales_contracts = \App\Models\SalesContract::select('sales_contract_no')->distinct()->get();
                                $Sales_contracts = \App\Models\SalesContract::whereIn('sales_contract_no', $Sales_contracts)->get();
                            @endphp
                            <select name="contract_no" class="form-control form-control-sm select2" aria-label="Filter by contract">
                                <option value="">Contract No.</option>
                                @foreach ($Sales_contracts as $contract)
                                    <option value="{{ $contract->sales_contract_no }}" {{ request('contract_no') == $contract->sales_contract_no ? 'selected' : '' }}>{{ $contract->sales_contract_no }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <input type="date" name="contract_date_from" class="form-control form-control-sm" value="{{ request('contract_date_from') }}" title="From">
                            <input type="date" name="contract_date_to" class="form-control form-control-sm" value="{{ request('contract_date_to') }}" title="To">
                        </div>

                        <div class="flex-fill">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search buyer or contract no" value="{{ request('search') }}" aria-label="Search">
                        </div>

                        <div>
                            <button type="submit" class="btn btn-light">Search</button>
                        </div>
                    </form>

                    <div class="d-flex align-items-center">
                        <form action="{{ route('sales-exports.index') }}" method="GET" class="me-2">
                            <button type="submit" class="btn btn-sm btn-light">Reset</button>
                        </form>

                        @can('Export-CURD')
                            <a href="{{ route('sales-exports.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-sm btn-success me-2" title="Export filtered exports to Excel" aria-label="Export to Excel">
                                <i class="fas fa-file-excel"></i> <span class="d-none d-md-inline">Export</span>
                            </a>
                            <a href="{{ route('sales-exports.pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-sm btn-danger me-2" title="Download PDF of filtered exports" aria-label="Download PDF">
                                <i class="fas fa-file-pdf"></i> <span class="d-none d-md-inline">PDF</span>
                            </a>
                            <a href="{{ route('sales-exports.report') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-sm btn-primary me-2" title="Download Export Report (Excel)">
                                <i class="fas fa-file-lines"></i> <span class="d-none d-md-inline">Report</span>
                            </a>
                            <a href="{{ route('sales-exports.report.pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-sm btn-secondary" title="Download Export Report (PDF)">
                                <i class="fas fa-file-pdf"></i> <span class="d-none d-md-inline">Report PDF</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

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
                                @foreach ($exports as $export)
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
                                                    'deleteRoute' => route('sales-exports.destroy', $export->id),
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
