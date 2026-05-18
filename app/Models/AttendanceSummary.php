<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id', 'period_type', 'period_start', 'period_end',
        'total_score', 'max_possible_score', 'score_percentage', 'grade',
        'days_present', 'days_absent',
        'early_count', 'punctual_count', 'on_time_count', 'grace_count', 'late_count',
        'normal_out_count', 'left_early_count', 'early_departure_count',
        'tenant_id',
    ];

    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'score_percentage' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gradeColor(): string
    {
        return match($this->grade) {
            'A' => 'bg-green-100 text-green-800 border-green-200',
            'B' => 'bg-blue-100 text-blue-800 border-blue-200',
            'C' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'D' => 'bg-orange-100 text-orange-800 border-orange-200',
            'F' => 'bg-red-100 text-red-800 border-red-200',
            default => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    }

    public function gradeLabel(): string
    {
        return match($this->grade) {
            'A' => 'Excellent',
            'B' => 'Good',
            'C' => 'Average',
            'D' => 'Below Average',
            'F' => 'Poor',
            default => 'N/A',
        };
    }
}
