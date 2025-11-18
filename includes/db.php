<?php
$host = '127.0.0.1';
$db   = 'ams_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  die("DB error: " . $e->getMessage());
}

try {
  $pdo->exec("ALTER TABLE customer ADD COLUMN notes TEXT NULL");
} catch (PDOException $e) {
  // Handle the case where the column already exists or other errors
}

try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS purchase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    vehicle_id INT DEFAULT NULL,
    inventory_id INT DEFAULT NULL,
    qty INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    coupon_id INT DEFAULT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customer(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicle(id),
    FOREIGN KEY (inventory_id) REFERENCES inventory(id),
    FOREIGN KEY (coupon_id) REFERENCES coupon(id)
  ) ENGINE=InnoDB;");
} catch (PDOException $e) {
  // Handle errors, such as table already existing
}

try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS employee (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    cnic VARCHAR(15) NOT NULL,
    role VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    hire_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB;");
} catch (PDOException $e) {
  // Handle errors, such as table already existing
}
