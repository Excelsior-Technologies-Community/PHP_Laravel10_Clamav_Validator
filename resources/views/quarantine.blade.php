<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quarantine - ClamAV Scanner</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: white;">
            <i class="fas fa-folder"></i> Quarantine
        </h1>
        <a href="/" class="btn btn-light">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($quarantined->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Filename</th>
                            <th>Virus Name</th>
                            <th>Quarantined At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quarantined as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->original_name }}</td>
                            <td><span class="badge bg-danger">{{ $item->virus_name ?? 'Unknown' }}</span></td>
                            <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <form action="{{ route('quarantine.restore', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <form action="{{ route('quarantine.delete', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this quarantined file?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <p>No quarantined files</p>
                </div>
            @endif
        </div>
    </div>
</div>

</body>
</html>