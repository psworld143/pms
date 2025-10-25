# 🚀 Deployment Guide for pms.seait.edu.ph

This guide will help you deploy the PMS system to your online server and fix the 404 error.

---

## 📋 Prerequisites

Before you start, make sure you have:
- ✅ Access to your hosting server (CyberPanel/SSH/FTP)
- ✅ Database credentials (see ONLINE_DATABASE_SETUP.md)
- ✅ Git installed on the server (or FTP access)
- ✅ PHP 7.4+ with PDO MySQL extension

---

## 🎯 Method 1: Deploy Using Git (RECOMMENDED)

### Step 1: SSH into Your Server

```bash
ssh your-username@pms.seait.edu.ph
# Or use the SSH access from your CyberPanel
```

### Step 2: Navigate to Your Web Root

The web root is typically one of these locations:
```bash
# For CyberPanel (most common)
cd /home/pms.seait.edu.ph/public_html

# OR for some hosts
cd /var/www/html

# OR for user-based hosting
cd ~/public_html
```

### Step 3: Clone the Repository

If the directory is empty:
```bash
# Clone the repository
git clone https://github.com/psworld143/pms.git .

# Note the dot (.) at the end - this clones into current directory
```

If there are existing files, backup first:
```bash
# Backup existing files
mkdir ../backup-$(date +%Y%m%d)
mv * ../backup-$(date +%Y%m%d)/

# Then clone
git clone https://github.com/psworld143/pms.git .
```

### Step 4: Set Permissions

```bash
# Make sure web server can read files
chmod -R 755 .

# Make specific directories writable
chmod -R 775 tmp/
chmod -R 775 tmp_sessions/
chmod -R 775 booking/tmp/
chmod -R 775 inventory/tmp/

# If these directories don't exist, create them
mkdir -p tmp tmp_sessions booking/tmp inventory/tmp
```

### Step 5: Configure Database

```bash
# Edit the database configuration
nano includes/database.local.php

# Or use vim if you prefer
vim includes/database.local.php
```

Fill in your database credentials (see ONLINE_DATABASE_SETUP.md):
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pms_pms_hotel');  // Your actual database name
define('DB_USER', 'pms_pms_hotel');  // Your actual username
define('DB_PASS', 'YOUR_PASSWORD');  // Your actual password
define('DB_PORT', 3306);
?>
```

Save the file (Ctrl+O, Enter, Ctrl+X in nano).

### Step 6: Import Database Schema

```bash
# If you have command-line access to MySQL
mysql -u pms_pms_hotel -p pms_pms_hotel < database/pms_pms_hotel\ \(cyberpanel\).sql
```

Or use **phpMyAdmin**:
1. Go to CyberPanel → Databases → phpMyAdmin
2. Select your database
3. Click "Import"
4. Upload `database/pms_pms_hotel (cyberpanel).sql`

### Step 7: Test the Deployment

Visit in your browser:
```
https://pms.seait.edu.ph/
```

You should see the PMS welcome page with module selection!

---

## 🎯 Method 2: Deploy Using FTP/SFTP

If you don't have SSH access:

### Step 1: Download Repository to Your Computer

```bash
# On your local computer
cd ~/Downloads
git clone https://github.com/psworld143/pms.git
```

### Step 2: Connect via FTP/SFTP

Use an FTP client like:
- **FileZilla** (free, cross-platform)
- **Cyberduck** (free, macOS/Windows)
- **WinSCP** (free, Windows)

**Connection details:**
- Host: `pms.seait.edu.ph` or your server IP
- Username: Your CyberPanel username
- Password: Your CyberPanel password
- Port: 22 (SFTP) or 21 (FTP)

### Step 3: Upload Files

1. Navigate to the remote directory (usually `/home/pms.seait.edu.ph/public_html`)
2. Upload ALL files from your local `pms` folder
3. Wait for upload to complete (may take several minutes)

### Step 4: Configure Database via Web

1. Using FTP, edit `includes/database.local.php`
2. Fill in your database credentials
3. Save and re-upload

### Step 5: Import Database

Use phpMyAdmin (via CyberPanel) to import the SQL file.

---

## 🎯 Method 3: Use Git Pull (if already deployed)

If you've already deployed but need to update:

```bash
# SSH into server
cd /home/pms.seait.edu.ph/public_html

# Pull latest changes
git pull origin main

# If you get conflicts with database.local.php:
git stash          # Save your local changes
git pull           # Pull updates
git stash pop      # Restore your local changes
```

---

## ✅ Verification Checklist

After deployment, verify these:

- [ ] **Home page loads**: Visit `https://pms.seait.edu.ph/`
- [ ] **Database connection works**: Visit `https://pms.seait.edu.ph/test_db_connection.php`
- [ ] **Booking system works**: Visit `https://pms.seait.edu.ph/booking/`
- [ ] **No PHP errors**: Check error logs if issues occur

---

## 🐛 Troubleshooting

### Issue: Still getting 404

**Possible causes:**

1. **Wrong directory**
   ```bash
   # Check where your files are
   ls -la /home/pms.seait.edu.ph/public_html
   
   # Make sure you see index.php
   ```

2. **Web server not pointing to correct directory**
   - Check CyberPanel → Websites → pms.seait.edu.ph
   - Verify "Document Root" is set to `/home/pms.seait.edu.ph/public_html`

3. **Files not uploaded**
   ```bash
   # Verify files exist
   ls -la /home/pms.seait.edu.ph/public_html/index.php
   ```

### Issue: 500 Internal Server Error

1. **Check permissions**
   ```bash
   chmod -R 755 /home/pms.seait.edu.ph/public_html
   ```

2. **Check error logs**
   ```bash
   tail -f /tmp/pms_errors.log
   # Or check CyberPanel error logs
   ```

3. **Check .htaccess**
   - Rename `.htaccess` to `.htaccess.bak` temporarily
   - If site works, there's an issue with .htaccess

### Issue: Database connection failed

- See **ONLINE_DATABASE_SETUP.md** for database configuration
- Run `test_db_connection.php` to diagnose

### Issue: Blank white page

1. **Enable error display temporarily**
   ```php
   <?php
   // Add to top of index.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ?>
   ```

2. **Check PHP version**
   ```bash
   php -v
   # Should be 7.4 or higher
   ```

---

## 🔒 Security Post-Deployment

After successful deployment:

1. **Remove test files**
   ```bash
   rm test_db_connection.php
   rm test_*.php
   rm debug_*.php
   ```

2. **Set proper permissions**
   ```bash
   # Make database config read-only
   chmod 600 includes/database.local.php
   ```

3. **Enable HTTPS** (if not already)
   - Use CyberPanel → SSL → Issue SSL
   - Or use Let's Encrypt

4. **Backup the database**
   ```bash
   mysqldump -u pms_pms_hotel -p pms_pms_hotel > backup-$(date +%Y%m%d).sql
   ```

---

## 📞 Getting Help

1. **Check error logs**: `/tmp/pms_errors.log` or CyberPanel logs
2. **Test database**: Visit `/test_db_connection.php`
3. **Contact hosting support**: For server-specific issues
4. **Check documentation**: ONLINE_DATABASE_SETUP.md

---

## 🔄 Updating the System

When you push changes to GitHub:

```bash
# On server
cd /home/pms.seait.edu.ph/public_html
git pull origin main
```

---

## 📝 Quick Reference

**Repository URL**: https://github.com/psworld143/pms.git  
**Main Branch**: main  
**Live URL**: https://pms.seait.edu.ph  
**Web Root**: `/home/pms.seait.edu.ph/public_html`  
**Database**: `pms_pms_hotel`

---

**Last Updated:** $(date +%Y-%m-%d)

Need more help? Check the error logs and test_db_connection.php first! 🚀

