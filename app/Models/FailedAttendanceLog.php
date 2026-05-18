<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class FailedAttendanceLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'action_attempted',
        'reason',
        'captured_face_path',
        'network_info',
        'attempted_at',
        'tenant_id',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
