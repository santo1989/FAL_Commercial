<x-backend.layouts.master>

    <x-slot name="pageTitle">
      Sales Contracts
    </x-slot>
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Create New Contract</h1>
            <!-- create a button to that will update the buyer list from the http://192.168.100.231:1008/buyers/all_buyer and update this projects buyers table -->
            <button id="updateBuyersBtn" class="btn btn-outline-primary">
    <i class="fas fa-sync"></i> Update Buyers
    <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
</button>
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

    <form action="{{ route('sales-contracts.store') }}" method="POST" class="form-horizontal p-4">
        
        {{-- Include the CSRF token for security --}}
        @csrf
        @include('sales-contracts.form')
       
        <a href="{{ route('sales-contracts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a> 
        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-save"></i> Save Contract</button>
    </form>
</div>
<script>
document.getElementById('updateBuyersBtn').addEventListener('click', async function() {
    const btn = this;
    const spinner = document.getElementById('spinner');
    
    btn.disabled = true;
    spinner.classList.remove('d-none');
    
    try {
        const response = await fetch('{{ route('buyers_list_update') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (response.ok) {
            console.log('Success:', data.message);
            alert('Buyers updated successfully!', data.message);
            
        } else {
            console.log('Error:', data.message);
            alert('Error updating buyers!', data.message);
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while updating buyers: ' + error.message);
    } finally {
        btn.disabled = false;
        spinner.classList.add('d-none');
    }
});
</script>
</x-backend.layouts.master>
 