<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class ParameterPollutionRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'parameter_pollution.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects HTTP Parameter Pollution (HPP) attempts.';
    }

    /**
     * @param SecurityContext $context
     * @return SecurityRuleResult
     */
    public function check(SecurityContext $context): SecurityRuleResult
    {
        $queryString = (string) $context->getRequest()->server('QUERY_STRING', '');
        if (strlen($queryString) < 5) {
            return SecurityRuleResult::clean($this->getId());
        }

        // Parse query string manually to detect duplicate non-array parameters (e.g. ?id=1&id=2)
        $pairs = explode('&', $queryString);
        $paramCounts = [];

        foreach ($pairs as $pair) {
            if ($pair === '') {
                continue;
            }
            $parts = explode('=', $pair, 2);
            $paramName = urldecode($parts[0]);

            // Ignore legitimate array parameters like item[]
            if (substr($paramName, -2) === '[]') {
                continue;
            }

            if (!isset($paramCounts[$paramName])) {
                $paramCounts[$paramName] = 0;
            }
            $paramCounts[$paramName]++;
        }

        foreach ($paramCounts as $param => $count) {
            if ($count > 1) {
                return SecurityRuleResult::threat(
                    $this->getId(),
                    'medium',
                    0.90,
                    55,
                    'HTTP Parameter Pollution (HPP) detected for parameter: ' . $param,
                    ['param' => $param, 'count' => $count]
                );
            }
        }

        return SecurityRuleResult::clean($this->getId());
    }
}
