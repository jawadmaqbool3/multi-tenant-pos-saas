# Architecture Constraints

This document defines the mandatory layer separation rules for this codebase.

## Dependency Rules

The application follows a strict layered architecture with unidirectional dependencies:

```
Request → Route (middlewares)
       ↓
    Controller (helpers, error handlers, services)
       ↓
      Service (helpers, other services, repositories)
       ↓
    Repository (models)
```

## Allowed Imports by Layer

| Layer | Can Import | Cannot Import |
|---|---|---|
| **Routes** | Middlewares | Controllers, Services, Repositories, Models |
| **Controllers** | Helpers, Error Handlers, Services, Form Requests, Models (for type hints/route model binding only) | Repositories. Controllers should not perform database queries using Models. |
| **Services** | Helpers, Other Services, Repositories, Models (for type hints/return types only) | Services may use Models for type hints, but must not perform database queries directly on Model classes. All DB operations go through Repositories. |
| **Repositories** | Models (Eloquent models), Helpers | Controllers, Services, Routes |

## Enforcement Guidelines

1. **Controllers**: If you need data access, create or call a Service method. Controllers may use Models for route model binding/type hints, but must NOT perform database queries (no `Product::find()`, `Product::where()`, etc.). Also must not call Repositories directly.
2. **Services**: All database queries must go through Repositories. If a repository doesn't exist for an operation, create one. Services may use Models for type hints/return types, but must NOT call Model query methods directly (no `Model::query()`, `Model::find()`, static calls).
3. **Repositories**: This is the data access layer - it's the only place where Eloquent ORM/Model static/instance query methods should be used directly.
4. **Transactions**: Controllers typically manage DB transactions and call services within them (as seen in existing codebase patterns).

## Current Violations

The codebase currently has some violations of these rules. See the analysis below for what needs to be refactored.

### Controller Violations (Must Fix)
Controllers importing Models or Repositories directly for non-type-hint purposes (or doing direct queries):
- Many controllers import Models for route model binding - this is now allowed if only used as type hints. However, no direct DB queries using Models.
- Controllers must not import Repositories. If they do, that needs to be fixed to go through Services.

### Service Violations (Should Fix)
Services performing direct Model queries or importing Models for query operations (type hints are fine, but direct DB access via Models is not):
- `app/Services/HomeService.php` - uses `Company::count()`, `Company::where()`, `User::whereNotNull()` (direct model queries)
- `app/Services/SettingService.php` - may be fine with type hints; check for direct model queries
- Other services importing Models for query operations need to use Repositories instead

*Note: ServiceProviders importing models for morphMap configuration is acceptable as this is registration/config code, not business logic.*
