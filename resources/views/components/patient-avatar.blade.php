@props(['patient' => null, 'size' => 40, 'name' => null])
@php
    $displayName = preg_replace('/\s+/u', ' ', trim($name ?? $patient?->name ?? $patient?->full_name ?? '')) ?: 'Patient';
    $parts = preg_split('/\s+/u', $displayName, -1, PREG_SPLIT_NO_EMPTY);
    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
    $avatarSize = max(24, min(160, (int) $size));
    // A walk-in ID is a different identity; never look up a User by the same ID.
    $photo = $patient instanceof \App\Models\User && $patient->hasProfileImage() ? $patient->profile_image_url : null;
@endphp
<span {{ $attributes->class(['rc-patient-avatar']) }}
      role="img" aria-label="Profile for {{ $displayName }}"
      style="display:inline-flex;position:relative;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;vertical-align:middle;width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:50%;background:var(--bg-card2,var(--color-secondary-soft));color:var(--text,var(--color-text));border:1px solid var(--border,var(--color-border));font-weight:700;font-size:{{ max(11, (int) round($avatarSize * .32)) }}px;">
    <span aria-hidden="true">{{ $initials }}</span>
    @if($photo)
        <img src="{{ $photo }}" alt="" loading="lazy" decoding="async"
             style="position:absolute;inset:0;width:100%;height:100% !important;object-fit:cover;border-radius:inherit;"
             onerror="this.remove();">
    @endif
</span>
