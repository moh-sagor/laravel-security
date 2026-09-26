# Package Architecture Overview

```
Incoming HTTP Request
         ↓
Request Normalizer (PayloadNormalizer)
         ↓
Security Context (SecurityContext)
         ↓
Rule Registry Pipeline (RuleRegistry -> SecurityRule[])
         ↓
Rule Results (SecurityRuleResult[])
         ↓
Risk Scoring Engine (RiskScore)
         ↓
Policy Evaluation (SecurityPolicy)
         ↓
Security Decision (SecurityDecision: ALLOW / LOG / THROTTLE / BLOCK)
         ↓
Event Dispatching & Redacted Logging (SecurityLogger / Events)
```

## Core Abstractions

* `SecurityEngine`: Central orchestrator running inspectors and returning decisions.
* `SecurityContext`: Encapsulates normalized payloads, client IP, route, headers, and user context.
* `SecurityRuleResult`: Structured result returned by each detector rule.
* `RiskScore`: Aggregates rule result scores (0 to 100) and confidence ratings.
* `SecurityPolicy`: Maps risk scores and mode configurations (`monitor`, `balanced`, `strict`) to final HTTP decisions.
