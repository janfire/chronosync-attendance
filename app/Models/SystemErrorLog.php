<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemErrorLog extends Model
{
    protected $fillable = [
        'reference_code', 'tenant_id', 'user_id', 'url', 'method', 
        'message', 'stack_trace', 'status'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
