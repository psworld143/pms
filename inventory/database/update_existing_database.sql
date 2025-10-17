-- Room Inventory Role-Based Access Control - Database Updates
-- This file updates your existing database schema to support role-based functionality

-- Step 1: Add missing columns to existing supply_requests table
ALTER TABLE supply_requests ADD COLUMN room_number VARCHAR(10) NULL;
ALTER TABLE supply_requests ADD COLUMN reason ENUM('missing', 'damaged', 'low_stock', 'replacement') NULL;

-- Step 2: Update existing room_inventory_transactions table to match our needs
-- Add missing columns if they don't exist
ALTER TABLE room_inventory_transactions ADD COLUMN room_inventory_id INT NULL;
ALTER TABLE room_inventory_transactions ADD COLUMN quantity_changed INT NULL;
ALTER TABLE room_inventory_transactions ADD COLUMN transaction_type_new ENUM('usage', 'restock', 'missing', 'damaged', 'removed', 'audit') NULL;

-- Step 3: Add assigned_housekeeping column to rooms table
ALTER TABLE rooms ADD COLUMN assigned_housekeeping INT NULL;

-- Step 4: Add last_housekeeping_check column to rooms table  
ALTER TABLE rooms ADD COLUMN last_housekeeping_check DATETIME NULL;

-- Step 5: Add last_updated column to room_inventory table
ALTER TABLE room_inventory ADD COLUMN last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Step 6: Add foreign key constraint for assigned_housekeeping
ALTER TABLE rooms ADD FOREIGN KEY (assigned_housekeeping) REFERENCES users(id) ON DELETE SET NULL;

-- Step 7: Add indexes for better performance
CREATE INDEX idx_supply_requests_room_number ON supply_requests(room_number);
CREATE INDEX idx_supply_requests_status ON supply_requests(status);
CREATE INDEX idx_supply_requests_requested_by ON supply_requests(requested_by);
CREATE INDEX idx_rooms_assigned_housekeeping ON rooms(assigned_housekeeping);

-- Step 8: Insert sample data for testing (optional)
INSERT INTO supply_requests (item_id, quantity_requested, room_number, reason, notes, requested_by, status) VALUES
(1, 2, '201', 'missing', 'Towels missing after guest checkout', 1, 'pending'),
(2, 1, '205', 'damaged', 'Hair dryer not working', 1, 'pending'),
(3, 3, '210', 'low_stock', 'Shampoo bottles running low', 1, 'approved');

-- Step 9: Sample room assignments (optional - assign some rooms to housekeeping user with ID 1)
UPDATE rooms SET assigned_housekeeping = 1 WHERE room_number IN ('201', '202', '203', '204', '205');

