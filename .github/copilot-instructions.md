# Sample Tracking System - Copilot Instructions

## Repository Summary

This is a **production-ready, pure PHP 8 sample tracking application** for managing laboratory samples with RFID tag assignment. The project is a 1:1 functional replica of the original Python/Django version, migrated to pure PHP with zero external dependencies.

**Key Characteristics:**
- Pure PHP 8 application (no frameworks, no Composer dependencies in production)
- MySQL/MariaDB database backend
- Arabic-first interface (RTL layout)
- Role-based access control (Admin, Operator, Viewer)
- Complete audit logging system
- RFID tag tracking and sample lifecycle management

## Technology Stack

- **Language:** PHP 8.0+ (pure PHP, no frameworks)
- **Database:** MySQL 5.7+ or MariaDB 10.3+
- **Web Server:** Apache with mod_rewrite (or Nginx equivalent)
- **Frontend:** Pure HTML/CSS (inline styles, no build process)
- **Session Management:** PHP native sessions
- **Authentication:** Bcrypt password hashing

## Project Structure

```
sample_tracking-v2/
├── .github/
│   └── copilot-instructions.md    # This file
├── config/
│   ├── database.php               # Database connection config
│   └── app.php                    # Application settings
├── includes/
│   ├── session.php                # Session & auth helpers
│   └── functions.php              # Utility functions
├── app/
│   ├── controllers/               # Business logic controllers
│   ├── models/                    # Database models
│   └── views/                     # HTML templates
├── public/                        # Apache web root (minimal)
│   ├── index.php                  # Production front controller
│   └── .htaccess                  # Apache rewrite rules
├── public_html/                   # Development/hosting version
│   ├── router.php                 # Router logic
│   ├── samples/                   # Sample management pages
│   ├── rfid/                      # RFID tag management
│   ├── auth/                      # Authentication pages
│   ├── lang/                      # Language files (ar.php, en.php)
│   └── vendor/                    # Dev dependencies (not in prod)
├── database/
│   ├── schema.sql                 # Database structure (run first)
│   └── seed.sql                   # Sample data with default users
├── index.php                      # Simple standalone entry point
└── README.md                      # Complete setup documentation
```

## Database Setup (ALWAYS REQUIRED FIRST)

**CRITICAL: Database must be set up before ANY code changes or testing.**

```bash
# 1. Create database
mysql -u root -p -e "CREATE DATABASE sample_tracking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import schema (REQUIRED - creates tables)
mysql -u root -p sample_tracking < database/schema.sql

# 3. Import seed data (OPTIONAL - adds default users for testing)
mysql -u root -p sample_tracking < database/seed.sql
```

**Default Test Credentials (after seed.sql):**
- Admin: `admin` / `admin123`
- Operator: `operator1` / `admin123`
- Viewer: `viewer1` / `admin123`

## Configuration

**Before running the application, configure database credentials:**

1. Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sample_tracking');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   ```

2. Edit `config/app.php`:
   ```php
   define('APP_URL', 'http://localhost');
   ```

3. For the simple `index.php` entry point, credentials are inline at the top of the file.

## Running the Application

**There are three ways to run this application:**

### Option 1: Simple PHP Built-in Server (Quickest for Testing)
```bash
# Run from project root
php -S localhost:8000 index.php

# Access at: http://localhost:8000?page=login
```

### Option 2: Production Version (Apache with public/ as document root)
```bash
# Configure Apache VirtualHost to point to public/ directory
# Requires mod_rewrite enabled
```

### Option 3: Development Version (public_html/)
```bash
# Run from public_html directory
cd public_html
php -S localhost:8000

# Access at: http://localhost:8000
```

**Note:** The simple `index.php` has inline routing for basic functionality (login, dashboard, samples). The full application in `public_html/` has complete features.

## Testing & Validation

**Manual Testing Workflow:**

1. **Database Connection Test:**
   ```bash
   mysql -u root -p sample_tracking -e "SHOW TABLES;"
   # Should show: users, samples, rfid_tags, audit_logs, etc.
   ```

2. **Login Test:**
   - Navigate to login page
   - Use default credentials (admin/admin123)
   - Should redirect to dashboard

3. **Sample CRUD Test:**
   - Create a new sample
   - Edit the sample
   - Check audit logs for actions
   - Verify RFID tag assignment

4. **Role-Based Access Test:**
   - Login as different roles (admin, operator, viewer)
   - Verify appropriate permissions

**No automated tests exist.** All validation is manual through the web interface.

## Common Issues & Workarounds

### Issue: "Database Connection Error"
**Cause:** Database credentials in config files are incorrect or database doesn't exist.
**Fix:** Verify `config/database.php` credentials and ensure database is created.

### Issue: "404 Page Not Found" with Apache
**Cause:** mod_rewrite not enabled or .htaccess not working.
**Fix:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
# Ensure AllowOverride All in Apache config
```

### Issue: "Session errors" or "Cannot start session"
**Cause:** Write permissions on session directory.
**Fix:**
```bash
# Check session.save_path in php.ini
php -i | grep session.save_path
# Ensure directory is writable
```

### Issue: "Upload errors" for avatars
**Cause:** Missing or unwritable uploads directory.
**Fix:**
```bash
mkdir -p public/uploads
chmod 777 public/uploads
```

### Issue: Arabic text displays incorrectly
**Cause:** Missing UTF-8 charset or RTL directives.
**Fix:** Ensure all HTML pages have:
```html
<html dir="rtl" lang="ar">
<meta charset="UTF-8">
```

## File Upload Requirements

The application supports user avatar uploads:
- **Location:** `public/uploads/` or `public_html/uploads/`
- **Permissions:** Directory must be writable (chmod 777 in dev, 755 in prod)
- **Supported formats:** JPG, JPEG, PNG, GIF
- **Max size:** Configured in php.ini (default 2MB)

## Security Considerations

**The application implements:**
- Password hashing with bcrypt
- SQL injection protection via PDO prepared statements
- CSRF protection on forms
- Session security and regeneration
- Input sanitization on all user inputs
- Secure file upload validation

**When making changes:**
- Always use prepared statements for database queries
- Sanitize all user inputs with `sanitize()` or `e()` functions
- Never output raw user data without escaping
- Maintain CSRF token validation on forms

## Key Code Patterns

### Database Queries (ALWAYS use prepared statements):
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
```

### Output Escaping (ALWAYS escape user data):
```php
echo e($user['username']); // or htmlspecialchars()
```

### Session Checking:
```php
requireLogin(); // Call at top of protected pages
```

### Audit Logging (used throughout):
```php
// Every create/update/delete logs to audit_logs table
```

## Documentation Files

- `README.md` - Complete setup and usage guide
- `DATABASE_REVIEW.md` - Database structure documentation
- `HOSTINGER_DEPLOYMENT_GUIDE.md` - Shared hosting deployment
- `FTP_UPLOAD_INSTRUCTIONS.md` - FTP deployment steps
- `FILEZILLA_FTP_GUIDE.md` - FileZilla setup
- `PRE_UPLOAD_CHECKLIST.md` - Pre-deployment checklist

## Build & Deployment

**No build process required** - This is pure PHP with no compilation, transpilation, or bundling.

**Deployment steps:**
1. Upload all files via FTP/SFTP
2. Import database schema
3. Configure database credentials
4. Set directory permissions
5. Point web server to `public/` directory

**For shared hosting:** See `HOSTINGER_DEPLOYMENT_GUIDE.md` for detailed cPanel instructions.

## Important Notes for Coding Agent

1. **Always run database setup before any testing** - The application will fail without a populated database.
2. **Use PHP 8+ syntax** - Modern PHP features are used throughout (null coalescing, arrow functions, etc.).
3. **Respect the RTL/Arabic interface** - All user-facing text should be in Arabic with RTL layout.
4. **No external dependencies in production** - Keep the project dependency-free (pure PHP only).
5. **Prepared statements are mandatory** - Never use string concatenation for SQL queries.
6. **Check existing documentation** - Most deployment and troubleshooting info is already documented in markdown files.
7. **Test manually via browser** - There are no automated tests; validation must be done through the web interface.
8. **Session management is critical** - Many features depend on proper session handling and user roles.
9. **File structure has two versions** - `public/` is minimal production version, `public_html/` has full dev features.
10. **Vendor directory exists but isn't required** - The `public_html/vendor/` has dev dependencies but production runs without them.
