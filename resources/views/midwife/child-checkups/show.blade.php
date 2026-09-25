@extends('midwife.layout')

@section('title', 'Child Checkup Details - Midwife Portal | ReproCare')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Checkup Details</h1>
            <p class="page-subtitle">{{ $child->full_name }} - {{ $checkup->checkup_date->format('M d, Y') }}</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('midwife.child-checkups.edit', [$child->id, $checkup->id]) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('midwife.child-checkups.index', $child->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Measurements</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Weight</p>
                        <h4>{{ $checkup->weight ?? '-' }} kg</h4>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Height</p>
                        <h4>{{ $checkup->height ?? '-' }} cm</h4>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted mb-1">Head Circumference</p>
                        <h4>{{ $checkup->head_circumference ?? '-' }} cm</h4>
                    </div>
                </div>
            </div>
        </div>

        @if($checkup->vaccinations_given)
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Vaccinations Given</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($checkup->vaccinations_given as $vaccine)
                    <span class="badge bg-primary">{{ $vaccine }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($checkup->developmental_milestones)
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Developmental Milestones</h5>
            </div>
            <div class="card-body">
                <p>{{ $checkup->developmental_milestones }}</p>
            </div>
        </div>
        @endif

        @if($checkup->notes)
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Notes</h5>
            </div>
            <div class="card-body">
                <p>{{ $checkup->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Checkup Info</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-1">Date</p>
                <h5>{{ $checkup->checkup_date->format('M d, Y') }}</h5>
                <hr>
                <p class="text-muted mb-1">Conducted By</p>
                <h5>{{ $checkup->conductedBy->name ?? '-' }}</h5>
                <hr>
                <p class="text-muted mb-1">Recorded At</p>
                <h5>{{ $checkup->created_at->format('M d, Y g:i A') }}</h5>
            </div>
        </div>
    </div>
</div>
@endsection
