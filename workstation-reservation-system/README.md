# Workstation Reservation System for Jitume Lab – STVC

## Overview
The Workstation Reservation System is a web-based application designed for the Jitume Lab at Seme Technical and Vocational College (STVC). This system allows students and staff to register, log in, and reserve available computers/workstations in the lab. It includes features for access control, reservation scheduling, and reporting for lab administrators.

## System Objectives
- **User Account Management**: Create user accounts for students and staff with validation, password encryption, and user roles (super_admin/admin/user).
- **User Authentication & Access Control**: Implement a secure login system using sessions with different dashboards for super admins, admins, and normal users.
- **Computer Reservation Functionality**: Users can view available workstations and reserve them for specific time slots, with prevention of double-booking. Admins can manage workstation availability and reservations.
- **Report Generation**: Admins can generate reports on reservation statistics, user activity, peak hours, and workstation usage.
- **Role-based Security**: Super admins have exclusive permissions to create admins, promote users to admin, and manage all user roles.

## Technical Stack
- **Backend**: PHP (Object-Oriented Programming preferred)
- **Frontend**: HTML5, CSS3, JavaScript (Bootstrap, Bootstrap icons, Sweetalert)
- **Database**: MySQL
- **Authentication**: PHP Sessions, optional use of tokens
- **Optional**: AJAX for smoother user experience

## Key Features
- Responsive User Interface
- Role-based access (User vs Admin vs Super Admin)
- Real-time workstation availability
- Admin dashboard with system statistics
- Secure data handling and sanitization
- Clean and documented code structure
- Super admin exclusive permissions for user role management

## Installation
1. Clone the repository:
   ```
   git clone https://github.com/yourusername/workstation-reservation-system.git
   ```
2. Navigate to the project directory:
   ```
   cd workstation-reservation-system
   ```
3. Install dependencies using Composer:
   ```
   composer install
   ```
4. Configure the database settings in the `.env` file based on the `.env.example` template.
5. Run the SQL schema to set up the database:
   ```
   mysql -u username -p database_name < sql/schema.sql
   ```
6. If you have an existing database, run the migration script:
   ```
   mysql -u username -p database_name < sql/migrate_to_super_admin.sql
   ```
7. Create the first super admin user:
   ```
   # Via browser: Navigate to /src/scripts/create_super_admin.php
   # Via CLI: php src/scripts/create_super_admin.php
   ```
8. Start the server and access the application through your web browser.

## Usage
- **User Registration**: Navigate to the registration page to create an account.
- **User Login**: Log in to access the user dashboard and reserve workstations.
- **Admin Dashboard**: Admins can manage reservations, view reports, and monitor user activity.
- **Super Admin Features**: Super admins have exclusive permissions to:
  - Create new admin users
  - Promote regular users to admin role
  - Delete admin users
  - Manage all user roles
  - Cannot be deleted by other users

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for details.