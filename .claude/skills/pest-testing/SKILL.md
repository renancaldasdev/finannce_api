---
name: pest-testing
description: Generate and run Pest tests for Laravel controllers, models, and services
user-invocable: true
---

# Pest Testing Skill

Generate comprehensive Pest tests for Laravel components.

## Capabilities

- **Model Tests**: Accessors, mutators, scopes, relationships, factories
- **Controller Tests**: Feature tests for API endpoints, request validation, authentication
- **Service Tests**: Unit tests for business logic, transaction handling
- **Integration Tests**: Database transactions, observers, state changes
- **Database Tests**: Seeders, migrations, schema validation

## Usage Examples

```
/pest-testing Generate tests for the Account model
/pest-testing Create feature tests for transaction API endpoints
/pest-testing Write tests for the UpdateTransactionService
```

## Workflow

1. Analyze the component (model, controller, service)
2. Generate appropriate test structure (Unit/Feature)
3. Create test cases with:
   - Proper test data using factories
   - Clear test descriptions
   - Appropriate assertions
   - Database transactions for isolation
4. Run tests: `composer test`
5. Document test coverage in execution ledger
