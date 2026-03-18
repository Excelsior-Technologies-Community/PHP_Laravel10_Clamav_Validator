# PHP_Laravel10_Clamav_Validator

## Project Description

PHP_Laravel10_Clamav_Validator is a simple Laravel 10 application that demonstrates how to scan uploaded files for viruses using ClamAV.

The application allows users to upload files through a web interface, and each file is scanned using the ClamAV antivirus engine via command line. Based on the scan result, the system displays whether the file is clean or infected.

This project is useful for beginners to understand file upload handling, security practices, and external tool integration in Laravel.



## Features

- Upload files using a simple UI
- Scan files using ClamAV antivirus
- Detect infected files
- Display success and error messages
- Dark mode user interface
- No external Laravel package required
- Beginner-friendly implementation



## Technologies Used

- Laravel 10 (PHP Framework)
- PHP 8+
- ClamAV (Antivirus Scanner)
- HTML & CSS (UI Design)
- MySQL (Optional Database)


## How It Works

1. User uploads a file through the form
2. Laravel stores the file in storage/app/uploads
3. The system runs ClamAV using exec() command
4. ClamAV scans the file for viruses
5. If virus is found → shows "Virus Found ❌"
6. If no virus → shows "File Clean ✅"

## Requirements

- PHP >= 8.1
- Composer
- Laravel 10
- XAMPP / Apache
- ClamAV installed (C:\ClamAV)


---



## Installation Steps


---


## STEP 1: Create Laravel 10 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel10_Clamav_Validator "10.*"

```

### Go inside project:

```
cd PHP_Laravel10_Clamav_Validator

```

#### Explanation:

This command creates a fresh Laravel 10 project using Composer and sets up all required files.

Then we enter the project folder to start development.




## STEP 2: Database Setup (Optional)

### Update database details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel10_Clamav_Validator
DB_USERNAME=root
DB_PASSWORD=

```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel10_Clamav_Validator

```

### Then Run:

```
php artisan migrate

```


#### Explanation:

This step connects Laravel to MySQL by updating the .env file.

Running migration creates default tables, but it’s optional for this project





## STEP 3:  Install ClamAV

### STEP 3.1 — Which file to download

#### From your list, click THIS:

```
clamav-1.5.2.win.x64.zip

```


### STEP 3.2 — After Download

#### You will get:

```
clamav-1.5.2.win.x64.zip

```


### STEP 3.3 — Extract

1. Right-click ZIP

2. Click Extract All

3. You will get folder:

```
clamav-1.5.2.win.x64

```



### STEP 3.4 — Rename

#### Rename folder to:

```
ClamAV

```


### STEP 3.5 — Move

#### Move it to:

```
C:\ClamAV

```


#### Explanation:

We download and install ClamAV manually to scan uploaded files for viruses.

The tool is placed in C:\ClamAV so Laravel can access it.





## STEP 4: Verify ClamAV Installation

### Run this command in CMD:

```
"C:\ClamAV\clamscan.exe" --version

```

### Expected output:

```
ClamAV 1.5.2

```

#### Explanation:

This step checks if ClamAV is installed correctly using the command line.

If version shows, it means the scanner is ready to use.




## STEP 5: Create Controller

### Run:

```
php artisan make:controller FileScanController

```

### Edit app/Http/Controllers/FileScanController.php:

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileScanController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads');

        $fullPath = storage_path('app/' . $path);

        // SIMPLE SCAN COMMAND
        $command = '"C:\\ClamAV\\clamscan.exe" ' . $fullPath;

        exec($command, $output);

        $result = implode("\n", $output);

        if (strpos($result, 'Infected files: 1') !== false) {
            return back()->with('error', 'Virus Found ❌');
        }

        return back()->with('success', 'File Clean ✅');
    }
}

```

#### Explanation:

The controller handles file upload, validation, and virus scanning logic.

It runs ClamAV using exec() and returns the scan result.




## STEP 6: Add Routes

### Edit routes/web.php:

```
use App\Http\Controllers\FileScanController;

Route::get('/', [FileScanController::class, 'index']);
Route::post('/upload', [FileScanController::class, 'scan'])->name('file.upload');

```

#### Explanation:

Routes connect URLs with controller methods.

It defines where to show the form and where to process file upload.




## STEP 7: Blade File

### resources/views/upload.blade.php

```
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

```

#### Explanation:

This step creates a user interface for uploading files.

It also displays success or error messages after scanning.




## STEP 8: Run the App

### Start dev server:

```
php artisan serve

```

### Open in browser:

```
http://127.0.0.1:8000

```

#### Explanation:

This command starts the Laravel development server.

Open browser to upload and scan files in real-time




## Application Screenshots:

### File Scanner UI:


<img src="screenshots/Screenshot 2026-03-18 104645.png" width="900">


### Upload File Screen:


<img src="screenshots/Screenshot 2026-03-18 104714.png" width="900">


### Scan Result Output:


<img src="screenshots/Screenshot 2026-03-18 104726.png" width="900">



---

## Project Folder Structure:

```
PHP_Laravel10_Clamav_Validator/
│
├── app/
│   └── Http/
│       └── Controllers/
│           └── FileScanController.php
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
│   └── index.php
│
├── resources/
│   └── views/
│       └── upload.blade.php
│
├── routes/
│   └── web.php
│
├── storage/
│   └── app/
│       └── uploads/        ← Uploaded files stored here
│
├── tests/
│
├── vendor/
│
├── .env
├── artisan
├── composer.json
└── README.md

```
