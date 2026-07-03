<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ScanHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ScanFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;

    protected $history;

    public function __construct(ScanHistory $history)
    {
        $this->history = $history;
    }

    public function handle(): void
    {
        $filePath = storage_path('app/uploads/' . $this->history->file_name);

        if (!file_exists($filePath)) {
            $this->history->update([
                'scan_status' => 'Failed',
                'scan_output' => 'File not found'
            ]);
            return;
        }

        try {
            $startTime = microtime(true);

            $output = shell_exec('clamdscan --no-summary ' . escapeshellarg($filePath) . ' 2>&1');

            Log::info('ClamAV Output: ' . $output);

            $duration = round(microtime(true) - $startTime, 2);

            if (str_contains($output, 'FOUND')) {
                preg_match('/:\s+(.+)\s+FOUND/', $output, $matches);
                $virusName = $matches[1] ?? 'Unknown Virus';

                $this->history->update([
                    'scan_status' => 'Infected',
                    'virus_name' => $virusName,
                    'scan_output' => $output,
                    'scan_duration' => $duration,
                ]);

                $this->moveToQuarantine($filePath);

            } elseif (str_contains($output, 'OK')) {
                $this->history->update([
                    'scan_status' => 'Clean',
                    'scan_output' => 'File scanned successfully - No threats detected',
                    'scan_duration' => $duration,
                ]);

            } else {
                $this->history->update([
                    'scan_status' => 'Error',
                    'scan_output' => $output ?: 'Unknown scan error',
                    'scan_duration' => $duration,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Scan error: ' . $e->getMessage());
            $this->history->update([
                'scan_status' => 'Failed',
                'scan_output' => 'Scan error: ' . $e->getMessage()
            ]);
        }
    }

    private function moveToQuarantine($filePath)
    {
        $quarantinePath = storage_path('app/quarantine');
        if (!file_exists($quarantinePath)) {
            mkdir($quarantinePath, 0777, true);
        }

        $fileName = basename($filePath);
        $newPath = $quarantinePath . '/' . $fileName;

        if (rename($filePath, $newPath)) {
            $this->history->update([
                'is_quarantined' => true,
                'quarantine_path' => 'quarantine/' . $fileName,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Scan job failed: ' . $exception->getMessage());
        $this->history->update([
            'scan_status' => 'Failed',
            'scan_output' => 'Job failed: ' . $exception->getMessage()
        ]);
    }
}