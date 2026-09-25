@extends('midwife.layout')

@section('title', 'Edit Child Checkup - Midwife Portal | ReproCare')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Edit Child Checkup</h1>
            <p class="page-subtitle">{{ $child->full_name }} - {{ $checkup->checkup_date->format('M d, Y') }}</p>
        </div>
        <a href="{{ route('midwife.child-checkups.index', $child->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('midwife.child-checkups.update', [$child->id, $checkup->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Checkup Date *</label>
                        <input type="date" name="checkup_date" class="form-control" required value="{{ old('checkup_date', $checkup->checkup_date->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" class="form-control" value="{{ old('weight', $checkup->weight) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" step="0.01" name="height" class="form-control" value="{{ old('height', $checkup->height) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Head Circumference (cm)</label>
                        <input type="number" step="0.01" name="head_circumference" class="form-control" value="{{ old('head_circumference', $checkup->head_circumference) }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Developmental Milestones</label>
                        <textarea name="developmental_milestones" class="form-control" rows="3">{{ old('developmental_milestones', $checkup->developmental_milestones) }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Vaccinations Given</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="BCG" class="form-check-input" id="bcg" @if(in_array('BCG', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="bcg">BCG</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="Hepatitis B" class="form-check-input" id="hepb" @if(in_array('Hepatitis B', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="hepb">Hepatitis B</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="Pentavalent" class="form-check-input" id="penta" @if(in_array('Pentavalent', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="penta">Pentavalent</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="OPV" class="form-check-input" id="opv" @if(in_array('OPV', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="opv">OPV</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="PCV" class="form-check-input" id="pcv" @if(in_array('PCV', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="pcv">PCV</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="IPV" class="form-check-input" id="ipv" @if(in_array('IPV', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="ipv">IPV</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="MMR" class="form-check-input" id="mmr" @if(in_array('MMR', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="mmr">MMR</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="Vitamin A" class="form-check-input" id="vita" @if(in_array('Vitamin A', $checkup->vaccinations_given ?? [])) checked @endif>
                                    <label class="form-check-label" for="vita">Vitamin A</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $checkup->notes) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Checkup
                </button>
                <a href="{{ route('midwife.child-checkups.show', [$child->id, $checkup->id]) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
