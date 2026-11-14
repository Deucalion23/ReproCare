{{--
    Shared "All Women" directory body used by the RHU and CHO portals.

    Expects:
      $womenRoute   (string) base route used for every filter link
      $scopeLabel   (string) human readable scope, e.g. "RHU 1 catchment"
      $totalCount, $registeredCount, $unregisteredCount, $pregnantCount,
      $pendingCount, $resultCount (int)
      $search, $type, $status, $barangay, $sort, $dateFrom, $dateTo (string)
      $pregnant (bool), $sorts, $statuses, $barangays
      $women (LengthAwarePaginator), $detailUrl (callable)
      $allIds (array of "type:id" keys matching the filters)
      $printBulkRoute (string|null) POST route for bulk printing; when set,
                      the table gains selection checkboxes + a print toolbar
--}}
@push('styles')
@include('includes.women-directory-styles')
@include('includes.report-queue-styles')
@endpush

@php
    $canBulkPrint = ! empty($printBulkRoute);
    $filtersActive = $search !== '' || $barangay !== '' || $status !== 'all' || $dateFrom !== '' || $dateTo !== '' || $pregnant;
    $keep = array_filter([
        'search' => $search !== '' ? $search : null,
        'barangay' => $barangay !== '' ? $barangay : null,
        'sort' => $sort !== 'date_desc' ? $sort : null,
        'date_from' => $dateFrom !== '' ? $dateFrom : null,
        'date_to' => $dateTo !== '' ? $dateTo : null,
    ]);
    $typeLink = function (string $type) use ($keep, $status, $womenRoute) {
        $params = $keep;
        if ($type === 'all') {
            unset($params['type']);
        } else {
            $params['type'] = $type;
        }
        if ($type === 'unregistered') {
            unset($params['status']);
        } elseif ($status !== 'all') {
            $params['status'] = $status;
        }

        return route($womenRoute, $params);
    };
    $pregnantLink = $pregnant
        ? route($womenRoute, $keep)
        : route($womenRoute, $keep + ['pregnant' => 1]);
    $pendingLink = $status === 'pending'
        ? route($womenRoute, $keep)
        : route($womenRoute, $keep + ['status' => 'pending']);
@endphp

{{-- Slim head bar. The two filters that had no home in the tab row below live
     here as pills; All / Registered / Unregistered stay in the tabs so their
     counts are not shown twice. --}}
<div class="rq-bar fade-in-card mb-2">
    <div class="rq-bar-id">
        <div class="rq-bar-title"><i class="bi bi-people-fill" aria-hidden="true"></i>All Women</div>
        <p class="rq-bar-subtitle">
            {{ $subtitle ?? 'Registered and unregistered women in your scope area.' }}
        </p>
    </div>

    <div class="rq-chips" aria-label="Directory filters">
        <a class="wd-tab {{ $pregnant ? 'active' : '' }}" href="{{ $pregnantLink }}">
            <i class="bi bi-heart-pulse me-1"></i> With Pregnancy ({{ $pregnantCount }})
        </a>
        <a class="wd-tab {{ $status === 'pending' ? 'active' : '' }}" href="{{ $pendingLink }}">
            <i class="bi bi-hourglass-split me-1"></i> Pending Review ({{ $pendingCount }})
        </a>
        <span class="wd-pill wd-pill-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ $scopeLabel }}</span>
    </div>

    @if (! empty($addWomanRoute))
        <a href="{{ route($addWomanRoute) }}" class="btn btn-sm wd-apply-btn">
            <i class="bi bi-person-plus-fill me-1"></i> {{ $addWomanLabel ?? 'Add Woman' }}
        </a>
    @endif
</div>

<div class="card fade-in-card wd-wrap"
     @if ($canBulkPrint)
         data-wd-directory
         data-wd-bulk-url="{{ route($printBulkRoute) }}"
         data-wd-csrf="{{ csrf_token() }}"
         data-wd-storage-key="wd-print:{{ $womenRoute }}:{{ auth()->id() }}"
         data-wd-all-ids='@json($allIds ?? [])'
     @endif>
    <div class="card-header bg-transparent p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="wd-tabs" role="tablist">
                <li>
                    <a class="wd-tab {{ $type === 'all' ? 'active' : '' }}" href="{{ $typeLink('all') }}">
                        <i class="bi bi-people me-1"></i> All ({{ $totalCount }})
                    </a>
                </li>
                <li>
                    <a class="wd-tab {{ $type === 'registered' ? 'active' : '' }}" href="{{ $typeLink('registered') }}">
                        <i class="bi bi-person-check me-1"></i> Registered ({{ $registeredCount }})
                    </a>
                </li>
                <li>
                    <a class="wd-tab {{ $type === 'unregistered' ? 'active' : '' }}" href="{{ $typeLink('unregistered') }}">
                        <i class="bi bi-person-plus me-1"></i> Unregistered ({{ $unregisteredCount }})
                    </a>
                </li>
            </ul>

            <form method="GET" action="{{ route($womenRoute) }}" class="wd-filters">
            <div class="wd-field wd-search-field">
                <label for="wd-search">Search</label>
                <div class="wd-search">
                    <input type="search" id="wd-search" name="search" class="form-control" value="{{ $search }}"
                           placeholder="Name, contact, email, purok...">
                    <button class="btn" type="submit" title="Search">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            <div class="wd-field wd-select-field">
                <label for="wd-barangay">Barangay</label>
                <select id="wd-barangay" name="barangay" class="form-select form-select-sm">
                    <option value="">All barangays</option>
                    @foreach ($barangays as $area)
                        <option value="{{ $area }}" {{ $barangay === $area ? 'selected' : '' }}>{{ $area }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="wd-status">Account status</label>
                <select id="wd-status" name="status" class="form-select form-select-sm" {{ $type === 'unregistered' ? 'disabled' : '' }}>
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All statuses</option>
                    @foreach ($statuses as $option)
                        <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-select-field">
                <label for="wd-sort">Sort by</label>
                <select id="wd-sort" name="sort" class="form-select form-select-sm">
                    @foreach ($sorts as $key => $label)
                        <option value="{{ $key }}" {{ $sort === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="wd-field wd-date-field">
                <label for="wd-date-from">Added from</label>
                <input type="date" id="wd-date-from" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" max="{{ date('Y-m-d') }}">
            </div>

            <div class="wd-field wd-date-field">
                <label for="wd-date-to">Added to</label>
                <input type="date" id="wd-date-to" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" max="{{ date('Y-m-d') }}">
            </div>

            @if ($type !== 'all')
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            @if ($pregnant)
                <input type="hidden" name="pregnant" value="1">
            @endif

            <div class="wd-actions d-flex gap-2">
                <button type="submit" class="btn btn-sm wd-apply-btn">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route($womenRoute) }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                    <i class="bi bi-x-lg me-1"></i> Clear
                </a>
            </div>
        </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-bottom">
            <span class="wd-count">
                Showing <strong>{{ $resultCount }}</strong>
                {{ Str::plural('woman', $resultCount) }}
                @if ($filtersActive)
                    <span class="text-muted">matching the current filters</span>
                @else
                    <span class="text-muted">in {{ $scopeLabel }}</span>
                @endif
            </span>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if ($filtersActive)
                    <a href="{{ route($womenRoute) }}" class="btn btn-sm btn-outline-secondary wd-clear-btn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                    </a>
                @endif
                @if ($canBulkPrint)
                    <div class="wd-selection" data-wd-selection hidden>
                        <span class="wd-selection-count">
                            <i class="bi bi-check2-square me-1"></i><strong data-wd-count>0</strong> selected
                        </span>
                        <button type="button" class="wd-link-btn" data-wd-select-all hidden>
                            Select all {{ $resultCount }}
                        </button>
                        <button type="button" class="btn btn-sm wd-apply-btn" data-wd-print>
                            <i class="bi bi-printer me-1"></i> Print selected
                        </button>
                        <button type="button" class="btn btn-sm wd-clear-btn" data-wd-clear>
                            Clear
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="table-responsive" style="overflow:visible;">
            <table class="table table-hover align-middle mb-0" style="font-size:0.78rem;">
                <thead>
                    <tr>
                        @if ($canBulkPrint)
                            <th class="wd-check-col py-3">
                                <input type="checkbox" class="wd-check-input" data-wd-check-page
                                       title="Select all women on this page" aria-label="Select all women on this page">
                            </th>
                        @endif
                        <th class="px-3 py-3">Woman</th>
                        <th class="py-3">Type / Status</th>
                        <th class="py-3">Age / DOB</th>
                        <th class="py-3">Contact</th>
                        <th class="py-3">Barangay / Purok</th>
                        <th class="py-3">Pregnancy</th>
                        <th class="py-3">Added</th>
                        <th class="py-3 text-center px-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($women as $woman)
                        @php
                            $isWalkIn = $woman->type === 'unregistered';
                            $displayName = $isWalkIn ? $woman->full_name : $woman->name;
                            $pregnancy = $woman->pregnancies->first();
                        @endphp
                        <tr>
                            @if ($canBulkPrint)
                                <td class="wd-check-col">
                                    <input type="checkbox" class="wd-check-input" data-wd-check
                                           value="{{ $woman->type }}:{{ $woman->id }}"
                                           aria-label="Select {{ $displayName ?: 'this woman' }} for bulk print">
                                </td>
                            @endif
                            <td class="px-3">
                                <div class="d-flex align-items-center gap-2">
                                    <x-patient-avatar :patient="$woman" :name="$displayName" :size="34" />
                                    <div style="min-width:0;">
                                        <div class="wd-name">{{ $displayName ?: 'Unknown' }}</div>
                                        <div class="wd-sub">{{ $isWalkIn ? 'Field-only profile' : ($woman->email ?: 'No email') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($isWalkIn)
                                    <span class="wd-pill wd-pill-unregistered"><i class="bi bi-person-plus"></i> Unregistered</span>
                                @else
                                    <span class="wd-pill wd-pill-registered"><i class="bi bi-person-check"></i> Registered</span>
                                    <div class="wd-sub mt-1">{{ ucfirst($woman->status ?? 'approved') }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($woman->date_of_birth)
                                    <div>{{ $woman->age }} y/o</div>
                                    <div class="wd-sub">{{ $woman->date_of_birth->format('M d, Y') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">{{ $woman->contact_number ?: '—' }}</td>
                            <td>
                                <div>{{ $woman->barangay ?: '—' }}</div>
                                <div class="wd-sub">{{ $woman->purok?->name ?: 'No purok' }}</div>
                            </td>
                            <td>
                                @if ($pregnancy)
                                    <span class="wd-pill wd-pill-pregnant">Pregnant</span>
                                    <div class="wd-sub mt-1">EDD {{ $pregnancy->edd ? $pregnancy->edd->format('M d, Y') : 'unknown' }}</div>
                                @else
                                    <span class="wd-pill wd-pill-muted">Not pregnant</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                {{ $woman->created_at ? $woman->created_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="text-center px-3">
                                <a href="{{ $detailUrl($woman) }}" class="btn btn-sm btn-outline-primary wd-action-btn">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canBulkPrint ? 9 : 8 }}" class="text-center py-5 text-muted">
                                <i class="bi bi-people" style="font-size:2rem;"></i>
                                <p class="mt-2 mb-0">
                                    {{ $filtersActive ? 'No women match the current filters.' : 'No women found in your scope area.' }}
                                </p>
                                @if ($filtersActive)
                                    <a href="{{ route($womenRoute) }}" class="btn btn-sm btn-outline-secondary wd-clear-btn mt-2">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset filters
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($women->hasPages())
            <div class="p-3">{{ $women->links('vendor.pagination.arrows-only') }}</div>
        @endif
    </div>
</div>

@if ($canBulkPrint)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-wd-directory]');
            if (!root) return;

            const bulkUrl = root.dataset.wdBulkUrl;
            const csrf = root.dataset.wdCsrf;
            const storageKey = root.dataset.wdStorageKey;
            const allIds = JSON.parse(root.dataset.wdAllIds || '[]');
            const pageChecks = [...root.querySelectorAll('[data-wd-check]')];
            const headCheck = root.querySelector('[data-wd-check-page]');
            const bar = root.querySelector('[data-wd-selection]');
            const countEl = root.querySelector('[data-wd-count]');
            const selectAllBtn = root.querySelector('[data-wd-select-all]');
            const printBtn = root.querySelector('[data-wd-print]');
            const clearBtn = root.querySelector('[data-wd-clear]');
            const MAX_BATCH = 100;

            // Selections survive paging / filtering (and a trip to a record
            // view) via sessionStorage, keyed per portal and staff account.
            let selected = new Set();
            try { selected = new Set(JSON.parse(sessionStorage.getItem(storageKey) || '[]')); } catch (e) { /* fresh start */ }

            const persist = () => {
                try { sessionStorage.setItem(storageKey, JSON.stringify([...selected])); } catch (e) { /* storage full/blocked */ }
            };

            const sync = () => {
                pageChecks.forEach(cb => {
                    cb.checked = selected.has(cb.value);
                    cb.closest('tr').classList.toggle('wd-row-selected', cb.checked);
                });
                const onPage = pageChecks.filter(cb => cb.checked).length;
                if (headCheck) {
                    headCheck.checked = pageChecks.length > 0 && onPage === pageChecks.length;
                    headCheck.indeterminate = onPage > 0 && onPage < pageChecks.length;
                }
                countEl.textContent = selected.size;
                bar.hidden = selected.size === 0;
                selectAllBtn.hidden = allIds.length === 0 || allIds.every(id => selected.has(id));
                printBtn.disabled = selected.size === 0;
            };

            pageChecks.forEach(cb => cb.addEventListener('change', () => {
                cb.checked ? selected.add(cb.value) : selected.delete(cb.value);
                persist(); sync();
            }));

            headCheck?.addEventListener('change', () => {
                pageChecks.forEach(cb => headCheck.checked ? selected.add(cb.value) : selected.delete(cb.value));
                persist(); sync();
            });

            selectAllBtn.addEventListener('click', () => {
                allIds.forEach(id => selected.add(id));
                persist(); sync();
            });

            clearBtn.addEventListener('click', () => {
                selected.clear();
                persist(); sync();
            });

            printBtn.addEventListener('click', () => {
                if (selected.size === 0) return;
                if (selected.size > MAX_BATCH) {
                    alert(`Print up to ${MAX_BATCH} women at a time. Narrow the selection and try again.`);
                    return;
                }
                // POST keeps long selections out of the URL; the print document
                // opens in a new tab so the directory (and selection) stays put.
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = bulkUrl;
                form.target = '_blank';
                const token = document.createElement('input');
                token.type = 'hidden'; token.name = '_token'; token.value = csrf;
                form.appendChild(token);
                [...selected].forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'women[]'; input.value = key;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
                form.remove();
            });

            sync();
        });
    </script>
    @endpush
@endif
