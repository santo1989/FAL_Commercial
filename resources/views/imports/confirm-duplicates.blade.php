{{-- resources/views/imports/confirm-duplicates.blade.php --}}
<x-backend.layouts.master>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Confirm Export Duplicates</div>

                    <div class="card-body">
                        @if(count($duplicates) > 0)
                            <form action="{{ route('export.confirm') }}" method="POST">
                                @csrf
                                <input type="hidden" name="contract_id" value="{{ Session::get('contract_id') }}">

                                <div class="alert alert-warning">
                                    <strong>Duplicate Records Found!</strong> 
                                    The following records already exist. Check the ones you wish to import anyway.
                                </div>
                                
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">Keep?</th>
                                            <th>Invoice No.</th>
                                            <th>Export Bill No.</th>
                                            <th>Amount (USD)</th>
                                            <th>Realized Amount</th>
                                            <th>Date Realized</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($duplicates as $index => $row)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" 
                                                           name="keep_ids[]" 
                                                           value="{{ $index }}"
                                                           checked>
                                                </td>
                                                <td>{{ $row['invoice_no'] ?? 'N/A' }}</td>
                                                <td>{{ $row['export_bill_no'] ?? 'N/A' }}</td>
                                                <td>{{ number_format($row['amount_usd_of_export_goods'] ?? 0, 2) }}</td>
                                                <td>{{ number_format($row['amount_usd_realised'] ?? 0, 2) }}</td>
                                                <td>{{ $row['date_of_realised'] ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('sales-contracts.show', Session::get('contract_id')) }}" 
                                       class="btn btn-danger">
                                        <i class="fas fa-times me-2"></i> Cancel Import
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i> Confirm Import
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                No duplicate records found. All data will be imported.
                            </div>
                            
                            <form action="{{ route('export.confirm') }}" method="POST">
                                @csrf
                                <input type="hidden" name="contract_id" value="{{ Session::get('contract_id') }}">
                                
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('sales-contracts.show', Session::get('contract_id')) }}" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i> Go Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i> Proceed with Import
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-backend.layouts.master>