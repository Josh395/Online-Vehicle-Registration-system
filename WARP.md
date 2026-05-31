# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is an **Online Vehicle Registration System** built for the Tanzania Revenue Authority (TRA). It's a complete web application that allows users to register vehicles online, make payments, and receive digital certificates. The system has both user-facing and administrative interfaces.

### Technology Stack
- **Backend**: PHP with PDO for database operations
- **Database**: MySQL (`vor` database)
- **PDF Generation**: TCPDF library for certificates, DOMPDF as backup
- **Frontend**: Vanilla HTML/CSS/JavaScript with Poppins font from Google Fonts
- **Server**: Designed for XAMPP/Apache environment

## Architecture and Structure

### Core Architecture Pattern
This follows a **traditional PHP MVC-style pattern** without a framework:
- **Front Controllers**: Each page is its own entry point (`index.php`, `dashboard.php`, `form.php`, etc.)
- **Shared Configuration**: `config.php` handles database connection and common functions
- **Template System**: `includes/header.php` and `includes/footer.php` for consistent layout
- **Authentication**: Session-based with role separation (users vs admins)

### Database Design
The system uses a **normalized relational database** with these core entities:
- **users**: Customer accounts (TIN-based authentication)
- **applications**: Vehicle registration requests with workflow states
- **admin_users**: Administrative staff with role-based permissions
- **payments**: Financial transaction records
- **notifications**: User communication system
- **uploads**: File attachment management

### Key Architectural Decisions
- **Stateful Workflow**: Applications progress through states (draft → submitted → under_review → approved/rejected)
- **File Management**: Uploads organized by application ID in `uploads/` directory
- **Dual Authentication**: Separate login systems for users (TIN-based) and admins (username-based)
- **PDF Generation**: Professional certificates with TCPDF, including security features

## Common Development Commands

### Database Management
```powershell
# Reset and recreate database schema
http://localhost/vehicleV2/reset-database.php

# Set up admin password (creates/updates admin user)
http://localhost/vehicleV2/setup-admin-password.php
```

### Local Development Setup
```powershell
# Start XAMPP services
Start-Service -Name "Apache2.4" -ErrorAction SilentlyContinue
Start-Service -Name "mysql" -ErrorAction SilentlyContinue

# Access application
Start-Process "http://localhost/vehicleV2"

# Access admin panel
Start-Process "http://localhost/vehicleV2/admin"
```

### File System Operations
```powershell
# Create uploads directory structure
New-Item -ItemType Directory -Force -Path "uploads"

# Set proper permissions for upload directory
icacls "uploads" /grant "Everyone:(OI)(CI)F"

# Check PHP errors
Get-Content -Path "C:\xampp\apache\logs\error.log" -Tail 50
```

## Core Business Logic

### User Registration Workflow
1. **Account Creation**: Users register with TIN, email, phone, and password
2. **Application Draft**: Users fill vehicle/personal information (can save as draft)
3. **Document Upload**: ID, vehicle photo, insurance documents
4. **Submission**: Application moves to "submitted" status
5. **Payment Processing**: Multiple payment methods (bank, mobile money, cash)
6. **Admin Review**: Staff can approve/reject applications
7. **Certificate Generation**: Approved applications generate PDF certificates

### Key Business Rules
- **TIN Authentication**: Primary key for user accounts (Tanzania tax ID)
- **Reference Numbers**: Auto-generated as `APP-YYYYMMDD-NNNN`
- **Fixed Pricing**: TZS 50.00 registration fee (configurable in form.php)
- **Document Requirements**: ID document, vehicle photo, insurance policy
- **Admin Roles**: SUPER_ADMIN and STAFF with different permissions

### File Upload Management
- **Structure**: `uploads/{application_id}/{file_type}_{timestamp}.{ext}`
- **Allowed Types**: PDF, JPG, JPEG, PNG files
- **File Types**: `id_document`, `vehicle_photo`, `insurance_doc`

## Development Guidelines

### Database Connection
All PHP files should include `config.php` first:
```php
include 'config.php';
requireLogin(); // For user-protected pages
requireAdmin(); // For admin-protected pages
```

### Authentication Functions
- `isLoggedIn()`: Check if user is authenticated
- `isAdminLoggedIn()`: Check if admin is authenticated  
- `requireLogin()`: Redirect to login if not authenticated
- `requireAdmin()`: Redirect to admin login if not admin

### Error Handling Pattern
```php
try {
    // Database operations
} catch (PDOException $e) {
    $error = 'User-friendly message: ' . $e->getMessage();
}
```

### Security Considerations
- **Password Hashing**: Uses `password_hash()` and `password_verify()`
- **SQL Injection Protection**: All queries use prepared statements
- **File Upload Validation**: Restricts file types and uses secure naming
- **Session Management**: Proper session handling with security checks

## PDF Certificate System

### TCPDF Integration
The certificate generation (`generate-pdf.php`) creates sophisticated official documents with:
- **Security Features**: Watermarks, microtext, daily verification codes
- **Professional Design**: Multi-layer borders, gradients, official seals
- **Data Validation**: Ensures only approved, paid applications get certificates
- **Error Handling**: Comprehensive error management with user feedback

### Certificate Components
- Official TRA branding and logos
- Vehicle and owner information in structured sections
- Security elements (watermarks, verification codes)
- Digital signatures and official seals
- Validity information and certificate numbers

## Default Credentials

### Admin Access
- **URL**: `http://localhost/vehicleV2/admin`
- **Username**: `admin`
- **Password**: `admin123`
- **Role**: SUPER_ADMIN

### Database Configuration
- **Host**: localhost
- **Database**: `vor`
- **Username**: `root`
- **Password**: (empty)

## File Structure Overview

### Key Directories
- **`admin/`**: Administrative interface with separate styling
- **`includes/`**: Shared templates (header.php, footer.php)
- **`images/`**: Static assets (logos, backgrounds)
- **`uploads/`**: User-uploaded files (created dynamically)
- **`dompdf-master/`**: PDF generation library (backup to TCPDF)
- **`tcpdf/`**: Primary PDF generation library

### Critical Files
- **`config.php`**: Database connection and core functions
- **`vor.sql`**: Database schema and initial data
- **`form.php`**: Main application form (handles both create and edit)
- **`generate-pdf.php`**: Certificate generation with advanced features
- **`payment.php`**: Payment processing interface
- **`reset-database.php`**: Development utility for database reset

This system represents a production-ready government application with proper security, workflow management, and document generation capabilities suitable for official vehicle registration processes.
