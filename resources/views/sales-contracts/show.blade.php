<x-backend.layouts.master>
    @php
        // Calculate updated contract value and quantity
        $baseValue = $contract->sales_contract_value;
        $revisedValue = $contract->Revised_value ?? 0;
        $totalContractValue = $baseValue + $revisedValue;

        $baseQty = $contract->quantity_pcs;
        $revisedQty = $contract->Revised_qty_pcs ?? 0;
        $totalQty = $baseQty + $revisedQty;

        // Calculate FOB
        $fob = $totalQty > 0 ? $totalContractValue / $totalQty : 0;

        // Calculate export summaries
        $exportPcs = DB::table('sales_exports')->where('contract_id', $contract->id)->sum('g_qty_pcs') ?? 0;

        $exportValue = DB::table('sales_exports')->where('contract_id', $contract->id)->sum('amount_usd') ?? 0;

        $shortExcessValue = $totalContractValue - $exportValue;
        $shortExcessPcs = $totalQty - $exportPcs;
    @endphp
    <x-slot name="pageTitle">
        Sales Contracts Details
    </x-slot>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1>Contract Details</h1>
                <a href="{{ route('sales-contracts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-file-contract"></i> Contract No:
                <span class="float-right">
                    @can('edit contracts')
                        <a href="{{ route('sales-contracts.edit', $contract->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endcan
                    @can('delete contracts')
                        <form action="{{ route('sales-contracts.destroy', $contract->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    @endcan
                </span>

                <h3 class="mb-0">{{ $contract->sales_contract_no }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Buyer</th>
                                    <td>{{ $contract->buyer_name }}</td>
                                </tr>
                                <tr>
                                    <th>Contract No.</th>
                                    <td>{{ $contract->sales_contract_no }}</td>
                                </tr>
                                <tr>
                                    <th>Sales Contract Value</th>
                                    <td>${{ number_format($totalContractValue, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="text-center bg-light">
                                            <h4>Revised Details</h4>
                                            <!--add a modal to add new Revised details and save it to the database-->
                                            <button class="btn btn-outline-primary" data-toggle="modal"
                                                data-target="#addRevisedModal">Add
                                                Revised Details</button>
                                            <button class="btn btn-outline-primary" data-toggle="modal"
                                                data-target="#ShowRevisedHistory">Revised History</button>
                                        </div>

                                    </td>
                                </tr>
                                <tr>
                                    <th>Quantity (Pcs)</th>
                                    <td>{{ number_format($totalQty) }} PCS</td>
                                </tr>
                                <tr>
                                    <th>FOB</th>
                                    <td>${{ number_format($fob, 4) }}</td>
                                </tr>
                                <tr>
                                    <th>Export (Pcs)</th>
                                    <td>
                                        @php
                                            $exportPcs = DB::table('sales_exports')
                                                ->where('contract_id', $contract->id)
                                                ->sum('g_qty_pcs');
                                        @endphp
                                        {{ number_format($exportPcs) }} PCS

                                    </td>
                                </tr>
                                <tr>
                                    <th>Export Value</th>
                                    <td>${{ number_format($exportValue, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Short/Excess Value</th>
                                    <td>${{ number_format($shortExcessValue, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Short/Excess (Pcs)</th>
                                    <td>{{ number_format($shortExcessPcs) }} PCS</td>
                                </tr>
                                <tr>
                                    <th>P. Realized Value</th>
                                    <td>${{ number_format($contract->p_realized_value, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Replace</th>
                                    <td>{{ $contract->replace ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <th>First Shipment Date</th>
                                    <td>
                                        @php

                                            $firstShipmentDate =
                                                DB::table('sales_exports')
                                                    ->where('contract_id', $contract->id)
                                                    ->min('shipment_date') ?? 'N/A';

                                        @endphp
                                        {{ $firstShipmentDate }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Last Shipment Date</th>
                                    <td>
                                        @php

                                            $lastShipmentDate =
                                                DB::table('sales_exports')
                                                    ->where('contract_id', $contract->id)
                                                    ->max('shipment_date') ?? 'N/A';

                                        @endphp
                                        {{ $lastShipmentDate }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Expiry Date</th>
                                    <td>
                                        @php
                                            $expiryDate = $contract->expiry_date
                                                ? $contract->expiry_date->format('d-M-Y')
                                                : 'N/A';
                                        @endphp
                                        {{ $expiryDate }}
                                    </td>

                                </tr>
                            </thead>
                        </table>

                    </div>
                    <div class="col-md-6">
                        <div class="text-center bg-light">
                            <h4>BTB Details</h4>

                        </div>
                        <table class="table table-bordered" id="btbTable">
                            <thead class="bg-light">
                                <tr>
                                    <th></th>
                                    <th>Amount (USD)</th>
                                    <th>% As per Contract</th>
                                    <th>% As per Export</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Fabrics Row -->
                                <tr class="btb-row" data-category="fabrics">
                                    <th>Fabrics</th>
                                    <td data-value="{{ $contract->fabrics_value }}">
                                        ${{ number_format($contract->fabrics_value, 2) }}
                                    </td>
                                    <td class="contract-percent"></td>
                                    <td class="export-percent"></td>
                                </tr>

                                <!-- Accessories Row -->
                                <tr class="btb-row" data-category="accessories">
                                    <th>Accessories</th>
                                    <td data-value="{{ $contract->accessories_value }}">
                                        ${{ number_format($contract->accessories_value, 2) }}
                                    </td>
                                    <td class="contract-percent"></td>
                                    <td class="export-percent"></td>
                                </tr>

                                <!-- Print/Emb Row -->
                                <tr class="btb-row" data-category="print_emb">
                                    <th>Print/Emb.</th>
                                    <td data-value="{{ $contract->print_emb_value }}">
                                        ${{ number_format($contract->print_emb_value, 2) }}
                                    </td>
                                    <td class="contract-percent"></td>
                                    <td class="export-percent"></td>
                                </tr>

                                <!-- Total BTB Row -->
                                <tr class="total-row">
                                    <th>Total BTB</th>
                                    <td id="totalBtbAmount"></td>
                                    <td id="totalContractPercent"></td>
                                    <td id="totalExportPercent"></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="text-center bg-light p-4 p-2 mb-4">
                            <h4>UD Details</h4> <!--add a modal to add new UD details and save it to the database-->
                            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#addUDModal">Add
                                UD</button>
                            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#ShowUDHistory">UD
                                History</button>






                        </div>
                        @php
                            // merge history with current
                            $allUd = collect($contract->ud_history ?? [])
                                ->map(
                                    fn($h) => [
                                        'value' => $h['ud_value'],
                                        'qty' => $h['ud_qty_pcs'],
                                        'used' => $h['used_value'] ?? 0,
                                    ],
                                )
                                ->push([
                                    'value' => $contract->ud_value,
                                    'qty' => $contract->ud_qty_pcs,
                                    'used' => $contract->data_1,
                                ]);

                            $totals = [
                                'value' => $allUd->sum('value'),
                                'qty' => $allUd->sum('qty'),
                                'used' => $allUd->sum('used'),
                            ];
                        @endphp

                        <table class="table table-bordered">
                            <tr>
                                <th>Total UD Value</th>
                                <td>${{ number_format($totals['value'], 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total UD Qty (PCS)</th>
                                <td>{{ number_format($totals['qty']) }} PCS</td>
                            </tr>
                            <tr>
                                <th>Total Used Value (USD)</th>
                                <td>${{ number_format($totals['used'], 2) }}</td>
                            </tr>
                            <tr>
                                <th>Bank Name (Current)</th>
                                <td>{{ $contract->bank_name }}</td>
                            </tr>
                        </table>

                        <!-- Add a 2 floating file upload buttons to upload the SalesExport excel file and the SalesImport file and save it to the database and back to this page -->
                        <!-- Add this to your Blade template -->
                        <div class="file-upload-buttons">
                            <div class="btn-group-horizontal" role="group" aria-label="File Upload Buttons">
                                @can('Import-CURD')
                                    <!-- Import tempelete file download -->
                                    <a href="{{ route('excel.import-template') }}"
                                        class="btn btn-primary btn-md rounded-pill mb-2">
                                        <i class="fas fa-download me-2"></i> Download Import Template </a>

                                    <!-- Import Upload -->
                                    <button type="button" class="btn btn-success btn-md rounded-pill mb-2"
                                        data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-file-import me-2"></i> Import File upload
                                    </button>
                                @endcan
                                @can('Export-CURD')
                                    <!-- Export template file download -->
                                    <a href="{{ route('excel.export-template') }}"
                                        class="btn btn-primary btn-md rounded-pill mb-2">
                                        <i class="fas fa-download me-2"></i> Download Export Template </a>

                                    <!-- Export Upload -->
                                    <button type="button" class="btn btn-info btn-md rounded-pill" data-bs-toggle="modal"
                                        data-bs-target="#exportModal">
                                        <i class="fas fa-file-export me-2"></i> Export File Upload
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <!-- import Modals for upload file -->
                        <div class="modal fade" id="importModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('import.upload', $contract->id) }}" method="POST"
                                        enctype="multipart/form-data" id="importForm">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Upload BTB/Import Data</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Upload the Import data file. Ensure the file is in the correct format.
                                            </p>


                                            <div class="mt-2">
                                                <label for="file" class="form-label">Select Import File</label>
                                                <input type="file" name="file" class="form-control"
                                                    accept=".xlsx, .xls" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Upload</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Export Modals for upload file -->
                        <div class="modal fade" id="exportModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('export.upload', $contract->id) }}" method="POST"
                                        enctype="multipart/form-data" id="exportForm">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Upload Export File</h5>
                                            <button type="button" class="btn-close"
                                                data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mt-2">
                                                <label for="file" class="form-label">Select Export File</label>
                                                <input type="file" name="file" class="form-control"
                                                    accept=".xlsx, .xls" required>

                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Upload</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs" id="contractTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="imports_tab_data" data-toggle="tab" href="#imports_data">
                    Imports ({{ $contract->imports->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="exports_tab_data" data-toggle="tab" href="#exports_data">
                    Exports ({{ $contract->exports->count() }})
                </a>
            </li>
        </ul>

        <div class="tab-content" id="contractTabsContent">
            <!-- Imports Tab -->
            <div class="tab-pane fade show active" id="imports_data">
                @include('partials.sales_imports_table', ['imports' => $contract->imports])
            </div>

            <!-- Exports Tab -->
            <div class="tab-pane fade" id="exports_data">
                @include('partials.sales_exports_table', ['exports' => $contract->exports])
            </div>
        </div>
    </div>

    <!-- Modal for adding Revised details -->
    <div class="modal fade" id="addRevisedModal" tabindex="-1" role="dialog"
        aria-labelledby="addRevisedModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUDModalLabel">Add Revised
                        Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sales-contracts.revised.store', $contract->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Revised No.</th>
                                    <td><input type="text" name="Revised_no" class="form-control" required></td>
                                </tr>
                                <tr>
                                    <th>Revised Date</th>
                                    <td>
                                        <input type="date" name="Revised_date" class="form-control" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Revised Value</th>
                                    <td><input type="number" name="Revised_value" class="form-control"
                                            step="0.01" required></td>
                                </tr>
                                <tr>
                                    <th>Revised Qty (Pcs)</th>
                                    <td><input type="number" name="Revised_qty_pcs" class="form-control" required>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-dismiss="modal"
                            aria-label="Close">
                            Close
                        </button>
                        <button type="submit" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal for showing Revised history -->
    <div class="modal fade" id="ShowRevisedHistory" tabindex="-1" role="dialog"
        aria-labelledby="ShowRevisedHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ShowRevisedHistoryLabel">Revised History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body ">
                    @if ($contract->revised_history)
                        <div class="mt-4">
                            <h5>Revised Change History</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>

                                        <th>Revised Number</th>
                                        <th>Value</th>
                                        <th>Qty (PCS)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_reverse($contract->revised_history) as $history)
                                        <tr>

                                            <td>{{ $history['Revised_no'] }}</td>
                                            <td>{{ number_format($history['Revised_value'], 2) }}</td>
                                            <td>{{ number_format($history['Revised_qty_pcs']) }}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding UD details -->
    <div class="modal fade" id="addUDModal" tabindex="-1" role="dialog" aria-labelledby="addUDModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUDModalLabel">Add UD Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('sales-contracts.ud.store', $contract->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>UD No.</th>
                                    <td><input type="text" name="ud_no" class="form-control" required></td>
                                </tr>
                                <tr>
                                    <th>UD Date</th>
                                    <td>
                                        <input type="date" name="ud_date" class="form-control" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th>UD Value</th>
                                    <td><input type="number" name="ud_value" class="form-control" step="0.01"
                                            required></td>
                                </tr>
                                <tr>
                                    <th>UD Value (Pcs)</th>
                                    <td><input type="number" name="ud_value_pcs" class="form-control" required></td>
                                </tr>
                                <tr>
                                    <th>Used Value (USD)</th>
                                    <td><input type="number" name="used_value" class="form-control" step="0.01">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Bank Name</th>
                                    <td>
                                        <input type="text" name="bank_name" class="form-control" required>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-dismiss="modal"
                            aria-label="Close">
                            Close
                        </button>
                        <button type="submit" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal for showing UD history -->
    <div class="modal fade" id="ShowUDHistory" tabindex="-1" role="dialog" aria-labelledby="ShowUDHistoryLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ShowUDHistoryLabel">UD History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if ($contract->ud_history)
                        <div class="mt-4">
                            <h5>UD Change History</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Changed At</th>
                                        <th>UD No</th>
                                        <th>Value</th>
                                        <th>Qty</th>
                                        <th>Used</th>
                                        <th>By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (array_reverse($contract->ud_history ?? []) as $h)
                                        <tr>
                                            <td>{{ $h['changed_at'] }}</td>
                                            <td>{{ $h['ud_no'] }}</td>
                                            <td>${{ number_format($h['ud_value'], 2) }}</td>
                                            <td>{{ number_format($h['ud_qty_pcs']) }}</td>
                                            <td>${{ number_format($h['used_value'] ?? 0, 2) }}</td>
                                            <td>{{ optional(\App\Models\User::find($h['changed_by']))->name ?? 'System' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity_pcs');
            const valueInput = document.getElementById('sales_contract_value');
            const fobInput = document.getElementById('fob');

            function calculateFOB() {
                const quantity = parseFloat(quantityInput.value) || 0;
                const value = parseFloat(valueInput.value) || 0;

                if (quantity > 0 && value > 0) {
                    fobInput.value = (value / quantity).toFixed(4);
                } else {
                    fobInput.value = 0;
                }
            }

            quantityInput.addEventListener('input', calculateFOB);
            valueInput.addEventListener('input', calculateFOB);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get base values from PHP
            const salesContractValue = {{ $totalContractValue }};
            const exportValue = {{ $exportValue }};
            let totalBtbValue = 0;

            // Calculate percentages for each row
            document.querySelectorAll('.btb-row').forEach(row => {
                const amount = parseFloat(row.querySelector('td[data-value]').dataset.value) || 0;
                totalBtbValue += amount;

                const contractPercent = salesContractValue > 0 ?
                    (amount / salesContractValue * 100).toFixed(2) :
                    '0.00';

                const exportPercent = exportValue > 0 ?
                    (amount / exportValue * 100).toFixed(2) :
                    '0.00';

                row.querySelector('.contract-percent').textContent = `${contractPercent}%`;
                row.querySelector('.export-percent').textContent = `${exportPercent}%`;
            });

            // Calculate totals
            document.getElementById('totalBtbAmount').textContent =
                `$${totalBtbValue.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;

            const totalContractPercent = salesContractValue > 0 ?
                (totalBtbValue / salesContractValue * 100).toFixed(2) :
                '0.00';

            const totalExportPercent = exportValue > 0 ?
                (totalBtbValue / exportValue * 100).toFixed(2) :
                '0.00';

            document.getElementById('totalContractPercent').textContent = `${totalContractPercent}%`;
            document.getElementById('totalExportPercent').textContent = `${totalExportPercent}%`;
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get base values from PHP
            const salesContractValue = {{ $contract->sales_contract_value }};
            const exportValue = {{ $contract->export_value }};
            let totalBtbValue = 0;

            // Calculate percentages for each row
            document.querySelectorAll('.btb-row').forEach(row => {
                const amount = parseFloat(row.querySelector('td[data-value]').dataset.value) || 0;
                totalBtbValue += amount;

                const contractPercent = salesContractValue > 0 ?
                    (amount / salesContractValue * 100).toFixed(2) :
                    '0.00';

                const exportPercent = exportValue > 0 ?
                    (amount / exportValue * 100).toFixed(2) :
                    '0.00';

                row.querySelector('.contract-percent').textContent = `${contractPercent}%`;
                row.querySelector('.export-percent').textContent = `${exportPercent}%`;
            });

            // Calculate totals
            document.getElementById('totalBtbAmount').textContent =
                `$${totalBtbValue.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;

            const totalContractPercent = salesContractValue > 0 ?
                (totalBtbValue / salesContractValue * 100).toFixed(2) :
                '0.00';

            const totalExportPercent = exportValue > 0 ?
                (totalBtbValue / exportValue * 100).toFixed(2) :
                '0.00';

            document.getElementById('totalContractPercent').textContent = `${totalContractPercent}%`;
            document.getElementById('totalExportPercent').textContent = `${totalExportPercent}%`;
        });
    </script>
</x-backend.layouts.master>
