<x-backend.layouts.master>
    <x-slot name="pageTitle">
        Sales Contracts
    </x-slot>
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <h1 class="text-center">Sales Contracts</h1>
            <div class="col-md-12">

                <a href="{{ route('home') }}" class="btn btn-outline-secondary float-left mr-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                @can('Sales-CURD')
                    <!-- New Contract button -->
                    <a href="{{ route('sales-contracts.create') }}" class="btn btn-outline-primary float-right mr-2">
                        <i class="fas fa-plus"></i> New Contract
                    </a>
                @endcan
            </div>
        </div>

        <div class="card">
            <!--card heard for searching and filtering-->
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Search and Filter</h5>
                <form action="{{ route('sales-contracts.index') }}" method="GET" class="form-inline">
                    <select name="buyer_id" class="form-control mr-2">
                        @php
                            $buyers = \App\Models\SalesContract::select('buyer_id')->distinct()->get(); // Fetch all distinct buyer IDs from the contracts
                            $buyers = \App\Models\Buyer::whereIn('id', $buyers)->get(); // Fetch all buyers based on the distinct IDs
                            // Fetch all buyers from the database
                            $selectedBuyer = request('buyer_id'); // Get the selected buyer ID from the request
                        @endphp
                        <option value="">Select Buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer->id }}"
                                {{ request('buyer_id') == $buyer->id ? 'selected' : '' }}>
                                {{ $buyer->name }}
                            </option>
                        @endforeach
                    </select>

                    @php
                        $Sales_contracts = \App\Models\SalesContract::select('sales_contract_no')->distinct()->get(); // Fetch all distinct contract numbers from the contracts
                        $Sales_contracts = \App\Models\SalesContract::whereIn(
                            'sales_contract_no',
                            $Sales_contracts,
                        )->get(); // Fetch all contracts based on the distinct numbers
                    @endphp
                    <select name="contract_no" class="form-control mr-2">
                        <option value="">Select Contract No.</option>
                        @foreach ($Sales_contracts as $contract)
                            <option value="{{ $contract->sales_contract_no }}"
                                {{ request('contract_no') == $contract->sales_contract_no ? 'selected' : '' }}>
                                {{ $contract->sales_contract_no }}
                            </option>
                        @endforeach
                    </select>

                    <!--date range filter-->
                    <label for="contract_date_to" class="mr-2">Start Date:</label>
                    <input type="date" name="contract_date_to" class="form-control mr-2" placeholder="Start Date"
                        value="{{ request('contract_date_to') }}">
                    <label for="contract_date_from" class="mr-2">End Date:</label>
                    <input type="date" name="contract_date_from" class="form-control mr-2" placeholder="End Date"
                        value="{{ request('contract_date_from') }}">

                    <input type="text" name="search" class="form-control mr-2"
                        placeholder="Search by buyer name, contract no" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-light">Search</button>
                </form>
                <!--reset--filter button-->
                <form action="{{ route('sales-contracts.index') }}" method="GET" class="form-inline">
                    <button type="submit" class="btn btn-light ml-2">Reset Filter</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Contract No.</th>
                                <th>Buyer</th>
                                <th>Contract Value</th>
                                <th>Quantity (PCS)</th>
                                <th>FOB</th>
                                <!-- New columns -->
                                <th>Export Value</th>
                                <th>Realization Value</th>
                                <th>BTB Value</th>
                                <th>BTB %</th>
                                <!-- End new columns -->
                                <th>Shipment Dates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contracts as $contract)
                                @php
                                    // Base contract values
                                    $baseValue = $contract->sales_contract_value;
                                    $baseQty = $contract->quantity_pcs;

                                    // Initialize revised totals
                                    $totalRevisedValue = $contract->Revised_value ?? 0;
                                    $totalRevisedQty = $contract->Revised_qty_pcs ?? 0;

                                    // Add all historical revisions
                                    if ($contract->revised_history) {
                                        foreach ($contract->revised_history as $history) {
                                            $totalRevisedValue += $history['Revised_value'] ?? 0;
                                            $totalRevisedQty += $history['Revised_qty_pcs'] ?? 0;
                                        }
                                    }

                                    // Calculate final totals
                                    $sales_contract_value = $baseValue + $totalRevisedValue;
                                    $quantity_pcs = $baseQty + $totalRevisedQty;

                                    // Calculate FOB
                                    $fob = $quantity_pcs > 0 ? $sales_contract_value / $quantity_pcs : 0;

                                    // find the first and last shipment dates
                                    $first_shipment_date = DB::table('sales_exports')
                                        ->where('contract_id', $contract->id)
                                        ->orderBy('shipment_date', 'asc')
                                        ->value('shipment_date');
                                    //if no shipment date found, set to null
                                    if (!$first_shipment_date) {
                                        $first_shipment_date = null;
                                    } else {
                                        $first_shipment_date = \Carbon\Carbon::parse($first_shipment_date)->format(
                                            'd-M-Y',
                                        );
                                    }
                                    $last_shipment_date = DB::table('sales_exports')
                                        ->where('contract_id', $contract->id)
                                        ->orderBy('shipment_date', 'desc')
                                        ->value('shipment_date');
                                    //if no shipment date found, set to null
                                    if (!$last_shipment_date) {
                                        $last_shipment_date = null;
                                    } else {
                                        $last_shipment_date = \Carbon\Carbon::parse($last_shipment_date)->format(
                                            'd-M-Y',
                                        );
                                    }
                                    // New calculations for the index page
                                    $exportValue = $contract->exports->sum('amount_usd');
                                    $realizationValue = $contract->exports->sum('realized_value');
                                    $btbValue =
                                        $contract->fabrics_value +
                                        $contract->accessories_value +
                                        $contract->print_emb_value;
                                    $btbPercentage = $exportValue > 0 ? ($btbValue / $exportValue) * 100 : 0;
                                @endphp

                                <tr>
                                    <td>{{ $contract->sales_contract_no }}</td>
                                    <td>{{ $contract->buyer_name }}</td>
                                    <td>${{ number_format($sales_contract_value, 2) }}</td>
                                    <td>{{ number_format($quantity_pcs) }}</td>

                                    <td>${{ number_format($fob, 4) }}</td>
                                    <!-- New columns data -->
                                    <td>${{ number_format($exportValue, 2) }}</td>
                                    <td>${{ number_format($realizationValue, 2) }}</td>
                                    <td>${{ number_format($btbValue, 2) }}</td>
                                    <td>{{ number_format($btbPercentage, 2) }}%</td>
                                    <!-- End new columns -->
                                    <td>
                                        @isset($first_shipment_date)
                                            {{ $first_shipment_date ?? '' }} to
                                        @endisset
                                        @isset($last_shipment_date)
                                            {{ $last_shipment_date ?? '' }}
                                        @endisset
                                        <br>
                                        @isset($contract->expiry_date)
                                            {{ $contract->expiry_date ?? '' }}
                                        @endisset
                                        <br>

                                    </td>
                                    <!-- live or Closed status in dropdown, if status is live then show green color, if closed then show red color, and can be clicked to change status -->
                                    <td>
                                        @can('Sales-CURD')
                                        <form action="{{ route('sales-contracts.closed', $contract->id) }}"
                                            method="POST" id="status-form-{{ $contract->id }}">
                                            @csrf
                                            @method('PUT')
                                            <select class="form-control form-control-sm" name="status"
                                                onchange="this.form.submit()">
                                                <option value="live"
                                                    {{ $contract->data_4 == 'live' ? 'selected' : '' }}>Live</option>
                                                <option value="closed"
                                                    {{ $contract->data_4 == 'closed' ? 'selected' : '' }}>Closed
                                                </option>
                                            </select>
                                        </form>
                                        @endcan
                                    </td>
                                    <td>
                                        <a href="{{ route('sales-contracts.show', $contract->id) }}"
                                            class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('Sales-CURD')
                                            <a href="{{ route('sales-contracts.edit', $contract->id) }}"
                                                class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <form action="{{ route('sales-contracts.destroy', $contract->id) }}"
                                                method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $contracts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-backend.layouts.master>
