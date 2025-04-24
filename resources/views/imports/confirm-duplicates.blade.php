<x-backend.layouts.master>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Confirm Duplicates</div>

                <div class="card-body">
                    @if(count($duplicates) > 0)
                        <form action="{{ $type === 'import' ? route('import.confirm') : route('export.confirm') }}" method="POST">
                            @csrf
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Keep?</th>
                                        <th>Contract ID</th>
                                        <!-- Add other headers -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($duplicates as $index => $row)
                                    <tr>
                                        <td>
                                            <input type="checkbox" 
                                                   name="keep_ids[]" 
                                                   value="{{ $index }}">
                                        </td>
                                        <td>{{ $row[0] }}</td>
                                        <!-- Add other columns -->
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <button class="btn btn-primary">Confirm Import</button>
                        </form>
                    @else
                        <p>No duplicates found</p>
                        <a href="{{ route('sales-imports.index') }}" class="btn btn-primary">
                            Continue to Import
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-backend.layouts.master>
