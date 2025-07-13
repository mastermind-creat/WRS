# Super Admin Implementation Guide

## Overview
The Workstation Reservation System now includes a three-tier role system with Super Admin, Admin, and User roles. Super Admins have exclusive permissions for user role management and cannot be deleted by other users.

## Role Hierarchy

### Super Admin (super_admin)
- **Highest privilege level**
- Can create admin users
- Can promote users to admin role
- Can delete admin users
- Can manage all user roles (user, admin, super_admin)
- Cannot be deleted by any user
- Access to all admin features

### Admin (admin)
- **Medium privilege level**
- Can manage reservations and workstations
- Can view reports and user activity
- Cannot create other admin users
- Cannot promote users to admin
- Cannot delete other admin users
- Cannot manage super admin roles

### User (user)
- **Basic privilege level**
- Can reserve workstations
- Can view their own reservations
- Cannot access admin features

## Database Changes

### Schema Updates
The `users` table role column has been updated to support the new role:

```sql
ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'user') DEFAULT 'user';
```

### Migration
For existing databases, run the migration script:
```sql
-- Run: sql/migrate_to_super_admin.sql
ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'user') DEFAULT 'user';
```

## Implementation Details

### 1. Authentication & Routing
- **File**: `src/controllers/AuthController.php`
- Super admins are routed to the admin dashboard
- Session stores the role for permission checking

### 2. User Management Restrictions
- **File**: `src/views/admin/users.php`
- Only super admins can promote users to admin
- Only super admins can delete admin users
- Super admins cannot be deleted
- Only super admins can create new admin users
- Role editing restrictions based on current user's role

### 3. Visual Indicators
- Super admin users have a red gradient badge with star icon
- Admin users have the original blue gradient badge
- Regular users have gray badges
- Action buttons are conditionally displayed based on permissions

### 4. Security Features
- CSRF protection on all user management actions
- Role validation before any privileged operations
- Proper error handling and user feedback
- Session-based role checking

## Setup Instructions

### 1. Database Migration
If you have an existing database:
```bash
mysql -u username -p database_name < sql/migrate_to_super_admin.sql
```

### 2. Create First Super Admin
**Option A: Browser**
1. Navigate to: `/src/scripts/create_super_admin.php`
2. Fill in the form with super admin credentials
3. Submit to create the first super admin

**Option B: Command Line**
```bash
php src/scripts/create_super_admin.php
```

### 3. Create Additional Admins
After creating a super admin, you can:
1. Log in as super admin
2. Use the admin panel to create additional admin users
3. Or use the CLI script: `php src/scripts/create_admin.php`

## Permission Matrix

| Action | Super Admin | Admin | User |
|--------|-------------|-------|------|
| View Users | ✅ | ✅ | ❌ |
| Create Admin | ✅ | ❌ | ❌ |
| Promote to Admin | ✅ | ❌ | ❌ |
| Delete Admin | ✅ | ❌ | ❌ |
| Delete Super Admin | ❌ | ❌ | ❌ |
| Edit User Roles | ✅ | Limited | ❌ |
| Delete Regular Users | ✅ | ✅ | ❌ |
| Manage Reservations | ✅ | ✅ | ❌ |
| View Reports | ✅ | ✅ | ❌ |

## Code Structure

### New Files
- `src/scripts/create_super_admin.php` - Super admin creation script
- `sql/migrate_to_super_admin.sql` - Database migration script
- `SUPER_ADMIN_IMPLEMENTATION.md` - This documentation

### Modified Files
- `sql/schema.sql` - Updated role ENUM
- `src/controllers/AuthController.php` - Updated routing
- `src/models/User.php` - Added role checking methods
- `src/views/admin/users.php` - Complete permission overhaul
- `src/scripts/create_admin.php` - Added super admin checks
- `README.md` - Updated documentation

### New Methods in User Model
- `isSuperAdmin($userId)` - Check if user is super admin
- `isAdmin($userId)` - Check if user is admin or super admin

## Security Considerations

1. **Role Validation**: All privileged operations check the current user's role
2. **CSRF Protection**: All forms include CSRF tokens
3. **Input Sanitization**: All user inputs are properly sanitized
4. **Session Security**: Role information is stored in secure sessions
5. **Error Handling**: Proper error messages without exposing system details

## Testing Checklist

- [ ] Super admin can create admin users
- [ ] Super admin can promote users to admin
- [ ] Super admin can delete admin users
- [ ] Super admin cannot be deleted
- [ ] Admin users cannot create other admins
- [ ] Admin users cannot promote users to admin
- [ ] Admin users cannot delete other admins
- [ ] Regular users cannot access admin features
- [ ] Role badges display correctly
- [ ] Action buttons show/hide based on permissions
- [ ] Error messages display for unauthorized actions

## Troubleshooting

### Common Issues

1. **"Only super admins can..." errors**
   - Ensure you're logged in as a super admin
   - Check that the user has the correct role in the database

2. **Super admin creation fails**
   - Ensure the database migration has been run
   - Check that no super admin already exists (only one can be created via script)

3. **Role changes not working**
   - Verify the database ENUM includes 'super_admin'
   - Check session variables are set correctly

### Database Queries for Debugging

```sql
-- Check all users and their roles
SELECT id, username, email, role FROM users ORDER BY role, username;

-- Check if super admin exists
SELECT COUNT(*) FROM users WHERE role = 'super_admin';

-- Check admin users
SELECT id, username, email FROM users WHERE role = 'admin';
```

## Future Enhancements

Potential improvements for the super admin system:
1. Audit logging for role changes
2. Super admin activity monitoring
3. Role-based API endpoints
4. Advanced permission granularity
5. Super admin dashboard with system health metrics 