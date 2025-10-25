-- Room Inventory Role-Based Access Control - Step by Step Installation
-- Run these commands one by one for your existing database

-- Step 1: Add room_number column to supply_requests table
ALTER TABLE supply_requests ADD COLUMN room_number VARCHAR(10) NULL;

-- Step 2: Add reason column to supply_requests table  
ALTER TABLE supply_requests ADD COLUMN reason ENUM('missing', 'damaged', 'low_stock', 'replacement') NULL;

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

