<x-backend.layouts.master>
 <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Upload Excel</div>

                <div class="card-body">
                    <a href="{{ route('import-template') }}" class="btn btn-success mb-3">
                        Download Template
                    </a>

                    <form action="{{ route('import.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <input type="file" name="file" class="form-control" required>
                        </div>
                        <button class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-backend.layouts.master>
