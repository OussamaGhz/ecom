-- Create stored procedures and triggers for e-commerce application

-- 1. Procedure to display order details and total
DELIMITER //
DROP PROCEDURE IF EXISTS GetOrderDetails //
CREATE PROCEDURE GetOrderDetails(IN p_order_id INT)
BEGIN
    -- Select order details
    SELECT o.id AS order_id, o.created_at, o.status, o.total_price, u.username, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = p_order_id;
    
    -- Select ordered items
    SELECT oi.item_id, i.name, i.brand, i.style, i.color, i.size, 
           oi.quantity, oi.price, (oi.quantity * oi.price) AS subtotal
    FROM order_items oi
    JOIN items i ON oi.item_id = i.id
    WHERE oi.order_id = p_order_id;
    
    -- Calculate total amount
    SELECT SUM(oi.quantity * oi.price) AS total_amount
    FROM order_items oi
    WHERE oi.order_id = p_order_id;
END //
DELIMITER ;

-- 2. Procedure to finalize an order
DELIMITER //
DROP PROCEDURE IF EXISTS FinalizeOrder //
CREATE PROCEDURE FinalizeOrder(
    IN p_user_id INT, 
    IN p_address VARCHAR(255),
    IN p_city VARCHAR(100),
    IN p_postal_code VARCHAR(20),
    OUT p_order_id INT
)
BEGIN
    DECLARE total DECIMAL(10,2) DEFAULT 0;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Create new order
    INSERT INTO orders (user_id, status, created_at) 
    VALUES (p_user_id, 'pending', NOW());
    
    SET p_order_id = LAST_INSERT_ID();
    
    -- Insert shipping address with only available fields
    -- Set non-collected fields to empty or default values
    INSERT INTO shipping_addresses (
        order_id, address_line1, address_line2, city, state, postal_code, country
    ) VALUES (
        p_order_id, p_address, '', p_city, '', p_postal_code, 'Default Country'
    );
    
    -- Note: No COMMIT here to allow PHP to handle the transaction
END //
DELIMITER ;

-- 3. Procedure to get customer order history
DELIMITER //
CREATE PROCEDURE GetCustomerOrderHistory(IN p_user_id INT)
BEGIN
    SELECT o.id, o.created_at, o.status, o.total_price,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
    FROM orders o
    WHERE o.user_id = p_user_id
    ORDER BY o.created_at DESC;
END //
DELIMITER ;

-- 4. Create table for canceled orders history
CREATE TABLE IF NOT EXISTS canceled_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2),
    cancel_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancel_reason VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS canceled_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canceled_order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (canceled_order_id) REFERENCES canceled_orders(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- 5. Trigger to update stock after order item is inserted
DELIMITER //
CREATE TRIGGER after_order_item_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    -- Update the stock in items table
    UPDATE items 
    SET stock = stock - NEW.quantity
    WHERE id = NEW.item_id;
END //
DELIMITER ;

-- 6. Trigger to prevent order if quantity exceeds stock
DELIMITER //
CREATE TRIGGER before_order_item_insert
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    DECLARE available_stock INT;
    
    -- Get available stock
    SELECT stock INTO available_stock
    FROM items
    WHERE id = NEW.item_id;
    
    -- Check if requested quantity exceeds available stock
    IF NEW.quantity > available_stock THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Requested quantity exceeds available stock';
    END IF;
END //
DELIMITER ;

-- 7. Procedure to cancel an order and restore stock
DELIMITER //
CREATE PROCEDURE CancelOrder(
    IN p_order_id INT,
    IN p_cancel_reason VARCHAR(255)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_total_price DECIMAL(10,2);
    DECLARE v_canceled_order_id INT;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Get order information
    SELECT user_id, total_price INTO v_user_id, v_total_price
    FROM orders
    WHERE id = p_order_id;
    
    -- Insert into canceled_orders
    INSERT INTO canceled_orders (order_id, user_id, total_price, cancel_reason)
    VALUES (p_order_id, v_user_id, v_total_price, p_cancel_reason);
    
    SET v_canceled_order_id = LAST_INSERT_ID();
    
    -- Copy order items to canceled_order_items
    INSERT INTO canceled_order_items (canceled_order_id, item_id, quantity, price)
    SELECT v_canceled_order_id, item_id, quantity, price
    FROM order_items
    WHERE order_id = p_order_id;
    
    -- Restore stock
    UPDATE items i
    JOIN order_items oi ON i.id = oi.item_id
    SET i.stock = i.stock + oi.quantity
    WHERE oi.order_id = p_order_id;
    
    -- Update order status to canceled
    UPDATE orders
    SET status = 'cancelled'
    WHERE id = p_order_id;
    
    -- Commit transaction
    COMMIT;
END //
DELIMITER ;