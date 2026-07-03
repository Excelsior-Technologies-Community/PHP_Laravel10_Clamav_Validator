<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ClamAV Scanner</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 25px;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 15px;
            transition: transform 0.3s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .upload-area {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-area:hover {
            border-color: #667eea;
            background: #f0f0ff;
        }

        .badge-clean { background: #d4edda; color: #155724; padding: 5px 12px; border-radius: 20px; }
        .badge-infected { background: #f8d7da; color: #721c24; padding: 5px 12px; border-radius: 20px; }
        .badge-pending { background: #fff3cd; color: #856404; padding: 5px 12px; border-radius: 20px; }
        .badge-quarantined { background: #cce5ff; color: #004085; padding: 5px 12px; border-radius: 20px; }

        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .loader-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loader-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
        }

        .btn-primary:hover {
            transform: scale(1.05);
        }

        .progress-bar-custom {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 10px;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>

<div class="main-container">

    <div class="text-center mb-4">
        <h1 style="color: white;">
            <i class="fas fa-shield-alt"></i> ClamAV Virus Scanner
        </h1>
        <p style="color: white;">Upload and scan files for viruses</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number text-primary">{{ $stats['total'] }}</div>
                <small class="text-muted">Total Scans</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number text-success">{{ $stats['clean'] }}</div>
                <small class="text-muted">Clean Files</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number text-danger">{{ $stats['infected'] }}</div>
                <small class="text-muted">Infected</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number text-warning">{{ $stats['pending'] }}</div>
                <small class="text-muted">Pending</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-number text-info">{{ $stats['quarantined'] }}</div>
                <small class="text-muted">Quarantined</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <a href="{{ route('quarantine.list') }}" class="btn btn-warning w-100">
                    <i class="fas fa-folder"></i> Quarantine
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="fas fa-upload text-primary"></i> Upload & Scan File
            </h5>

            <form id="uploadForm" enctype="multipart/form-data">
                @csrf

                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt fa-4x text-muted mb-3"></i>
                    <p class="text-muted mb-2">Click or drag file to upload</p>
                    <input type="file" name="file" id="fileInput" class="d-none" required>
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-folder-open"></i> Browse Files
                    </button>
                    <div id="selectedFile" class="mt-3 small text-muted"></div>
                </div>

                <div class="progress-bar-custom" id="progressBar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                        <i class="fas fa-shield-alt"></i> Scan Now
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('scan.index') }}" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by filename..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="Clean" {{ request('status') == 'Clean' ? 'selected' : '' }}>Clean</option>
                        <option value="Infected" {{ request('status') == 'Infected' ? 'selected' : '' }}>Infected</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Filename</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Scanned At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $history)
                <tr>
                    <td>{{ $history->id }}</td>
                    <td>{{ Str::limit($history->original_name, 40) }}</td>
                    <td>{{ $history->file_type }}</td>
                    <td>{{ $history->file_size }}</td>
                    <td>
                        @if($history->scan_status == 'Clean')
                            <span class="badge-clean">✅ Clean</span>
                        @elseif($history->scan_status == 'Infected')
                            <span class="badge-infected">❌ Infected</span>
                        @elseif($history->scan_status == 'Pending')
                            <span class="badge-pending">⏳ Pending</span>
                        @else
                            <span class="badge-danger">⚠️ Error</span>
                        @endif
                        @if($history->is_quarantined)
                            <span class="badge-quarantined">📦 Quarantined</span>
                        @endif
                    </td>
                    <td>{{ $history->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <form action="{{ route('delete.history', $history->id) }}" method="POST" onsubmit="return confirm('Delete this record?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2 d-block"></i>
                        No scan history found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $histories->appends(request()->query())->links() }}
    </div>
</div>

<div class="loader-overlay" id="loaderOverlay">
    <div class="loader-content">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Scanning file...</p>
        <div id="scanStatusText" class="small text-muted">Queued for scanning</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const fileInput = document.getElementById('fileInput');
    const selectedFileDiv = document.getElementById('selectedFile');
    const uploadArea = document.getElementById('uploadArea');
    const form = document.getElementById('uploadForm');
    const loader = document.getElementById('loaderOverlay');
    const progressBar = document.getElementById('progressBar');
    const progressFill = document.getElementById('progressFill');
    const scanStatusText = document.getElementById('scanStatusText');

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            const fileSize = (file.size / 1024).toFixed(2);
            selectedFileDiv.innerHTML = `<i class="fas fa-file"></i> Selected: ${file.name} (${fileSize} KB)`;
            selectedFileDiv.style.color = '#667eea';
        }
    });

    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (fileInput.files.length === 0) {
            alert('Please select a file');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        loader.style.display = 'flex';
        progressBar.style.display = 'block';
        progressFill.style.width = '0%';

        fetch('{{ route("file.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                progressFill.style.width = '100%';
                scanStatusText.textContent = 'File queued for scanning #' + data.scan_id;
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                alert('Error: ' + data.message);
                loader.style.display = 'none';
                progressBar.style.display = 'none';
            }
        })
        .catch(error => {
            alert('Error uploading file');
            loader.style.display = 'none';
            progressBar.style.display = 'none';
        });
    });

    function checkScanStatus(id) {
        fetch('/scan/status/' + id)
            .then(response => response.json())
            .then(data => {
                scanStatusText.textContent = 'Status: ' + data.status;
                if (data.status === 'Pending') {
                    setTimeout(() => checkScanStatus(id), 3000);
                } else {
                    loader.style.display = 'none';
                    progressBar.style.display = 'none';
                    location.reload();
                }
            });
    }
</script>

</body>
</html>