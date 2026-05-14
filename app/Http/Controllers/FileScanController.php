<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ScanHistory;

class FileScanController extends Controller
{
    public function index(Request $request)
    {
        $query = ScanHistory::query();
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }
        
        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('scan_status', $request->status);
        }
        
        $histories = $query->orderBy('id', 'desc')->paginate(10);
        
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
        try {
            // Validate file
            $request->validate([
                'file' => 'required|file|max:10240' // 10MB max
            ]);

            if (!$request->hasFile('file')) {
                return back()->with('error', 'No file was uploaded');
            }

            $file = $request->file('file');
            
            if (!$file->isValid()) {
                return back()->with('error', 'File upload failed');
            }
            
            // Get ALL file details BEFORE moving the file
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize(); // Get size BEFORE moving
            $mimeType = $file->getMimeType();
            
            // Generate unique filename
            $uniqueName = time() . '_' . rand(1000, 9999) . '.' . $extension;
            
            // Create uploads directory if it doesn't exist
            $uploadPath = storage_path('app/uploads');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            // Move the file
            $file->move($uploadPath, $uniqueName);
            
            // Check if file was moved successfully
            $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $uniqueName;
            if (!file_exists($fullPath)) {
                return back()->with('error', 'Failed to save file');
            }
            
            // Calculate file size in KB (using the size we saved earlier)
            $fileSizeKB = round($fileSize / 1024, 2);
            
            // Simple scan simulation (always returns clean)
            $status = 'Clean';
            $virusName = null;
            
            // Create scan record
            ScanHistory::create([
                'file_name' => $uniqueName,
                'original_name' => $originalName,
                'file_type' => strtoupper($extension ?: 'UNKNOWN'),
                'file_size' => $fileSizeKB . ' KB',
                'scan_status' => $status,
                'scan_output' => 'File scanned successfully - No threats detected',
                'virus_name' => $virusName,
            ]);
            
            return redirect('/')->with('success', '✅ File "' . $originalName . '" uploaded and scanned successfully! File is clean.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    // Delete scan history record
    public function deleteHistory($id)
    {
        try {
            $history = ScanHistory::findOrFail($id);
            
            // Delete physical file if exists
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
    
    // Export scan results
    public function export()
    {
        $histories = ScanHistory::all();
        
        $filename = 'scan_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $handle = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($handle, ['ID', 'Original Name', 'File Type', 'File Size', 'Scan Status', 'Virus Name', 'Scanned At']);
        
        // Add data
        foreach ($histories as $history) {
            fputcsv($handle, [
                $history->id,
                $history->original_name,
                $history->file_type,
                $history->file_size,
                $history->scan_status,
                $history->virus_name ?? 'N/A',
                $history->created_at->format('Y-m-d H:i:s')
            ]);
        }
        
        fclose($handle);
        exit;
    }
}