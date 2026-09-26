<?php

namespace Sagor\LaravelSecurity\Firewall\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class RequestSizeRule implements SecurityRule
{
    /**
     * @return string
     */
    public function getId(): string
    {
        return 'request_size.detector';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Detects oversized request payloads exceeding configured security limits.';
    }

    /**
     * @param SecurityContext $context
     * @return SecurityRuleResult
     */
    public function check(SecurityContext $context): SecurityRuleResult
    {
        $maxSize = (int) config('security.max_request_size', config('shield.max_request_size', 10485760));
        $contentLength = (int) $context->getRequest()->header('Content-Length', 0);

        if ($contentLength > 0 && $contentLength > $maxSize) {
            return SecurityRuleResult::threat(
                $this->getId(),
                'high',
                0.99,
                85,
                sprintf('Oversized request payload detected: %d bytes (limit: %d bytes).', $contentLength, $maxSize),
                ['content_length' => $contentLength, 'max_size' => $maxSize]
            );
        }

        return SecurityRuleResult::clean($this->getId());
    }
}
