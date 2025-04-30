<div class="btn-group">
    @isset($editRoute)
    <a href="{{ $editRoute }}" class="btn btn-sm btn-warning" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    @endisset
    
    @isset($deleteRoute)
    <form action="{{ $deleteRoute }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" 
                onclick="return confirm('Are you sure?')" title="Delete">
            <i class="fas fa-trash"></i>
        </button>
    </form>
    @endisset
</div>