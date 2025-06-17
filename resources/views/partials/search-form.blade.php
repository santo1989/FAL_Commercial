<div class="row mb-4">
    <div class="col-md-12">
        <form method="GET" action="{{ url()->current() }}">
            <div class="input-group">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search..." value="{{ request('search') }}">
                <div class="input-group-append">
                    <button class="btn btn-outline-danger ml-1" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary ml-1"
                       title="Clear Search" style="text-decoration: none;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>