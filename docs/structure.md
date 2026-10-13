# Project Structure

This is a Laravel 8 Point of Sale (POS) application built with a modular architecture using [Laravel Modules](https://github.com/nWidart/laravel-modules).

## Tech Stack

- **PHP**: 8.1.34
- **Laravel**: 8.75
- **Laravel Modules**: ^8.0 (modular monolith architecture)
- **Frontend**: Blade templates, Laravel Mix, jQuery/DataTables (yajra/laravel-datatables)
- **Database**: MySQL (Laravel migrations)
- **Authentication**: Laravel Auth with custom User model in Employee module
- **Authorization**: Custom RBAC (Role-Based Access Control) system with permissions

## Root Directory Structure

```
pos/
├── app/                    # Core Laravel application
├── bootstrap/              # Bootstrap files
├── config/                 # Laravel configuration files
├── database/               # Core database migrations, factories, seeders
├── docs/                   # Project documentation
├── Modules/                # Modular domain packages
├── public/                 # Web root
├── resources/              # Views, CSS, JS, assets
├── routes/                 # Web/API routes
├── storage/                # App storage
├── tests/                  # Test files
├── vendor/                 # Composer dependencies
├── composer.json           # PHP dependencies
├── package.json            # Node dependencies
└── modules_statuses.json   # Active modules
```

## Core Application (`app/`)

The core app contains shared utilities, base classes, and foundational components.

```
app/
├── Console/                # Artisan commands
├── Core/
│   └── Helper.php          # Utility helper functions (file upload, text formatting, number to words)
├── DataTables/            # DataTables classes
├── Exceptions/            # Exception handlers
├── Http/
│   ├── Controllers/       # Base/core controllers
│   │   ├── Controller.php  # Base controller
│   │   ├── HomeController.php
│   │   ├── CompanyController.php
│   │   ├── SettingController.php
│   │   ├── AsideSettingController.php
│   │   ├── DashboardSettingController.php
│   │   └── CacheController.php
│   ├── Middleware/        # HTTP middleware
│   │   ├── Authenticate.php
│   │   ├── EnsureRole.php      # Role-based access control
│   │   ├── EnsurePermission.php # Permission-based access control
│   │   ├── RedirectIfAuthenticated.php
│   │   ├── VerifyCsrfToken.php
│   │   └── ...
│   └── Traits/
│       └── RoleTrait.php       # Role/permission checking logic for models
├── Models/                # Core Eloquent models
│   ├── UIDModel.php       # Base model with UUID auto-generation
│   ├── Company.php
│   ├── Setting.php
│   └── DashboardSetting.php
├── Providers/             # Service providers
├── Repositories/          # Repository pattern implementations
├── Services/              # Core services
└── View/                  # View components/composers
```

### Key Core Concepts

- **UIDModel**: Base model that auto-generates UUIDs in the `uid` field on creation and uses `uid` as primary key by default (with `resetKey()` to switch back to `id`)
- **Helper**: Common utilities for file uploads (with image resizing via Intervention Image), string manipulation, and number-to-words conversion
- **RoleTrait**: Provides `hasPermission()`, `hasRole()`, and `role()` relationship - works with polymorphic mappings via morphClassName resolution
- **Custom Middleware**: `EnsureRole` and `EnsurePermission` for route-level authorization

## Modules (`Modules/`)

This app uses nwidart/laravel-modules for domain separation. Each module follows a standard structure.

### Active Modules

From `modules_statuses.json` and module configs:
1. **ChartOfAccounts** - Chart of accounts/accounting functionality
2. **Employee** - User management, authentication, employees, attendance
3. **Inventory** - Products, categories, stocks, promotions, barcode generation
4. **Order** - Sales, orders, refunds, payments
5. **RBAC** - Roles, permissions, access control
6. **Supplier** - Suppliers, purchase orders

### Standard Module Structure

Each module typically contains:
```
ModuleName/
├── Config/
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Traits/
├── Models/
├── Providers/
│   └── ModuleNameServiceProvider.php  # Registers views, migrations, routes, morph maps
├── Repositories/
├── Resources/
│   └── views/                         # Blade templates (namespaced)
├── routes/
│   └── web.php
├── Services/
├── Tests/
└── module.json
```

### 1. Employee Module (Core Auth)

Handles authentication, users, and employee management.

**Key Models:**
- `User` (`Modules/Employee/Models/User.php`) - Custom User model (extends Authenticatable)
  - Uses UUID (`uid`) as primary key, auto-generated via `Helper::_uid()`
  - Uses traits: `HasApiTokens`, `Notifiable`, `EmployeeTrait`, `RoleTrait`
  - Has relationships to company, role (via user_roles), employee info, attendance
  - Auth guard configured in `config/auth.php` to use this model

**Controllers:**
- `LoginController.php` - Login/logout handling
- `UserController.php`, `EmployeeController.php`, `AttendanceHistoryController.php`

**Routes** (`Modules/Employee/routes/web.php`):
- `any('login')` - Login page/action
- `post('logout')` - Logout
- Authenticated routes for users, employees, attendance (with permission middleware)

### 2. RBAC Module (Authorization)

Role-Based Access Control system.

**Key Models:**
- `Role.php` - User roles, has permissions through pivot
- `Permission.php` - Individual permissions
- `PermissionModule.php` - Permission module grouping
- `PermissionRole.php` - Pivot role-permissions
- `UserRole.php` - Pivot user-roles (polymorphic user_type)

**Polymorphic Mappings** (in RBACServiceProvider):
- `role` → `Role::class`
- `permission` → `Permission::class`
- `user_role` → `UserRole::class`

**Routes:**
- `roles` resource (permission: `view role`)
- `permissions` resource (permission: `view permission`)

### 3. Inventory Module

Manages products, categories, stock, promotions.

**Models:** Product, Category, Stock, Promotion, etc.
**Controllers:** ProductController, CategoryController, StockController, PromotionController
**Key Features:** Barcode printing (`print_barcode`), stock management

**Routes:** Resource routes for categories, products, promotions, stocks with permissions; print routes for stock reports and barcodes

### 4. Order Module

Handles sales and orders.

**Controllers:** SaleController, OrderController
**Routes:** Resource routes for sales, orders; order actions (cancel, mark paid, refund); print routes for invoices/reports

### 5. Supplier Module

Supplier and purchase order management.

### 6. ChartOfAccounts Module

Accounting/chart of accounts functionality.

## Authentication & Authorization

### Authentication
- Uses Laravel's session-based auth (`web` guard)
- Custom User model: `Modules\Employee\Models\User::class` (configured in `config/auth.php`)
- Login flow handled by `Modules/Employee\Services/LoginService.php`
- On successful login, sets session with company settings

### Authorization (Custom RBAC)

The app implements a custom RBAC system (not Spatie Permission package). Key components:

1. **RoleTrait** (`app/Http/Traits/RoleTrait.php`): Mixed into User model
   - `hasRole($role)` - Check if user has a specific role name
   - `hasPermission($permission)` - Check if user's role has a specific permission name
   - `role()` - Get user's role via hasOneThrough through user_roles pivot

2. **Role Model** (`Modules/RBAC/Models/Role.php`): 
   - Has many permissions through `PermissionRole` pivot
   - `hasPermission($name)` method

3. **Middleware:**
   - `role:superadmin` - Route middleware to check role
   - `permission:view product` - Route middleware to check permission
   - Defined in `app/Http/Kernel.php`

4. **Blade Directives** (`app/Providers/AppServiceProvider.php`):
   - `@can('permission')` / `@endcan`
   - `@role('role')` / `@endrole`
   - `@setting('name')` - Access company setting

5. **Polymorphic Relations:**
   - Uses `Relation::morphMap()` in module service providers to map aliases (`role`, `user_role`, `permission`) to actual classes
   - This allows flexible model references

## Database Design

### Core Tables (Root Migrations)

- `companies` - Multi-tenant/company support
- `settings` - Company/application settings
- `dashboard_settings` - Dashboard configuration
- `failed_jobs`, `personal_access_tokens` - Laravel defaults

### Module Migrations

Each module has its own migrations under `Modules/{Module}/Database/Migrations/`. Notable:
- **Employee**: `users`, `password_resets`, `employee_personal_information`, `attendance_histories`
- **RBAC**: `roles`, `permissions`, `permission_modules`, `permission_roles`, `user_roles`
- **Others**: Domain-specific tables for inventory, orders, suppliers, chart of accounts

### Multi-tenancy

The app appears to support multi-company/multi-tenant setup:
- `Company` model
- `users.company_id` links to companies
- Settings scoped by `company_id` (User model has `setting()` method)
- Many models have `scopeFilter()` that scopes by `company_id` for non-superadmin users

## Routing

### Web Routes (`routes/web.php`)
- `GET /clear-cache` - Clear cache (artisan optimize:clear)
- Authenticated group:
  - `GET /` → HomeController@index (dashboard)
  - Superadmin only: companies resource
  - Settings routes with permission middleware (general, public dashboard, aside/sidebar)

### Module Routes
Each module registers its web routes in its ServiceProvider (typically under web middleware group). Routes are namespaced by module and use permission middleware.

### API Routes (`routes/api.php`)
Standard Laravel API routes.

## Views & Frontend

- Blade templates organized by module in `Modules/{Module}/Resources/views/`
- Core views in `resources/views/` (layouts, components, settings, etc.)
- Laravel Mix for asset compilation (`package.json`)
- Uses jQuery, DataTables (yajra/laravel-datatables-oracle ^9.21)

## File Uploads

Handled by `Helper::uploadFile()` which:
- Accepts file, width, height, optional filename, destination path
- Resizes images using Intervention Image (^2.7) if dimensions specified
- Returns generated filename

## Barcode Generation

Supports barcodes via:
- `milon/barcode` ^9.0
- `picqer/php-barcode-generator` ^2.2
- `simplesoftwareio/simple-qrcode` ^4.2

## Code Conventions & Architecture Constraints

### Layered Architecture Rules

The following dependency flow must be strictly enforced:

```
Request → Route (middlewares allowed)
       → Controller (can import: helpers, error handlers, services)
       → Service (can import: helpers, other services, repositories)
       → Repository (can import: models)
```

**Strict Constraints:**
- **Controllers** may import Models for type hints and route model binding only. They MUST NOT perform direct database queries using Models (no `Model::find()`, `Model::where()`, etc.) and MUST NOT call Repositories directly. All data access must go through Services.
- **Services** may import/use Models for type hints and return types only. They MUST NOT perform direct database queries using Models. All data access must go through Repositories.
- **Repositories** are the only layer permitted to directly interact with Eloquent Models and perform database queries.

> See [architecture-constraints.md](./architecture-constraints.md) for detailed rules and examples.
- **Routes** may import/apply middleware only.
- Helpers and utility classes may be imported across layers as needed.

### Code Patterns

- Uses UID-based models (extends `UIDModel`) for business entities in many cases
- Repository pattern used (Repositories directories in app and modules)
- Service layer for business logic (Services directories)
- Form Requests for validation
- DataTables classes for table rendering
- Transactional operations in controllers (DB::beginTransaction/commit/rollback)
- Permission-based access control on routes and in UI (Blade directives)

## Configuration Notes

- Default string length set to 191 in AppServiceProvider (for older MySQL compatibility)
- Auth uses custom User model from Employee module
- Modules configured in `config/modules.php` and `modules_statuses.json`
- CORS handled via `fruitcake/laravel-cors` ^2.0
