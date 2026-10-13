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
| **Controllers** | Helpers, Error Handlers, Services, Form Requests | Repositories, Models (Eloquent models) |
| **Services** | Helpers, Other Services, Repositories | Models (Eloquent models). Note: Services may receive/return model instances from repositories, but must not `use` Model classes directly for database operations. |
| **Repositories** | Models (Eloquent models), Helpers | Controllers, Services, Routes |

## Enforcement Guidelines

1. **Controllers**: If you need data access, create or call a Service method. Never do `Product::find()`, `ProductRepository::find()`, or import `Product` model in a controller.
2. **Services**: All database queries must go through Repositories. If a repository doesn't exist for an operation, create one. Avoid direct model usage.
3. **Repositories**: This is the data access layer - it's the only place where Eloquent ORM should be used directly.
4. **Transactions**: Controllers typically manage DB transactions and call services within them (as seen in existing codebase patterns).

## Current Violations

The codebase currently has some violations of these rules. See the analysis below for what needs to be refactored.

### Controller Violations (Must Fix)
Controllers importing Models or Repositories directly:
- `app/Http/Controllers/DashboardSettingController.php` - imports `DashboardSetting` model
- `app/Http/Controllers/SettingController.php` - imports `Setting` model  
- `app/Http/Controllers/CompanyController.php` - imports `Company` model
- `app/Http/Controllers/AsideSettingController.php` - imports `AsideSetting` model
- `app/Http/Controllers/DefaultController.php` - imports `Company` model
- `Modules/ChartOfAccounts/Http/Controllers/AccountSettingController.php` - imports `Company` model
- `Modules/ChartOfAccounts/Http/Controllers/VoucherTypeController.php` - imports `VoucherType` model
- `Modules/ChartOfAccounts/Http/Controllers/VoucherController.php` - imports `Voucher` model
- `Modules/RBAC/Http/Controllers/RoleController.php` - imports `Role` model
- `Modules/RBAC/Http/Controllers/PermissionController.php` - imports `Permission` model
- `Modules/Order/Http/Controllers/OrderController.php` - imports `Order` model
- `Modules/Supplier/Http/Controllers/PurchaseOrderController.php` - imports `PurchaseOrder` model
- `Modules/Supplier/Http/Controllers/SupplierController.php` - imports `Supplier` model
- `Modules/Inventory/Http/Controllers/ProductController.php` - imports `Product` model
- `Modules/Inventory/Http/Controllers/CategoryController.php` - imports `Category` model
- `Modules/Inventory/Http/Controllers/PromotionController.php` - imports `Promotion` model
- `Modules/Employee/Http/Controllers/UserController.php` - imports `User` model
- `Modules/Employee/Http/Controllers/AttendanceHistoryController.php` - imports `AttendanceHistory` model
- `Modules/Employee/Http/Controllers/EmployeeController.php` - imports `User` model

### Service Violations (Should Fix)
Services importing Models directly instead of going through Repositories:
- `app/Services/HomeService.php` - imports `Company`, `User` models
- `app/Services/SettingService.php` - imports `Setting` model
- `Modules/Order/Services/OrderService.php` - imports `Order` model
- `Modules/Supplier/Services/PurchaseOrderService.php` - imports `PurchaseOrder` model
- `Modules/Supplier/Services/SupplierService.php` - imports `Supplier` model
- `Modules/Inventory/Services/CategoryService.php` - imports `Category` model

*Note: ServiceProviders importing models for morphMap configuration is acceptable as this is registration/config code, not business logic.*
