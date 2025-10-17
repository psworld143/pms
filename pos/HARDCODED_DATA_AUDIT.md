# POS System - Hardcoded Data Audit Report

**Date Generated:** October 17, 2025  
**System:** Hotel PMS Point of Sale (POS) System  
**Purpose:** Identify all pages containing hardcoded/mock data that should be replaced with dynamic database queries

---

## Executive Summary

This document lists all POS system pages that currently use hardcoded, sample, or mock data instead of pulling information from the database. These pages need to be updated to use dynamic data for production use.

---

## 🔴 Critical Priority - Core Functions

### 1. Restaurant Module ✅ **FIXED**

#### **File:** `/pos/restaurant/index.php`
- **Status:** ✅ COMPLETED - October 17, 2025
- **Lines:** 488-562 (updated)
- **Previous Issue:** Mock guest search results with hardcoded data
- **Solution Implemented:**
  - Created dynamic API endpoint: `/pos/api/search-guests.php`
  - Updated frontend to use fetch API for real-time guest search
  - Enhanced UI with loading states, status badges, and better formatting
  - Added guest phone numbers and check-in status display
- **Features Added:**
  - Real-time search with minimum 2 characters
  - Displays checked-in guests with priority
  - Shows room assignments and reservation status
  - Security: Session validation and SQL injection protection
  - Activity logging for all guest searches
- **Impact:** ✅ Now fully functional with real guest data from database

---

## 🟡 Medium Priority - Display/UI Functions

### 2. Events Module

#### **File:** `/pos/events/bookings.php`
- **Lines:** 210-280
- **Hardcoded Data:** Sample event bookings display
- **Description:** Three hardcoded sample bookings shown instead of database records
- **Sample Data:**
  - Johnson Wedding - $8,500 (Confirmed)
  - Corporate Conference - $12,000 (Pending)
  - Birthday Celebration (Emma Rodriguez)
- **Impact:** No real booking data displayed
- **Recommended Fix:** Create database query to fetch actual bookings from `pos_orders` table

---

### 3. Quick Sales Module

#### **File:** `/pos/quick-sales/transactions.php`
- **Lines:** 209-280
- **Hardcoded Data:** Sample transaction history
- **Description:** Three hardcoded sample transactions instead of real data
- **Sample Data:**
  - Transaction #QS-2024-001 - ₱285.00 (Coffee, Sandwich, Chips)
  - Transaction #QS-2024-002 - ₱425.00 (Souvenir T-shirt, Keychain)
  - Transaction #QS-2024-003 (Soft Drinks, Snacks)
- **Impact:** Cannot view actual transaction history
- **Recommended Fix:** Query `pos_transactions` table filtered by service_type

---

### 4. Gift Shop Module

#### **File:** `/pos/gift-shop/products.php`
- **Lines:** 170-200
- **Hardcoded Data:** Sample product cards with Unsplash images
- **Description:** Static product display with placeholder images
- **Sample Data:**
  - Hotel Logo Mug - $24.99
  - Uses external Unsplash images
- **Impact:** Cannot manage actual gift shop inventory
- **Recommended Fix:** Query `pos_menu_items` table with category filter for gift shop items

---

### 5. Spa & Wellness Module

#### **File:** `/pos/spa/services.php`
- **Lines:** 251-265
- **Hardcoded Data:** Sample spa service cards
- **Description:** Static service cards with external images
- **Sample Data:**
  - Swedish Massage - 60 min (with Unsplash image)
- **Impact:** Cannot display actual spa services
- **Recommended Fix:** Query `pos_menu_items` table filtered by spa categories

---

### 6. Room Service Module

#### **File:** `/pos/room-service/delivery.php`
- **Lines:** 225
- **Hardcoded Data:** Sample delivery order display
- **Description:** Static delivery orders shown
- **Impact:** Cannot track actual room service deliveries
- **Recommended Fix:** Query `pos_orders` table with service_type = 'room-service'

#### **File:** `/pos/room-service/menu.php`
- **Lines:** 251
- **Hardcoded Data:** Sample menu items
- **Description:** Static menu display
- **Impact:** Cannot show actual room service menu
- **Recommended Fix:** Use `getMenuItems()` function with room service category filter

---

## 🟢 Low Priority - Placeholder Values

### 7. POS Functions

#### **File:** `/pos/includes/pos-functions.php`
- **Lines:** 716
- **Hardcoded Data:** Average transaction time placeholder
- **Description:** `$avg_transaction_time = 45;` is a static value
- **Impact:** Inaccurate statistics display
- **Recommended Fix:** Calculate actual average transaction time from database:
  ```php
  SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, completed_at)) as avg_time
  FROM pos_transactions 
  WHERE status = 'completed'
  ```

---

## 📊 Database Schema References

### Tables Containing POS Data

| Table Name | Purpose | Related Modules |
|------------|---------|-----------------|
| `pos_transactions` | All POS sales transactions | All modules |
| `pos_menu_items` | Products/services/menu items | All modules |
| `pos_orders` | Active orders and bookings | Restaurant, Room Service, Spa, Events |
| `pos_tables` | Restaurant table management | Restaurant |
| `pos_payments` | Payment records | All modules |
| `pos_inventory` | Stock levels | Gift Shop |
| `guests` | Hotel guest information | All modules (for guest lookup) |

---

## 🎯 Recommended Action Plan

### Phase 1: Core Functionality (Week 1)
1. ✅ Fix guest search in Restaurant module
2. ✅ Implement real transaction history in Quick Sales

### Phase 2: Display Data (Week 2)
3. ✅ Replace sample bookings in Events module
4. ✅ Connect Gift Shop products to database
5. ✅ Connect Spa services to database
6. ✅ Fix Room Service delivery and menu displays

### Phase 3: Calculations (Week 3)
7. ✅ Calculate actual average transaction time
8. ✅ Verify all statistics are pulling from database

---

## 📝 API Endpoints Needed

### Missing/Incomplete APIs

1. **Guest Search API**
   - **Endpoint:** `/pos/api/search-guests.php`
   - **Method:** GET
   - **Parameters:** `search` (name or room number)
   - **Returns:** Array of matching guests with id, name, room number

2. **Booking Management API**
   - **Endpoint:** `/pos/api/get-event-bookings.php`
   - **Method:** GET
   - **Parameters:** `status`, `date_range`
   - **Returns:** Array of event bookings

3. **Transaction History API**
   - **Endpoint:** `/pos/api/get-transactions.php`
   - **Method:** GET
   - **Parameters:** `service_type`, `date_range`, `limit`
   - **Returns:** Array of transactions

---

## ⚠️ Important Notes

1. **Database Schema:** All replacements assume the POS database schema from `/pos/database/pos_schema.sql` is properly implemented
2. **Data Seeding:** Use `/pos/database/pos_schema.sql` sample data section for testing
3. **Image Handling:** External Unsplash URLs should be replaced with local image uploads
4. **Testing:** Each module should be tested after replacing hardcoded data

---

## 🔍 Detection Method

Hardcoded data was identified using:
```bash
grep -r "mock\|sample\|dummy\|hardcoded" /pos/
grep -r "\[\s*\['" /pos/
grep -r "const.*=\s*\[" /pos/ --include="*.php"
```

---

## 📞 Contact

For questions about this audit or implementation assistance, contact the development team.

---

**Status Legend:**
- 🔴 Critical Priority - Core functionality broken
- 🟡 Medium Priority - Display/UI issues
- 🟢 Low Priority - Minor placeholder values

**Last Updated:** October 17, 2025

