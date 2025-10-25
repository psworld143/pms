-- Step-by-step installation for Room Inventory Role-Based Access Control
-- Run these commands one by one to avoid errors

-- Step 1: Create supply_requests table
DROP TABLE IF EXISTS supply_requests;
CREATE TABLE supply_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    quantity_requested INT NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    reason ENUM('missing', 'damaged', 'low_stock', 'replacement') NOT NULL,
    notes TEXT,
    requested_by INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'in_progress', 'completed') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_requested_by (requested_by),
    INDEX idx_created_at (created_at)
);

-- Step 2: Create room_inventory_transactions table
DROP TABLE IF EXISTS room_inventory_transactions;
CREATE TABLE room_inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_inventory_id INT NOT NULL,
    transaction_type ENUM('usage', 'restock', 'missing', 'damaged', 'removed', 'audit') NOT NULL,
    quantity_changed INT NOT NULL,
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    user_id INT NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_inventory_id) REFERENCES room_inventory(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Step 3: Add assigned_housekeeping column to rooms table
ALTER TABLE rooms ADD COLUMN assigned_housekeeping INT NULL;

-- Step 4: Add last_housekeeping_check column to rooms table
ALTER TABLE rooms ADD COLUMN last_housekeeping_check DATETIME NULL;

-- Step 5: Add foreign key constraint for assigned_housekeeping
ALTER TABLE rooms ADD FOREIGN KEY (assigned_housekeeping) REFERENCES users(id) ON DELETE SET NULL;

-- Step 6: Add index for assigned_housekeeping
ALTER TABLE rooms ADD INDEX idx_assigned_housekeeping (assigned_housekeeping);

-- Step 7: Add last_updated column to room_inventory table
ALTER TABLE room_inventory ADD COLUMN last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Step 8: Create performance indexes
CREATE INDEX idx_room_inventory_room_id ON room_inventory(room_id);
CREATE INDEX idx_room_inventory_item_id ON room_inventory(item_id);
CREATE INDEX idx_room_inventory_quantity ON room_inventory(quantity_current);

-- Step 9: Insert sample data (optional - only if you want test data)
INSERT INTO supply_requests (item_id, quantity_requested, room_number, reason, notes, requested_by, status) VALUES
(1, 2, '201', 'missing', 'Towels missing after guest checkout', 1, 'pending'),
(2, 1, '205', 'damaged', 'Hair dryer not working', 1, 'pending'),
(3, 3, '210', 'low_stock', 'Shampoo bottles running low', 1, 'approved');

-- Step 10: Sample room assignments (optional - only if you want test assignments)
UPDATE rooms SET assigned_housekeeping = 1 WHERE room_number IN ('201', '202', '203', '204', '205');

