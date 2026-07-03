<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ScanHistory;
use App\Models\Quarantine;
use App\Jobs\ScanFileJob;
use Illuminate\Support\Facades\Log;

class FileScanController extends Controller
{
    public function index(Request $request)
    {
        $query = ScanHistory::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('scan_status', $request->status);
        }

        $histories = $query->orderBy('id', 'desc')->paginate(10);

        $stats = [
            'total' => ScanHistory::count(),
            'clean' => ScanHistory::where('scan_status', 'Clean')->count(),
            'infected' => ScanHistory::where('scan_status', 'Infected')->count(),
            'quarantined' => ScanHistory::where('is_quarantined', true)->count(),
            'pending' => ScanHistory::where('scan_status', 'Pending')->count(),
        ];

        return view('upload', compact('histories', 'stats'));
    }

    public function scan(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:1048576'
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();

            $uniqueName = time() . '_' . rand(1000, 9999) . '.' . $extension;
            $uploadPath = storage_path('app/uploads');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $file->move($uploadPath, $uniqueName);

            $history = ScanHistory::create([
                'file_name' => $uniqueName,
                'original_name' => $originalName,
                'file_type' => strtoupper($extension ?: 'UNKNOWN'),
                'file_size' => round($fileSize / 1024, 2) . ' KB',
                'scan_status' => 'Pending',
                'scan_output' => 'Queued for scanning...',
                'virus_name' => null,
                'is_quarantined' => false,
            ]);

            ScanFileJob::dispatch($history);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded and queued for scanning',
                'scan_id' => $history->id
            ]);

        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scanStatus($id)
    {
        $history = ScanHistory::findOrFail($id);
        return response()->json([
            'id' => $history->id,
            'status' => $history->scan_status,
            'virus_name' => $history->virus_name,
            'is_quarantined' => $history->is_quarantined,
        ]);
    }

    public function quarantineList()
    {
        $quarantined = ScanHistory::where('is_quarantined', true)->get();
        return view('quarantine', compact('quarantined'));
    }

    public function restoreQuarantine($id)
    {
        $history = ScanHistory::findOrFail($id);
        $filePath = storage_path('app/quarantine/' . $history->file_name);

        if (file_exists($filePath)) {
            $restorePath = storage_path('app/uploads/' . $history->file_name);
            rename($filePath, $restorePath);
            $history->update(['is_quarantined' => false]);
        }

        return back()->with('success', 'File restored from quarantine');
    }

    public function deleteQuarantine($id)
    {
        $history = ScanHistory::findOrFail($id);
        $filePath = storage_path('app/quarantine/' . $history->file_name);

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $history->delete();
        return back()->with('success', 'Quarantined file deleted');
    }

    public function deleteHistory($id)
    {
        try {
            $history = ScanHistory::findOrFail($id);
            $filePath = storage_path('app/uploads/' . $history->file_name);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $history->delete();
            return back()->with('success', 'Record deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete record');
        }
    }

    public function export()
    {
        $histories = ScanHistory::all();
        $filename = 'scan_report_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['ID', 'Original Name', 'File Type', 'File Size', 'Scan Status', 'Virus Name', 'Quarantined', 'Scanned At']);

        foreach ($histories as $history) {
            fputcsv($handle, [
                $history->id,
                $history->original_name,
                $history->file_type,
                $history->file_size,
                $history->scan_status,
                $history->virus_name ?? 'N/A',
                $history->is_quarantined ? 'Yes' : 'No',
                $history->created_at->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);
        exit;
    }
}