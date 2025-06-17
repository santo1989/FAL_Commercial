<x-backend.layouts.master>
    <x-slot name="pageTitle">
        Create Import Record
    </x-slot>
    
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Create Import Record</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sales-imports.store') }}" method="POST">
                    @csrf
                    @include('sales-imports.form')
                    <!-- Submit button -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Create</button>
                        <a href="{{ route('sales-imports.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backend.layouts.master>