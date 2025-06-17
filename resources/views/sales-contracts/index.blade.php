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
                    @foreach($buyers as $buyer)
                        <option value="{{ $buyer->id }}" {{ (request('buyer_id') == $buyer->id) ? 'selected' : '' }}>
                            {{ $buyer->name }}
                        </option>
                    @endforeach
                </select>
                
                @php
                    $Sales_contracts = \App\Models\SalesContract::select('sales_contract_no')->distinct()->get(); // Fetch all distinct contract numbers from the contracts
                    $Sales_contracts = \App\Models\SalesContract::whereIn('sales_contract_no', $Sales_contracts)->get(); // Fetch all contracts based on the distinct numbers
                @endphp
                <select name="contract_no" class="form-control mr-2">
                    <option value="">Select Contract No.</option>
                    @foreach($Sales_contracts as $contract)
                        <option value="{{ $contract->sales_contract_no }}" {{ (request('contract_no') == $contract->sales_contract_no) ? 'selected' : '' }}>
                            {{ $contract->sales_contract_no }}
                        </option>
                    @endforeach
                </select>
                
                <!--date range filter-->
                <label for="contract_date_to" class="mr-2">Start Date:</label>
                <input type="date" name="contract_date_to" class="form-control mr-2" placeholder="Start Date" value="{{ request('contract_date_to') }}">
                <label for="contract_date_from" class="mr-2">End Date:</label>
                <input type="date" name="contract_date_from" class="form-control mr-2" placeholder="End Date" value="{{ request('contract_date_from') }}">

                <input type="text" name="search" class="form-control mr-2" placeholder="Search by buyer name, contract no"
                 value="{{ request('search') }}">
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
                            <th>Shipment Dates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contracts as $contract)
                        <tr>
                            <td>{{ $contract->sales_contract_no }}</td>
                            <td>{{ $contract->buyer_name }}</td>
                            <td>${{ number_format($contract->sales_contract_value, 2) }}</td>
                            <td>{{ number_format($contract->quantity_pcs) }}</td>
                            @php
                                $fob =  $contract->sales_contract_value / $contract->quantity_pcs ;
                            @endphp
                            <td>${{ number_format($fob, 4) }}</td>
                            <td>
                                @isset($contract->first_shipment_date) 
                                {{ $contract->first_shipment_date->format('d-M-y') ?? '' }} to
                                @endisset
                                @isset($contract->last_shipment_date) 
                                {{ $contract->last_shipment_date->format('d-M-y') ?? '' }}
                                @endisset<br>
                                @isset($contract->expiry_date) 
                                {{ $contract->expiry_date->format('d-M-y') ?? '' }}
                                @endisset<br>
                                 
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
 