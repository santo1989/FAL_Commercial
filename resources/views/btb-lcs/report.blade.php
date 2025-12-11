<x-backend.layouts.master>
    <x-slot name="pageTitle">BTB/LC Value Report</x-slot>

    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('btb-lcs.index') }}" class="btn btn-sm btn-outline-secondary me-2" title="Back to BTB LC List">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <h3 class="mb-0">BTB/LC Value Report</h3>
                </div>
                <div>
                    @php $qs = http_build_query(request()->query()); @endphp
                    <a href="{{ route('btb-lcs.report.excel') }}{{ $qs ? '?'.$qs : '' }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Download Excel
                    </a>
                </div>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" id="filterForm">
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">From Date</label>
                            <input type="date" name="date_from" value="{{ request('date_from', now()->subYear()->format('Y-m-d')) }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">To Date</label>
                            <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Bank</label>
                            <select name="bank_name" class="form-control form-control-sm select2">
                                <option value="">-- All Banks --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}" {{ request('bank_name') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('btb-lcs.report') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Amounts in US$</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Sl</th>
                                <th>Bank Name</th>
                                @foreach($months as $month)
                                    <th class="text-end">{{ $month }}</th>
                                @endforeach
                                <th class="text-end fw-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $grandTotal = 0;
                                $monthlyGrandTotals = array_fill(0, count($months), 0);
                                $sl = 1;
                            @endphp

                            @foreach($reportData as $bankGroup => $categories)
                                @php
                                    $subTotal = 0;
                                    $monthlySubTotals = array_fill(0, count($months), 0);
                                @endphp

                                @foreach($categories as $category => $monthlyData)
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>{{ $category }}</td>
                                        @foreach($months as $idx => $month)
                                            @php
                                                $value = $monthlyData[$month] ?? 0;
                                                $monthlySubTotals[$idx] += $value;
                                                $monthlyGrandTotals[$idx] += $value;
                                            @endphp
                                            <td class="text-end">{{ $value > 0 ? number_format($value, 2) : '' }}</td>
                                        @endforeach
                                        @php $rowTotal = array_sum($monthlyData); $subTotal += $rowTotal; @endphp
                                        <td class="text-end fw-bold">{{ $rowTotal > 0 ? number_format($rowTotal, 2) : '' }}</td>
                                    </tr>
                                @endforeach

                                <tr class="table-secondary fw-bold">
                                    <td>{{ $sl++ }}</td>
                                    <td>Sub Total - {{ $bankGroup }}</td>
                                    @foreach($monthlySubTotals as $subTotalValue)
                                        <td class="text-end">{{ $subTotalValue > 0 ? number_format($subTotalValue, 2) : '' }}</td>
                                    @endforeach
                                    <td class="text-end">{{ $subTotal > 0 ? number_format($subTotal, 2) : '' }}</td>
                                </tr>

                                @php $grandTotal += $subTotal; @endphp
                            @endforeach

                            <tr class="table-dark fw-bold">
                                <td>{{ $sl }}</td>
                                <td>Grand Total</td>
                                @foreach($monthlyGrandTotals as $grandTotalValue)
                                    <td class="text-end">{{ $grandTotalValue > 0 ? number_format($grandTotalValue, 2) : '' }}</td>
                                @endforeach
                                <td class="text-end">{{ $grandTotal > 0 ? number_format($grandTotal, 2) : '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if(count($reportData) == 0)
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No data available for the selected filters.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        });
    </script>
    @endpush
</x-backend.layouts.master>
