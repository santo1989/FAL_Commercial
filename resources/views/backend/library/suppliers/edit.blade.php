<x-backend.layouts.master>
    <x-slot name="pageTitle">
        Edit Supplier Information
    </x-slot>

    <x-slot name='breadCrumb'>
        <x-backend.layouts.elements.breadcrumb>
            <x-slot name="pageHeader"> Supplier </x-slot>
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Supplier</a></li>
            <li class="breadcrumb-item active">Edit Supplier Information</li>
        </x-backend.layouts.elements.breadcrumb>
    </x-slot>


    <x-backend.layouts.elements.errors />
    <form action="{{ route('suppliers.update', ['supplier' => $supplier->id]) }}" method="post"
        enctype="multipart/form-data">
        <div class="pb-3">
            @csrf
            @method('put')
            @php
                $divisions = App\Models\Division::all();
            @endphp

            <div class="form-group">
                <label for="division_id">Division Name</label>
                <select name="division_id" id="division_id" class="form-control">
                    <option value="">Select Division</option>
                    @foreach ($divisions as $divisions)
                        <option value="{{ $divisions->id }}"
                            {{ $supplier->division_id == $divisions->id ? 'selected' : '' }}>
                            {{ $divisions->name }}</option>
                    @endforeach
                </select>
            </div>
            <br>

            @php
                $companies = App\Models\Company::all();
            @endphp
            <div class="form-group">
                <label for="company_id">Company Name</label>
                <select name="company_id" id="company_id" class="form-control">
                    <option value="">Select Company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}"
                            {{ $supplier->company_id == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <br>
            <br>
            <x-backend.form.input name="name" type="text" label="Supplier Name" :value="$supplier->name" />



            <br>

            <x-backend.form.saveButton>Save</x-backend.form.saveButton>
        </div>
    </form>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#division_id').on('change', function() {
                var divisionId = $(this).val();

                if (divisionId) {
                    $.ajax({
                        url: '/get-company-designation/' + divisionId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            console.log(data);
                            const companySelect = $('#company_id');

                            companySelect.empty();
                            companySelect.append('<option value="">Select Company</option>');
                            $.each(data.company, function(index, company) {
                                companySelect.append(
                                    `<option value="${company.id}">${company.name}</option>`
                                );
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    alert('Select a division first for company list');
                }
            });
        });
    </script>
</x-backend.layouts.master>
