<x-backend.layouts.master>

    <x-slot name="pageTitle">
      Sales Import Records
    </x-slot>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="float-left">Import Records</h1>
            <div class="float-right">
                <a href="{{ route('sales-imports.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> New Import
                </a>
                <a href="{{ route('import-template') }}" class="btn btn-primary">
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
                    <thead class="bg-secondary text-white">
                        <tr>
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
                            <td>{{ $import->btb_lc_no }}</td>
                            <td>{{ $import->date->format('d-M-Y') }}</td>
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
 