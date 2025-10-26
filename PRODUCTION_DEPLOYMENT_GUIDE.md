# Production Deployment Guide - Fix 404 Error

## 🔴 Issue: 404 Error on Production Server
**URL:** https://pms.seait.edu.ph/pms/pos/restaurant/menu.php  
**Status:** File exists in GitHub but not on production server

---

## ✅ Solution: Deploy Latest Changes to Production

### Option 1: SSH into Production Server (Recommended)

1. **Connect to Production Server:**
```bash
ssh username@pms.seait.edu.ph
```

2. **Navigate to Project Directory:**
```bash
cd /path/to/pms
# Common paths:
# cd /home/pms/public_html/pms
# cd /var/www/html/pms
# cd /usr/local/lsws/Example/html/pms  (if using LiteSpeed)
```

3. **Pull Latest Changes:**
```bash
git pull origin main
```

4. **Fix File Permissions:**
```bash
chmod -R 755 pos/
chmod 644 pos/restaurant/menu.php
```

5. **Verify File Exists:**
```bash
ls -la pos/restaurant/menu.php
```

---

### Option 2: Use Git Webhook (If Configured)

1. **Access Webhook URL:**
```
https://pms.seait.edu.ph/pms/git-webhook.php
```

2. **This should automatically pull latest changes**

3. **Verify by accessing:**
```
https://pms.seait.edu.ph/pms/pos/restaurant/menu.php
```

---

### Option 3: Use CyberPanel/cPanel File Manager

1. **Login to CyberPanel/cPanel**
2. **Navigate to File Manager**
3. **Go to:** `/pms/pos/restaurant/`
4. **Check if `menu.php` exists**
5. **If not:**
   - Use "Upload" to upload the file from local
   - Or use "Git Pull" function if available

---

## 🔍 Diagnostic Commands

### Check if Git is Configured:
```bash
cd /path/to/pms
git status
git remote -v
```

### Check File Permissions:
```bash
ls -la pos/restaurant/menu.php
# Should show: -rw-r--r-- or similar
```

### Check Apache/LiteSpeed User:
```bash
ps aux | grep -E 'apache|httpd|lsws' | head -1
# Common users: www-data, apache, nobody, lshttpd
```

### Fix Ownership (if needed):
```bash
# Replace 'www-data' with your web server user
sudo chown -R www-data:www-data pos/
```

---

## 🚨 Common Issues & Solutions

### Issue 1: Permission Denied
```bash
# Fix permissions
chmod -R 755 pos/
find pos/ -type f -name "*.php" -exec chmod 644 {} \;
```

### Issue 2: Git Pull Fails
```bash
# Reset to remote state
git fetch origin
git reset --hard origin/main
```

### Issue 3: Wrong Directory
```bash
# Find the correct directory
find /home -name "menu.php" -path "*/pos/restaurant/*" 2>/dev/null
find /var/www -name "menu.php" -path "*/pos/restaurant/*" 2>/dev/null
```

### Issue 4: SELinux Blocking (CentOS/RHEL)
```bash
# Check SELinux status
sestatus

# If enabled, set correct context
sudo chcon -R -t httpd_sys_content_t /path/to/pms/pos/
```

---

## 📋 Deployment Checklist

- [ ] SSH access to production server
- [ ] Navigate to correct project directory
- [ ] Run `git pull origin main`
- [ ] Check file exists: `ls -la pos/restaurant/menu.php`
- [ ] Fix permissions: `chmod 644 pos/restaurant/menu.php`
- [ ] Fix ownership: `chown www-data:www-data pos/restaurant/menu.php`
- [ ] Test URL: https://pms.seait.edu.ph/pms/pos/restaurant/menu.php
- [ ] Check session works (try to login)
- [ ] Verify other POS pages work

---

## 🔧 Quick Fix Script

Save as `deploy.sh` and run on production server:

```bash
#!/bin/bash
# Quick deployment script for PMS

echo "🚀 Deploying PMS Updates..."

# Navigate to project
cd /home/pms/public_html/pms || exit 1

# Pull latest changes
echo "📥 Pulling from GitHub..."
git pull origin main

# Fix permissions
echo "🔒 Fixing permissions..."
find pos/ -type d -exec chmod 755 {} \;
find pos/ -type f -exec chmod 644 {} \;

# Fix ownership (change www-data if needed)
echo "👤 Fixing ownership..."
sudo chown -R www-data:www-data pos/

# Clear cache if exists
if [ -d "tmp_sessions" ]; then
    echo "🗑️  Clearing sessions..."
    rm -rf tmp_sessions/*
fi

echo "✅ Deployment complete!"
echo "🔗 Test: https://pms.seait.edu.ph/pms/pos/restaurant/menu.php"
```

Run with:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 🌐 Production Server Information

**Repository:** https://github.com/psworld143/pms.git  
**Branch:** main  
**Production URL:** https://pms.seait.edu.ph/pms/  
**Git Webhook:** https://pms.seait.edu.ph/pms/git-webhook.php

---

## 📞 If All Else Fails

1. **Manual Upload:**
   - Download `pos/restaurant/menu.php` from GitHub
   - Upload via FTP/SFTP to production server
   - Set permissions to 644

2. **Contact Server Admin:**
   - Server may have restrictions
   - May need to disable mod_security temporarily
   - Check server error logs

3. **Check Server Logs:**
```bash
# Apache
tail -f /var/log/apache2/error.log

# LiteSpeed
tail -f /usr/local/lsws/logs/error.log

# CyberPanel
tail -f /home/cyberpanel/logs/access.log
```

---

## ✅ Expected Result

After deployment:
- ✅ https://pms.seait.edu.ph/pms/pos/restaurant/menu.php loads
- ✅ Shows "Restaurant Menu Management" heading
- ✅ Can search and add menu items
- ✅ All other POS pages work
- ✅ Navigation sidebar works

---

**Last Updated:** 2024-10-25  
**Status:** Awaiting production deployment

