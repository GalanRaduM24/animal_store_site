-- Drop tables if they exist
BEGIN
    EXECUTE IMMEDIATE 'DROP TABLE cart';
    EXECUTE IMMEDIATE 'DROP TABLE products';
    EXECUTE IMMEDIATE 'DROP TABLE departments';
EXCEPTION
    WHEN OTHERS THEN
        NULL; -- Ignore if table does not exist
END;
/

-- Recreate tables
CREATE TABLE departments (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(100) NOT NULL
);

CREATE TABLE products (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(100) NOT NULL,
    price NUMBER(10,2) NOT NULL,
    description VARCHAR2(1000),
    image_path VARCHAR2(255),
    department_id NUMBER,
    stock NUMBER DEFAULT 0,
    CONSTRAINT fk_department FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE cart (
    id NUMBER PRIMARY KEY,
    user_id NUMBER,
    product_id NUMBER,
    quantity NUMBER,
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Create sequence for cart ID
CREATE SEQUENCE cart_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

-- Function to calculate total cart value
CREATE OR REPLACE FUNCTION calculate_cart_total(p_user_id IN NUMBER)
RETURN NUMBER
IS
    v_total NUMBER := 0;
BEGIN
    SELECT NVL(SUM(p.price * c.quantity), 0)
    INTO v_total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = p_user_id;
    
    RETURN v_total;
END;
/

-- Procedure to update stock after purchase
CREATE OR REPLACE PROCEDURE process_purchase(
    p_product_id IN NUMBER,
    p_quantity IN NUMBER
)
IS
    v_current_stock NUMBER;
BEGIN
    -- Get current stock
    SELECT stock INTO v_current_stock
    FROM products
    WHERE id = p_product_id
    FOR UPDATE;
    
    -- Check if enough stock
    IF v_current_stock >= p_quantity THEN
        -- Update stock
        UPDATE products
        SET stock = stock - p_quantity
        WHERE id = p_product_id;
        
        COMMIT;
    ELSE
        RAISE_APPLICATION_ERROR(-20001, 'Insufficient stock available');
    END IF;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RAISE_APPLICATION_ERROR(-20002, 'Product not found');
    WHEN OTHERS THEN
        ROLLBACK;
        RAISE;
END;
/

-- Trigger to prevent negative stock
CREATE OR REPLACE TRIGGER prevent_negative_stock
BEFORE UPDATE ON products
FOR EACH ROW
BEGIN
    IF :NEW.stock < 0 THEN
        RAISE_APPLICATION_ERROR(-20003, 'Stock cannot be negative');
    END IF;
END;
/

-- Trigger to validate cart quantity
CREATE OR REPLACE TRIGGER validate_cart_quantity
BEFORE INSERT OR UPDATE ON cart
FOR EACH ROW
DECLARE
    v_stock NUMBER;
BEGIN
    SELECT stock INTO v_stock
    FROM products
    WHERE id = :NEW.product_id;
    
    IF :NEW.quantity <= 0 THEN
        RAISE_APPLICATION_ERROR(-20004, 'Cart quantity must be greater than 0');
    ELSIF :NEW.quantity > v_stock THEN
        RAISE_APPLICATION_ERROR(-20005, 'Cart quantity exceeds available stock');
    END IF;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RAISE_APPLICATION_ERROR(-20006, 'Product not found');
END;
/

CREATE SEQUENCE product_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

DECLARE
  max_id NUMBER;
BEGIN
  SELECT NVL(MAX(id), 0) + 1 INTO max_id FROM products;
  EXECUTE IMMEDIATE 'DROP SEQUENCE product_seq';
  EXECUTE IMMEDIATE 'CREATE SEQUENCE product_seq START WITH ' || max_id || ' INCREMENT BY 1 NOCACHE NOCYCLE';
END;
/


-- Insert sample data
INSERT INTO departments (id, name) VALUES (1, 'Dog Supplies');
INSERT INTO departments (id, name) VALUES (2, 'Cat Supplies');
INSERT INTO departments (id, name) VALUES (3, 'Aquatic Pets');

INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
(1, 'Premium Dog Food', 49.99, 'Nutritious dry food for adult dogs', 'dog_food.png', 1, 40);

INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
(2, 'Cat Scratching Post', 29.99, 'Durable scratching post for cats', 'cat_scratching_post.png', 2, 30);

INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
(3, 'Aquarium Starter Kit', 89.99, 'Complete kit for beginner fish keepers', 'aquarium_kit.png', 3, 15);

INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
(4, 'Dog Chew Toy', 12.50, 'Safe and fun chew toy for dogs', 'dog_chew_toy.png', 1, 60);




/*
SELECT * FROM departments;
SELECT * FROM products;
SELECT * FROM cart;

-- Describe table columns
DESC departments;
DESC products;
DESC cart;

SELECT column_name, data_type, data_length 
FROM user_tab_columns 
WHERE table_name = 'PRODUCTS';

SELECT table_name FROM user_tables;
*/