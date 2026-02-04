# Hostinger Deployment Guide - Step by Step

## ⚠️ Current Issue
You're seeing **raw PHP code** instead of the app running. This means:
- PHP files are being displayed as text
- The routing/execution is broken

---

## 🔧 Step 1: Fix the .htaccess Configuration

The app needs proper URL rewriting. Create/update `.htaccess` in your **public_html** folder:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Remove index.php from URL
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L,QSA]
</IfModule>

# Prevent direct access to sensitive files
<FilesMatch "\.(sql|env|json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect uploads directory
<Directory "uploads">
    php_flag engine off
    AddType text/plain .php .php3 .php4 .php5 .php6 .php7 .phtml .phps
</Directory>
```

---

## 📊 Step 2: Create Database Schema via phpMyAdmin

1. **Login to Hostinger Control Panel**
   - Go to: https://hpanel.hostinger.com/

2. **Access phpMyAdmin**
   - Left sidebar → Databases → Click your database name
   - Or → Database Manager → Click phpMyAdmin icon

3. **Select your database**
   - Click: `u164058768_sample_trackin`

4. **Import Schema**
   - Click **Import** tab (top menu)
   - Click **Choose File** → Select `database/schema.sql`
   - Click **Go** to import

5. **Verify Tables Created**
   - Click **Structure** tab
   - You should see: `users`, `rfid_tags`, `samples`, `audit_logs`

---

## 📝 Step 3: Insert Seed Data

1. **In phpMyAdmin**, click **Import** again
2. **Import Seed Data**
   - Select `database/seed.sql`
   - Click **Go**

3. **Verify Data Inserted**
   ```sql
   SELECT * FROM users;
   SELECT * FROM samples;
   SELECT * FROM rfid_tags;
   ```

---

## 📂 Step 4: Upload Files via FTP/SFTP

You need to upload **all** PHP files to Hostinger:

**To upload:**
1. Use FileZilla or File Manager in Hostinger
2. Navigate to: `/public_html/` (root folder)
3. Upload entire `public_html` contents there

**Files that MUST exist:**
```
/public_html/
├── index.php              ← Main entry point
├── router.php             ← For development (optional for Hostinger)
├── .htaccess              ← URL rewriting
├── config/
│   ├── db.php            ✓ Already updated
│   ├── auth.php
│   ├── helpers.php
│   └── lang.php
├── auth/
│   ├── login.php
│   ├── register.php
│   ├── profile.php
│   └── ...
├── samples/
├── rfid/
├── audit/
├── partials/
├── lang/
├── assets/
│   └── style.css
├── uploads/
│   └── avatars/          ← Create this folder!
└── vendor/               ← Composer dependencies
```

---

## 🔒 Step 5: Set File Permissions

**Via Hostinger File Manager:**

1. Right-click `/public_html/uploads/` → **Change Permissions**
2. Set to: `755` (read+execute for all, write for owner)

3. Right-click `/public_html/uploads/avatars/` → **Change Permissions**
4. Set to: `755`

---

## 🧪 Step 6: Test Database Connection

Create a test file: `/public_html/test_db.php`

```php
<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=u164058768_sample_trackin;charset=utf8mb4',
        'u164058768_admin_track',
        'O^I~KYTdlykfPCa4',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connected!<br>";
    
    $result = $pdo->query('SELECT COUNT(*) as count FROM users');
    $row = $result->fetch();
    echo "✅ Users in database: " . $row['count'];
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

**Test**: Visit `https://mediumblue-eel-296461.hostingsite.com/test_db.php`

If you see "✅ Database connected!" → Your DB is working!

---

## 🌐 Step 7: Access Your App

**After all steps above:**
- Login: https://mediumblue-eel-296461.hostingsite.com/
- Register: https://mediumblue-eel-296461.hostingsite.com/index.php?page=register

**Or with cleaner URLs (if .htaccess works):**
- https://mediumblue-eel-296461.hostingsite.com/auth/login
- https://mediumblue-eel-296461.hostingsite.com/auth/register

---

## 🔑 Test Login Credentials

After seed data is imported:

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Admin |
| operator1 | admin123 | Operator |
| viewer1 | admin123 | Viewer |

---

## ❌ Troubleshooting

### Issue: Still seeing raw PHP code
**Solution:**
1. Ensure `.htaccess` is in `/public_html/` (root)
2. Check Hostinger → Hosting → PHP Configuration → Enable `mod_rewrite`
3. Check file permissions (files should be 644, folders 755)

### Issue: "Access denied for user 'root'@'localhost'"
**Solution:** Already fixed - we updated `config/db.php` with Hostinger credentials

### Issue: 404 Page Not Found
**Solution:** 
1. Check `.htaccess` is present in `/public_html/`
2. Verify rewrite rules are enabled in your Hostinger panel

### Issue: White blank page
**Solution:** 
1. Check Hostinger error logs in Control Panel
2. Ensure `vendor/` folder with Composer dependencies is uploaded

---

## 📋 Deployment Checklist

- [ ] Database credentials updated in `config/db.php`
- [ ] `.htaccess` file created in `/public_html/`
- [ ] All PHP files uploaded to `/public_html/`
- [ ] `schema.sql` imported in phpMyAdmin
- [ ] `seed.sql` imported in phpMyAdmin
- [ ] File permissions set (755 for folders, 644 for files)
- [ ] `/uploads/` and `/uploads/avatars/` folders created with 755 permissions
- [ ] Test database connection via `test_db.php`
- [ ] Login page accessible and working

---

## 🚀 You're Done!

Once all steps are complete:
1. Visit your domain
2. Register a new account OR login with `admin/admin123`
3. Start using the app!

---

## 📞 Need Help?

If you're stuck on any step, let me know the specific error message and I'll fix it!
