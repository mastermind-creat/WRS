-- Migration script to add super_admin role support
-- Run this script on existing databases to update the users table

-- Update the role ENUM to include super_admin
ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'user') DEFAULT 'user';

-- Note: After running this migration, you should create a super admin user
-- using the create_super_admin.php script before using the system 