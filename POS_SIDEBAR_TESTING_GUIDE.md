# POS Sidebar Testing Guide

## Quick Test Instructions

### Test 1: Desktop View (≥ 1024px width)
1. Open any POS page in your browser:
   - Main: `http://localhost/pms/pos/`
   - Restaurant: `http://localhost/pms/pos/restaurant/`
   - Room Service: `http://localhost/pms/pos/room-service/`
   - Spa: `http://localhost/pms/pos/spa/`
   - Gift Shop: `http://localhost/pms/pos/gift-shop/`
   - Events: `http://localhost/pms/pos/events/`
   - Quick Sales: `http://localhost/pms/pos/quick-sales/`

2. **Expected Result:**
   - ✅ Sidebar is visible on the left side
   - ✅ Sidebar has navigation menu items
   - ✅ Main content has left margin (not overlapping sidebar)
   - ✅ Header is at the top with POS branding
   - ✅ Hamburger menu button is NOT visible

### Test 2: Mobile View (< 1024px width)
1. Open any POS page
2. Resize browser window to < 1024px OR use browser DevTools device emulation
3. Click the hamburger menu button (☰) in the header

**Expected Result:**
   - ✅ Sidebar slides in from the left
   - ✅ Dark overlay appears behind sidebar
   - ✅ Sidebar displays all navigation items
   - ✅ Main content is still visible (partially covered by overlay)

4. Click the dark overlay or anywhere outside the sidebar

**Expected Result:**
   - ✅ Sidebar slides out to the left (disappears)
   - ✅ Overlay disappears
   - ✅ Main content is fully visible again

5. Open sidebar again, then press `Escape` key

**Expected Result:**
   - ✅ Sidebar closes
   - ✅ Overlay disappears

### Test 3: Sidebar Navigation
1. Open sidebar (on mobile) or view it (on desktop)
2. Click on any menu item with a submenu (e.g., Restaurant POS, Room Service)

**Expected Result:**
   - ✅ Submenu expands/collapses
   - ✅ Chevron icon rotates
   - ✅ Submenu items are clickable

3. Click on a navigation link

**Expected Result:**
   - ✅ Page navigates to the correct module
   - ✅ Active menu item is highlighted

### Test 4: Header Functionality
1. Click on the user profile button (top right corner)

**Expected Result:**
   - ✅ Dropdown menu appears
   - ✅ Shows user name and role
   - ✅ Shows Profile, PMS Dashboard, Logout links

2. Click outside the dropdown

**Expected Result:**
   - ✅ Dropdown closes

### Test 5: Responsive Behavior
1. Open a POS page in desktop view (sidebar visible)
2. Resize browser window to mobile size

**Expected Result:**
   - ✅ Sidebar automatically hides
   - ✅ Hamburger menu button appears
   - ✅ Main content expands to full width

3. Resize back to desktop size

**Expected Result:**
   - ✅ Sidebar automatically shows
   - ✅ Hamburger menu button disappears
   - ✅ Main content has left margin again

## Common Issues & Solutions

### Issue: Sidebar not showing on desktop
**Solution:** 
- Check browser console for JavaScript errors
- Verify Tailwind CSS is loading (check Network tab)
- Ensure window width is ≥ 1024px

### Issue: Toggle button not working
**Solution:**
- Check browser console for errors
- Verify `pos-header.php` is included before `pos-sidebar.php`
- Clear browser cache and reload

### Issue: Sidebar overlaps main content on desktop
**Solution:**
- Verify main element has `class="main-content"` attribute
- Check CSS is loading correctly
- Inspect element to ensure left margin is applied

### Issue: Overlay not appearing
**Solution:**
- Check that `sidebar-overlay` div exists in the DOM
- Verify z-index values are correct
- Check that `hidden` class is being removed

### Issue: Session-related errors
**Solution:**
- Ensure you're logged in to the POS system
- Check that session variables are set:
  - `$_SESSION['pos_user_id']`
  - `$_SESSION['pos_user_name']`
  - `$_SESSION['pos_user_role']`

## Browser DevTools Inspection

### Check Sidebar Classes (when open on mobile)
```html
<nav id="sidebar" class="... translate-x-0 ...">
```

### Check Sidebar Classes (when closed on mobile)
```html
<nav id="sidebar" class="... -translate-x-full ...">
```

### Check Overlay (when sidebar is open)
```html
<div id="sidebar-overlay" class="... bg-opacity-50 z-30 lg:hidden">
```

### Check Overlay (when sidebar is closed)
```html
<div id="sidebar-overlay" class="... bg-opacity-50 z-30 lg:hidden hidden">
```

## JavaScript Console Commands

Test sidebar functions in browser console:

```javascript
// Open sidebar
openSidebar();

// Close sidebar
closeSidebar();

// Toggle submenu (example: 'restaurant')
toggleSubmenu('restaurant');

// Check if sidebar is open
document.getElementById('sidebar').classList.contains('-translate-x-full');
// Returns false if open, true if closed
```

## Success Criteria

All of the following should be true:
- ✅ Sidebar visible on desktop (width ≥ 1024px)
- ✅ Sidebar hidden on mobile (width < 1024px)
- ✅ Toggle button works on mobile
- ✅ Overlay appears/disappears correctly
- ✅ Sidebar slides smoothly with animation
- ✅ Clicking outside closes sidebar (mobile)
- ✅ Escape key closes sidebar
- ✅ Main content properly spaced on desktop
- ✅ No JavaScript errors in console
- ✅ Navigation links work correctly
- ✅ Submenus expand/collapse
- ✅ Active menu item is highlighted

## Need Help?

If issues persist:
1. Check `POS_SIDEBAR_FIX_SUMMARY.md` for technical details
2. Review browser console for error messages
3. Verify all session variables are set
4. Ensure XAMPP server is running
5. Clear browser cache and hard reload (Ctrl+Shift+R / Cmd+Shift+R)

