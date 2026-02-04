# Sample Tracking System - Pure PHP

A production-ready, pure PHP 8 sample tracking application migrated from Python/Django. No frameworks, no dependencies - just PHP, MySQL, and standard web technologies.

## Features

### Core Functionality
- ✅ User authentication (login/logout) with session management
- ✅ Role-based access control (Admin, Operator, Viewer)
- ✅ Sample CRUD operations
- ✅ RFID tag assignment and tracking
- ✅ Sample lifecycle workflow (pending → checked → approved/rejected)
- ✅ Complete audit logging of all actions
- ✅ Search and filter samples
- ✅ Reports (samples, RFID checks, approvals, audit logs)
- ✅ CSV export functionality
- ✅ User profile management with avatar upload
- ✅ Admin user management panel

### Security Features
- Password hashing (bcrypt)
- CSRF protection on all forms
- SQL injection protection (prepared statements)
- Session security and regeneration
- Input sanitization
- Secure file uploads

### Technical Stack
- PHP 8.x
- MySQL/MariaDB
- Pure HTML/CSS
- No external dependencies
- No Composer required
- No frameworks

## File Structure

```
php/
├── config/
│   ├── database.php       # Database configuration
│   └── app.php           # Application settings
├── includes/
│   ├── session.php       # Session & auth helpers
│   └── functions.php     # Utility functions
├── app/
│   ├── controllers/      # Business logic
│   │   ├── AuthController.php
│   │   ├── SampleController.php
│   │   ├── UserController.php
│   │   └── ReportController.php
│   ├── models/          # Database models
│   │   ├── User.php
│   │   ├── Sample.php
│   │   ├── RFIDTag.php
│   │   └── AuditLog.php
│   └── views/           # HTML templates
│       ├── layout.php
│       ├── auth/
│       ├── samples/
│       ├── users/
│       └── reports/
├── public/              # Web root
│   ├── index.php       # Front controller
│   ├── .htaccess       # Apache configuration
│   ├── css/
│   ├── js/
│   └── uploads/        # User uploads
└── database/
    ├── schema.sql      # Database structure
    └── seed.sql        # Sample data
```

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or MariaDB 10.3+
- Apache with mod_rewrite (or equivalent)
- Write permissions on `public/uploads/`

### Step 1: Database Setup

1. Create database:
```sql
CREATE DATABASE sample_tracking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import schema:
```bash
mysql -u root -p sample_tracking < database/schema.sql
```

3. Import sample data (optional):
```bash
mysql -u root -p sample_tracking < database/seed.sql
```

### Step 2: Configure Application

1. Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sample_tracking');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Edit `config/app.php`:
```php
define('APP_URL', 'http://your-domain.com');
```

3. Create uploads directory:
```bash
mkdir -p public/uploads
chmod 755 public/uploads
```

### Step 3: Configure Apache

Point your document root to the `public/` directory.

For shared hosting, upload all files and ensure `.htaccess` is active.

Example Apache VirtualHost:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/php/public
    
    <Directory /path/to/php/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Step 4: Set Permissions

```bash
chmod -R 755 public/
chmod -R 777 public/uploads/
```

## Default Credentials

After running `seed.sql`, you can login with:

- **Admin**: username: `admin`, password: `admin123`
- **Operator**: username: `operator1`, password: `admin123`
- **Viewer**: username: `viewer1`, password: `admin123`

**⚠️ Change these passwords immediately in production!**

## User Roles

### Admin
- Full access to all features
- User management
- All sample operations
- All reports

### Operator
- View and modify samples
- RFID checks and approvals
- View reports and export data
- Cannot manage users

### Viewer
- View samples only
- View reports only
- Cannot modify data
- Cannot export

## Usage

### Adding a Sample

1. Navigate to "إضافة عينة" (Add Sample)
2. Fill in sample details:
   - Sample type (نوع العينة)
   - Category (التصنيف)
   - Person name (اسم الشخص)
   - Collection date (تاريخ الجمع)
   - Location (الموقع) - optional
   - RFID tag
3. Click "حفظ" (Save)

### Sample Workflow

1. **Pending** (قيد الفحص): Initial state
2. **Checked** (تم التحقق): After RFID verification
3. **Approved** (معتمدة): Final approval
4. **Rejected** (مرفوضة): Rejected at any stage

### Generating Reports

1. Navigate to "التقارير" (Reports)
2. Select report type:
   - Samples report
   - RFID check report
   - Approval report
   - Audit log
3. Apply filters (date range, user)
4. Export to CSV if needed

## Deployment to Shared Hosting

### cPanel Instructions

1. Upload all files via File Manager or FTP
2. Extract to a folder (e.g., `sample_tracking`)
3. Move contents of `public/` to `public_html/`
4. Move other folders outside `public_html/` for security
5. Update paths in `public_html/index.php`
6. Create MySQL database via cPanel
7. Import `schema.sql` via phpMyAdmin
8. Configure `config/database.php`
9. Set folder permissions
10. Access via your domain

### Security Checklist for Production

- [ ] Change all default passwords
- [ ] Update database credentials
- [ ] Set `APP_URL` correctly
- [ ] Disable PHP error display
- [ ] Enable HTTPS
- [ ] Set secure session settings
- [ ] Restrict database user permissions
- [ ] Regular backups
- [ ] Keep PHP updated

## Troubleshooting

### Database Connection Error
- Check `config/database.php` credentials
- Verify MySQL service is running
- Test connection with: `mysql -u username -p`

### Page Not Found (404)
- Ensure `.htaccess` exists in `public/`
- Verify `mod_rewrite` is enabled: `a2enmod rewrite`
- Check Apache `AllowOverride` is set to `All`

### Upload Errors
- Check `public/uploads/` exists
- Verify write permissions: `chmod 777 public/uploads/`
- Check PHP `upload_max_filesize` in php.ini

### Session Issues
- Check session.save_path in php.ini
- Ensure write permissions on session directory
- Clear browser cookies

## Performance Tips

- Enable OPcache for PHP
- Use MySQL query cache
- Add indexes to frequently queried columns
- Implement pagination for large datasets
- Compress static assets

## Maintenance

### Backup Database
```bash
mysqldump -u root -p sample_tracking > backup_$(date +%Y%m%d).sql
```

### Clear Old Sessions
```bash
find /tmp -name 'sess_*' -mtime +1 -delete
```

### Monitor Logs
Check audit_logs table regularly for suspicious activity.

## Migration from Python Version

This application is a **1:1 functional replica** of the original Python/Django version:

✅ All features preserved
✅ Same workflows
✅ Same database structure  
✅ Same user experience
✅ No features removed
✅ No features added

### Differences
- Pure PHP instead of Django framework
- Simplified Arabic interface (translations removed)
- CSV export instead of Excel/PDF
- Inline CSS instead of external files

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review server error logs
3. Verify database connectivity
4. Check file permissions

## License

© 2026 GetSolution Co. All rights reserved.

## Version

**1.0.0** - Pure PHP Migration (February 2026)
- Complete migration from Python/Django
- Production-ready
- Zero external dependencies
"# sample_tracking-v2" 
