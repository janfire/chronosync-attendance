<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceScore extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id', 'attendance_date',
        'clock_in_log_id', 'clock_out_log_id',
        'clock_in_time', 'clock_out_time',
        'clock_in_status', 'clock_out_status',
        'clock_in_points', 'clock_out_points', 'total_points',
        'tenant_id',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    const MAX_DAILY_POINTS = 15;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clockInLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class, 'clock_in_log_id');
    }

    public function clockOutLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class, 'clock_out_log_id');
    }

    public function clockInStatusColor(): string
    {
        return match($this->clock_in_status) {
            'early'    => 'bg-green-100 text-green-800',
            'punctual' => 'bg-blue-100 text-blue-800',
            'on_time'  => 'bg-yellow-100 text-yellow-800',
            'grace'    => 'bg-orange-100 text-orange-800',
            'late'     => 'bg-red-100 text-red-800',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public function clockOutStatusColor(): string
    {
        return match($this->clock_out_status) {
            'normal'          => 'bg-green-100 text-green-800',
            'late_departure'  => 'bg-blue-100 text-blue-800',
            'left_early'      => 'bg-yellow-100 text-yellow-800',
            'early_departure' => 'bg-red-100 text-red-800',
            default           => 'bg-gray-100 text-gray-500',
        };
    }

    public function clockInStatusLabel(): string
    {
        return match($this->clock_in_status) {
            'early'    => 'Early Arrival',
            'punctual' => 'Punctual',
            'on_time'  => 'On Time',
            'grace'    => 'Grace Period',
            'late'     => 'Late Arrival',
            default    => 'Absent',
        };
    }

    public function clockOutStatusLabel(): string
    {
        return match($this->clock_out_status) {
            'normal'          => 'Normal',
            'late_departure'  => 'Late Departure',
            'left_early'      => 'Left Early',
            'early_departure' => 'Early Departure',
            default           => 'Absent',
        };
    }
}
