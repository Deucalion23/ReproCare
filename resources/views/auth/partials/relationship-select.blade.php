{{-- Shared emergency-contact relationship picker (complete-profile + register).
     Dropdown of common relationships + "Others" revealing a specify box.
     Params: $name (field name, also used as id), $required (bool),
     $placeholder (optional first-option label). --}}
@php
    $relOptions = ['Husband', 'Wife', 'Partner', 'Mother', 'Father', 'Son', 'Daughter', 'Brother', 'Sister', 'Grandmother', 'Grandfather', 'Aunt', 'Uncle', 'Cousin', 'Friend', 'Neighbor', 'Co-worker', 'Others'];
    $relOld = old($name);
    $relOther = old($name . '_other');
    if (is_string($relOld) && $relOld !== '' && ! in_array($relOld, $relOptions, true)) {
        // Previously typed custom value (or tampered POST): preserve the
        // text by folding it into Others + specify.
        if (strcasecmp($relOld, 'Others') !== 0) {
            $relOther = ($relOther !== null && $relOther !== '') ? $relOther : $relOld;
        }
        $relOld = 'Others';
    }
    $relShowOther = ($relOld === 'Others');
@endphp
<div class="field-wrap">
    <i class="bi bi-heart f-icon" aria-hidden="true"></i>
    <select name="{{ $name }}" id="{{ $name }}" class="rel-select" data-rel-select {{ ($required ?? false) ? 'required' : '' }} aria-label="Relationship">
        <option value="" disabled {{ !$relOld ? 'selected' : '' }}>{{ $placeholder ?? 'Select relationship…' }}</option>
        @foreach($relOptions as $option)
            <option value="{{ $option }}" {{ $relOld === $option ? 'selected' : '' }}>{{ $option === 'Others' ? 'Others (please specify)' : $option }}</option>
        @endforeach
    </select>
</div>
<div class="field-wrap rel-other-wrap" style="{{ $relShowOther ? '' : 'display:none;' }}margin-top:8px;">
    <i class="bi bi-pencil f-icon" aria-hidden="true"></i>
    <input type="text" name="{{ $name }}_other" id="{{ $name }}_other" class="rel-other-input" value="{{ $relOther }}" placeholder="Please specify…" maxlength="255" data-rel-other>
</div>

@push('styles')
<style>
    /* Custom chevron: .field-wrap rules set appearance:none on selects. */
    select.rel-select {
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' d='M2 5l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat:no-repeat;
        background-position:right 14px center;
        background-size:12px;
        padding-right:38px !important;
        cursor:pointer;
    }
</style>
@endpush
