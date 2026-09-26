<?php

namespace Sagor\LaravelSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityRuleModel extends Model
{
    protected $table = 'shield_security_rules';

    protected $fillable = [
        'rule_id',
        'name',
        'category',
        'severity',
        'enabled',
        'score',
        'description',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'score' => 'integer',
    ];
}
