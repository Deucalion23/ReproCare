@extends('rhu.layout')

@section('title', 'Database Backup & Restore - RHU Portal | ReproCare')

@section('rhu-content')

<div class="page-hero fade-in-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="page-hero-title">Database Backup & Restore</div>
            <p class="page-hero-subtitle" style="font-weight:600;">
                <i class="bi bi-database-fill me-1"></i> Keep system data safe, export backups, and restore snapshots.
            </p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show fade-in-card mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show fade-in-card mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <!-- Database Info & Export -->
        <div class="card fade-in-card mb-4">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700" style="color:var(--primary);">Database Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 rounded bg-light">
                            <span class="text-xs text-muted d-block uppercase tracking-wider mb-1">Database Type</span>
                            <span class="fw-700 text-dark">{{ ucfirst($dbType) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded bg-light">
                            <span class="text-xs text-muted d-block uppercase tracking-wider mb-1">Database Name</span>
                            <span class="fw-700 text-dark" style="word-break:break-all;">
                                @if($dbType === 'sqlite')
                                    database.sqlite
                                @else
                                    {{ config('database.connections.mysql.database') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @if($dbType === 'sqlite' && file_exists(database_path('database.sqlite')))
                        <div class="col-sm-6">
                            <div class="p-3 rounded bg-light">
                                <span class="text-xs text-muted d-block uppercase tracking-wider mb-1">File Size</span>
                                <span class="fw-700 text-dark">{{ number_format(filesize(database_path('database.sqlite')) / 1024, 2) }} KB</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded bg-light">
                                <span class="text-xs text-muted d-block uppercase tracking-wider mb-1">Last Modified</span>
                                <span class="fw-700 text-dark">{{ date('Y-m-d H:i:s', filemtime(database_path('database.sqlite'))) }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="my-4" style="border-color:var(--border);">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h6 class="fw-700 mb-1">Create System Backup</h6>
                        <p class="text-muted text-xs mb-0">Generate and download an archive file containing all current database contents.</p>
                    </div>
                    <form action="{{ route('rhu.database.export') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-download me-2"></i>Download Backup
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Existing Backups -->
        <div class="card fade-in-card">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700" style="color:var(--cyan);">Existing Backups ({{ count($backups) }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if(count($backups) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4">Filename</th>
                                    <th>Size</th>
                                    <th>Date Created</th>
                                    <th class="text-end px-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backups as $backup)
                                    <tr>
                                        <td class="px-4 fw-600 text-dark" style="font-size:0.875rem;">
                                            <i class="bi bi-file-earmark-binary me-2 text-muted"></i>{{ $backup['name'] }}
                                        </td>
                                        <td>{{ $backup['size'] }}</td>
                                        <td style="font-size:0.8rem; color:var(--text-muted);">{{ $backup['date'] }}</td>
                                        <td class="text-end px-4">
                                            <div class="d-flex justify-content-end gap-2 tbl-actions">
                                                <a href="{{ route('rhu.database.download', $backup['name']) }}" 
                                                   class="btn btn-sm btn-icon btn-primary" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <form action="{{ route('rhu.database.delete', $backup['name']) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this backup file permanentely?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-danger" title="Delete">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-server" style="font-size:2.5rem; color:var(--text-muted);"></i>
                        <h6 class="mt-3 mb-1 fw-700">No Backups Found</h6>
                        <p class="text-muted text-xs px-4">There are currently no stored backup files in storage/app/backups. Create one using the download option above.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Import / Restore -->
        <div class="card border-warning mb-4 fade-in-card">
            <div class="card-header bg-transparent py-3" style="border-bottom:1px solid color-mix(in srgb, var(--color-warning) 15%, transparent);">
                <h5 class="mb-0 fw-700 text-warning">Import / Restore Database
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-danger mb-4" style="background-color:color-mix(in srgb, var(--color-danger) 10%, transparent); border-color:color-mix(in srgb, var(--color-danger) 20%, transparent); color:var(--color-danger-text);">
                    <div class="d-flex">
                        <i class="bi bi-slash-circle-fill me-2" style="font-size:1.2rem;"></i>
                        <div>
                            <strong class="d-block mb-1">CRITICAL WARNING:</strong>
                            Importing a database will <strong>COMPLETELY OVERWRITE and REPLACE</strong> all tables and information in the current system. This action is instantaneous and cannot be undone!
                        </div>
                    </div>
                </div>

                <form action="{{ route('rhu.database.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="backup_file" class="form-label fw-600 text-dark">Select Backup File</label>
                        <input type="file" class="form-control @error('backup_file') is-invalid @enderror" 
                               id="backup_file" name="backup_file" 
                               accept=".sqlite,.sql,.db,.txt" required>
                        <div class="form-text mt-2" style="font-size:0.75rem;">
                            Accepted formats: {{ $dbType === 'sqlite' ? '.sqlite, .db, .txt' : '.sql' }}. Max size: 50MB.
                        </div>
                        @error('backup_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4 p-3 rounded" style="background:color-mix(in srgb, var(--color-warning) 5%, transparent); border:1px dashed color-mix(in srgb, var(--color-warning) 25%, transparent);">
                        <input class="form-check-input ms-0 me-2" type="checkbox" id="confirm_import" required>
                        <label class="form-check-label text-dark fw-600 text-xs" for="confirm_import">
                            I explicitly understand that all existing system data will be replaced by the imported backup file.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 py-2 text-dark fw-700">
                        <i class="bi bi-upload me-2"></i>Perform Restore
                    </button>
                </form>
            </div>
        </div>

        <!-- Transfer Instructions -->
        <div class="card fade-in-card">
            <div class="card-header bg-transparent py-3">
                <h5 class="mb-0 fw-700 text-muted">PC-to-PC Migration Guide
                </h5>
            </div>
            <div class="card-body" style="font-size:0.85rem;">
                <h6 class="fw-700 text-dark mb-2">Method 1: Portal Upload / Restore (Recommended)</h6>
                <ol class="ps-3 mb-4 text-muted">
                    <li class="mb-1">Click <strong>Download Backup</strong> on the source computer.</li>
                    <li class="mb-1">Copy the downloaded file onto a USB drive or cloud storage.</li>
                    <li class="mb-1">Log in to the ReproCare RHU Portal on the destination computer.</li>
                    <li>Use the <strong>Import / Restore</strong> panel to upload and activate the database file.</li>
                </ol>

                <h6 class="fw-700 text-dark mb-2">Method 2: Manual SQLite Copy (Developers Only)</h6>
                <ol class="ps-3 mb-0 text-muted">
                    <li class="mb-1">Locate the physical database file at: <code class="p-1 rounded bg-light" style="font-size:0.75rem;">{{ database_path('database.sqlite') }}</code></li>
                    <li class="mb-1">Copy this database file directly.</li>
                    <li>Paste it into the corresponding directory on the target server.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

@endsection
