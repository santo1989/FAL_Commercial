<div class="form-row">
    <div class="form-group col-md-4">
        <label for="buyer_id">Buyer Name <span class="text-danger">*</span></label>
        @php
            $buyers = \App\Models\Buyer::all(); // Fetch all buyers from the database
            $selectedBuyer = old('buyer_id', $contract->buyer_id ?? ''); // Get the selected buyer ID from the old input or contract data
        @endphp
        <select name="buyer_id" id="buyer_id" class="form-control @error('buyer_id') is-invalid @enderror" required>
            <option value="">Select Buyer</option>
            @foreach($buyers as $buyer)
                <option value="{{ $buyer->id }}" {{ (old('buyer_id', $contract->buyer_id ?? '') == $buyer->id) ? 'selected' : '' }}>
                    {{ $buyer->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group col-md-4">
        <label for="sales_contract_no">Contract No. <span class="text-danger">*</span></label>
        <input type="text" name="sales_contract_no" id="sales_contract_no" 
               class="form-control @error('sales_contract_no') is-invalid @enderror" 
               value="{{ old('sales_contract_no', $contract->sales_contract_no ?? '') }}" required>
        @error('sales_contract_no')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="contract_date">Contract Date. <span class="text-danger">*</span></label>
        <input type="date" name="contract_date" id="contract_date" 
               class="form-control @error('contract_date') is-invalid @enderror" 
               value="{{ old('contract_date', $contract->contract_date ?? '') }}" required>
        @error('contract_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="sales_contract_value">Contract Value <span class="text-danger">*</span></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">$</span>
            </div>
            <input type="number" step="0.01" name="sales_contract_value" id="sales_contract_value" 
                   class="form-control @error('sales_contract_value') is-invalid @enderror" 
                   value="{{ old('sales_contract_value', $contract->sales_contract_value ?? '') }}" required>
        </div>
        @error('sales_contract_value')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-4">
        <label for="quantity_pcs">Quantity (PCS) <span class="text-danger">*</span></label>
        <input type="number" name="quantity_pcs" id="quantity_pcs" 
               class="form-control @error('quantity_pcs') is-invalid @enderror" 
               value="{{ old('quantity_pcs', $contract->quantity_pcs ?? '') }}" required>
        @error('quantity_pcs')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <!--FOB value came from quantity_pcs / sales_contract_value -->
     <div class="form-group col-md-4">
        <label for="fob">FOB <span class="text-danger">*</span></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">$</span>
            </div>
            <input type="number" step="0.0001" name="fob" id="fob" 
                   class="form-control @error('fob') is-invalid @enderror" 
                   value="{{ old('fob', $contract->fob ?? '') }}" required readonly>
        </div>
        @error('fob')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>


<div class="form-row">
   <!-- Revised_Contract_details is yes or no selection, defult is no -->
    <div class="form-group col-md-4">
        <label for="revised_contract_details">Revised Contract Details <span class="text-danger">*</span></label>
        <select name="revised_contract_details" id="revised_contract_details" 
                class="form-control @error('revised_contract_details') is-invalid @enderror" required>
            <option value="no" {{ (old('revised_contract_details', $contract->revised_contract_details ?? 'no') == 'no') ? 'selected' : '' }}>No</option>
            <option value="yes" {{ (old('revised_contract_details', $contract->revised_contract_details ?? '') == 'yes') ? 'selected' : '' }}>Yes</option>
        </select>
        @error('revised_contract_details')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>


<script>
    $(document).ready(function() {
        // must be sales_contract_value and quantity_pcs is greater than 0 and fob is readonly and dynamically calculated 
        $('#sales_contract_value, #quantity_pcs').on('input', function() {
            var salesContractValue = parseFloat($('#sales_contract_value').val()) || 0;
            var quantityPcs = parseFloat($('#quantity_pcs').val()) || 0;
            
            if (salesContractValue > 0 && quantityPcs > 0) {
                var fobValue = (salesContractValue / quantityPcs).toFixed(3); // Calculate FOB value
                $('#fob').val(fobValue); // Set the FOB value in the input field
            } else {
                $('#fob').val('0.000'); // Clear the FOB value if inputs are invalid
            }
        });
        // Trigger the input event on page load to calculate FOB if values are already present 
        $('#sales_contract_value, #quantity_pcs').trigger('input');
        //hold the all input fields and select fields in the form and check if the value is empty or not, if empty then show the error message and set the focus on the first empty field and save data in browser local storage until the user fill all the fields and click on save button
        $('form').on('submit', function(e) {
            var isValid = true;
            $(this).find('input, select').each(function() {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).addClass('is-invalid'); // Add invalid class to empty fields
                    $(this).focus(); // Set focus on the first empty field
                    return false; // Break the loop
                } else {
                    $(this).removeClass('is-invalid'); // Remove invalid class if field is filled
                }
            });
            if (!isValid) {
                e.preventDefault(); // Prevent form submission if there are empty fields
            }
        });
        // Save form data in local storage
        $('input, select').on('change', function() {
            var formData = $(this).closest('form').serializeArray();
            var data = {};
            $.each(formData, function(index, field) {
                data[field.name] = field.value;
            });
            localStorage.setItem('salesContractFormData', JSON.stringify(data)); // Save form data in local storage
        });
        // Load form data from local storage on page load
        var savedData = localStorage.getItem('salesContractFormData');
        if (savedData) {
            var data = JSON.parse(savedData);
            $.each(data, function(name, value) {
                $('input[name="' + name + '"], select[name="' + name + '"]').val(value); // Set saved values in the form fields
            });
        }
        // Clear local storage on form submission
        $('form').on('submit', function() {
            localStorage.removeItem('salesContractFormData'); // Clear local storage on form submission
        });
        // Clear local storage on page unload
        $(window).on('beforeunload', function() {
            localStorage.removeItem('salesContractFormData'); // Clear local storage on page unload
        });
    });
       
</script>