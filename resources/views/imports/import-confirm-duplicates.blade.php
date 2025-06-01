<x-backend.layouts.master>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Confirm Import Duplicates</div>

                    <div class="card-body">
                        @if (count($duplicates) > 0)
                            <form action="{{ route('import.confirm')  }}" method="POST">
                                @csrf
                                <input type="hidden" name="contract_id" value="{{ Session::get('contract_id') }}">

                                <div class="alert alert-warning">
                                    <strong>Duplicate Records Found!</strong>
                                    The following records already exist. Uncheck the ones you wish to skip.
                                </div>

                               
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">Skip?</th>
                                            <th>BTB LC NO.</th>
                                            <th>DATE</th>
                                            <th>Description</th>
                                            <th>Fabric Value</th>
                                            <th>Accessories Value</th>
                                            <th>Fabric Qty (Kgs)</th>
                                            <th>Accessories Qty</th>
                                            <th>Printing/Emb Qty</th>
                                            <th>Printing/Emb Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($duplicates as $index => $row)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" 
                                                           name="keep_ids[{{ $index }}]" 
                                                           value="1"
                                                           checked>
                                                </td>
                                                <td>{{ $row['btb_lc_no'] ?? 'N/A' }}</td>
                                                <td>{{ $row['date'] ?? 'N/A' }}</td>
                                                <td>{{ $row['description'] ?? 'N/A' }}</td>
                                                <td>{{ number_format($row['fabric_value'] ?? 0, 2) }}</td>
                                                <td>{{ number_format($row['accessories_value'] ?? 0, 2) }}</td>
                                                <td>{{ number_format($row['fabric_qty_in_kgs'] ?? 0, 2) }}</td>
                                                <td>{{ number_format($row['accessories_qty'] ?? 0, 2) }}</td>
                                                <td>{{ number_format($row['printing_embroidery_qty'] ?? 0, 2) }}</td>
                                                <td>{{ number_format($row['printing_embroidery_value'] ?? 0, 2) }}</td>
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
                            
                            <form action="{{ route('import.confirm') }}" method="POST">
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
