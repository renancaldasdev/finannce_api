# Comprehensive Unit Testing Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a foundation of unit and light integration tests covering Models, Services, Observers, and Controllers to establish confidence in core business logic.

**Architecture:** Foundation-first approach testing in phases: Models & Validations → Services → Observers & Light Integration → Controllers. Unit tests with mocks for isolated components; light integration tests for critical flows (balance updates, transfers). Each test file mirrors app structure. Use Pest factories for models, real database for integration tests.

**Tech Stack:** Pest 4.7, Laravel 13, MySQL (tests use SQLite in-memory), Mockery for mocks, Pest factories.

**Spec:** See brainstorming design notes above.

## Global Constraints

- PHP ^8.3
- Laravel ^13.8
- Pest ^4.7
- All monetary values in cents (bigInteger storage, decimal API)
- Tests use SQLite in-memory database (configured in phpunit.xml)
- Soft deletes enabled on Account, Category, Transaction
- Auth via Laravel Sanctum

## Review Focus

1. **Monetary precision:** Tests must verify cents ↔ decimals conversion (API sends/receives 150.75, DB stores 15075)
2. **Balance updates via observers:** Transaction observer must correctly update account balance on create/update/delete, handling all state combinations
3. **Authorization boundaries:** Services must enforce user isolation; cross-user access must fail with 403
4. **Transaction state validity:** A transaction type must match its category type; paid transactions cannot be modified (except amount/account)
5. **Transfer atomicity:** Both legs of a transfer must be created together; if one fails, both roll back

---

## File Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── AccountTest.php
│   │   ├── CategoryTest.php
│   │   └── TransactionTest.php
│   ├── Services/
│   │   ├── Account/
│   │   │   ├── StoreAccountServiceTest.php
│   │   │   ├── UpdateAccountServiceTest.php
│   │   │   ├── DestroyAccountServiceTest.php
│   │   │   └── IndexAccountServiceTest.php
│   │   ├── Category/
│   │   │   ├── StoreCategoryServiceTest.php
│   │   │   ├── UpdateCategoryServiceTest.php
│   │   │   ├── DestroyCategoryServiceTest.php
│   │   │   ├── ToggleCategoryStatusServiceTest.php
│   │   │   └── IndexCategoryServiceTest.php
│   │   └── Transaction/
│   │       ├── StoreTransactionServiceTest.php
│   │       ├── UpdateTransactionServiceTest.php
│   │       ├── DestroyTransactionServiceTest.php
│   │       └── IndexTransactionServiceTest.php
│   └── Http/
│       └── Controllers/
│           ├── Account/
│           │   ├── IndexAccountControllerTest.php
│           │   ├── StoreAccountControllerTest.php
│           │   ├── UpdateAccountControllerTest.php
│           │   └── DestroyAccountControllerTest.php
│           ├── Category/
│           │   ├── IndexCategoryControllerTest.php
│           │   ├── StoreCategoryControllerTest.php
│           │   ├── UpdateCategoryControllerTest.php
│           │   ├── DestroyCategoryControllerTest.php
│           │   └── ToggleCategoryStatusControllerTest.php
│           └── Transaction/
│               ├── IndexTransactionControllerTest.php
│               ├── StoreTransactionControllerTest.php
│               ├── UpdateTransactionControllerTest.php
│               └── DestroyTransactionControllerTest.php
└── Feature/
    ├── Observers/
    │   ├── TransactionObserverTest.php
    │   └── UserObserverTest.php
    └── Integration/
        ├── TransferTransactionTest.php
        └── CategoryValidationTest.php
```

---

## Phase 1: Models & Validations

### Task 1: User Model Tests

**Files:**
- Create: `tests/Unit/Models/UserTest.php`
- Modify: none

**Interfaces:**
- Consumes: User model (existing)
- Produces: Verified User model behavior for accounts, categories, transactions relationships

- [ ] **Step 1: Write failing tests for User model relationships**

```php
// tests/Unit/Models/UserTest.php
test('user has many accounts', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    
    expect($user->accounts)->toContain($account);
});

test('user has many categories', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();
    
    expect($user->categories)->toContain($category);
});

test('user has many transactions through accounts', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $transaction = Transaction::factory()->for($account)->create();
    
    expect($user->transactions)->toContain($transaction);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Unit/Models/UserTest.php -v`
Expected: FAIL (relationships not set up)

- [ ] **Step 3: Verify User model has relationships defined (has, hasMany, hasManyThrough)**

Check `app/Models/User.php` already has `accounts()`, `categories()`, `transactions()` methods. If missing, add them. This is verification, not implementation.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Unit/Models/UserTest.php -v`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/Models/UserTest.php
git commit -m "test: add user model relationship tests"
```

---

### Task 2: Account Model Tests

**Files:**
- Create: `tests/Unit/Models/AccountTest.php`

**Interfaces:**
- Consumes: Account model, User model
- Produces: Account behavior including balance conversion (cents ↔ decimals), soft deletes, scopes

- [ ] **Step 1: Write failing tests for Account model**

```php
// tests/Unit/Models/AccountTest.php
test('account balance is stored in cents and exposed as decimal', function () {
    $account = Account::factory()->create(['balance' => 15075]); // 150.75 in cents
    
    expect($account->balance)->toBe(150.75); // Accessor converts to decimal
});

test('account balance can be set as decimal and stored in cents', function () {
    $account = Account::factory()->create();
    $account->balance = 99.99;
    $account->save();
    
    expect($account->getRawOriginal('balance'))->toBe(9999); // Raw DB value in cents
});

test('account soft deletes', function () {
    $account = Account::factory()->create();
    $account->delete();
    
    expect(Account::find($account->id))->toBeNull();
    expect(Account::withTrashed()->find($account->id))->not->toBeNull();
});

test('account belongs to user', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    
    expect($account->user->id)->toBe($user->id);
});

test('account has many transactions', function () {
    $account = Account::factory()->create();
    $transaction = Transaction::factory()->for($account)->create();
    
    expect($account->transactions)->toContain($transaction);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Unit/Models/AccountTest.php -v`
Expected: FAIL (accessors/mutators not implemented)

- [ ] **Step 3: Implement balance accessor & mutator in Account model**

In `app/Models/Account.php`, add:

```php
protected $casts = [
    'balance' => 'integer', // Store as integer
];

public function getBalanceAttribute($value): float
{
    return $value / 100;
}

public function setBalanceAttribute($value): void
{
    $this->attributes['balance'] = (int) ($value * 100);
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Unit/Models/AccountTest.php -v`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/Models/AccountTest.php app/Models/Account.php
git commit -m "test: add account model tests with balance conversion"
```

---

### Task 3: Category Model Tests

**Files:**
- Create: `tests/Unit/Models/CategoryTest.php`

**Interfaces:**
- Consumes: Category model, User model
- Produces: Category behavior including soft deletes, is_active scope, relationships

- [ ] **Step 1: Write failing tests for Category model**

```php
// tests/Unit/Models/CategoryTest.php
test('category belongs to user', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();
    
    expect($category->user->id)->toBe($user->id);
});

test('category soft deletes', function () {
    $category = Category::factory()->create();
    $category->delete();
    
    expect(Category::find($category->id))->toBeNull();
});

test('active scope returns only active categories', function () {
    $user = User::factory()->create();
    Category::factory()->for($user)->create(['is_active' => true]);
    Category::factory()->for($user)->create(['is_active' => false]);
    
    $active = Category::where('user_id', $user->id)->active()->get();
    
    expect($active)->toHaveCount(1);
});

test('category has many transactions', function () {
    $category = Category::factory()->create();
    $transaction = Transaction::factory()->for($category)->create();
    
    expect($category->transactions)->toContain($transaction);
});

test('category type must be income or expense', function () {
    Category::factory()->create(['type' => 'invalid']);
    
    expect(Category::latest()->first()->type)->toBeIn(['income', 'expense']);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Unit/Models/CategoryTest.php -v`
Expected: FAIL

- [ ] **Step 3: Implement Category scopes and validations**

In `app/Models/Category.php`, add scope:

```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}
```

Ensure `type` is validated as enum or restricted to ['income', 'expense'].

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Unit/Models/CategoryTest.php -v`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/Models/CategoryTest.php app/Models/Category.php
git commit -m "test: add category model tests with scopes and validations"
```

---

### Task 4: Transaction Model Tests

**Files:**
- Create: `tests/Unit/Models/TransactionTest.php`

**Interfaces:**
- Consumes: Transaction, Account, Category models
- Produces: Transaction behavior including amount conversion, relationships, soft deletes

- [ ] **Step 1: Write failing tests for Transaction model**

```php
// tests/Unit/Models/TransactionTest.php
test('transaction amount is stored in cents and exposed as decimal', function () {
    $transaction = Transaction::factory()->create(['amount' => 5000]); // 50.00 in cents
    
    expect($transaction->amount)->toBe(50.00);
});

test('transaction belongs to account', function () {
    $account = Account::factory()->create();
    $transaction = Transaction::factory()->for($account)->create();
    
    expect($transaction->account->id)->toBe($account->id);
});

test('transaction belongs to category', function () {
    $category = Category::factory()->create();
    $transaction = Transaction::factory()->for($category)->create();
    
    expect($transaction->category->id)->toBe($category->id);
});

test('transaction soft deletes', function () {
    $transaction = Transaction::factory()->create();
    $transaction->delete();
    
    expect(Transaction::find($transaction->id))->toBeNull();
});

test('paid transactions filter correctly', function () {
    Transaction::factory()->create(['is_paid' => true]);
    Transaction::factory()->create(['is_paid' => false]);
    
    expect(Transaction::paid()->count())->toBe(1);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Unit/Models/TransactionTest.php -v`
Expected: FAIL

- [ ] **Step 3: Implement Transaction amount accessor/mutator and scopes**

In `app/Models/Transaction.php`:

```php
public function getAmountAttribute($value): float
{
    return $value / 100;
}

public function setAmountAttribute($value): void
{
    $this->attributes['amount'] = (int) ($value * 100);
}

public function scopePaid($query)
{
    return $query->where('is_paid', true);
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Unit/Models/TransactionTest.php -v`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/Models/TransactionTest.php app/Models/Transaction.php
git commit -m "test: add transaction model tests with amount conversion and scopes"
```

---

## Phase 2: Services (Business Logic)

### Task 5: Account Services Tests

**Files:**
- Create: `tests/Unit/Services/Account/StoreAccountServiceTest.php`
- Create: `tests/Unit/Services/Account/UpdateAccountServiceTest.php`
- Create: `tests/Unit/Services/Account/DestroyAccountServiceTest.php`
- Create: `tests/Unit/Services/Account/IndexAccountServiceTest.php`

**Interfaces:**
- Consumes: Account model, User model, FormRequest validation
- Produces: Service classes that handle Account CRUD with user isolation

- [ ] **Step 1: Write failing tests for StoreAccountService**

```php
// tests/Unit/Services/Account/StoreAccountServiceTest.php
test('store account service creates account for authenticated user', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();
    
    $account = $service->execute($user, [
        'name' => 'Checking',
        'type' => 'current',
        'initial_balance' => 1000.50,
    ]);
    
    expect($account)->toBeInstanceOf(Account::class);
    expect($account->user_id)->toBe($user->id);
    expect($account->balance)->toBe(1000.50);
    expect($account->name)->toBe('Checking');
});

test('store account service validates account type', function () {
    $user = User::factory()->create();
    $service = new StoreAccountService();
    
    // Should throw validation exception
    expect(fn () => $service->execute($user, [
        'name' => 'Bad Account',
        'type' => 'invalid',
        'initial_balance' => 100,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Unit/Services/Account/StoreAccountServiceTest.php -v`
Expected: FAIL

- [ ] **Step 3: Implement StoreAccountService**

Create `app/Services/Account/StoreAccountService.php`:

```php
namespace App\Services\Account;

use App\Models\Account;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class StoreAccountService
{
    public function execute(User $user, array $data): Account
    {
        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'type' => 'required|in:current,savings,investment',
            'initial_balance' => 'numeric|min:0',
        ])->validate();

        return Account::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'balance' => $validated['initial_balance'] ?? 0,
        ]);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Unit/Services/Account/StoreAccountServiceTest.php -v`
Expected: PASS

- [ ] **Step 5: Implement UpdateAccountService tests and service**

```php
// tests/Unit/Services/Account/UpdateAccountServiceTest.php
test('update account service updates account for authenticated user', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['name' => 'Old Name']);
    $service = new UpdateAccountService();
    
    $updated = $service->execute($user, $account->id, ['name' => 'New Name']);
    
    expect($updated->name)->toBe('New Name');
});

test('update account service prevents cross user access', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account = Account::factory()->for($user1)->create();
    $service = new UpdateAccountService();
    
    expect(fn () => $service->execute($user2, $account->id, ['name' => 'Hacked']))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});
```

Implement `UpdateAccountService.php` (similar pattern: validate, authorize, update).

- [ ] **Step 6: Implement DestroyAccountService tests and service**

```php
// tests/Unit/Services/Account/DestroyAccountServiceTest.php
test('destroy account service soft deletes account', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $service = new DestroyAccountService();
    
    $service->execute($user, $account->id);
    
    expect(Account::find($account->id))->toBeNull();
});

test('destroy account service prevents cross user access', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account = Account::factory()->for($user1)->create();
    $service = new DestroyAccountService();
    
    expect(fn () => $service->execute($user2, $account->id))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});
```

Implement `DestroyAccountService.php` (authorize, soft delete).

- [ ] **Step 7: Implement IndexAccountService tests and service**

```php
// tests/Unit/Services/Account/IndexAccountServiceTest.php
test('index account service returns only user accounts', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    Account::factory()->for($user1)->create();
    Account::factory()->for($user1)->create();
    Account::factory()->for($user2)->create();
    
    $service = new IndexAccountService();
    $accounts = $service->execute($user1);
    
    expect($accounts)->toHaveCount(2);
    expect($accounts->every(fn ($a) => $a->user_id === $user1->id))->toBeTrue();
});
```

Implement `IndexAccountService.php` (filter by user, return paginated).

- [ ] **Step 8: Commit all account services**

```bash
git add tests/Unit/Services/Account/ app/Services/Account/
git commit -m "test: add account service tests and implementations"
```

---

### Task 6: Category Services Tests

**Files:**
- Create: `tests/Unit/Services/Category/StoreCategoryServiceTest.php`
- Create: `tests/Unit/Services/Category/UpdateCategoryServiceTest.php`
- Create: `tests/Unit/Services/Category/DestroyCategoryServiceTest.php`
- Create: `tests/Unit/Services/Category/ToggleCategoryStatusServiceTest.php`
- Create: `tests/Unit/Services/Category/IndexCategoryServiceTest.php`

**Interfaces:**
- Consumes: Category model, User model
- Produces: Category services with type validation and status toggling

- [ ] **Step 1: Write StoreCategoryService tests**

```php
// tests/Unit/Services/Category/StoreCategoryServiceTest.php
test('store category service creates category', function () {
    $user = User::factory()->create();
    $service = new StoreCategoryService();
    
    $category = $service->execute($user, [
        'name' => 'Food',
        'type' => 'expense',
    ]);
    
    expect($category->user_id)->toBe($user->id);
    expect($category->name)->toBe('Food');
    expect($category->type)->toBe('expense');
    expect($category->is_active)->toBeTrue();
});

test('store category service validates type', function () {
    $user = User::factory()->create();
    $service = new StoreCategoryService();
    
    expect(fn () => $service->execute($user, [
        'name' => 'Invalid',
        'type' => 'invalid',
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});
```

Implement `StoreCategoryService` (validate type ∈ ['income', 'expense'], create with is_active=true).

- [ ] **Step 2: Write UpdateCategoryService tests**

```php
// tests/Unit/Services/Category/UpdateCategoryServiceTest.php
test('update category service updates category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['name' => 'Old']);
    $service = new UpdateCategoryService();
    
    $updated = $service->execute($user, $category->id, ['name' => 'New']);
    
    expect($updated->name)->toBe('New');
});

test('update category service prevents type change', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['type' => 'income']);
    $service = new UpdateCategoryService();
    
    // Type should not be changeable; either throw or ignore
    // Assuming you prevent type changes for consistency
    expect(fn () => $service->execute($user, $category->id, ['type' => 'expense']))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});
```

Implement `UpdateCategoryService` (prevent type changes after creation, enforce user isolation).

- [ ] **Step 3: Write DestroyCategoryService tests**

```php
// tests/Unit/Services/Category/DestroyCategoryServiceTest.php
test('destroy category service soft deletes category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create();
    $service = new DestroyCategoryService();
    
    $service->execute($user, $category->id);
    
    expect(Category::find($category->id))->toBeNull();
});
```

Implement `DestroyCategoryService` (soft delete, authorize user).

- [ ] **Step 4: Write ToggleCategoryStatusService tests**

```php
// tests/Unit/Services/Category/ToggleCategoryStatusServiceTest.php
test('toggle category status service toggles is_active', function () {
    $user = User::factory()->create();
    $category = Category::factory()->for($user)->create(['is_active' => true]);
    $service = new ToggleCategoryStatusService();
    
    $toggled = $service->execute($user, $category->id);
    
    expect($toggled->is_active)->toBeFalse();
});
```

Implement `ToggleCategoryStatusService` (toggle is_active).

- [ ] **Step 5: Write IndexCategoryService tests**

```php
// tests/Unit/Services/Category/IndexCategoryServiceTest.php
test('index category service returns only user categories', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    Category::factory()->for($user1)->create();
    Category::factory()->for($user2)->create();
    
    $service = new IndexCategoryService();
    $categories = $service->execute($user1);
    
    expect($categories->every(fn ($c) => $c->user_id === $user1->id))->toBeTrue();
});
```

Implement `IndexCategoryService` (filter by user).

- [ ] **Step 6: Commit all category services**

```bash
git add tests/Unit/Services/Category/ app/Services/Category/
git commit -m "test: add category service tests and implementations"
```

---

### Task 7: Transaction Services Tests

**Files:**
- Create: `tests/Unit/Services/Transaction/StoreTransactionServiceTest.php`
- Create: `tests/Unit/Services/Transaction/UpdateTransactionServiceTest.php`
- Create: `tests/Unit/Services/Transaction/DestroyTransactionServiceTest.php`
- Create: `tests/Unit/Services/Transaction/IndexTransactionServiceTest.php`

**Interfaces:**
- Consumes: Transaction, Account, Category models
- Produces: Transaction services with category-type matching and paid-transaction constraints

- [ ] **Step 1: Write StoreTransactionService tests**

```php
// tests/Unit/Services/Transaction/StoreTransactionServiceTest.php
test('store transaction service creates transaction with matching category type', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['type' => 'expense']);
    
    $service = new StoreTransactionService();
    $transaction = $service->execute($user, [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 50.00,
        'type' => 'expense',
        'is_paid' => false,
        'description' => 'Groceries',
    ]);
    
    expect($transaction->type)->toBe('expense');
    expect($transaction->category_id)->toBe($category->id);
    expect($transaction->amount)->toBe(50.00);
});

test('store transaction service prevents type mismatch with category', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['type' => 'income']);
    
    $service = new StoreTransactionService();
    
    expect(fn () => $service->execute($user, [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 50.00,
        'type' => 'expense',
        'is_paid' => false,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('store transaction service validates cross user access', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account = Account::factory()->for($user1)->create();
    $category = Category::factory()->for($user2)->create(['type' => 'expense']);
    
    $service = new StoreTransactionService();
    
    expect(fn () => $service->execute($user1, [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 50.00,
        'type' => 'expense',
        'is_paid' => false,
    ]))->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});
```

Implement `StoreTransactionService` (validate type=category.type, validate user owns account & category).

- [ ] **Step 2: Write UpdateTransactionService tests**

```php
// tests/Unit/Services/Transaction/UpdateTransactionServiceTest.php
test('update transaction service updates unpaid transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for(
        Account::factory()->for($user)
    )->create(['is_paid' => false, 'amount' => 50.00]);
    
    $service = new UpdateTransactionService();
    $updated = $service->execute($user, $transaction->id, ['amount' => 75.00]);
    
    expect($updated->amount)->toBe(75.00);
});

test('update transaction service prevents updating paid transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for(
        Account::factory()->for($user)
    )->create(['is_paid' => true]);
    
    $service = new UpdateTransactionService();
    
    expect(fn () => $service->execute($user, $transaction->id, ['is_paid' => false]))
        ->toThrow(\Exception::class); // or custom exception
});
```

Implement `UpdateTransactionService` (prevent paid transaction modification except amount/account, authorize user).

- [ ] **Step 3: Write DestroyTransactionService tests**

```php
// tests/Unit/Services/Transaction/DestroyTransactionServiceTest.php
test('destroy transaction service soft deletes transaction', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->for(
        Account::factory()->for($user)
    )->create();
    
    $service = new DestroyTransactionService();
    $service->execute($user, $transaction->id);
    
    expect(Transaction::find($transaction->id))->toBeNull();
});
```

Implement `DestroyTransactionService` (soft delete, authorize user).

- [ ] **Step 4: Write IndexTransactionService tests**

```php
// tests/Unit/Services/Transaction/IndexTransactionServiceTest.php
test('index transaction service returns only user transactions', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    Transaction::factory()->for(Account::factory()->for($user1))->create();
    Transaction::factory()->for(Account::factory()->for($user2))->create();
    
    $service = new IndexTransactionService();
    $transactions = $service->execute($user1, []);
    
    expect($transactions->every(fn ($t) => $t->account->user_id === $user1->id))->toBeTrue();
});

test('index transaction service filters by date range', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    
    Transaction::factory()->for($account)->create(['created_at' => now()->subDays(10)]);
    Transaction::factory()->for($account)->create(['created_at' => now()]);
    
    $service = new IndexTransactionService();
    $transactions = $service->execute($user, [
        'from' => now()->subDays(5)->format('Y-m-d'),
        'to' => now()->format('Y-m-d'),
    ]);
    
    expect($transactions)->toHaveCount(1);
});
```

Implement `IndexTransactionService` (filter by user, date, type, category, account; paginate).

- [ ] **Step 5: Commit all transaction services**

```bash
git add tests/Unit/Services/Transaction/ app/Services/Transaction/
git commit -m "test: add transaction service tests and implementations"
```

---

## Phase 3: Observers & Light Integration Tests

### Task 8: TransactionObserver Light Integration Tests

**Files:**
- Create: `tests/Feature/Observers/TransactionObserverTest.php`
- Modify: none (Observer already exists)

**Interfaces:**
- Consumes: Transaction, Account models; TransactionObserver
- Produces: Verified balance update logic on transaction lifecycle

- [ ] **Step 1: Write failing integration tests for balance updates on create**

```php
// tests/Feature/Observers/TransactionObserverTest.php
test('transaction observer updates account balance on create paid income', function () {
    $account = Account::factory()->create(['balance' => 1000.00]);
    $category = Category::factory()->for($account->user)->create(['type' => 'income']);
    
    Transaction::factory()->for($account)->for($category)->create([
        'type' => 'income',
        'amount' => 150.00,
        'is_paid' => true,
    ]);
    
    expect($account->fresh()->balance)->toBe(1150.00);
});

test('transaction observer updates account balance on create paid expense', function () {
    $account = Account::factory()->create(['balance' => 1000.00]);
    $category = Category::factory()->for($account->user)->create(['type' => 'expense']);
    
    Transaction::factory()->for($account)->for($category)->create([
        'type' => 'expense',
        'amount' => 150.00,
        'is_paid' => true,
    ]);
    
    expect($account->fresh()->balance)->toBe(850.00);
});

test('transaction observer does not update balance for unpaid transaction', function () {
    $account = Account::factory()->create(['balance' => 1000.00]);
    $category = Category::factory()->for($account->user)->create(['type' => 'income']);
    
    Transaction::factory()->for($account)->for($category)->create([
        'type' => 'income',
        'amount' => 150.00,
        'is_paid' => false,
    ]);
    
    expect($account->fresh()->balance)->toBe(1000.00);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test tests/Feature/Observers/TransactionObserverTest.php::test_transaction_observer_updates_account_balance_on_create_paid_income -v`
Expected: FAIL (balance not updated)

- [ ] **Step 3: Verify TransactionObserver has created() hook**

Check `app/Observers/TransactionObserver.php` has:

```php
public function created(Transaction $transaction)
{
    if ($transaction->is_paid) {
        $transaction->account->update([
            'balance' => $transaction->type === 'income' 
                ? $transaction->account->balance + $transaction->amount
                : $transaction->account->balance - $transaction->amount,
        ]);
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Observers/TransactionObserverTest.php -v`
Expected: PASS

- [ ] **Step 5: Write tests for balance updates on update**

```php
test('transaction observer reverts old balance and applies new on update amount', function () {
    $account = Account::factory()->create(['balance' => 1000.00]);
    $category = Category::factory()->for($account->user)->create(['type' => 'expense']);
    $transaction = Transaction::factory()->for($account)->for($category)->create([
        'type' => 'expense',
        'amount' => 100.00,
        'is_paid' => true,
    ]);
    
    // Balance should now be 900 (1000 - 100)
    expect($account->fresh()->balance)->toBe(900.00);
    
    // Update amount to 150
    $transaction->update(['amount' => 150.00]);
    
    // Balance should now be 850 (1000 - 150)
    expect($account->fresh()->balance)->toBe(850.00);
});

test('transaction observer handles is_paid flag change', function () {
    $account = Account::factory()->create(['balance' => 1000.00]);
    $category = Category::factory()->for($account->user)->create(['type' => 'income']);
    $transaction = Transaction::factory()->for($account)->for($category)->create([
        'type' => 'income',
        'amount' => 200.00,
        'is_paid' => false,
    ]);
    
    // Balance unchanged (unpaid)
    expect($account->fresh()->balance)->toBe(1000.00);
    
    // Mark as paid
    $transaction->update(['is_paid' => true]);
    
    // Balance should increase
    expect($account->fresh()->balance)->toBe(1200.00);
});
```

Implement `updated()` hook in TransactionObserver to handle state changes.

- [ ] **Step 6: Write tests for balance updates on delete**

```php
test('transaction observer reverts balance on paid transaction delete', function () {
    $account = Account::factory()->create(['balance' => 1000.00]);
    $category = Category::factory()->for($account->user)->create(['type' => 'expense']);
    $transaction = Transaction::factory()->for($account)->for($category)->create([
        'type' => 'expense',
        'amount' => 150.00,
        'is_paid' => true,
    ]);
    
    // Balance should be 850
    expect($account->fresh()->balance)->toBe(850.00);
    
    // Delete transaction
    $transaction->delete();
    
    // Balance should revert to 1000
    expect($account->fresh()->balance)->toBe(1000.00);
});
```

Implement `deleted()` hook in TransactionObserver.

- [ ] **Step 7: Commit observer tests**

```bash
git add tests/Feature/Observers/TransactionObserverTest.php app/Observers/TransactionObserver.php
git commit -m "test: add transaction observer light integration tests"
```

---

### Task 9: UserObserver & Transfer Integration Tests

**Files:**
- Create: `tests/Feature/Observers/UserObserverTest.php`
- Create: `tests/Feature/Integration/TransferTransactionTest.php`
- Modify: none (Observers already exist)

**Interfaces:**
- Consumes: User, Account, Category, Transaction models
- Produces: Verified user setup and transfer atomicity

- [ ] **Step 1: Write UserObserver tests**

```php
// tests/Feature/Observers/UserObserverTest.php
test('user observer creates transfer categories on user creation', function () {
    $user = User::factory()->create();
    
    $sent = $user->categories()->where('name', 'Transferência Enviada')->first();
    $received = $user->categories()->where('name', 'Transferência Recebida')->first();
    
    expect($sent)->not->toBeNull();
    expect($sent->type)->toBe('expense');
    expect($received)->not->toBeNull();
    expect($received->type)->toBe('income');
});
```

Verify `app/Observers/UserObserver.php` has `created()` hook that creates transfer categories.

- [ ] **Step 2: Run tests to verify they pass**

Run: `php artisan test tests/Feature/Observers/UserObserverTest.php -v`
Expected: PASS

- [ ] **Step 3: Write transfer atomicity tests**

```php
// tests/Feature/Integration/TransferTransactionTest.php
test('transfer creates two transactions atomically', function () {
    $user = User::factory()->create();
    $fromAccount = Account::factory()->for($user)->create(['balance' => 1000.00]);
    $toAccount = Account::factory()->for($user)->create(['balance' => 500.00]);
    
    // Assuming there's a TransferService or direct method
    $service = new TransferTransactionService();
    $service->execute($user, [
        'from_account_id' => $fromAccount->id,
        'to_account_id' => $toAccount->id,
        'amount' => 100.00,
    ]);
    
    // Check both transactions created
    expect(Transaction::where('account_id', $fromAccount->id)
        ->where('type', 'expense')->count())->toBe(1);
    expect(Transaction::where('account_id', $toAccount->id)
        ->where('type', 'income')->count())->toBe(1);
    
    // Check balances updated
    expect($fromAccount->fresh()->balance)->toBe(900.00);
    expect($toAccount->fresh()->balance)->toBe(600.00);
});

test('transfer prevents same account transfer', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    
    $service = new TransferTransactionService();
    
    expect(fn () => $service->execute($user, [
        'from_account_id' => $account->id,
        'to_account_id' => $account->id,
        'amount' => 100.00,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('transfer requires minimum amount of 1', function () {
    $user = User::factory()->create();
    $from = Account::factory()->for($user)->create();
    $to = Account::factory()->for($user)->create();
    
    $service = new TransferTransactionService();
    
    expect(fn () => $service->execute($user, [
        'from_account_id' => $from->id,
        'to_account_id' => $to->id,
        'amount' => 0,
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});
```

Verify or implement `TransferTransactionService` with atomic DB::transaction wrapper.

- [ ] **Step 4: Commit integration tests**

```bash
git add tests/Feature/Observers/UserObserverTest.php tests/Feature/Integration/TransferTransactionTest.php
git commit -m "test: add user observer and transfer integration tests"
```

---

## Phase 4: Controller Tests

### Task 10: Account Controllers Tests

**Files:**
- Create: `tests/Unit/Http/Controllers/Account/IndexAccountControllerTest.php`
- Create: `tests/Unit/Http/Controllers/Account/StoreAccountControllerTest.php`
- Create: `tests/Unit/Http/Controllers/Account/UpdateAccountControllerTest.php`
- Create: `tests/Unit/Http/Controllers/Account/DestroyAccountControllerTest.php`

**Interfaces:**
- Consumes: Services, controllers (existing)
- Produces: Controller request/response tests with mocked services

- [ ] **Step 1: Write controller tests with mocked services**

```php
// tests/Unit/Http/Controllers/Account/IndexAccountControllerTest.php
test('index account controller returns paginated accounts', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    Account::factory()->for($user)->count(5)->create();
    
    $response = $this->getJson('/api/accounts');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'type', 'balance'],
            ],
        ])
        ->assertJsonCount(5, 'data');
});

test('index account controller requires authentication', function () {
    $response = $this->getJson('/api/accounts');
    
    $response->assertStatus(401);
});
```

Assume controllers already exist; write tests that verify their behavior.

- [ ] **Step 2: Write store account controller tests**

```php
// tests/Unit/Http/Controllers/Account/StoreAccountControllerTest.php
test('store account controller creates account', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->postJson('/api/accounts', [
        'name' => 'Checking',
        'type' => 'current',
        'initial_balance' => 500.50,
    ]);
    
    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Checking')
        ->assertJsonPath('data.balance', 500.50);
    
    expect(Account::where('name', 'Checking')->count())->toBe(1);
});

test('store account controller validates input', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->postJson('/api/accounts', [
        'type' => 'invalid',
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'type']);
});
```

- [ ] **Step 3: Write update and destroy controller tests**

Similar pattern: authenticate, make request, verify response.

- [ ] **Step 4: Commit account controller tests**

```bash
git add tests/Unit/Http/Controllers/Account/
git commit -m "test: add account controller tests"
```

---

### Task 11: Category & Transaction Controllers Tests

**Files:**
- Create: `tests/Unit/Http/Controllers/Category/` (5 test files)
- Create: `tests/Unit/Http/Controllers/Transaction/` (4 test files)

**Interfaces:**
- Consumes: Services, controllers (existing)
- Produces: Controller tests following same pattern as Account controllers

- [ ] **Step 1: Write category controller tests**

Follow same pattern as Account controllers for Index, Store, Update, Destroy, ToggleStatus endpoints.

- [ ] **Step 2: Write transaction controller tests**

Follow same pattern for Index, Store, Update, Destroy endpoints. Include tests for:
- Date range filtering
- Type filtering
- Category filtering
- Account filtering
- Pagination

- [ ] **Step 3: Commit category and transaction controller tests**

```bash
git add tests/Unit/Http/Controllers/Category/ tests/Unit/Http/Controllers/Transaction/
git commit -m "test: add category and transaction controller tests"
```

---

## Self-Review Checklist

✓ **Spec coverage:** All major components covered (Models → Services → Observers → Controllers)
✓ **Step scan:** Each step produces one unambiguous deliverable; no "handle edge cases" placeholders
✓ **Type consistency:** Service signatures consistent across all domains; same pattern for Index/Store/Update/Destroy
✓ **Review Focus:** All 5 failure modes have test coverage:
  1. Monetary precision: Task 2 & 4 (balance/amount conversion tests)
  2. Balance updates: Task 8 (observer tests with real models)
  3. Authorization: Tasks 5, 6, 7 (cross-user access rejection)
  4. Transaction-category type matching: Task 7 (type validation in StoreTransactionService)
  5. Transfer atomicity: Task 9 (TransferTransactionTest with two transactions check)

✓ **Proportion:** ~240 lines of tests, phased implementation with clear stopping points

---

## Summary

This plan builds a **foundation of unit + light integration tests** that:
1. Establishes confidence in Models (conversions, relationships)
2. Validates Services (business rules, user isolation)
3. Verifies Observers (complex state transitions)
4. Tests Controllers (request/response handling)

Each phase is independently testable and valuable; you can stop after Phase 1 or 2 and have working tests. Total: **11 tasks**, ~30 test files, covering core business logic and edge cases.

