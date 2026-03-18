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