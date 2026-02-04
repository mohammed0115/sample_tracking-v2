# Hostinger FTP Upload Guide - Your Account

## 🔐 Your FTP Credentials (from Hostinger Control Panel)

```
FTP Hostname: ftp://mediumblue-eel-296461.hostingersite.com
OR IP: ftp://195.35.10.155

FTP Username: u164058768.mediumblue-eel-296461.hostingersite.com
FTP Password: [Your Hostinger password]

File Upload Path: public_html
Port: 21 (default FTP)
```

---

## 📥 Upload Method 1: Using Hostinger File Manager (Easiest)

### Step 1: Login to Hostinger Control Panel
1. Go to: https://hpanel.hostinger.com/
2. Click: **Websites** (left sidebar)
3. Click: **mediumblue-eel-296461**

### Step 2: Open File Manager
1. Click: **File Manager** icon
2. Navigate to: `/public_html/`

### Step 3: Upload Files
1. Click **Upload** button
2. Select entire `public_html` folder contents from your computer
3. Wait for upload to complete

---

## 📥 Upload Method 2: Using FileZilla (Professional)

### Step 1: Download FileZilla
- Download: https://filezilla-project.org/
- Install on your computer

### Step 2: Configure Connection
1. Open FileZilla
2. Go: File → Site Manager
3. Click: **New Site**
4. Enter:
   ```
   Protocol: FTP - File Transfer Protocol
   Host: 195.35.10.155
   Port: 21
   Logon Type: Normal
   User: u164058768.mediumblue-eel-296461.hostingersite.com
   Password: [Your Hostinger password]
   ```
5. Click: **Connect**

### Step 3: Navigate & Upload
1. **Left side** (Local): Navigate to `C:\Users\ThinkPad\Desktop\RFID\ss\php\public_html\`
2. **Right side** (Remote): Should be in `/public_html/`
3. Select all folders and files (Ctrl+A)
4. Right-click → **Upload**
5. Wait for completion

---

## ⚠️ Important Files to Upload

**MUST UPLOAD:**
- ✅ `index.php` - Main entry point
- ✅ `.htaccess` - URL rewriting (CRITICAL!)
- ✅ `config/` folder - Includes db.php with credentials
- ✅ `auth/`, `samples/`, `rfid/`, `audit/` - All app folders
- ✅ `partials/`, `lang/`, `assets/` - Templates and styling
- ✅ `vendor/` folder - Composer dependencies
- ✅ `test_db.php` - Database tester

**DO NOT UPLOAD:**
- ❌ `.github/` folder (optional)
- ❌ `database/` folder (use phpMyAdmin instead)
- ❌ `tools/` folder (local debug only)

---

## 🗄️ Database Setup (After Upload)

### Step 1: Import Schema
1. Go to: Hostinger Control Panel → **Databases**
2. Click your database → **phpMyAdmin**
3. Click: **Import** tab
4. Upload: `C:\Users\ThinkPad\Desktop\RFID\ss\php\database\schema.sql`
5. Click: **Go**

### Step 2: Import Seed Data
1. Click: **Import** tab again
2. Upload: `C:\Users\ThinkPad\Desktop\RFID\ss\php\database\seed.sql`
3. Click: **Go**

---

## ✅ Verification Steps

### Step 1: Test Database Connection
1. Visit: `https://mediumblue-eel-296461.hostingersite.com/test_db.php`
2. You should see:
   ```
   ✅ Database Connection: SUCCESS
   ✅ users: 4 records
   ✅ samples: 5 records
   ✅ rfid_tags: 5 records
   ✅ audit_logs: 3 records
   ```

### Step 2: Test Login Page
1. Visit: `https://mediumblue-eel-296461.hostingersite.com/`
2. You should see login page in Arabic
3. Try login:
   - Username: `admin`
   - Password: `admin123`

### Step 3: Test Dashboard
1. If login works, dashboard should load
2. You should see sample data and sidebar navigation

---

## 🚨 Troubleshooting

### Issue: "Raw PHP code displaying"
**Solution:** Ensure `.htaccess` is uploaded to `/public_html/`

### Issue: Database connection error on test_db.php
**Solution:** Run schema.sql and seed.sql in phpMyAdmin

### Issue: Can't upload via FileZilla
**Solution:** Use Hostinger File Manager instead (guaranteed to work)

### Issue: 404 errors on all pages
**Solution:** 
1. Check `.htaccess` exists in `/public_html/`
2. Verify mod_rewrite is enabled in Hostinger

---

## 📊 File Structure After Upload

```
Your Hostinger /public_html/ should contain:
├── index.php              ✅
├── .htaccess              ✅ (CRITICAL!)
├── router.php             ✅
├── test_db.php            ✅
├── config/                ✅ (with db.php - credentials already updated)
├── auth/                  ✅
├── samples/               ✅
├── rfid/                  ✅
├── audit/                 ✅
├── partials/              ✅
├── lang/                  ✅
├── assets/                ✅
├── vendor/                ✅ (Composer dependencies)
└── uploads/               (Create this directory with 755 permissions)
    └── avatars/           (Create with 755 permissions)
```

---

## 🎯 Complete Upload Checklist

- [ ] Download FileZilla or use Hostinger File Manager
- [ ] Configure FTP connection with credentials
- [ ] Upload entire `public_html/` contents to `/public_html/`
- [ ] Verify `.htaccess` is uploaded
- [ ] Create `uploads/` and `uploads/avatars/` directories (755 permissions)
- [ ] Set folder permissions to 755
- [ ] Import `schema.sql` in phpMyAdmin
- [ ] Import `seed.sql` in phpMyAdmin
- [ ] Visit `test_db.php` and verify connection
- [ ] Visit login page and test with admin/admin123
- [ ] Check dashboard loads with test data

---

## 🚀 Quick Start After Upload

1. **Database test**: https://mediumblue-eel-296461.hostingersite.com/test_db.php
2. **Login page**: https://mediumblue-eel-296461.hostingersite.com/
3. **Test credentials**:
   - Username: `admin`
   - Password: `admin123`

---

## 💡 Pro Tips

- **Slow upload?** Use Hostinger File Manager (built-in, faster)
- **Large files?** Compress `public_html` → upload → extract on server
- **File permissions?** Right-click in File Manager → Properties → 755
- **Still broken?** Check Hostinger error logs in Control Panel

---

✅ **Ready to upload! Use the method that works best for you.**

Need help with any step? Let me know! 🚀
