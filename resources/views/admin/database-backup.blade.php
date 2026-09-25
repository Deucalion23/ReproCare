@extends('admin.layout')

@section('title', 'Database Backup & Restore')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Database Backup & Restore</h2>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <!-- Database Info -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Database Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Database Type:</strong></td>
                                    <td>{{ ucfirst($dbType) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Database Path:</strong></td>
                                    <td>
                                        @if($dbType === 'sqlite')
                                            {{ database_path('database.sqlite') }}
                                        @else
                                            {{ config('database.connections.mysql.database') }}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($dbType === 'sqlite' && file_exists(database_path('database.sqlite')))
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>File Size:</strong></td>
                                        <td>{{ number_format(filesize(database_path('database.sqlite')) / 1024, 2) }} KB</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Modified:</strong></td>
                                        <td>{{ date('Y-m-d H:i:s', filemtime(database_path('database.sqlite'))) }}</td>
                                    </tr>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Export Section -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Export Database</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Create a backup of your current database. This file can be used to restore the system on another computer or recover from data loss.
                    </p>
                    <form action="{{ route('midwife.database.export') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Download Backup
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Import Section -->
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Import Database</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <strong>Warning:</strong> Importing a database will REPLACE all current data. This action cannot be undone. Make sure to export your current data first!
                    </div>
                    <form action="{{ route('midwife.database.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="backup_file" class="form-label">Select Backup File</label>
                            <input type="file" class="form-control @error('backup_file') is-invalid @enderror" 
                                   id="backup_file" name="backup_file" 
                                   accept=".sqlite,.sql,.db,.txt" required>
                            <div class="form-text">
                                Accepted formats: {{ $dbType === 'sqlite' ? '.sqlite, .db' : '.sql' }}
                            </div>
                            @error('backup_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_import" required>
                            <label class="form-check-label" for="confirm_import">
                                I understand this will replace all current data
                            </label>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-upload me-2"></i>Import Database
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Existing Backups -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Existing Backups ({{ count($backups) }})</h5>
                </div>
                <div class="card-body">
                    @if(count($backups) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Size</th>
                                        <th>Date Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($backups as $backup)
                                        <tr>
                                            <td>{{ $backup['name'] }}</td>
                                            <td>{{ $backup['size'] }}</td>
                                            <td>{{ $backup['date'] }}</td>
                                            <td>
                                                <a href="{{ route('midwife.database.download', $backup['name']) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form action="{{ route('midwife.database.delete', $backup['name']) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this backup?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No backups found in storage/app/backups</p>
                    @endif
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">How to Transfer to Another PC</h5>
                </div>
                <div class="card-body">
                    <h6>Method 1: Using This Interface</h6>
                    <ol>
                        <li>Click "Download Backup" to export your database</li>
                        <li>Copy the downloaded file to the new PC</li>
                        <li>On the new PC, install ReproCare and access this page</li>
                        <li>Use "Import Database" to restore your data</li>
                    </ol>
                    
                    <h6>Method 2: Manual File Copy (SQLite Only)</h6>
                    <ol>
                        <li>Locate file: <code>{{ database_path('database.sqlite') }}</code></li>
                        <li>Copy this file to the new PC's same location</li>
                        <li>Done! All data is transferred</li>
                    </ol>
                    
                    <h6>Method 3: Command Line</h6>
                    <pre class="bg-dark text-light p-3 rounded"><code># Export
php artisan db:export

# Import
php artisan db:import /path/to/backup.sql</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
