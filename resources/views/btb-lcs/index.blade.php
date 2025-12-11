<x-backend.layouts.master>
    <x-slot name="pageTitle">BTB LC Records</x-slot>

    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary me-2" title="Back to Home">
                        <i class="fas fa-arrow-left"></i> Home
                    </a>
                    <h3 class="mb-0">BTB LC Records</h3>
                </div>
                <div>
                    <a href="{{ route('btb-lcs.create') }}" class="btn btn-primary">New BTB LC</a>
                    @php $qs = http_build_query(request()->query()); @endphp
                    <a href="{{ route('btb-lcs.export') }}{{ $qs ? '?'.$qs : '' }}" class="btn btn-success">Export Excel</a>
                    <a href="{{ route('btb-lcs.pdf') }}{{ $qs ? '?'.$qs : '' }}" class="btn btn-secondary">Download PDF</a>
                    <a href="{{ route('btb-lcs.report') }}" class="btn btn-info"><i class="fas fa-chart-bar"></i> Value Report</a>
                </div>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <select name="contract_id" class="form-control form-control-sm select2" aria-label="Filter by contract">
                                <option value="">-- All Contracts --</option>
                                @foreach($contracts as $c)
                                    <option value="{{ $c->id }}" {{ request('contract_id') == $c->id ? 'selected' : '' }}>{{ $c->sales_contract_no }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="btb_lc_no" value="{{ request('btb_lc_no') }}" placeholder="BTB/LC No" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="From">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="To">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            @php
                                $banks = [
                                    'AB Bank PLC','Al-Arafah Islami Bank PLC','Bangladesh Commerce Bank Limited','Bank Al-Falah Limited (Pakistan)','Bank Asia Limited','Bengal Commercial Bank Limited','BRAC Bank PLC','City Bank PLC','Commercial Bank of Ceylon PLC (Sri Lanka)','Community Bank Bangladesh Limited','Dhaka Bank Limited','Dhaka Mercantile Co-Operative Bank Limited','Dutch-Bangla Bank Limited','Eastern Bank PLC','First Security Islami Bank PLC','Global Islami Bank PLC','Habib Bank Limited (Pakistan)','HSBC (United Kingdom)','ICB Islamic Bank PLC','IFIC Bank PLC','Islami Bank Bangladesh PLC','Jamuna Bank Limited','Meghna Bank Limited','Mercantile Bank PLC','Midland Bank Limited','Modhumoti Bank Limited','Mutual Trust Bank Limited','National Bank of Pakistan (Pakistan)','National Credit & Commerce Bank Limited','NRB Bank Limited','NRBC Bank PLC','One Bank Limited','Premier Bank Limited','Prime Bank PLC','Pubali Bank Limited','Shahjalal Islami Bank PLC','Shimanto Bank Limited','Social Islami Bank PLC','South Bangla Agriculture and Commerce Bank Limited','Southeast Bank Limited','Standard Bank PLC','Standard Chartered Bank (United Kingdom)','State Bank of India (India)','Trust Bank PLC','Union Bank PLC','United Commercial Bank PLC','Uttara Bank PLC','Woori Bank (South Korea)'
                                ];
                            @endphp
                            <select name="bank_name" class="form-control form-control-sm select2" aria-label="Filter by bank">
                                <option value="">-- All Banks --</option>
                                @foreach($banks as $b)
                                    <option value="{{ $b }}" {{ request('bank_name') == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="import_type" class="form-control form-control-sm select2" aria-label="Filter by import type">
                                <option value="">-- All Import Types --</option>
                                <option value="Goods" {{ request('import_type') == 'Goods' ? 'selected' : '' }}>Goods</option>
                                <option value="Services" {{ request('import_type') == 'Services' ? 'selected' : '' }}>Services</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex">
                            <button class="btn btn-sm btn-primary me-2">Filter</button>
                            <a href="{{ route('btb-lcs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Contract</th>
                            <th>BTB LC No</th>
                            <th>Acceptance Date</th>
                            <th>Mature Date</th>
                            <th>Bank</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($btb as $row)
                            <tr>
                                <td>{{ $row->id }}</td>
                                <td>{{ optional($row->contract)->sales_contract_no }}</td>
                                <td>{{ $row->btb_lc_no }}</td>
                                    <td>{{ $row->aceptence_date ? \Carbon\Carbon::parse($row->aceptence_date)->format('d-M-Y') : '' }}</td>
                                    <td>{{ $row->mature_date ? \Carbon\Carbon::parse($row->mature_date)->format('d-M-Y') : '' }}</td>
                                <td>{{ $row->bank_name }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('btb-lcs.show', $row->id) }}" class="btn btn-sm btn-info" title="View">View</a>
                                        <a href="{{ route('btb-lcs.edit', $row->id) }}" class="btn btn-sm btn-warning" title="Edit">Edit</a>
                                        <form action="{{ route('btb-lcs.destroy', $row->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Delete">Delete</button>
                                        </form>
                                    </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $btb->links() }}</div>
            </div>
        </div>
    </div>
</x-backend.layouts.master>