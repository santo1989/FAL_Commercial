<x-backend.layouts.master>
    <x-slot name="pageTitle">BTB LC #{{ $item->id }}</x-slot>

    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <h3 class="mb-0">BTB LC #{{ $item->id }}</h3>
                <a href="{{ route('btb-lcs.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Contract</dt>
                    <dd class="col-sm-9">{{ optional($item->contract)->sales_contract_no }}</dd>

                    <dt class="col-sm-3">BTB LC No</dt>
                    <dd class="col-sm-9">{{ $item->btb_lc_no }}</dd>

                    <dt class="col-sm-3">Aceptence Date</dt>
                    <dd class="col-sm-9">{{ optional($item->aceptence_date)->format('d-M-Y') }}</dd>

                    <dt class="col-sm-3">Tenor (days)</dt>
                    <dd class="col-sm-9">{{ $item->tenor_days }}</dd>

                    <dt class="col-sm-3">Mature Date</dt>
                    <dd class="col-sm-9">{{ optional($item->mature_date)->format('d-M-Y') }}</dd>

                    <dt class="col-sm-3">Bank</dt>
                    <dd class="col-sm-9">{{ $item->bank_name }}</dd>
                </dl>
            </div>
        </div>
    </div>
</x-backend.layouts.master>