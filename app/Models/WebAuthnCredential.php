<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebAuthnCredential extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'webauthn_credentials';

    protected $fillable = [
        'user_id',
        'credential_id',
        'public_key',
        'counter',
        'device_type',
        'tenant_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}