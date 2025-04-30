<div class="form-row">
    <div class="form-group col-md-6">
        <label for="contract_id">Contract <span class="text-danger">*</span></label>
        <select name="contract_id" id="contract_id" 
                class="form-control @error('contract_id') is-invalid @enderror" required>
            <option value="">Select Contract</option>
            @foreach($contracts as $contract)
                <option value="{{ $contract->id }}" 
                    {{ (old('contract_id', $export->contract_id ?? '') == $contract->id) ? 'selected' : '' }}>
                    {{ $contract->sales_contract_no }} - {{ $contract->buyer_name }}
                </option>
            @endforeach
        </select>
        @error('contract_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="invoice_no">Invoice No. <span class="text-danger">*</span></label>
        <input type="text" name="invoice_no" id="invoice_no" 
               class="form-control @error('invoice_no') is-invalid @enderror" 
               value="{{ old('invoice_no', $export->invoice_no ?? '') }}" required>
        @error('invoice_no')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="export_bill_no">Export Bill No. <span class="text-danger">*</span></label>
        <input type="text" name="export_bill_no" id="export_bill_no" 
               class="form-control @error('export_bill_no') is-invalid @enderror" 
               value="{{ old('export_bill_no', $export->export_bill_no ?? '') }}" required>
        @error('export_bill_no')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="amount_usd">Amount (USD) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="amount_usd" id="amount_usd" 
               class="form-control @error('amount_usd') is-invalid @enderror" 
               value="{{ old('amount_usd', $export->amount_usd ?? '') }}" required>
        @error('amount_usd')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="realized_value">Realized Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="realized_value" id="realized_value" 
               class="form-control @error('realized_value') is-invalid @enderror" 
               value="{{ old('realized_value', $export->realized_value ?? '') }}" required>
        @error('realized_value')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-4">
        <label for="g_qty_pcs">Quantity (PCS) <span class="text-danger">*</span></label>
        <input type="number" name="g_qty_pcs" id="g_qty_pcs" 
               class="form-control @error('g_qty_pcs') is-invalid @enderror" 
               value="{{ old('g_qty_pcs', $export->g_qty_pcs ?? '') }}" required>
        @error('g_qty_pcs')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-4">
        <label for="date_of_realized">Realized Date <span class="text-danger">*</span></label>
        <input type="date" name="date_of_realized" id="date_of_realized" 
               class="form-control @error('date_of_realized') is-invalid @enderror" 
               value="{{ old('date_of_realized', $export->date_of_realized ?? '') }}" required>
        @error('date_of_realized')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>