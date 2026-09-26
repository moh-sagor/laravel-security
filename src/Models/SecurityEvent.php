<?php

namespace Sagor\LaravelSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityEvent extends Model
{
    protected $table = 'shield_security_events';

    protected $fillable = [
        'event_id',
        'type',
        'severity',
        'confidence',
        'risk_score',
        'ip_hash',
        'ip_address_encrypted',
        'user_id',
        'route',
        'method',
        'user_agent_hash',
        'country',
        'payload_hash',
        'action',
        'metadata',
    ];

    protected $casts = [
        'confidence' => 'float',
        'risk_score' => 'integer',
        'metadata' => 'array',
    ];
}
