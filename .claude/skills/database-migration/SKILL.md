---
name: database-migration
description: Create and test Laravel database migrations with rollback validation
user-invocable: true
---

# Database Migration Skill

Create, test, and document Laravel database migrations safely.

## Capabilities

- **Schema Creation**: New tables, columns, indices, foreign keys
- **Schema Modifications**: Column changes, renaming, dropping
- **Relationships**: Define proper foreign key constraints
- **Testing**: Validate migrations with up/down cycle
- **Documentation**: Update execution ledger with schema changes

## Usage Examples

```
/database-migration Create a migration for a new payment_methods table
/database-migration Add status column to transactions with default value
/database-migration Create migration to add indexes for performance
```

## Workflow

1. Analyze required schema changes
2. Generate migration file with:
   - Proper timestamp naming
   - Clear method names (up/down)
   - Indexed columns
   - Foreign key constraints
   - Sensible defaults
3. Test migration:
   - Fresh migrate: `php artisan migrate:fresh`
   - Verify schema: `php artisan tinker`
   - Test rollback: `php artisan migrate:rollback`
4. Document changes in execution ledger
5. Commit with clear message

## Best Practices

- Use `php artisan make:migration` to generate skeleton
- Always test both up and down migrations
- Add indices for frequently queried columns
- Use proper constraints (nullable, default, unsigned)
- Keep database consistent with tests (.env.testing)
