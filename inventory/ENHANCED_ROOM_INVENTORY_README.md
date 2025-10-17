# Enhanced Room Inventory System with Integrated Requests

## 🎯 **Overview**

The Enhanced Room Inventory System provides a comprehensive solution for managing room inventory with integrated request functionality. It supports both **Housekeeping** and **Manager** roles with tailored interfaces and workflows.

## 🚀 **Key Features**

### **For Housekeeping Staff:**
- ✅ View assigned rooms and their inventory
- ✅ Update item status (Used, Missing, Damaged)
- ✅ Request replacements for missing/damaged items
- ✅ Real-time inventory tracking
- ✅ Room check functionality
- ✅ Integrated request management

### **For Managers:**
- ✅ View all room inventory across the hotel
- ✅ Add/remove items from rooms
- ✅ Approve/reject supply requests
- ✅ Generate inventory reports
- ✅ Monitor housekeeping activities
- ✅ Full audit trail

## 📁 **File Structure**

```
inventory/
├── enhanced-room-inventory.php          # Main enhanced room inventory page
├── room-inventory.php                   # Redirects to enhanced version
├── setup-room-inventory.php             # Setup and testing page
├── request-management.php               # Manager request management
├── api/
│   ├── get-housekeeping-stats.php      # Housekeeping statistics
│   ├── get-room-inventory-stats.php    # Manager statistics
│   ├── get-room-details.php            # Room details with inventory
│   ├── get-hotel-floors.php            # Floor information
│   ├── get-rooms-for-floor.php         # Rooms by floor
│   ├── update-item-status.php          # Update item status
│   ├── create-supply-request.php       # Create supply requests
│   ├── get-pending-requests.php        # Get pending requests
│   ├── approve-supply-request.php      # Approve/reject requests
│   ├── seed-room-inventory.php         # Seed sample data
│   └── test-all-apis.php               # API testing
├── database/
│   └── fix_room_inventory_tables.sql   # Database setup script
└── ENHANCED_ROOM_INVENTORY_README.md   # This file
```

## 🗄️ **Database Schema**

### **Core Tables:**
- `rooms` - Hotel rooms with housekeeping assignments
- `inventory_items` - Available inventory items
- `room_inventory` - Items assigned to specific rooms
- `supply_requests` - Requests for items by housekeeping
- `room_inventory_transactions` - Audit trail for all changes

### **Key Relationships:**
- `room_inventory.room_id` → `rooms.id`
- `room_inventory.item_id` → `inventory_items.id`
- `supply_requests.item_id` → `inventory_items.id`
- `supply_requests.requested_by` → `users.id`

## 🛠️ **Setup Instructions**

### **Step 1: Database Setup**
1. Run the database setup script:
   ```sql
   -- Run this in your database
   SOURCE inventory/database/fix_room_inventory_tables.sql;
   ```

### **Step 2: Access Setup Page**
1. Navigate to: `http://localhost/pms/inventory/setup-room-inventory.php`
2. Click "Run Database Setup" to create tables
3. Click "Seed Sample Data" to create test data

### **Step 3: Test the System**
1. Use the "Test APIs" button to verify all endpoints
2. Test both housekeeping and manager views
3. Navigate to the main room inventory page

## 🎮 **Usage Guide**

### **For Housekeeping Staff:**

1. **Access Room Inventory:**
   - Go to `inventory/enhanced-room-inventory.php`
   - View your assigned rooms on the selected floor

2. **Check Room Inventory:**
   - Click on any room card to view details
   - See all inventory items with current quantities
   - Update item status using action buttons

3. **Request Items:**
   - Click "Request Items" in room details
   - Select item, quantity, and reason
   - Submit request for manager approval

4. **Update Item Status:**
   - Mark items as "Used", "Missing", or "Damaged"
   - System automatically tracks changes
   - Low stock items are highlighted

### **For Managers:**

1. **View All Rooms:**
   - Access the same page with manager permissions
   - See all hotel rooms, not just assigned ones
   - Monitor inventory levels across the property

2. **Manage Requests:**
   - Go to "Request Management" in sidebar
   - View all pending supply requests
   - Approve or reject requests with comments

3. **Add/Remove Items:**
   - Use "Add Item" button in room details
   - Assign new items to rooms
   - Set quantities and par levels

## 🔧 **API Endpoints**

### **Statistics APIs:**
- `GET api/get-housekeeping-stats.php` - Housekeeping statistics
- `GET api/get-room-inventory-stats.php` - Manager statistics

### **Room Management APIs:**
- `GET api/get-hotel-floors.php` - Get available floors
- `GET api/get-rooms-for-floor.php?floor=X` - Get rooms for floor
- `GET api/get-room-details.php?room_id=X` - Get room details with inventory

### **Inventory Management APIs:**
- `POST api/update-item-status.php` - Update item status (housekeeping)
- `POST api/create-supply-request.php` - Create supply request
- `GET api/get-pending-requests.php` - Get pending requests (manager)
- `POST api/approve-supply-request.php` - Approve/reject request

### **Utility APIs:**
- `POST api/seed-room-inventory.php` - Seed sample data
- `GET api/test-all-apis.php` - Test all APIs

## 🎨 **UI Features**

### **Responsive Design:**
- Mobile-friendly interface
- Collapsible sidebar for mobile
- Touch-friendly buttons and controls

### **Real-time Updates:**
- Live statistics updates
- Dynamic room status indicators
- Instant feedback on actions

### **Role-based Interface:**
- Different layouts for housekeeping vs manager
- Contextual action buttons
- Appropriate data visibility

## 🔍 **Troubleshooting**

### **Common Issues:**

1. **"No Inventory Items" showing:**
   - Run the database setup script
   - Seed sample data using setup page
   - Check if `room_inventory` table has data

2. **"Error" in statistics cards:**
   - Check database connection
   - Verify table structure
   - Run API tests to identify issues

3. **Requests not working:**
   - Ensure `supply_requests` table exists
   - Check user permissions
   - Verify session management

### **Debug Steps:**
1. Check browser console for JavaScript errors
2. Use the setup page to test APIs
3. Verify database tables and data
4. Check session management

## 🚀 **Advanced Features**

### **Audit Trail:**
- All changes are logged in `room_inventory_transactions`
- Track who made changes and when
- Full history of item status changes

### **Smart Notifications:**
- Low stock alerts
- Missing item notifications
- Request status updates

### **Bulk Operations:**
- Assign multiple items to rooms
- Process multiple requests at once
- Generate comprehensive reports

## 📊 **Reporting**

The system provides various reports:
- Room inventory status
- Missing/damaged items
- Supply request history
- Housekeeping activity logs

## 🔐 **Security Features**

- Role-based access control
- Session management
- Input validation
- SQL injection prevention
- XSS protection

## 🎯 **Future Enhancements**

- Mobile app integration
- Barcode scanning
- Automated reordering
- Advanced analytics
- Integration with POS system

## 📞 **Support**

For issues or questions:
1. Check this README first
2. Use the setup page for diagnostics
3. Check browser console for errors
4. Verify database setup

---

**Version:** 2.0  
**Last Updated:** January 2025  
**Compatibility:** PHP 7.4+, MySQL 5.7+

