# MedScript - Prescription Management System

A complete web-based solution for medical prescription upload and quotation management system.



## 🚀 Features Implemented

### Part A: User Features
- ✅ User registration (Name, email, address, contact, DOB)
- ✅ User login system
- ✅ Prescription upload (Max 5 images with note, delivery address, time slots)
- ✅ User dashboard with prescription history

### Part B: Pharmacy Features
- ✅ View uploaded prescriptions
- ✅ Prepare quotations with dynamic drug items
- ✅ Send quotations to users
- ✅ Email notifications
- ✅ Track quotation acceptance/rejection

### Additional Features
- ✅ Role-based access control (User/Pharmacy)
- ✅ Responsive Bootstrap UI
- ✅ File upload with validation
- ✅ Email notifications
- ✅ Real-time status updates

## 🔧 Installation Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional)

### Setup Steps
1. **Extract** the zip file to your web server directory
2. **Create MySQL database**: `prescription_system`
3. **Import database**: Execute `database.sql` in your MySQL
4. **Configure database**: Edit `config/db.php` with your credentials:
```php
$host = 'localhost';
$dbname = 'prescription_system';
$username = 'your_username';
$password = 'your_password';
