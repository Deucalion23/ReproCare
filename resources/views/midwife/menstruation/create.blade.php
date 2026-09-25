@extends('midwife.layout')

@section('title', 'Add Menstruation Record - ReproCare')

@section('midwife-content')
<div class="py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Menstruation Record</h1>
        <a href="{{ route('midwife.menstruation.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Records
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">New Cycle Record</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('midwife.menstruation.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Patient</label>
                        <select class="form-select" name="user_id" required>
                            <option value="">Select Patient</option>
                            @foreach($women as $w)
                                <option value="{{ $w->id }}" {{ (string) old('user_id', $woman?->id ?? '') === (string) $w->id ? 'selected' : '' }}>
                                    {{ $w->name }} ({{ $w->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Period Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="{{ old('start_date') }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Period End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ old('end_date') }}">
                        <small class="form-text text-muted">Leave blank if still ongoing</small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Save Record</button>
                    <a href="{{ route('midwife.menstruation.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
