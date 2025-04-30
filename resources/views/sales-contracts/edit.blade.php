<x-backend.layouts.master>

    <x-slot name="pageTitle">
      Sales Contracts
    </x-slot>
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Edit Contract: {{ $contract->sales_contract_no }}</h1>
        </div>
    </div>

    <form action="{{ route('sales-contracts.update', $contract->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('sales-contracts.form')
        <button type="submit" class="btn btn-primary">Update Contract</button>
    </form>
</div>

</x-backend.layouts.master>
 