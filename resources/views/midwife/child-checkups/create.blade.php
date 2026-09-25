@extends('midwife.layout')

@section('title', 'Add Child Checkup - Midwife Portal | ReproCare')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">Add Child Checkup</h1>
            <p class="page-subtitle">{{ $child->full_name }}</p>
        </div>
        <a href="{{ route('midwife.child-checkups.index', $child->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('midwife.child-checkups.store', $child->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Checkup Date *</label>
                        <input type="date" name="checkup_date" class="form-control" required value="{{ old('checkup_date', now()->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" class="form-control" value="{{ old('weight') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" step="0.01" name="height" class="form-control" value="{{ old('height') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Head Circumference (cm)</label>
                        <input type="number" step="0.01" name="head_circumference" class="form-control" value="{{ old('head_circumference') }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Developmental Milestones</label>
                        <textarea name="developmental_milestones" class="form-control" rows="3">{{ old('developmental_milestones') }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Vaccinations Given</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="BCG" class="form-check-input" id="bcg">
                                    <label class="form-check-label" for="bcg">BCG</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="Hepatitis B" class="form-check-input" id="hepb">
                                    <label class="form-check-label" for="hepb">Hepatitis B</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="Pentavalent" class="form-check-input" id="penta">
                                    <label class="form-check-label" for="penta">Pentavalent</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="OPV" class="form-check-input" id="opv">
                                    <label class="form-check-label" for="opv">OPV</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="PCV" class="form-check-input" id="pcv">
                                    <label class="form-check-label" for="pcv">PCV</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="IPV" class="form-check-input" id="ipv">
                                    <label class="form-check-label" for="ipv">IPV</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="MMR" class="form-check-input" id="mmr">
                                    <label class="form-check-label" for="mmr">MMR</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="vaccinations_given[]" value="Vitamin A" class="form-check-input" id="vita">
                                    <label class="form-check-label" for="vita">Vitamin A</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Checkup
                </button>
                <a href="{{ route('midwife.child-checkups.index', $child->id) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
