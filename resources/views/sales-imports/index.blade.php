<x-backend.layouts.master>

    <x-slot name="pageTitle">
      Sales Import Records
    </x-slot>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
           
            <h3 class="text-center mb-0">
                <i class="fas fa-file-import"></i>  Import Records
            </h3>
            <div class="float-left">
                <!--back button-->
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back </a>
            </div>
            <div class="float-right">
                @can('Admin')
                    <a href="{{ route('sales-imports.create') }}" class="btn btn-success btn-md rounded-pill mb-2">
                    <i class="fas fa-plus"></i> New Import
                </a>
                @endcan
                
                 <!-- Import tempelete file download -->
                 <a href="{{ route('excel.import-template') }}"
                 class="btn btn-primary btn-md rounded-pill mb-2">
                 <i class="fas fa-download me-2"></i> Download Import Template </a>
            </div>
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
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Sales Contract No.</th>
                            <th>BTB LC No.</th>
                            <th>Date</th>
                            <th>Fabric Value</th>
                            <th>Accessories Value</th>
                            <th>Fabric Qty (KG)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($imports as $import)
                        <tr>
                            <td>{{ $import->salesContract->sales_contract_no }}</td>
                            <td>{{ $import->btb_lc_no }}</td>
                            <td>{{ $import->date }}</td>
                            <td>${{ number_format($import->fabric_value, 2) }}</td>
                            <td>${{ number_format($import->accessories_value, 2) }}</td>
                            <td>{{ number_format($import->fabric_qty_kg, 2) }}</td>
                            <td>
                                @include('partials.actions', [
                                    'editRoute' => route('sales-imports.edit', $import->id),
                                    'deleteRoute' => route('sales-imports.destroy', $import->id)
                                ])
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $imports->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
</x-backend.layouts.master>
 