<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quarantine extends Model
{
    protected $table = 'quarantines';

    protected $fillable = [
        'original_path',
        'quarantine_path',
        'file_name',
        'original_name',
        'virus_name',
        'scan_history_id',
        'scanned_at',
        'restored_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'restored_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scanHistory(): BelongsTo
    {
        return $this->belongsTo(ScanHistory::class);
    }

    public function scopeAvailable($query)
    {
        return $query->whereNull('restored_at');
    }

    public function scopeRestored($query)
    {
        return $query->whereNotNull('restored_at');
    }

    public function scopeByVirus($query, $virusName)
    {
        return $query->where('virus_name', $virusName);
    }

    public function isRestored(): bool
    {
        return $this->restored_at !== null;
    }

    public function getVirusNameDisplay(): string
    {
        return $this->virus_name ?? 'Unknown Virus';
    }

    public function getQuarantinePathDisplay(): string
    {
        return basename($this->quarantine_path);
    }
}