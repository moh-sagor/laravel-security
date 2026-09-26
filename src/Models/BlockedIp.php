<?php

namespace Sagor\LaravelSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $table = 'shield_blocked_ips';

    protected $fillable = [
        'ip_address',
        'ip_hash',
        'reason',
        'is_permanent',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
