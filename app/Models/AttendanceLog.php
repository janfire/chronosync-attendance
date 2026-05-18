<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'timestamp',
        'network_info',
        'latitude',
        'longitude',
        'location_accuracy',
        'location_name',
        'tenant_id',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for clock in/out actions
    public function scopeClockIns($query)
    {
        return $query->where('action', 'clock_in');
    }

    public function scopeClockOuts($query)
    {
        return $query->where('action', 'clock_out');
    }

    // Scope for today's records
    public function scopeToday($query)
    {
        return $query->whereDate('timestamp', today());
    }
}
