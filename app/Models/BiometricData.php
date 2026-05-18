<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricData extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'user_name',
        'hand_preference',
        'fingerprint_data',
        'fingerprint_template',
        'fingerprint_device_id',
        'facial_data',
        'facial_encoding',
        'fingerprint_status',
        'facial_status',
        'fingerprint_captured_at',
        'facial_captured_at',
        'tenant_id',
    ];

    protected $casts = [
        'fingerprint_captured_at' => 'datetime',
        'facial_captured_at' => 'datetime',
        'facial_encoding' => 'array',
        'fingerprint_template' => 'encrypted', // Encrypt fingerprint data
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}