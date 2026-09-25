@extends('rhu.layout')

@section('title', 'Request Supplies - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Request Supplies
            </div>
            <p class="page-hero-subtitle">
                Submit a new inventory replenishment request to the City Health Office.
            </p>
        </div>
        <a href="{{ route('rhu.supply-requests.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Supply Requests
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
        <h6 class="alert-heading fw-bold mb-2">Please resolve the following errors:</h6>
        <ul class="mb-0 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card fade-in-card">
    <div class="card-body">
        <form method="POST" action="{{ route('rhu.supply-requests.store') }}">
            @csrf

            @php($catalog = config('supply_catalog'))
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="supply-category" class="form-label required-label">Category</label>
                    <select id="supply-category" name="supply_category" class="form-select" required>
                        <option value="">Select category</option>
                        @foreach($catalog['categories'] as $key => $category)
                            <option value="{{ $key }}" @selected(old('supply_category') === $key)>{{ $category['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="supply-name" class="form-label required-label">Item / Supply Name</label>
                    <select id="supply-name" name="supply_name" class="form-select" required data-selected="{{ old('supply_name') }}">
                        <option value="">Select an item</option>
                        @foreach($catalog['categories'] as $category)
                            <optgroup label="{{ $category['label'] }}">
                                @foreach($category['items'] as $name => $units)<option value="{{ $name }}" @selected(old('supply_name') === $name)>{{ $name }}</option>@endforeach
                            </optgroup>
                        @endforeach
                        <option value="__other__" @selected(old('supply_name') === '__other__')>Other item (specify)</option>
                    </select>
                    <small class="text-muted">Choose a category first to see matching supplies.</small>
                </div>
                <div class="col-12" id="other-supply-field">
                    <label for="supply-name-other" class="form-label">Other item name</label>
                    <input id="supply-name-other" name="supply_name_other" class="form-control" maxlength="255" value="{{ old('supply_name_other') }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required-label">Quantity Requested</label>
                    <input type="number" name="quantity_requested" class="form-control" value="{{ old('quantity_requested') }}" min="1" placeholder="e.g. 500" required>
                </div>

                <div class="col-md-4">
                    <label for="supply-unit" class="form-label required-label">Unit of Measure</label>
                    <select id="supply-unit" name="unit" class="form-select" required data-selected="{{ old('unit') }}">
                        <option value="">Select unit</option>
                        @foreach($catalog['units'] as $unit)<option value="{{ $unit }}" @selected(old('unit') === $unit)>{{ ucfirst($unit) }}</option>@endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label required-label">Urgency Level</label>
                    <select name="urgency" class="form-select" required>
                        <option value="routine" {{ old('urgency') === 'routine' ? 'selected' : '' }}>Routine (Stock replenishment)</option>
                        <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent (Low stock level)</option>
                        <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Emergency (Stockout / Critical need)</option>
                    </select>
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label required-label">Reason / Clinical Justification</label>
                <textarea name="reason" rows="4" class="form-control" placeholder="Provide context about current stock levels, patient intake numbers, or specific health center needs." required>{{ old('reason') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-1"></i> Submit Request
                </button>
                <a href="{{ route('rhu.supply-requests.index') }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script type="application/json" id="supply-catalog">{!! \Illuminate\Support\Js::encode($catalog) !!}</script>
<script src="{{ asset('js/supply-request-form.js') }}?v={{ filemtime(public_path('js/supply-request-form.js')) }}" defer></script>
@endpush
