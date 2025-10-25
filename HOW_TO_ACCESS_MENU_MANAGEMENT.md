# How to Access Menu Management in POS

## Step-by-Step Instructions

### Current Navigation (With Submenu)

1. **Login to POS System**
   - Go to `http://localhost/pms/pos/`
   - You should see the "Point of Sale Dashboard"

2. **Open Sidebar** (on mobile)
   - If on mobile/small screen, click the hamburger menu (☰) to open sidebar
   - On desktop, sidebar is already visible

3. **Click "Restaurant POS"** to expand the submenu
   - Look for the menu item that says "Restaurant POS" with a utensils icon (🍴)
   - Click on it (it's a BUTTON, not a link)
   - A submenu should expand below it showing:
     - Menu Management
     - Active Orders
     - Table Management
     - Restaurant Reports

4. **Click "Menu Management"**
   - Once the submenu is expanded, click on "Menu Management"
   - You should now see the "Restaurant Menu Management" page

### Direct URL Access

You can also access Menu Management directly by going to:
```
http://localhost/pms/pos/restaurant/menu.php
```

## Troubleshooting

### Issue: Clicking "Restaurant POS" doesn't expand the submenu

**Solution:**
1. Open browser Developer Tools (F12)
2. Click on Console tab
3. Click "Restaurant POS" button
4. Check for error messages or console logs
5. You should see:
   ```
   toggleSubmenu called with key: restaurant
   Submenu element: <ul id="submenu-restaurant">...
   Chevron element: <i id="chevron-restaurant">...
   Showing submenu for: restaurant
   ```

### Issue: Still showing "Point of Sale Dashboard" after clicking

**Possible Causes:**
1. **You clicked the wrong menu item** - Make sure you're clicking "Menu Management" inside the Restaurant submenu, not "POS Dashboard"
2. **JavaScript error** - Check browser console (F12) for errors
3. **Session issue** - Make sure you're logged in with correct POS credentials

### Issue: Can't find "Menu Management" in the sidebar

**Solution:**
1. Make sure you're looking in the **Restaurant POS** submenu
2. The menu structure is:
   ```
   - POS Dashboard  (top level)
   - Restaurant POS (click to expand) ←  Click here first!
     - Menu Management ←  Then click here
     - Active Orders
     - Table Management  
     - Restaurant Reports
   - Room Service (click to expand)
   - Spa & Wellness (click to expand)
   - etc...
   ```

## What You Should See

When on the **Point of Sale Dashboard** (`/pms/pos/index.php`):
- Page title: "Point of Sale Dashboard"
- Content: Statistics, service categories, recent transactions

When on **Menu Management** (`/pms/pos/restaurant/menu.php`):
- Page title: "Restaurant Menu Management"
- Content: Menu items grid/list, add item button, categories, filters

## Verify Correct Page

To verify you're on the correct page:
1. Check the URL in your browser address bar
2. Check the page heading at the top of the content area
3. Menu Management URL should be: `/pms/pos/restaurant/menu.php`
4. Menu Management heading should say: "Restaurant Menu Management"

## Alternative: Make Menu Management a Top-Level Menu Item

If you want Menu Management to be easier to access (not in a submenu), we can modify the sidebar navigation structure. Let me know if you'd like this change!

