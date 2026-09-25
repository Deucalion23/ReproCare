{{-- ReproCare fatal verification error for BHW President jurisdiction conflicts.
     Renders only when the `barangay` error bag carries a VALIDATION ERROR message. --}}
@if($errors->has('barangay') && str_starts_with((string) $errors->first('barangay'), 'VALIDATION ERROR'))
<div class="alert alert-danger fade show mb-4" role="alert"
     style="border-radius:14px; background:var(--color-danger-soft); border:1px solid var(--color-danger-soft); border-left:5px solid var(--color-danger-text); color:var(--color-danger-text);">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-shield-exclamation-fill flex-shrink-0" style="font-size:1.6rem; color:var(--color-danger-text);"></i>
        <div>
            <div class="fw-800 mb-1" style="font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:0.02em;">
                <i class="bi bi-octagon-fill me-1"></i>Assignment BLOCKED — Jurisdiction Conflict
            </div>
            <div style="font-size:0.9rem; line-height:1.6;">{{ $errors->first('barangay') }}</div>
        </div>
    </div>
</div>
@endif
