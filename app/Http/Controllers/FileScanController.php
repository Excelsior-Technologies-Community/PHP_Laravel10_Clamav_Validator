<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ScanHistory;

class FileScanController extends Controller
{
    public function index()
    {
        $histories = ScanHistory::orderBy('id', 'asc')->paginate(2);

        $totalScans = ScanHistory::count();
        $cleanFiles = ScanHistory::where('scan_status', 'Clean')->count();
        $infectedFiles = ScanHistory::where('scan_status', 'Infected')->count();

        return view('upload', compact(
            'histories',
            'totalScans',
            'cleanFiles',
            'infectedFiles'
        ));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,docx,zip|max:5120'
        ]);

        $file = $request->file('file');

        $path = $file->store('uploads');

        $fullPath = storage_path('app/' . $path);

        $command = '"C:\\ClamAV\\clamscan.exe" "' . $fullPath . '"';

        exec($command, $output);

        $result = implode("\n", $output);

        $status = 'Clean';

        if (strpos($result, 'Infected files: 1') !== false) {

            $status = 'Infected';

            // Auto delete infected file
            Storage::delete($path);
        }

        ScanHistory::create([
            'file_name' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => round($file->getSize() / 1024, 2) . ' KB',
            'scan_status' => $status,
            'scan_output' => $result,
        ]);

        if ($status == 'Infected') {
            return back()->with('error', 'Virus Found ❌ File Deleted Automatically');
        }

        return back()->with('success', 'File Clean ✅');
    }
}