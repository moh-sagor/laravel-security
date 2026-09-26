<?php

namespace Sagor\LaravelSecurity\Firewall;

use Sagor\LaravelSecurity\Contracts\SecurityRule;

class RuleRegistry
{
    /**
     * @var SecurityRule[]
     */
    protected $rules = [];

    /**
     * Register a security rule instance.
     *
     * @param SecurityRule $rule
     * @return $this
     */
    public function addRule(SecurityRule $rule)
    {
        $this->rules[$rule->getId()] = $rule;
        return $this;
    }

    /**
     * Remove a rule by ID.
     *
     * @param string $id
     * @return $this
     */
    public function removeRule(string $id)
    {
        unset($this->rules[$id]);
        return $this;
    }

    /**
     * Get all registered rules.
     *
     * @return SecurityRule[]
     */
    public function getRules(): array
    {
        return array_values($this->rules);
    }

    /**
     * Get a specific rule by ID.
     *
     * @param string $id
     * @return SecurityRule|null
     */
    public function getRule(string $id)
    {
        return isset($this->rules[$id]) ? $this->rules[$id] : null;
    }

    /**
     * Check if a rule is registered.
     *
     * @param string $id
     * @return bool
     */
    public function hasRule(string $id): bool
    {
        return isset($this->rules[$id]);
    }
}
