<!DOCTYPE html>
<html>

<head>
    <title>Laravel ClamAV Scanner</title>

    <style>
        body {
            background: #0f172a;
            font-family: Arial, sans-serif;
            color: #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #1e293b;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #38bdf8;
        }

        input[type="file"] {
            background: #0f172a;
            color: #e2e8f0;
            padding: 10px;
            border: 1px solid #334155;
            border-radius: 8px;
            width: 100%;
            margin-bottom: 15px;
        }

        button {
            background: #22c55e;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #16a34a;
        }

        .success {
            background: #064e3b;
            color: #4ade80;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .error {
            background: #450a0a;
            color: #f87171;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 15px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2>🛡️ File Scanner</h2>

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="error">{{ $error }}</div>
            @endforeach
        @endif

        <form action="{{ route('file.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" required>
            <button type="submit">Upload & Scan</button>
        </form>

        <div class="footer">
            Laravel 10 • ClamAV Scanner
        </div>
    </div>

</body>

</html>