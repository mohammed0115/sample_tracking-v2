# FileZilla FTP Upload - Step by Step

## 📥 Step 1: Download & Install FileZilla

1. **Download FileZilla Client** (FREE)
   - Visit: https://filezilla-project.org/download.php
   - Download: **FileZilla Client** (NOT Server)
   - Install on your computer

2. **Open FileZilla**
   - Launch the application

---

## 🔧 Step 2: Configure FTP Connection

### Method A: Quick Connect (Fastest)

1. **Top of window**, fill in:**
   ```
   Host: 195.35.10.155
   Username: u164058768.mediumblue-eel-296461.hostingersite.com
   Password: [Your Hostinger password]
   Port: 21
   ```

2. Click: **Quickconnect** (blue button)

3. Wait for connection... should say "Connected" at bottom

---

### Method B: Site Manager (Save for Later)

1. Click: **File** → **Site Manager**
2. Click: **New Site** button
3. Fill in:
   ```
   Protocol: FTP - File Transfer Protocol
   Host: 195.35.10.155
   Port: 21
   Logon Type: Normal
   User: u164058768.mediumblue-eel-296461.hostingersite.com
   Password: [Your Hostinger password]
   ```
4. Click: **Connect**

---

## 📂 Step 3: Navigate to Upload Folder

### Left Side (Your Computer):
1. Navigate to: `C:\Users\ThinkPad\Desktop\RFID\ss\php\public_html\`
2. You should see all your PHP files and folders here

### Right Side (Hostinger Server):
1. After connecting, navigate to: `/public_html/`
2. (Should be empty or have default files)

---

## 📤 Step 4: Upload Files

### Select ALL Files to Upload:

1. **In LEFT panel (your computer)**, click inside the file list
2. Press: **Ctrl + A** (select all)
3. You should see all files highlighted:
   - `index.php`
   - `.htaccess`
   - `config/` folder
   - `auth/` folder
   - `samples/` folder
   - `rfid/` folder
   - `audit/` folder
   - `partials/` folder
   - `lang/` folder
   - `assets/` folder
   - `vendor/` folder
   - `test_db.php`

### Upload:
1. Right-click on selected files
2. Click: **Upload**
3. **Watch the transfer** in the bottom panel
4. **Wait for completion** (may take 2-5 minutes depending on speed)

### Status Messages:
```
✅ "Successfully transferred" = Upload complete
❌ "Failed" = Check connection and retry
```

---

## ✅ Step 5: Verify Upload

### Check Hostinger File Manager:
1. Go to: Hostinger Control Panel → File Manager
2. Navigate to: `/public_html/`
3. You should see all your files there

### Check if `.htaccess` is visible:
- It's a hidden file, so enable "Show hidden files"
- In Hostinger File Manager → Settings → Show hidden files

---

## 🗂️ Step 6: Create Required Directories

Via **Hostinger File Manager** (or FileZilla):

1. **Create folder:** `uploads`
   - Right-click in `/public_html/` → New folder → Name: `uploads`

2. **Create subfolder:** `uploads/avatars`
   - Go into `uploads/` folder
   - Right-click → New folder → Name: `avatars`

3. **Set permissions** to 755:
   - Right-click `uploads/` → Properties → Permissions: 755
   - Right-click `uploads/avatars/` → Properties → Permissions: 755

---

## 🗄️ Step 7: Import Database

**Now your files are uploaded. Import the database:**

1. **Login to Hostinger Control Panel**
   - Go: https://hpanel.hostinger.com/

2. **Open phpMyAdmin**
   - Left sidebar → Databases
   - Click your database → phpMyAdmin

3. **Import Schema**
   - Click: **Import** tab (top)
   - Click: **Choose File**
   - Select: `C:\Users\ThinkPad\Desktop\RFID\ss\php\database\schema.sql`
   - Click: **Go**
   - Wait for success message

4. **Import Seed Data**
   - Click: **Import** tab again
   - Click: **Choose File**
   - Select: `C:\Users\ThinkPad\Desktop\RFID\ss\php\database\seed.sql`
   - Click: **Go**
   - Wait for success message

---

## 🧪 Step 8: Test Everything

### Test 1: Database Connection
1. Visit: `https://mediumblue-eel-296461.hostingersite.com/test_db.php`
2. Should show:
   ```
   ✅ Database Connection: SUCCESS
   ✅ users: 4 records
   ✅ samples: 5 records
   ✅ rfid_tags: 5 records
   ✅ audit_logs: 3 records
   ```

### Test 2: Login Page
1. Visit: `https://mediumblue-eel-296461.hostingersite.com/`
2. Should display login page in **Arabic**
3. Try login:
   ```
   Username: admin
   Password: admin123
   ```

### Test 3: Dashboard
1. If login works, you should see:
   - Arabic navigation sidebar
   - Sample list with test data
   - All features accessible

---

## 🚨 Troubleshooting

### Issue: FileZilla says "Connection refused"
**Solution:**
- Check FTP username and password
- Verify host: `195.35.10.155`
- Wait 5 minutes, try again

### Issue: Upload stuck or very slow
**Solution:**
- Compress public_html → upload zip → extract on server
- Or use Hostinger File Manager instead

### Issue: Still seeing raw PHP code
**Solution:**
- Verify `.htaccess` was uploaded
- Check it's in `/public_html/` root
- Wait 10 minutes for cache clear

### Issue: 404 errors
**Solution:**
- Ensure `.htaccess` exists
- Check Hostinger enabled mod_rewrite
- Contact Hostinger support if needed

### Issue: Database connection error
**Solution:**
- Run `test_db.php` to see exact error
- Verify schema.sql and seed.sql imported
- Check database credentials in config/db.php

---

## 📋 Complete Checklist

### Upload Phase:
- [ ] FileZilla installed and opened
- [ ] Connected to FTP: 195.35.10.155
- [ ] All files from public_html/ uploaded
- [ ] `.htaccess` verified in /public_html/
- [ ] `uploads/` and `uploads/avatars/` directories created
- [ ] Permissions set to 755

### Database Phase:
- [ ] Logged into Hostinger Control Panel
- [ ] Opened phpMyAdmin
- [ ] Imported schema.sql
- [ ] Imported seed.sql
- [ ] Both imports showed success messages

### Testing Phase:
- [ ] Visited test_db.php and got ✅ success
- [ ] Visited login page and saw Arabic interface
- [ ] Successfully logged in as admin/admin123
- [ ] Dashboard loaded with test data

---

## 🎉 Success!

Once all steps are complete and tests pass:
- Your app is **LIVE** on Hostinger! 🚀
- Users can access: `https://mediumblue-eel-296461.hostingersite.com/`
- Admin can manage everything from dashboard
- All features working (RFID, forensic, warehouse validators, etc.)

---

## 💡 Quick Reference

**FTP Connection Details:**
```
Host: 195.35.10.155
Username: u164058768.mediumblue-eel-296461.hostingersite.com
Password: [Your Hostinger password]
Port: 21
```

**Upload Target:** `/public_html/`

**Test URL:** `https://mediumblue-eel-296461.hostingersite.com/test_db.php`

**Login:** `admin` / `admin123`

---

**Need help? Share any error messages and I'll fix them immediately!** ✅
