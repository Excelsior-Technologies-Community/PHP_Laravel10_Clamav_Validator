<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced ClamAV Scanner</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #020617, #0f172a);
            min-height: 100vh;
            color: white;
            padding: 30px;
        }

        .container {
            max-width: 1250px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 35px;
            color: #38bdf8;
            font-size: 42px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            transition: 0.3s ease;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .card:hover {
            transform: translateY(-6px);
            border-color: rgba(56, 189, 248, 0.5);
        }

        .card h2 {
            font-size: 42px;
            margin-bottom: 10px;
            color: #4ade80;
        }

        .card p {
            color: #cbd5e1;
            font-size: 16px;
        }

        .upload-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 22px;
            margin-bottom: 35px;
            border: 2px dashed #38bdf8;
            text-align: center;
            backdrop-filter: blur(12px);
        }

        .upload-box h2 {
            margin-bottom: 20px;
            font-size: 32px;
            color: #f8fafc;
        }

        input[type=file] {
            width: 100%;
            padding: 16px;
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 15px;
        }

        button {
            margin-top: 25px;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            transform: scale(1.01);
            opacity: 0.95;
        }

        .success {
            background: rgba(34, 197, 94, 0.15);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .error {
            background: rgba(239, 68, 68, 0.15);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
        }

        table th {
            background: #1e293b;
            padding: 18px;
            font-size: 15px;
            white-space: nowrap;
            color: #f8fafc;
        }

        table td {
            background: rgba(255, 255, 255, 0.04);
            padding: 18px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            word-break: break-word;
            max-width: 250px;
            color: white;
        }

        table tr {
            transition: 0.3s;
        }

        table tr:hover td {
            background: rgba(56, 189, 248, 0.08);
        }

        .filename {
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            margin: auto;
        }

        .badge-clean {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: bold;
            display: inline-block;
        }

        /* Bootstrap Pagination Custom */

        .pagination .page-link {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            color: white;
            border-radius: 10px;
            margin: 0 4px;
        }

        .pagination .page-link:hover {
            background: rgba(56,189,248,0.2);
            border-color: #38bdf8;
            color: white;
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            border: none;
            color: white;
        }

        .pagination .disabled .page-link {
            background: rgba(255,255,255,0.03);
            color: #94a3b8;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
        }

        .loader {
            display: none;
            margin-top: 25px;
        }

        .spinner {
            border: 5px solid rgba(255, 255, 255, 0.1);
            border-top: 5px solid #38bdf8;
            border-radius: 50%;
            width: 65px;
            height: 65px;
            animation: spin 1s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        @media(max-width:768px) {

            body {
                padding: 15px;
            }

            h1 {
                font-size: 32px;
            }

            .upload-box {
                padding: 25px;
            }

            table th,
            table td {
                padding: 14px;
                font-size: 13px;
            }

            .card h2 {
                font-size: 34px;
            }
        }

    </style>
</head>

<body>

<div class="container">

    <h1>🛡️ Advanced ClamAV Scanner</h1>

    <div class="stats">

        <div class="card">
            <h2>{{ $totalScans }}</h2>
            <p>Total Scans</p>
        </div>

        <div class="card">
            <h2>{{ $cleanFiles }}</h2>
            <p>Clean Files</p>
        </div>

        <div class="card">
            <h2>{{ $infectedFiles }}</h2>
            <p>Infected Files</p>
        </div>

    </div>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="error">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            <div class="error">
                {{ $error }}
            </div>
        @endforeach
    @endif

    <div class="upload-box">

        <h2>Upload & Scan File</h2>

        <form action="{{ route('file.upload') }}"
              method="POST"
              enctype="multipart/form-data"
              id="scanForm">

            @csrf

            <input type="file" name="file" required>

            <button type="submit">
                Upload & Scan
            </button>

            <div class="loader" id="loader">
                <div class="spinner"></div>
                <p style="margin-top:15px;">Scanning File...</p>
            </div>

        </form>

    </div>

    <div class="table-wrapper">

        <table>

            <thead>
            <tr>
                <th>ID</th>
                <th>Original Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Status</th>
                <th>Scanned At</th>
            </tr>
            </thead>

            <tbody>

            @forelse($histories as $history)

                <tr>

                    <td>{{ $history->id }}</td>

                    <td>
                        <span class="filename"
                              title="{{ $history->original_name }}">
                            {{ \Illuminate\Support\Str::limit($history->original_name, 45) }}
                        </span>
                    </td>

                    <td>{{ strtoupper($history->file_type) }}</td>

                    <td>{{ $history->file_size }}</td>

                    <td>
                        @if($history->scan_status == 'Clean')

                            <span class="badge-clean">
                                ✅ Clean
                            </span>

                        @else

                            <span class="badge-danger">
                                ❌ Infected
                            </span>

                        @endif
                    </td>

                    <td>
                        {{ $history->created_at->format('d M Y h:i A') }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6">
                        No Scan History Found
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <!-- Bootstrap Pagination -->

    <div class="d-flex justify-content-center mt-4">
        {{ $histories->links('pagination::bootstrap-5') }}
    </div>

    <div class="footer">
        Laravel 10 • ClamAV Security Scanner
    </div>

</div>

<script>

    const form = document.getElementById('scanForm');

    form.addEventListener('submit', function () {

        document.getElementById('loader').style.display = 'block';

    });

</script>

</body>

</html>