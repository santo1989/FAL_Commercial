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
            <a href="{{ route('sales-contracts.create') }}" class="btn btn-outline-primary float-right mr-2">
                <i class="fas fa-plus"></i> New Contract
            </a>
        </div>
    </div>

    <div class="card">
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
                                {{-- @can('edit-contract') --}}
                                <a href="{{ route('sales-contracts.edit', $contract->id) }}" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {{-- @endcan
                                @can('delete-contract') --}}
                                <form action="{{ route('sales-contracts.destroy', $contract->id) }}" 
                                      method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                {{-- @endcan --}}
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
 