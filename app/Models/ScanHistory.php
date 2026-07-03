<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'original_name',
        'file_type',
        'file_size',
        'scan_status',
        'virus_name',
        'scan_output',
        'is_quarantined',
        'quarantine_path',
        'scan_duration',
    ];

    protected $casts = [
        'is_quarantined' => 'boolean',
        'scan_duration' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeClean($query)
    {
        return $query->where('scan_status', 'Clean');
    }

    public function scopeInfected($query)
    {
        return $query->where('scan_status', 'Infected');
    }

    public function scopePending($query)
    {
        return $query->where('scan_status', 'Pending');
    }

    public function scopeQuarantined($query)
    {
        return $query->where('is_quarantined', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('scan_status', 'Failed');
    }

    public function quarantine()
    {
        return $this->hasOne(Quarantine::class);
    }

    // ✅ FIXED: Renamed to avoid conflict with Laravel's isClean()
    public function isScanClean(): bool
    {
        return $this->scan_status === 'Clean';
    }

    public function isScanInfected(): bool
    {
        return $this->scan_status === 'Infected';
    }

    public function isScanPending(): bool
    {
        return $this->scan_status === 'Pending';
    }

    public function isFileQuarantined(): bool
    {
        return $this->is_quarantined === true;
    }

    public function getFileSizeInKB(): string
    {
        return $this->file_size . ' KB';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->scan_status) {
            'Clean' => 'bg-success',
            'Infected' => 'bg-danger',
            'Pending' => 'bg-warning text-dark',
            'Failed' => 'bg-dark',
            'Error' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getStatusIcon(): string
    {
        return match($this->scan_status) {
            'Clean' => 'fa-check-circle',
            'Infected' => 'fa-exclamation-triangle',
            'Pending' => 'fa-spinner fa-spin',
            'Failed' => 'fa-times-circle',
            'Error' => 'fa-exclamation-circle',
            default => 'fa-circle'
        };
    }
}