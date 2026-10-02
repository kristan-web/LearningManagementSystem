@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">School Years</h1>
        <a href="{{ route('admin.school-years.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> New School Year
        </a>
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#rolloverModal">
            <i class="bi bi-arrow-repeat"></i> Perform Rollover
        </button>

    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schoolYears as $schoolYear)
                        <tr>
                            <td>{{ $schoolYear->year }}</td>
                            <td>
                                <span class="badge bg-{{ $schoolYear->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($schoolYear->status) }}
                                </span>
                            </td>
                            <td>{{ $schoolYear->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.school-years.edit', $schoolYear) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.school-years.destroy', $schoolYear) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this school year? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Rollover Modal -->
<div class="modal fade" id="rolloverModal" tabindex="-1" aria-labelledby="rolloverModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.school-years.rollover') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rolloverModalLabel">Perform School Year Rollover</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>This will close the current active school year and start a new one. Are you sure?</p>
                    <div class="mb-3">
                        <label for="new_year" class="form-label">New School Year (e.g., 2026-2027)</label>
                        <input type="text" class="form-control" id="new_year" name="new_year" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Rollover</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
