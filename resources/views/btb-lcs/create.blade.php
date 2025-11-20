<x-backend.layouts.master>
    <x-slot name="pageTitle">Create BTB LC</x-slot>

    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col-md-12 d-flex justify-content-between align-items-center">
                <h3 class="mb-0">New BTB LC</h3>
                <a href="{{ route('btb-lcs.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('btb-lcs.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Contract</label>
                            <select name="contract_id" class="form-control">
                                <option value="">-- Select Contract --</option>
                                @foreach($contracts as $c)
                                    <option value="{{ $c->id }}">{{ $c->sales_contract_no }}</option>
                                @endforeach
                            </select>
                        </div>

                        

                        <div class="col-md-4 mb-3">
                            <label>Existing BTB/LC numbers (for selected contract)</label>
                            <select id="existing_btb_select" class="form-control form-control-sm select2">
                                <option value="">-- Select BTB/LC no --</option>
                            </select>
                            <small class="text-muted">Select a BTB/LC from this contract to auto-fill BTB LC No, date and import record.</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>BTB LC No</label>
                            <input type="text" id="btb_lc_no_input" name="btb_lc_no" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Date</label>
                            <input type="date" id="btb_lc_date_input" name="date" class="form-control" readonly>
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
                            <select id="bank_name_select" name="bank_name" class="form-control">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $b)
                                    <option value="{{ $b }}" {{ old('bank_name') == $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Aceptence Date</label>
                            <input type="date" name="aceptence_date" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Aceptence Value</label>
                            <input type="number" step="0.01" name="aceptence_value" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Aceptence Type</label>
                            <select id="aceptence_type_select" name="aceptence_type" class="form-control">
                                <option value="">--</option>
                                <option>DP</option>
                                <option>Sight</option>
                                <option>USENCE</option>
                                <option>EDF</option>
                                <option>UPAS</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Tenor Days</label>
                            <input type="number" name="tenor_days" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Exten Tenor Date Of (day number)</label>
                            <input type="number" name="tenor_date_of" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Mature Date (auto if left blank)</label>
                            <input type="date" name="mature_date" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Repayment Date</label>
                            <input type="date" name="repayment_date" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Repayment Value</label>
                            <input type="number" step="0.01" name="repayment_value" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Closing Balance</label>
                            <input type="number" step="0.01" name="closing_balance" class="form-control">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Proclument Type</label>
                            <select id="proclument_type_select" name="proclument_type" class="form-control">
                                <option value="">--</option>
                                <option value="local">Local</option>
                                <option value="overseas">Overseas</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Import Type</label>
                            <select name="import_type" class="form-control">
                                <option value="">--</option>
                                <option>Fabric</option>
                                <option>Accessories</option>
                                <option>Packing</option>
                                <option>Print & Embroidery</option>
                                <option>Services</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const contractSelect = document.querySelector('select[name="contract_id"]');
            const existingSelect = document.getElementById('existing_btb_select');
            const btbInput = document.getElementById('btb_lc_no_input');
            const dateInput = document.getElementById('btb_lc_date_input');

            // base URL for imports-by-contract (use url() for correct base)
            const baseUrl = '{{ url('btb-lcs/imports-by-contract') }}';

            async function loadExisting(contractId) {
                if (!existingSelect) return;
                existingSelect.innerHTML = '<option value="">-- Select BTB/LC no --</option>';
                if (!contractId) return;
                try {
                    const url = baseUrl + '/' + encodeURIComponent(contractId);
                    console && console.log && console.log('Fetching existing BTB/LCs from', url);
                    // Include credentials so the session cookie is sent (routes are behind auth middleware)
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                    if (!res.ok) {
                        // Try to log response body for easier debugging (e.g., HTML login page)
                        let body = '';
                        try { body = await res.text(); } catch (e) { body = ''; }
                        console && console.error && console.error('Fetch failed', res.status, res.statusText, body);
                        return;
                    }
                    // Attempt to parse JSON, but guard if server returned HTML (e.g. redirect to login)
                    let data;
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.indexOf('application/json') === -1) {
                        const txt = await res.text();
                        console && console.error && console.error('Expected JSON but got:', txt.substring(0, 200));
                        return;
                    }
                    data = await res.json();
                    if (!Array.isArray(data)) return;
                    data.forEach(item => {
                        const opt = document.createElement('option');
                        // set option value to the BTB/LC number so selection directly maps to the readonly field
                        opt.value = item.btb_lc_no || '';
                        opt.dataset.btb = item.btb_lc_no || '';
                        opt.dataset.date = item.date || '';
                        opt.textContent = (item.btb_lc_no || '') + (item.date ? ' (' + item.date + ')' : '');
                        existingSelect.appendChild(opt);
                    });
                    // trigger Select2 update if present
                    if (window.jQuery && $(existingSelect).data('select2')) {
                        $(existingSelect).trigger('change.select2');
                    }
                } catch (e) {
                    console && console.error && console.error('Error loading existing BTB/LCs', e);
                }
            }

            if (contractSelect) {
                contractSelect.addEventListener('change', function () {
                    loadExisting(this.value);
                    if (btbInput) btbInput.value = '';
                    if (dateInput) dateInput.value = '';
                });
                // on page load, if a contract is preselected, load existing items
                if (contractSelect.value) {
                    loadExisting(contractSelect.value);
                }
            }

            if (existingSelect) {
                existingSelect.addEventListener('change', function () {
                    const sel = this.options[this.selectedIndex];
                    if (!sel) return;
                    const btb = sel.dataset ? sel.dataset.btb || '' : '';
                    const date = sel.dataset ? sel.dataset.date || '' : '';
                    if (btbInput) btbInput.value = btb;
                    if (dateInput && date) dateInput.value = date;

                    // Auto-select bank, aceptence_type and proclument_type based on BTB/LC number pattern
                    try {
                        const bankSelect = document.getElementById('bank_name_select');
                        const aceptSelect = document.getElementById('aceptence_type_select');
                        const proclSelect = document.getElementById('proclument_type_select');

                            if (btb && typeof btb === 'string') {
                            // keep only digits for pattern parsing
                            const digits = btb.replace(/\D/g, '');
                            // need at least 8 digits to inspect prefix(4) and code at positions 7-8 (0-based 6-7)
                            if (digits.length >= 8) {
                                const prefix4 = digits.substring(0, 4);
                                // per example and spec: use 7th-8th digits (1-based) => indices 6 and 7 (0-based)
                                const code89 = digits.substring(6, 8);

                                // Bank mapping: 1559 -> Prime Bank PLC, 1555 -> BRAC Bank PLC
                                let bankName = null;
                                if (prefix4 === '1559') bankName = 'Prime Bank PLC';
                                else if (prefix4 === '1555') bankName = 'BRAC Bank PLC';

                                if (bankName && bankSelect) {
                                    const opt = Array.from(bankSelect.options).find(o => o.value === bankName);
                                    if (opt) {
                                        bankSelect.value = bankName;
                                        if (window.jQuery && $(bankSelect).data('select2')) {
                                            $(bankSelect).trigger('change');
                                        }
                                    }
                                }

                                // Acceptance & Procurement mapping from code89
                                let acept = null, procl = null;
                                if (code89 === '03') { acept = 'EDF'; procl = 'local'; }
                                else if (code89 === '05') { acept = 'EDF'; procl = 'overseas'; }
                                else if (code89 === '04') { acept = 'USENCE'; procl = 'local'; }
                                else if (code89 === '06') { acept = 'USENCE'; procl = 'overseas'; }

                                if (acept && aceptSelect) {
                                    // find option by value or by displayed text
                                    const opt = Array.from(aceptSelect.options).find(o => o.value === acept || o.textContent.trim() === acept);
                                    if (opt) {
                                        aceptSelect.value = opt.value;
                                        if (window.jQuery && $(aceptSelect).data('select2')) $(aceptSelect).trigger('change');
                                    }
                                }

                                if (procl && proclSelect) {
                                    const opt = Array.from(proclSelect.options).find(o => o.value === procl || o.textContent.trim().toLowerCase() === procl);
                                    if (opt) {
                                        proclSelect.value = opt.value;
                                        if (window.jQuery && $(proclSelect).data('select2')) $(proclSelect).trigger('change');
                                    }
                                }
                            }
                        }
                    } catch (e) {
                        console && console.error && console.error('Auto-select mapping error', e);
                    }
                });
            }
        });
    </script>
</x-backend.layouts.master>