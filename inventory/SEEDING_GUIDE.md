# Inventory Seeding Guide

This guide explains how to seed the inventory database with sample data for testing and demonstration purposes.

## 🚀 Quick Start

### Method 1: Using the Seeder Tool (Recommended)

```bash
# Navigate to the inventory directory
cd /Applications/XAMPP/xamppfiles/htdocs/pms/inventory

# Check current inventory status
php inventory-seeder.php status

# Seed with sample data
php inventory-seeder.php seed

# Clear all inventory data (use with caution!)
php inventory-seeder.php clear --confirm

# Show help
php inventory-seeder.php help
```

### Method 2: Using the Direct Seeding Script

```bash
# Navigate to the inventory directory
cd /Applications/XAMPP/xamppfiles/htdocs/pms/inventory

# Run the seeding script
php seed-inventory-items.php
```

## 📊 Sample Data Included

The seeding process adds **24 sample inventory items** across **5 categories**:

### 🍽️ Food & Beverage (5 items)
- Coffee Beans (Arabica) - 50kg
- Tea Bags (Assorted) - 25 boxes
- Bottled Water (500ml) - 200 bottles
- Breakfast Cereal - 30 boxes
- Fresh Milk (1L) - 40 liters

### 🛁 Amenities (6 items)
- Bath Towels (White) - 150 pieces
- Hand Towels - 200 pieces
- Bathrobes (Cotton) - 80 pieces
- Shampoo (Hotel Size) - 300 bottles
- Body Lotion - 250 bottles
- Soap Bars (Luxury) - 400 pieces

### 🧽 Cleaning Supplies (5 items)
- All-Purpose Cleaner - 50 bottles
- Glass Cleaner - 30 bottles
- Disinfectant Spray - 40 bottles
- Microfiber Cloths - 25 packs
- Vacuum Bags - 20 packs

### 📋 Office Supplies (4 items)
- A4 Paper (White) - 15 reams
- Ballpoint Pens (Blue) - 10 boxes
- Stapler with Staples - 5 pieces
- File Folders (Manila) - 8 boxes

### 🔧 Maintenance (4 items)
- Light Bulbs (LED) - 100 pieces
- Air Filter (HVAC) - 20 pieces
- Paint (White) - 8 gallons
- Door Handles (Brass) - 25 pieces

## 💰 Total Value

- **Total Items**: 24
- **Total Inventory Value**: ₱178,910.00
- **Categories**: 5

## 🛠️ Database Schema

The seeding process works with the actual database schema:

### inventory_categories
- `id` (Primary Key)
- `name` (Unique)
- `description`
- `created_at`

### inventory_items
- `id` (Primary Key)
- `item_name`
- `category_id` (Foreign Key)
- `current_stock`
- `minimum_stock`
- `unit_price`
- `description`
- `last_updated`
- `created_at`

## 🔄 Seeding Process

1. **Category Creation**: Creates categories if they don't exist
2. **Duplicate Check**: Skips items that already exist (by name)
3. **Item Creation**: Inserts new inventory items with proper relationships
4. **Data Validation**: Ensures all required fields are populated
5. **Summary Report**: Shows seeding results and current inventory status

## ⚠️ Important Notes

- **Safe to Run Multiple Times**: The script checks for existing items and skips duplicates
- **No Data Loss**: Existing inventory data is preserved
- **Database Connection**: Uses the main PMS database connection
- **Error Handling**: Comprehensive error handling with detailed logging

## 🧪 Testing the Seeded Data

After seeding, you can:

1. **View in Web Interface**: Visit `http://localhost/pms/inventory/items.php`
2. **Check Database Directly**:
   ```sql
   SELECT c.name as category, COUNT(i.id) as items, 
          SUM(i.current_stock * i.unit_price) as total_value 
   FROM inventory_categories c 
   LEFT JOIN inventory_items i ON c.id = i.category_id 
   GROUP BY c.id, c.name 
   ORDER BY c.name;
   ```
3. **Use the Seeder Tool**: `php inventory-seeder.php status`

## 🗑️ Clearing Data

To remove all seeded data:

```bash
php inventory-seeder.php clear --confirm
```

**Warning**: This will delete ALL inventory data, not just seeded items!

## 🔧 Customization

To add your own sample data:

1. Edit `seed-inventory-items.php` or `inventory-seeder.php`
2. Add items to the `$sample_items` array
3. Follow the existing format:
   ```php
   [
       'name' => 'Item Name',
       'category' => 'Category Name',
       'sku' => 'SKU-CODE',
       'description' => 'Item description',
       'unit' => 'Unit of measurement',
       'quantity' => 100,
       'minimum_stock' => 10,
       'cost_price' => 25.00,
       'supplier' => 'Supplier Name'
   ]
   ```

## 📝 Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Ensure MySQL is running
   - Check database credentials in `../includes/database.php`

2. **Permission Errors**:
   - Ensure PHP has write access to the database
   - Check MySQL user permissions

3. **Schema Mismatch**:
   - The scripts are designed for the actual database schema
   - If you have a different schema, update the SQL queries accordingly

### Getting Help

- Check the console output for detailed error messages
- Review the database logs for SQL errors
- Ensure all required database tables exist

## 🎯 Next Steps

After seeding:

1. **Test the Web Interface**: Navigate through all inventory pages
2. **Create Transactions**: Add some stock movements
3. **Generate Reports**: Test the reporting functionality
4. **Train Users**: Use the seeded data for training scenarios

---

**Happy Seeding! 🌱**
