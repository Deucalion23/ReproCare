@extends('midwife.layout')

@section('title', 'Child Checkups - Midwife Portal | ReproCare')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Child Checkups</h1>
            <p class="page-subtitle">{{ $child->full_name }} - Checkup History</p>
        </div>
        <a href="{{ route('midwife.child-checkups.create', $child->id) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Checkup
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($checkups->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Weight (kg)</th>
                            <th>Height (cm)</th>
                            <th>Head Circumference (cm)</th>
                            <th>Vaccinations</th>
                            <th>Conducted By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checkups as $checkup)
                        <tr>
                            <td>{{ $checkup->checkup_date->format('M d, Y') }}</td>
                            <td>{{ $checkup->weight ?? '-' }}</td>
                            <td>{{ $checkup->height ?? '-' }}</td>
                            <td>{{ $checkup->head_circumference ?? '-' }}</td>
                            <td>
                                @if($checkup->vaccinations_given)
                                    {{ implode(', ', $checkup->vaccinations_given) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $checkup->conductedBy->name ?? '-' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('midwife.child-checkups.show', [$child->id, $checkup->id]) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('midwife.child-checkups.edit', [$child->id, $checkup->id]) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <x-archive-form :action="route('midwife.child-checkups.destroy', [$child->id, $checkup->id])" label="" title="Archive checkup (retained for audit)" btnClass="btn btn-sm btn-warning text-white" icon="bi bi-archive" confirmText="Archive this child checkup? Growth and immunization history will be retained for audit." />
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                <p class="text-muted mt-3">No checkups recorded yet</p>
                <a href="{{ route('midwife.child-checkups.create', $child->id) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add First Checkup
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
