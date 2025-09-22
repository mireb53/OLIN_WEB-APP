# OLIN User Management Module

## Overview
Fully featured user management with role-based access (Super Admin, School Admin, Admin, Instructor, Student), school scoping, policies, bulk import, and modal-driven UI.

## Roles & Permissions
- Super Admin: Full access across all schools (create/update/delete/reset any user).
- School Admin: Manage only users (instructor/student/admin) within their school. Cannot affect Super or other School Admins.
- Admin: Legacy/light admin (currently limited to viewing; extend as needed).
- Instructor / Student: Standard users (cannot manage others).

## Data Model Additions
- `schools` table with `name`, `code`, `status`.
- `users.school_id` nullable foreign key.
- Role constants defined in `User` model for consistency.

## Authorization
`App\Policies\UserPolicy` centralizes create/update/delete/reset logic.
Registered via `AuthServiceProvider`.

## Controller Enhancements
`UserManagementController` now:
- Applies school scoping for school admins.
- Uses policies for each mutating action.
- Supports filtering by role, status, search, and (Super Admin only) school.
- Bulk import auto-assigns `school_id` for school admins or accepts provided school for Super Admin.

## Blade UI (`resources/views/admin/user_management.blade.php`)
- Tabs for role context (instructor/student/admin).
- Filters: search, status, optional school selector.
- Conditional action buttons with `@can` directives.
- Add/Edit modals include school selection for Super Admin.
- Status badges (active/inactive/suspended).
- Bulk import only appears when Add User role = Student.

## JavaScript
- Handles modals open/close, dynamic edit population, school selection population, bulk import toggle.
- Data attributes used for transferring row state to modals.

## Seeders
- `SchoolSeeder` creates sample schools.
- `SuperAdminSeeder` creates `super_admin` and `school_admin` sample accounts.
- `DatabaseSeeder` calls both and adds a legacy admin.

## Bulk Import
- Accepts CSV. Required columns: Name, Email, Password.
- Assigns role=student, status=active, sets `school_id` (scoped or passed).
- Aggregates errors and reports summary message.

## Validation
- Enforces uniqueness of email, password confirmation, role inclusion, and school existence.
- School admin role assignment restricted to their own school internally.

## Next Extensions (Not Yet Implemented)
- Soft deletes / suspension audit trail.
- Excel parsing for .xlsx (currently CSV only).
- Frontend AJAX filtering for seamless UX.
- 2FA gating for destructive actions (framework present in controller already for code issuance).

## Quick Start
```
php artisan migrate
php artisan db:seed
php artisan serve
```
Log in as:
- superadmin@olin.test / SuperSecure123!
- schooladmin@olin.test / SchoolSecure123!

## Security Notes
- Policies prevent privilege escalation.
- School scoping enforced server-side; do not rely on form restrictions alone.

## Maintenance
- Add new roles via constants and policy adjustments.
- Extend bulk import parser when supporting Excel libraries.

---
Generated on: 2025-09-21
