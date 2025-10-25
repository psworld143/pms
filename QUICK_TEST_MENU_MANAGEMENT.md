# Quick Test - Menu Management Access

## 🚀 Fast Test (2 minutes)

### Test 1: Direct Access
1. Go to: **`http://localhost/pms/pos/restaurant/menu.php`**
2. ✅ Should see: **"Restaurant Menu Management"** heading
3. ✅ Should NOT see: "Point of Sale Dashboard"

**If this works:** Menu Management page is fine, just navigation needs fixing.

### Test 2: Sidebar Navigation
1. Go to: `http://localhost/pms/pos/index.php`
2. Press **F12** → Click **Console** tab
3. In sidebar, click **"Restaurant POS"**
4. Look for these console messages:
   ```
   toggleSubmenu called with key: restaurant
   Submenu element: <ul id="submenu-restaurant">...
   Showing submenu for: restaurant
   ```
5. ✅ Submenu should expand showing "Menu Management"
6. Click **"Menu Management"**
7. ✅ URL should change to `/restaurant/menu.php`
8. ✅ Heading should show "Restaurant Menu Management"

### Test 3: Use Test Page
1. Go to: `http://localhost/pms/pos/test-navigation.php`
2. Click **"Run Sidebar Tests"** button
3. ✅ All tests should show green checkmarks (✓)
4. Click the green **"Menu Management"** box
5. ✅ Should navigate to Menu Management page

## 📊 Quick Diagnosis

### See this in console?
**"toggleSubmenu called with key: restaurant"** → ✅ JavaScript is working

**"Could not find submenu or chevron"** → ❌ Sidebar HTML issue

**No console messages at all** → ❌ JavaScript not loading

### See submenu expand?
**Yes** → ✅ Sidebar structure is correct

**No** → ❌ Check browser cache, hard refresh (Ctrl+Shift+R)

### Clicking Menu Management navigates?
**Yes** → ✅ EVERYTHING WORKS!

**No, stays on same page** → ❌ Link might be broken, check URL

## 🎯 Expected Results

| Action | Expected Result |
|--------|----------------|
| Visit `/pos/restaurant/menu.php` directly | Shows "Restaurant Menu Management" |
| Click "Restaurant POS" in sidebar | Submenu expands below it |
| Console after clicking | Shows logging messages |
| Submenu contains | "Menu Management" link |
| Click "Menu Management" | Navigates to menu page |
| Page heading changes to | "Restaurant Menu Management" |

## ⚡ Common Issues & Quick Fixes

### "Still shows Point of Sale Dashboard"
→ You're on the main POS page, not menu management
→ Check URL: Should be `/restaurant/menu.php`, not `/index.php`

### "Can't see Menu Management option"
→ Click "Restaurant POS" first to expand the submenu
→ It's NOT a top-level menu item, it's in a submenu

### "Clicking does nothing"
→ Hard refresh: `Ctrl+Shift+R` or `Cmd+Shift+R`
→ Clear cache completely
→ Check browser console for errors

### "JavaScript errors in console"
→ Check that `pos-sidebar.js` file exists at `/pos/assets/js/pos-sidebar.js`
→ Verify file permissions

## ✅ Success!

You'll know it's working when:
1. ✅ Console shows toggle messages
2. ✅ Submenu expands with animation
3. ✅ You see "Menu Management" link
4. ✅ Clicking it changes the URL
5. ✅ Page shows "Restaurant Menu Management" heading

## 🆘 Still Not Working?

1. **Hard refresh** (Ctrl+Shift+R)
2. **Clear all browser cache**
3. **Check console for errors** (F12 → Console tab)
4. **Try test page** (`/pos/test-navigation.php`)
5. **Try direct URL** (`/pos/restaurant/menu.php`)

If direct URL works but sidebar doesn't:
- Browser cache issue
- JavaScript not loading
- Check `pos-sidebar.js` file exists

If nothing works:
- Check Apache/PHP errors
- Verify session is active (logged in)
- Check file permissions

