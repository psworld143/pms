# 🔧 Online Database Setup Guide

This guide will help you fix the database connection on your online server (pms.seait.edu.ph).

## 📋 Prerequisites

You need the following information from your hosting provider (CyberPanel/cPanel):
- Database Host (usually `localhost`)
- Database Name
- Database Username  
- Database Password

---

## 🚀 Step-by-Step Setup

### Step 1: Get Your Database Credentials

1. **Log into CyberPanel** at your hosting provider
2. Go to **Databases** → **MySQL Databases**
3. Find or create a database for PMS (e.g., `pms_pms_hotel`)
4. Note down:
   - Database name (e.g., `pms_pms_hotel`)
   - Database user (e.g., `pms_pms_hotel`)
   - Database password (create one if needed)

### Step 2: Configure Database Connection

1. **On your online server**, edit the file: `includes/database.local.php`

2. **Uncomment and fill in the ONLINE SERVER section** with your actual credentials:

```php
<?php
// ONLINE SERVER CONFIGURATION (pms.seait.edu.ph)
define('DB_HOST', 'localhost');  // Usually 'localhost'
define('DB_NAME', 'pms_pms_hotel');  // Your actual database name
define('DB_USER', 'pms_pms_hotel');  // Your actual database username
define('DB_PASS', 'YOUR_ACTUAL_PASSWORD');  // Your actual password from CyberPanel
define('DB_PORT', 3306);  // Usually 3306
?>
```

3. **Save the file**

### Step 3: Upload Database Schema

If your database is empty, you need to import the schema:

1. Go to **phpMyAdmin** in CyberPanel
2. Select your database (`pms_pms_hotel`)
3. Click **Import**
4. Upload one of these SQL files:
   - `database/pms_pms_hotel (cyberpanel).sql` (recommended for online)
   - Or the latest schema file

### Step 4: Test Connection

1. Visit: `https://pms.seait.edu.ph/test_db_connection.php`
2. Check if all tests pass ✅
3. If any test fails, review the error messages

---

## ✅ Verification

After setup, you should see:
- ✅ Configuration file exists
- ✅ Database configuration loaded
- ✅ PDO MySQL driver available
- ✅ Database connection established
- ✅ Tables found in database

---

## 🔒 Security Notes

1. **NEVER commit `database.local.php` to Git** - It's already in `.gitignore`
2. Use **strong passwords** for your database
3. **Delete test files** after setup:
   - `test_db_connection.php`
   - Any `test_*.php` files

---

## 🐛 Troubleshooting

### Error: "Access denied for user"
- ✅ Check username and password in `database.local.php`
- ✅ Verify user exists in CyberPanel MySQL section
- ✅ Ensure user has permissions on the database

### Error: "Unknown database"
- ✅ Check database name is correct
- ✅ Create database in CyberPanel if it doesn't exist
- ✅ Import SQL schema

### Error: "Can't connect to MySQL server"
- ✅ Check if MySQL service is running
- ✅ Verify DB_HOST is set to 'localhost'
- ✅ Contact hosting provider if issue persists

### Error: "PDO driver not available"
- ✅ Contact hosting provider to enable PDO MySQL extension
- ✅ Check PHP version (should be 7.4+ or 8.x)

---

## 📞 Need Help?

1. **Check the error logs**: `/tmp/pms_errors.log`
2. **Run the test**: `test_db_connection.php`
3. **Contact your hosting provider** for server-specific issues

---

## 🔄 For Local Development (XAMPP)

If you're working locally with XAMPP, use these settings in `database.local.php`:

```php
<?php
// LOCAL XAMPP CONFIGURATION
define('DB_HOST', 'localhost');
define('DB_NAME', 'pms_hotel');
define('DB_USER', 'root');
define('DB_PASS', '');  // Empty for XAMPP
define('DB_PORT', 3306);
?>
```

---

## 📝 Quick Checklist

- [ ] Got database credentials from CyberPanel
- [ ] Created/edited `includes/database.local.php`
- [ ] Filled in correct DB_HOST, DB_NAME, DB_USER, DB_PASS
- [ ] Imported database schema (if new database)
- [ ] Tested connection at `test_db_connection.php`
- [ ] All tests passing ✅
- [ ] Can access booking/inventory/pos systems

---

**Last Updated:** $(date +%Y-%m-%d)

