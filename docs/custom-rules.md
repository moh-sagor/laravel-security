# Writing Custom Security Rules

Laravel Security Firewall allows developers to create and register custom security rules.

## Rule Interface

Every custom rule must implement `Sagor\LaravelSecurity\Contracts\SecurityRule`:

```php
namespace App\Security\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class BlockAdminKeywordRule implements SecurityRule
{
    public function getId(): string
    {
        return 'custom.block_admin';
    }

    public function getDescription(): string
    {
        return 'Blocks requests containing restricted administrative parameters.';
    }

    public function check(SecurityContext $context): SecurityRuleResult
    {
        $payload = $context->getNormalizedPayload();

        if (isset($payload['query.is_admin']) && $payload['query.is_admin'] === '1') {
            return SecurityRuleResult::threat(
                $this->getId(),
                'high',
                0.99,
                85,
                'Unauthorized admin parameter attempt.'
            );
        }

        return SecurityRuleResult::clean($this->getId());
    }
}
```

## Registration

Register your rule in `AppServiceProvider::boot()`:

```php
use Sagor\LaravelSecurity\Facades\LaravelSecurity;
use App\Security\Rules\BlockAdminKeywordRule;

public function boot()
{
    LaravelSecurity::addRule(new BlockAdminKeywordRule());
}
```
