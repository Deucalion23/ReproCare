@php
    $appearance = app(\App\Services\AppearanceTheme::class)->current();
    $colorOptions = config('appearance.colors');
@endphp
<div class="pref-card fade-in-card">
    <div class="pref-card-header">
        <div class="pref-card-header-icon"><i class="bi bi-palette" aria-hidden="true"></i></div>
        <div><h6>System-wide appearance</h6><p>Shared colors for every ReproCare role</p></div>
    </div>
    <div class="pref-card-body">
        <p class="text-muted">Saved colors apply to all users on their next page load. The saved light or dark mode also applies then; users can still use their personal mode toggle.</p>
        <form id="city-appearance-form" method="POST" action="{{ route('cho.settings.update') }}"
              data-has-errors="{{ old('section') === 'appearance' && $errors->any() ? 'true' : 'false' }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="appearance">
            <p class="mb-2 fw-semibold">ReproCare color palette</p>
            <div class="appearance-options mb-4" aria-label="Fixed ReproCare color palette">
            @foreach(['primary' => 'Primary color', 'secondary' => 'Secondary color', 'accent' => 'Accent color'] as $slot => $label)
                <div class="appearance-choice" aria-label="{{ $label }}: {{ $colorOptions[$appearance[$slot]]['label'] }}">
                    <input type="hidden" name="{{ $slot }}" value="{{ $appearance[$slot] }}">
                    <span class="appearance-swatch" style="--swatch:{{ $colorOptions[$appearance[$slot]]['hex'] }}" aria-hidden="true"></span>
                    <span><strong>{{ $label }}</strong><small>{{ $colorOptions[$appearance[$slot]]['label'] }} · {{ $colorOptions[$appearance[$slot]]['hex'] }}</small></span>
                </div>
            @endforeach
            </div>
            {{-- The brand palette is fixed; only the default display mode is configurable. --}}
            <fieldset class="appearance-group">
                <legend>Theme mode</legend>
                <div class="appearance-options">
                    @foreach(['light' => 'Light Mode', 'dark' => 'Dark Mode'] as $mode => $label)
                        <label class="appearance-choice">
                            <input type="radio" name="mode" value="{{ $mode }}" required @checked(old('mode', $appearance['mode']) === $mode)>
                            <i class="bi bi-{{ $mode === 'light' ? 'sun' : 'moon' }}" aria-hidden="true"></i><strong>{{ $label }}</strong>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <p id="appearance-preview-status" class="text-muted" aria-live="polite"></p>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Save Appearance</button>
                <button type="button" id="appearance-reset" class="btn btn-outline-secondary">Reset to ReproCare Default</button>
            </div>
        </form>
    </div>
</div>
