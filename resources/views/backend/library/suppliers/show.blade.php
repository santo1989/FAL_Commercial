<x-backend.layouts.master>
    <x-slot name="pageTitle">
        Supplier Inforomation
    </x-slot>

    <x-slot name='breadCrumb'>
        <x-backend.layouts.elements.breadcrumb>
            <x-slot name="pageHeader"> Supplier </x-slot>

            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}">Supplier</a></li>
        </x-backend.layouts.elements.breadcrumb>
    </x-slot>

    <section class="content">
        <div class="container-fluid">

            @if (session('message'))
                <div class="alert alert-success">
                    <span class="close" data-dismiss="alert">&times;</span>
                    <strong>{{ session('message') }}.</strong>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">

                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            {{-- supplier Table goes here --}}
                            <table class="table table-bordered">
                                <tr>
                                    <th>Division</th>
                                    <td>{{ $supplier->division->name }}</td>
                                </tr>
                                <tr>
                                    <th>Company</th>
                                    <td>{{ $supplier->company->name }}</td>
                                </tr> 
                                <tr>
                                    <th>Supplier name</th>
                                    <td>{{ $supplier->name }}</td>
                                </tr>

                            </table>

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->


                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
</x-backend.layouts.master>
