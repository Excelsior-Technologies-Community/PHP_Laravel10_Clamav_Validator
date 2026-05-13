<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanHistory extends Model
{
    protected $fillable = [
        'file_name',
        'original_name',
        'file_type',
        'file_size',
        'scan_status',
        'scan_output',
    ];
}