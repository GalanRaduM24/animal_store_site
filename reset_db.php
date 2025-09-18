<?php
require_once 'config/database.php';

// Drop existing tables
$tables = ['cart', 'products', 'departments'];
foreach ($tables as $table) {
    $dropQuery = "DROP TABLE $table CASCADE CONSTRAINTS";
    $stmt = oci_parse($conn, $dropQuery);
    @oci_execute($stmt); // @ to suppress errors if table doesn't exist
    oci_free_statement($stmt);
}

// Create tables
$createDept = "CREATE TABLE departments (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(100) NOT NULL
)";
$stmt = oci_parse($conn, $createDept);
oci_execute($stmt);
oci_free_statement($stmt);

$createProducts = "CREATE TABLE products (
    id NUMBER PRIMARY KEY,
    name VARCHAR2(100) NOT NULL,
    price NUMBER(10,2) NOT NULL,
    description VARCHAR2(1000),
    image_path VARCHAR2(255),
    department_id NUMBER,
    stock NUMBER DEFAULT 0,
    CONSTRAINT fk_department FOREIGN KEY (department_id) REFERENCES departments(id)
)";
$stmt = oci_parse($conn, $createProducts);
oci_execute($stmt);
oci_free_statement($stmt);

$createCart = "CREATE TABLE cart (
    id NUMBER PRIMARY KEY,
    user_id NUMBER,
    product_id NUMBER,
    quantity NUMBER,
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(id)
)";
$stmt = oci_parse($conn, $createCart);
oci_execute($stmt);
oci_free_statement($stmt);

// Drop sequence if exists
$dropSeq = "BEGIN
    EXECUTE IMMEDIATE 'DROP SEQUENCE cart_seq';
EXCEPTION
    WHEN OTHERS THEN
        IF SQLCODE != -2289 THEN -- -2289: sequence does not exist
            RAISE;
        END IF;
END;";
$stmt = oci_parse($conn, $dropSeq);
oci_execute($stmt);
oci_free_statement($stmt);

// Create sequence for cart ID
$createSeq = "CREATE SEQUENCE cart_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE";
$stmt = oci_parse($conn, $createSeq);
oci_execute($stmt);
oci_free_statement($stmt);

// Create function to calculate cart total
$createFunction = "CREATE OR REPLACE FUNCTION calculate_cart_total(p_user_id IN NUMBER)
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
    END;";
$stmt = oci_parse($conn, $createFunction);
oci_execute($stmt);
oci_free_statement($stmt);

// Create procedure to process purchase
$createProcedure = "CREATE OR REPLACE PROCEDURE process_purchase(
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
END;";
$stmt = oci_parse($conn, $createProcedure);
oci_execute($stmt);
oci_free_statement($stmt);

// Create trigger to prevent negative stock
$createTrigger1 = "CREATE OR REPLACE TRIGGER prevent_negative_stock
    BEFORE UPDATE ON products
    FOR EACH ROW
    BEGIN
        IF :NEW.stock < 0 THEN
            RAISE_APPLICATION_ERROR(-20003, 'Stock cannot be negative');
        END IF;
    END;";
$stmt = oci_parse($conn, $createTrigger1);
oci_execute($stmt);
oci_free_statement($stmt);

// Create trigger to validate cart quantity
$createTrigger2 = "CREATE OR REPLACE TRIGGER validate_cart_quantity
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
    END;";
$stmt = oci_parse($conn, $createTrigger2);
oci_execute($stmt);
oci_free_statement($stmt);

// Insert sample data
$insertDept1 = "INSERT INTO departments (id, name) VALUES (1, 'Dog Supplies')";
$stmt = oci_parse($conn, $insertDept1);
oci_execute($stmt);
oci_free_statement($stmt);

$insertDept2 = "INSERT INTO departments (id, name) VALUES (2, 'Cat Supplies')";
$stmt = oci_parse($conn, $insertDept2);
oci_execute($stmt);
oci_free_statement($stmt);

$insertDept3 = "INSERT INTO departments (id, name) VALUES (3, 'Aquatic Pets')";
$stmt = oci_parse($conn, $insertDept3);
oci_execute($stmt);
oci_free_statement($stmt);

$insertProduct1 = "INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
    (1, 'Premium Dog Food', 49.99, 'Nutritious dry food for adult dogs', 'dog_food.png', 1, 40)";
$stmt = oci_parse($conn, $insertProduct1);
oci_execute($stmt);
oci_free_statement($stmt);

$insertProduct2 = "INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
    (2, 'Cat Scratching Post', 29.99, 'Durable scratching post for cats', 'cat_scratching_post.png', 2, 30)";
$stmt = oci_parse($conn, $insertProduct2);
oci_execute($stmt);
oci_free_statement($stmt);

$insertProduct3 = "INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
    (3, 'Aquarium Starter Kit', 89.99, 'Complete kit for beginner fish keepers', 'aquarium_kit.png', 3, 15)";
$stmt = oci_parse($conn, $insertProduct3);
oci_execute($stmt);
oci_free_statement($stmt);

$insertProduct4 = "INSERT INTO products (id, name, price, description, image_path, department_id, stock) VALUES
    (4, 'Dog Chew Toy', 12.50, 'Safe and fun chew toy for dogs', 'dog_chew_toy.png', 1, 60)";
$stmt = oci_parse($conn, $insertProduct4);
oci_execute($stmt);
oci_free_statement($stmt);

echo "Database reset completed successfully!";
oci_close($conn);
?>