<div class="card">
    <div class="card-header bg-secondary text-white">
        <h4 class="mb-0"><i class="fas fa-download"></i> Import Records</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="bg-light">
                        <th>BTB L/C No.</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Fabric (Value)</th>
                        <th>Accessories (Value)</th>
                        <th>Fabric Qty (KG)</th>
                        <th>Print/Emb. (Value)</th>
                        <th>Print/Emb. (Qty)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td>{{ $import->btb_lc_no }}</td>
                            <td>
                                @if ($import->date)
                                    {{ \Carbon\Carbon::parse($import->date)->format('d-M-Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $import->description }}</td>
                            <td class="text-right">${{ number_format($import->fabric_value, 2) }}</td>
                            <td class="text-right">${{ number_format($import->accessories_value, 2) }}</td>
                            <td class="text-right">{{ number_format($import->fabric_qty_kg, 2) }} KG</td>
                            <td class="text-right">${{ number_format($import->print_emb_value, 2) }}</td>
                            <td class="text-right">{{ number_format($import->print_emb_qty, 2) }} KG</td>

                            <td>
                                <a href="{{ route('sales-imports.edit', $import->id) }}" class="btn btn-sm btn-warning"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sales-imports.destroy', $import->id) }}" method="POST"
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
                            <td colspan="8" class="text-center">No import records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
