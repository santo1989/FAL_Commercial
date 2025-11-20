<x-backend.layouts.master>
    <x-slot name="pageTitle">Edit BTB LC</x-slot>

    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Edit BTB LC #{{ $btbLc->id }}</h3>
                <a href="{{ route('btb-lcs.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('btb-lcs.update', $btbLc->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Contract</label>
                            <select name="contract_id" class="form-control">
                                <option value="">-- Select Contract --</option>
                                @foreach($contracts as $c)
                                    <option value="{{ $c->id }}" {{ $btbLc->contract_id == $c->id ? 'selected' : '' }}>{{ $c->sales_contract_no }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Import Record (optional)</label>
                            <select name="import_id" class="form-control">
                                <option value="">-- Select Import --</option>
                                @foreach($imports as $imp)
                                    <option value="{{ $imp->id }}" {{ $btbLc->import_id == $imp->id ? 'selected' : '' }}>{{ optional($imp->salesContract)->sales_contract_no }} - {{ $imp->btb_lc_no }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Existing BTB/LC numbers (for selected contract)</label>
                            <select id="existing_btb_select" class="form-control">
                                <option value="">-- Select BTB/LC no --</option>
                            </select>
                            <small class="text-muted">Select a BTB/LC from this contract to auto-fill BTB LC No, date and import record.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>BTB LC No</label>
                            <input type="text" id="btb_lc_no_input" name="btb_lc_no" value="{{ $btbLc->btb_lc_no }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Date</label>
                            <input type="date" name="date" value="{{ optional($btbLc->date)->format('Y-m-d') }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Bank Name</label>
                            @php
                                $banks = [
                                    'AB Bank PLC',
                                    'Al-Arafah Islami Bank PLC',
                                    'Bangladesh Commerce Bank Limited',
                                    'Bank Al-Falah Limited (Pakistan)',
                                    'Bank Asia Limited',
                                    'Bengal Commercial Bank Limited',
                                    'BRAC Bank PLC',
                                    'City Bank PLC',
                                    'Commercial Bank of Ceylon PLC (Sri Lanka)',
                                    'Community Bank Bangladesh Limited',
                                    'Dhaka Bank Limited',
                                    'Dhaka Mercantile Co-Operative Bank Limited',
                                    'Dutch-Bangla Bank Limited',
                                    'Eastern Bank PLC',
                                    'First Security Islami Bank PLC',
                                    'Global Islami Bank PLC',
                                    'Habib Bank Limited (Pakistan)',
                                    'HSBC (United Kingdom)',
                                    'ICB Islamic Bank PLC',
                                    'IFIC Bank PLC',
                                    'Islami Bank Bangladesh PLC',
                                    'Jamuna Bank Limited',
                                    'Meghna Bank Limited',
                                    'Mercantile Bank PLC',
                                    'Midland Bank Limited',
                                    'Modhumoti Bank Limited',
                                    'Mutual Trust Bank Limited',
                                    'National Bank of Pakistan (Pakistan)',
                                    'National Credit & Commerce Bank Limited',
                                    'NRB Bank Limited',
                                    'NRBC Bank PLC',
                                    'One Bank Limited',
                                    'Premier Bank Limited',
                                    'Prime Bank PLC',
                                    'Pubali Bank Limited',
                                    'Shahjalal Islami Bank PLC',
                                    'Shimanto Bank Limited',
                                    'Social Islami Bank PLC',
                                    'South Bangla Agriculture and Commerce Bank Limited',
                                    'Southeast Bank Limited',
                                    'Standard Bank PLC',
                                    'Standard Chartered Bank (United Kingdom)',
                                    'State Bank of India (India)',
                                    'Trust Bank PLC',
                                    'Union Bank PLC',
                                    'United Commercial Bank PLC',
                                    'Uttara Bank PLC',
                                    'Woori Bank (South Korea)'
                                ];
                            @endphp
                            <select name="bank_name" class="form-control">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $b)
                                    <option value="{{ $b }}" {{ (old('bank_name', $btbLc->bank_name) == $b) ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Aceptence Date</label>
                            <input type="date" name="aceptence_date" value="{{ optional($btbLc->aceptence_date)->format('Y-m-d') }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Aceptence Value</label>
                            <input type="number" step="0.01" name="aceptence_value" value="{{ $btbLc->aceptence_value }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Aceptence Type</label>
                            <select name="aceptence_type" class="form-control">
                                <option value="">--</option>
                                <option {{ $btbLc->aceptence_type == 'DP' ? 'selected' : '' }}>DP</option>
                                <option {{ $btbLc->aceptence_type == 'Sight' ? 'selected' : '' }}>Sight</option>
                                <option {{ $btbLc->aceptence_type == 'USENCE' ? 'selected' : '' }}>USENCE</option>
                                <option {{ $btbLc->aceptence_type == 'EDF' ? 'selected' : '' }}>EDF</option>
                                <option {{ $btbLc->aceptence_type == 'UPAS' ? 'selected' : '' }}>UPAS</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Tenor Days</label>
                            <input type="number" name="tenor_days" value="{{ $btbLc->tenor_days }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Tenor Date Of (day number)</label>
                            <input type="number" name="tenor_date_of" value="{{ $btbLc->tenor_date_of }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Mature Date (auto if left blank)</label>
                            <input type="date" name="mature_date" value="{{ optional($btbLc->mature_date)->format('Y-m-d') }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Repayment Date</label>
                            <input type="date" name="repayment_date" value="{{ optional($btbLc->repayment_date)->format('Y-m-d') }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Repayment Value</label>
                            <input type="number" step="0.01" name="repayment_value" value="{{ $btbLc->repayment_value }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Closing Balance</label>
                            <input type="number" step="0.01" name="closing_balance" value="{{ $btbLc->closing_balance }}" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Proclument Type</label>
                            <select name="proclument_type" class="form-control">
                                <option value="">--</option>
                                <option value="local" {{ $btbLc->proclument_type == 'local' ? 'selected' : '' }}>Local</option>
                                <option value="overseas" {{ $btbLc->proclument_type == 'overseas' ? 'selected' : '' }}>Overseas</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Import Type</label>
                            <select name="import_type" class="form-control">
                                <option value="">--</option>
                                <option {{ $btbLc->import_type == 'Goods' ? 'selected' : '' }}>Goods</option>
                                <option {{ $btbLc->import_type == 'Services' ? 'selected' : '' }}>Services</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        (function(){
            const contractSelect = document.querySelector('select[name="contract_id"]');
            const existingSelect = document.getElementById('existing_btb_select');
            const btbInput = document.getElementById('btb_lc_no_input');
            const dateInput = document.querySelector('input[name="date"]');
            const importSelect = document.querySelector('select[name="import_id"]');

            async function loadExisting(contractId, preselectImportId){
                existingSelect.innerHTML = '<option value="">-- Select BTB/LC no --</option>';
                if(!contractId) return;
                try{
                    const res = await fetch('/btb-lcs/imports-by-contract/' + contractId);
                    if(!res.ok) return;
                    const data = await res.json();
                    data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.import_id;
                        opt.dataset.btb = item.btb_lc_no;
                        opt.dataset.date = item.date || '';
                        opt.textContent = item.btb_lc_no + (item.date ? ' ('+item.date+')' : '');
                        existingSelect.appendChild(opt);
                    });
                    if(preselectImportId){
                        const found = Array.from(existingSelect.options).find(o => o.value == preselectImportId);
                        if(found){
                            existingSelect.value = preselectImportId;
                            // trigger change to populate fields
                            const ev = new Event('change');
                            existingSelect.dispatchEvent(ev);
                        }
                    }
                }catch(e){
                    console.error(e);
                }
            }

            contractSelect && contractSelect.addEventListener('change', function(){
                loadExisting(this.value);
            });

            existingSelect && existingSelect.addEventListener('change', function(){
                const sel = this.options[this.selectedIndex];
                if(!sel || !sel.dataset) return;
                const btb = sel.dataset.btb || '';
                const date = sel.dataset.date || '';
                const importId = sel.value || '';
                if(btbInput) btbInput.value = btb;
                if(dateInput && date) dateInput.value = date;
                if(importSelect){
                    const opt = Array.from(importSelect.options).find(o => o.value === importId);
                    if(opt){ importSelect.value = importId; }
                }
            });

            // on load, populate existing list for current contract and preselect
            document.addEventListener('DOMContentLoaded', function(){
                const cId = contractSelect ? contractSelect.value : null;
                const preImport = '{{ $btbLc->import_id ?? '' }}';
                if(cId) loadExisting(cId, preImport);
            });
        })();
    </script>
</x-backend.layouts.master>