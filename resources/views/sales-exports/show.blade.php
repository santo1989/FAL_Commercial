<x-backend.layouts.master>

    <x-slot name="pageTitle">
      Sales Contracts Details
    </x-slot>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Contract Details</h1>
            <a href="{{ route('sales-contracts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">{{ $contract->sales_contract_no }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Buyer:</strong> {{ $contract->buyer_name }}</p>
                    <p><strong>Total Value:</strong> ${{ number_format($contract->sales_contract_value, 2) }}</p>
                    <p><strong>Quantity:</strong> {{ number_format($contract->quantity_pcs) }} PCS</p>
                </div>
                <div class="col-md-6">
                    <p><strong>FOB:</strong> ${{ number_format($contract->fob, 4) }}</p>
                    <p><strong>First Shipment:</strong> {{ $contract->first_shipment_date->format('d-M-Y') }}</p>
                    <p><strong>Last Shipment:</strong> {{ $contract->last_shipment_date->format('d-M-Y') }}</p>
                    <p><strong>Expiry:</strong> {{ $contract->expiry_date->format('d-M-Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs" id="contractTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="imports-tab" data-toggle="tab" href="#imports">
                Imports ({{ $contract->imports->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="exports-tab" data-toggle="tab" href="#exports">
                Exports ({{ $contract->exports->count() }})
            </a>
        </li>
    </ul>

    <div class="tab-content" id="contractTabsContent">
        <!-- Imports Tab -->
        <div class="tab-pane fade show active" id="imports">
            @include('sales-imports.partials.table', ['imports' => $contract->imports])
        </div>

        <!-- Exports Tab -->
        <div class="tab-pane fade" id="exports">
            @include('sales-exports.partials.table', ['exports' => $contract->exports])
        </div>
    </div>
</div>
</x-backend.layouts.master>
 