<div class="card">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0"><i class="fas fa-upload"></i> Export Records</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="bg-light">
                        <th>Invoice No.</th>
                        <th>Export Bill No.</th>
                        <th>Amount (USD)</th>
                        <th>Realized Value</th>
                        <th>Quantity (PCS)</th>
                        <th>Realized Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exports as $export)
                    <tr>
                        <td>{{ $export->invoice_no }}</td>
                        <td>{{ $export->export_bill_no }}</td>
                        <td class="text-right">${{ number_format($export->amount_usd, 2) }}</td>
                        <td class="text-right">${{ number_format($export->realized_value, 2) }}</td>
                        <td class="text-right">{{ number_format($export->g_qty_pcs) }}</td>
                        <td>
                            @if ($export->date_of_realized)
                                    {{ \Carbon\Carbon::parse($export->date_of_realized)->format('d-M-Y') }}
                                @else
                                    N/A
                                @endif
                        </td>
                        <td>
                            <a href="{{ route('sales-exports.edit', $export->id) }}"
                                class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('sales-exports.destroy', $export->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                    onclick="return confirm('Are you sure you want to delete this record?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No export records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>