<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AttendanceException extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'type',
        'note',
        'status',
        'requested_by',
        'approved_by',
        'requested_at',
        'handled_at',
        'meta',
        'tenant_id',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'handled_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
