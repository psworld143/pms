# Database Files - PMS Hotel

## Current Database Exports

### `pms_pms_hotel_production.sql` (443 KB)
- **Complete database export** including structure and data
- **Exported from:** pms_pms_hotel @ seait.edu.ph (Production)
- **Export Date:** October 26, 2025
- **Size:** 443 KB
- **Lines:** 3,578
- **Tables:** 101
- **Includes:** All tables, data, and indexes

### `pms_pms_hotel_schema_only.sql` (24 KB)
- **Database structure only** (no data)
- **Useful for:** Setting up new environments without sample data
- **Size:** 24 KB
- **Tables:** 101 (structure only)

Both files represent the **latest production database** with all working systems:
- ✅ POS Training System (with system separation)
- ✅ Booking System
- ✅ Inventory System
- ✅ All training data properly categorized
- ✅ scenario_questions and question_options tables
- ✅ System identifier column in training_attempts

---

## How to Use:

### Option 1: Import Complete Database (with data)
```bash
# Using MySQL
mysql -u root -p pms_pms_hotel < pms_pms_hotel_production.sql

# Using XAMPP
/Applications/XAMPP/xamppfiles/bin/mysql -u root pms_pms_hotel < pms_pms_hotel_production.sql
```

### Option 2: Import Schema Only (no data)
```bash
mysql -u root -p pms_pms_hotel < pms_pms_hotel_schema_only.sql
```

### Option 3: Via phpMyAdmin
1. Open http://localhost/phpmyadmin
2. Create database `pms_pms_hotel` (if doesn't exist)
3. Select the database
4. Click "Import" tab
5. Choose `pms_pms_hotel_production.sql`
6. Click "Go"

---

## Database Contents:

### Core Tables
- `users` - System users
- `rooms` - Hotel rooms
- `reservations` - Booking reservations
- `guests` - Guest information
- `bills` - Billing records

### Training System Tables
- `training_scenarios` - Training scenarios (POS, Booking, etc.)
- `scenario_questions` - Questions for scenarios
- `question_options` - Multiple choice options
- `training_attempts` - User training attempts (with system separation)
- `training_certificates` - Earned certificates
- `customer_service_scenarios` - Customer service training
- `problem_scenarios` - Problem-solving training

### POS Training Data
- 24 POS scenarios (category: `pos_*`)
- 10 questions for first 5 scenarios
- System identifier: `system = 'pos'`
- 5 training attempts by user 1073

### System Separation
The `training_attempts` table now includes a `system` column:
- `'pos'` - POS training attempts (5 records)
- `'booking'` - Booking training attempts (27 records)
- `'inventory'` - Inventory training attempts (future)

---

## Documentation Files:

### `README_POS_TRAINING.md`
Setup guide for POS training system

---

## Maintenance

### To Export Fresh Copy:
```bash
mysqldump -u pms_pms_hotel -p'020894HotelPMS' -h seait.edu.ph \
  --single-transaction --quick --lock-tables=false \
  pms_pms_hotel > pms_pms_hotel_production.sql
```

### To Check Data Consistency:
See `TRAINING_SYSTEM_SEPARATION.md` for verification queries.

---

## Notes:

- **Old SQL files removed** - All previous migration files deleted for clean codebase
- **Single source of truth** - One production export with all fixes applied
- **System separation** - Training data properly categorized by system (POS/Booking/Inventory)
- **Ready to deploy** - Can be imported on any environment

---

**Export Date:** October 26, 2025
**Database:** pms_pms_hotel @ seait.edu.ph
**Size:** 443 KB
**Status:** ✅ Complete and Current

