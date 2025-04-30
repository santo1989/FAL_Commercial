<div class="form-row">
    <div class="form-group col-md-6">
        <label for="contract_id">Contract <span class="text-danger">*</span></label>
        <select name="contract_id" id="contract_id" 
                class="form-control @error('contract_id') is-invalid @enderror" required>
            <option value="">Select Contract</option>
            @foreach($contracts as $contract)
                <option value="{{ $contract->id }}" 
                    {{ (old('contract_id', $import->contract_id ?? '') == $contract->id) ? 'selected' : '' }}>
                    {{ $contract->sales_contract_no }} - {{ $contract->buyer_name }}
                </option>
            @endforeach
        </select>
        @error('contract_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="btb_lc_no">BTB/LC No.</label>
        <input type="text" name="btb_lc_no" id="btb_lc_no" 
               class="form-control @error('btb_lc_no') is-invalid @enderror" 
               value="{{ old('btb_lc_no', $import->btb_lc_no ?? '') }}">
        @error('btb_lc_no')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="date">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" id="date" 
               class="form-control @error('date') is-invalid @enderror" 
               value="{{ old('date', $import->date ?? '') }}" required>
        @error('date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-6">
        <label for="description">Description</label>
        <input type="text" name="description" id="description" 
               class="form-control @error('description') is-invalid @enderror" 
               value="{{ old('description', $import->description ?? '') }}">
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="fabric_value">Fabric Value</label>
        <input type="number" step="0.01" name="fabric_value" id="fabric_value" 
               class="form-control @error('fabric_value') is-invalid @enderror" 
               value="{{ old('fabric_value', $import->fabric_value ?? 0 ) }}">
        @error('fabric_value')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-4">
        <label for="accessories_value">Accessories Value</label>
        <input type="number" step="0.01" name="accessories_value" id="accessories_value" 
               class="form-control @error('accessories_value') is-invalid @enderror" 
               value="{{ old('accessories_value', $import->accessories_value ?? 0 ) }}">
        @error('accessories_value')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-4">
        <label for="fabric_qty_kg">Fabric Qty (KG)</label>
        <input type="number" step="0.01" name="fabric_qty_kg" id="fabric_qty_kg" 
               class="form-control @error('fabric_qty_kg') is-invalid @enderror" 
               value="{{ old('fabric_qty_kg', $import->fabric_qty_kg ?? 0 ) }}">
        @error('fabric_qty_kg')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>