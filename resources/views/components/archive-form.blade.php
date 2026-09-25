{{-- Reusable Archive button: collects a mandatory audit reason, then submits.
     Nothing is ever hard-deleted: clinical rows soft-delete (deleted_at) and
     accounts flip to status=archived. Restore stays available in the Archives Hub. --}}
@props([
    'action',
    'method' => 'DELETE',
    'label' => 'Archive',
    'title' => 'Archive (retained for audit)',
    'btnClass' => 'btn btn-sm btn-outline-warning',
    'icon' => 'bi bi-archive',
    'confirmText' => 'Archive this record? It will be hidden from active lists but retained for audit and can be restored.',
])
<form action="{{ $action }}" method="POST" {{ $attributes->merge(['class' => 'd-inline']) }} onsubmit="return reproArchivePrompt(this, this.dataset.confirmText);" data-confirm-text="{{ $confirmText }}">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method(strtoupper($method))
    @endif
    <button type="submit" class="{{ $btnClass }}" title="{{ $title }}">
        <i class="{{ $icon }} me-1"></i>{{ $label }}
    </button>
</form>
@once
<script>
    // Shared archive guardrail: every archive carries who/when/why for the audit trail.
    function reproArchivePrompt(form, confirmText) {
        var reason = prompt('Reason for archiving? (required for audit, e.g. duplicate entry, relocated, resigned)', '');
        if (reason === null) return false;
        if (reason.trim() === '') { alert('A reason for archiving is required.'); return false; }
        var hidden = document.createElement('input');
        hidden.type = 'hidden'; hidden.name = 'reason'; hidden.value = reason.trim();
        form.appendChild(hidden);
        return confirm(confirmText || 'Archive this record? It will be retained for audit.');
    }
</script>
@endonce
