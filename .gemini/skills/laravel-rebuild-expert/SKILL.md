---
name: laravel-rebuild-expert
description: Specialized guidance for rebuilding legacy PHP applications into modern Laravel (v11+). Use when converting files from 'patokan' (reference folder) into Laravel migrations, models, controllers, and Blade views, ensuring PSR-12 standards and security.
---

# Laravel Rebuild Expert

You are an expert in Laravel and application modernization. Your goal is to migrate the legacy "Global Market Trade" application into this new Laravel workspace.

## Core Rebuild Workflow

1. **Database Migration:**
   - Refer to `patokan/global-market-trade/install/schema.sql`.
   - Generate Laravel migrations (`php artisan make:migration`) for each table.
   - Use proper Laravel data types (e.g., `$table->id()`, `$table->decimal('amount', 18, 2)`, `$table->timestamps()`).
   - Add foreign key constraints where applicable.

2. **Model Definition:**
   - Create Eloquent models in `app/Models/`.
   - Define `$fillable` or `$guarded` properties.
   - Set up relationships (e.g., `User hasMany WalletTransaction`).

3. **Authentication & Security:**
   - Use Laravel's built-in authentication (`Laravel Breeze` or `Fortify` if requested, or manual `Auth` guard).
   - Migrate session-based logic from `patokan/.../auth/` to Laravel Middleware and Controllers.
   - Ensure CSRF protection is active on all forms.

4. **Business Logic (Controllers & APIs):**
   - Convert logic from `api/` and `admin/api/` into Laravel Controllers.
   - Use Request Validation classes for input sanitization.
   - Return JSON responses for API routes and Blade views for Web routes.

5. **Frontend (Blade & Assets):**
   - Convert `partials/` and main PHP files into Blade templates (`resources/views/`).
   - Re-organize assets from `myasset/` and `user/` into `public/` or process via Vite.
   - Modernize Bootstrap 3 elements to current standards while maintaining the "patokan" visual identity.

## Critical Rules
- **Do NOT modify `patokan/`**: It is read-only.
- **Environment First**: Use `.env` for all configurations.
- **Testing**: Create feature tests for critical paths (Login, Deposit, Investment).
- **Naming**: Follow Laravel's naming conventions (StudlyCase for Models, camelCase for methods).

## Reference Files
- Database: `patokan/global-market-trade/install/schema.sql`
- Auth: `patokan/global-market-trade/auth/_auth.php`
- UI: `patokan/global-market-trade/index.php`
