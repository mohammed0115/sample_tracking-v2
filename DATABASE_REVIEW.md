# Database Schema & Seed Data Review

## ✅ Database Structure (Complete)

### Database: `sample_tracking`
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci

---

## 📊 Tables Overview

### 1. **users** (4 records)
**Purpose**: User accounts with role-based access control

| Column | Type | Key | Default | Description |
|--------|------|-----|---------|-------------|
| id | int(11) | PRI, AUTO_INCREMENT | - | Unique user ID |
| username | varchar(150) | UNI, IDX | - | Login username |
| email | varchar(255) | IDX | - | User email |
| password | varchar(255) | - | - | Bcrypt hash |
| first_name | varchar(150) | - | NULL | First name |
| last_name | varchar(150) | - | NULL | Last name |
| role | enum | IDX | 'Viewer' | Admin/Operator/Viewer |
| is_active | tinyint(1) | - | 1 | Account status |
| avatar | varchar(255) | - | NULL | Profile picture path |
| created_at | timestamp | - | CURRENT_TIMESTAMP | Creation time |
| updated_at | timestamp | - | CURRENT_TIMESTAMP | Last update |

**Seed Users**:
- `admin` (Admin) - password: admin123
- `operator1` (Operator) - password: admin123  
- `viewer1` (Viewer) - password: admin123

---

### 2. **rfid_tags** (5 records)
**Purpose**: RFID tag registry

| Column | Type | Key | Default | Description |
|--------|------|-----|---------|-------------|
| id | int(11) | PRI, AUTO_INCREMENT | - | Unique tag ID |
| uid | varchar(64) | UNI, IDX | - | Tag UID |
| is_active | tinyint(1) | - | 1 | Tag status |
| created_at | timestamp | - | CURRENT_TIMESTAMP | Registration time |

**Seed Tags**:
- RFID-AX92-7781
- RFID-BB12-1234
- RFID-CC34-5678
- RFID-DD56-9012
- RFID-EE78-3456

---

### 3. **samples** (5 records)
**Purpose**: Sample tracking with RFID linkage

| Column | Type | Key | Default | Description |
|--------|------|-----|---------|-------------|
| id | int(11) | PRI, AUTO_INCREMENT | - | Unique sample ID |
| sample_number | varchar(50) | UNI, IDX | - | Auto-generated number |
| sample_type | varchar(50) | IDX | - | Sample type (دم, لعاب, etc) |
| category | varchar(50) | IDX | - | Category (جنائية, طب شرعي, etc) |
| person_name | varchar(100) | - | - | Person/donor name |
| collected_date | date | IDX | - | Collection date |
| location | varchar(100) | - | NULL | Collection location |
| rfid_id | int(11) | FK | - | Foreign key to rfid_tags |
| status | enum | IDX | 'pending' | pending/checked/approved/rejected |
| created_at | timestamp | - | CURRENT_TIMESTAMP | Creation time |
| updated_at | timestamp | - | CURRENT_TIMESTAMP | Last update |

**Foreign Key**: `rfid_id` → `rfid_tags.id`

**Seed Samples**: 5 samples with various statuses (pending/checked/approved)

---

### 4. **audit_logs** (3 records)
**Purpose**: Chain-of-custody audit trail

| Column | Type | Key | Default | Description |
|--------|------|-----|---------|-------------|
| id | int(11) | PRI, AUTO_INCREMENT | - | Unique log ID |
| user_id | int(11) | FK, IDX | - | User who performed action |
| sample_id | int(11) | FK, IDX | NULL | Sample affected (if any) |
| action | varchar(255) | - | - | Action description |
| timestamp | timestamp | IDX | CURRENT_TIMESTAMP | Action time |

**Foreign Keys**: 
- `user_id` → `users.id`
- `sample_id` → `samples.id`

---

## 🔐 Security Features

✅ **Password Hashing**: Bcrypt (PASSWORD_BCRYPT)  
✅ **Foreign Key Constraints**: Enforced referential integrity  
✅ **Indexes**: Optimized queries on frequently searched fields  
✅ **Unique Constraints**: Prevent duplicate usernames, emails, sample numbers, RFID UIDs  
✅ **Audit Logging**: All actions tracked with timestamps  

---

## 📁 File System Requirements

### Upload Directories (Created automatically by PHP):
```
public_html/uploads/
├── avatars/         # User profile pictures
└── samples/         # Sample attachments (future feature)
```

---

## 🚀 Deployment Checklist

### Pre-Deployment:
- [x] Schema created (schema.sql)
- [x] Seed data ready (seed.sql)
- [x] Foreign keys configured
- [x] Indexes optimized
- [x] Avatar field added to users table
- [x] All tables use utf8mb4 for Arabic support

### For Production Server:
1. Create database: `sample_tracking`
2. Run: `schema.sql` (creates all tables)
3. Run: `seed.sql` (inserts default data)
4. Update `config/db.php` with production credentials
5. Create upload directories with proper permissions (755)
6. Test admin login: username=`admin`, password=`admin123`

---

## 📊 Current Data Summary

- **Users**: 4 (1 Admin, 1 Operator, 1 Viewer, + 1 custom)
- **RFID Tags**: 5 active tags
- **Samples**: 5 samples (various statuses)
- **Audit Logs**: 3 entries

---

## ⚠️ Production Recommendations

1. **Change default passwords** after first login
2. **Backup database** regularly with timestamps
3. **Monitor audit_logs** table growth (add cleanup policy if needed)
4. **Set upload file size limits** in php.ini (max_upload_filesize)
5. **Restrict uploads directory** via .htaccess (no script execution)
6. **Enable SSL/TLS** for production domain
7. **Database user**: Create restricted user (not root) with only necessary privileges

---

## 🔄 Sample Workflow States

```
pending → checked → approved
         ↓
      rejected (any state)
```

**Rules**:
- Only Admin/Operator can check (pending → checked)
- Only Admin/Operator can approve (checked → approved)
- Anyone can reject from any state
- RFID check only allowed on pending samples

---

## 💾 Export Schema Command

```bash
mysqldump -u root sample_tracking > backup_$(date +%Y%m%d).sql
```

Database is **production-ready**! ✅
