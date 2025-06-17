<x-backend.layouts.master>
    <x-slot name="pageTitle">
        Edit Import Record
    </x-slot>
    
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Edit Import Record</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sales-imports.update', $import->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Include the form partial -->
                    @include('sales-imports.form')
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('sales-imports.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backend.layouts.master>