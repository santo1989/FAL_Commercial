<x-backend.layouts.master>
    <x-slot name="pageTitle">
        Create Export Record
    </x-slot>
    
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Create Export Record</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sales-exports.store') }}" method="POST">
                    @csrf
                    @include('sales-exports.form')
                    <!-- Submit button -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Create</button>
                        <a href="{{ route('sales-exports.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backend.layouts.master>