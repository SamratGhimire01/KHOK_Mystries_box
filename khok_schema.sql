-- ─────────────────────────────────────────
--  K HO K — MySQL Database Schema (CORRECTED)
--  Run: mysql -u root -p < khok_schema.sql
-- ─────────────────────────────────────────

CREATE DATABASE IF NOT EXISTS khok_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE khok_db;

-- USERS
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    phone         VARCHAR(20)   NOT NULL,
    address       TEXT,
    city          VARCHAR(100),
    role          ENUM('customer','admin','delivery') DEFAULT 'customer',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- PRODUCTS
CREATE TABLE IF NOT EXISTS products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150)   NOT NULL,
    brand        VARCHAR(100),
    category     VARCHAR(100),
    price        DECIMAL(10,2)  NOT NULL,
    rarity       ENUM('common','uncommon','rare','ultra_rare','legendary') DEFAULT 'common',
    weight       DECIMAL(5,2)   NOT NULL DEFAULT 1.00,
    stock        INT UNSIGNED   DEFAULT 0,
    image        VARCHAR(255),
    is_active    TINYINT(1)     DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_rarity (rarity),
    INDEX idx_price (price)
);

-- BOXES
CREATE TABLE IF NOT EXISTS boxes (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(50)    NOT NULL UNIQUE,
    name         VARCHAR(100)   NOT NULL,
    tagline      VARCHAR(150),
    price        DECIMAL(10,2)  NOT NULL,
    min_products TINYINT        DEFAULT 1,
    max_products TINYINT        DEFAULT 3,
    is_active    TINYINT(1)     DEFAULT 1,
    INDEX idx_slug (slug),
    INDEX idx_price (price)
);

-- BOX PRODUCT POOL
CREATE TABLE IF NOT EXISTS box_product_pool (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    box_id     INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (box_id)     REFERENCES boxes(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_box_product (box_id, product_id)
);

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_ref      VARCHAR(30)   NOT NULL UNIQUE,
    user_id        INT UNSIGNED NULL,
    box_id         INT UNSIGNED  NOT NULL,
    customer_name  VARCHAR(100)  NOT NULL,
    phone          VARCHAR(20)   NOT NULL,
    address        TEXT          NOT NULL,
    city           VARCHAR(100),
    total_amount   DECIMAL(10,2) NOT NULL,
    payment_method ENUM('esewa','fonepay','cod') DEFAULT 'esewa',
    payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
    order_status   ENUM('placed','confirmed','packed','shipped','delivered','cancelled') DEFAULT 'placed',
    notes          TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE RESTRICT,
    INDEX idx_order_ref (order_ref),
    INDEX idx_user_id (user_id),
    INDEX idx_status (order_status)
);

-- ORDER ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity   INT UNSIGNED DEFAULT 1,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);

-- DELIVERY TRACKING
CREATE TABLE IF NOT EXISTS delivery_tracking (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id         INT UNSIGNED NOT NULL UNIQUE,
    status           ENUM('confirmed','in_transit','delivered') DEFAULT 'confirmed',
    proof_image      VARCHAR(255),
    delivery_note    TEXT,
    estimated_date   DATE,
    delivered_at     TIMESTAMP NULL,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_status (status)
);

-- ─────────────────────────────────────────
--  SEED DATA
-- ─────────────────────────────────────────

-- Admin user (password: 'password' — hash below)
-- To generate a new hash for 'admin123', use PHP: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (full_name, email, password_hash, phone, role) VALUES
('K HO K Admin', 'admin@khok.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9800000000', 'admin');

-- Boxes
INSERT INTO boxes (slug, name, tagline, price, min_products, max_products) VALUES
('shadow',  'Shadow Box',  'रहस्यको सुरुवात',      999,   1, 2),
('core',    'Core Box',    'असली खेल सुरु हुन्छ', 2999,  1, 3),
('pulse',   'Pulse Box',   'धड्कन बढ्छ',           4999,  2, 4),
('elite',   'Elite Box',   'छनोट भएकाहरूको लागि', 9999,  2, 5),
('phantom', 'Phantom Box', 'सपनाको छेउमा',         24999, 3, 6),
('god',     'GOD BOX',     'देवताको छनोट',         99999, 5, 10);

-- Products (33 total, rarity ENUM now includes 'uncommon')
INSERT INTO products (name, brand, category, price, rarity, weight, stock) VALUES
('Phone Stand',              'Generic',    'Accessory',  500,   'common',    20.00, 100),
('USB-C Cable 1m',           'Generic',    'Accessory',  600,   'common',    20.00, 100),
('K HO K Sticker Pack',      'K HO K',     'Merch',      350,   'common',    18.00, 200),
('RGB Desk Lamp',            'Generic RGB','Accessory',  1200,  'common',    13.00, 50),
('Mobile Cooling Fan',       'Black Shark','Accessory',  1800,  'common',    14.00, 40),
('Gaming Mouse Pad',         'Redragon',   'Gaming',     1500,  'common',    17.00, 60),
('Ultima Atom 720 Earbuds',  'Ultima',     'Audio',      2899,  'common',    18.00, 40),
('Ultima Atom 320 Earbuds',  'Ultima',     'Audio',      2899,  'common',    14.00, 40),
('RGB Gaming Mouse',         'Fantech',    'Gaming',     2500,  'common',    16.00, 35),
('Ultima Boom 311 Earbuds',  'Ultima',     'Audio',      3499,  'common',    15.00, 35),
('Logitech M331 Mouse',      'Logitech',   'Peripheral', 2800,  'common',    10.00, 30),
('Fantech RGB Keyboard',     'Fantech',    'Gaming',     4500,  'uncommon',   9.00, 25),
('JBL Go Speaker',           'JBL',        'Audio',      4500,  'uncommon',   8.00, 25),
('Redragon Mech Keyboard',   'Redragon',   'Gaming',     5500,  'uncommon',   7.00, 20),
('HyperX Gaming Headset',    'HyperX',     'Audio',      6500,  'uncommon',   6.00, 20),
('Logitech G304 Mouse',      'Logitech',   'Gaming',     5800,  'uncommon',   6.00, 20),
('Razer DeathAdder',         'Razer',      'Gaming',     6000,  'uncommon',   5.00, 15),
('Ultima Watch Nova Pro',    'Ultima',     'Wearable',   5999,  'uncommon',   7.00, 15),
('Logitech Webcam',          'Logitech',   'Peripheral', 7500,  'uncommon',   5.00, 15),
('Xbox Controller',          'Xbox',       'Gaming',     7500,  'rare',       4.00, 10),
('Fantech Gaming Combo',     'Fantech',    'Gaming',     8000,  'rare',       4.00, 10),
('JBL Flip Speaker',         'JBL',        'Audio',     12000,  'rare',       4.00, 10),
('Stream Microphone Kit',    'Fantech',    'Creator',    9000,  'rare',       4.00, 10),
('Apple AirPods 2nd Gen',    'Apple',      'Audio',     18000,  'rare',       2.00, 8),
('Yamaha Acoustic Guitar',   'Yamaha',     'Lifestyle', 18000,  'rare',       2.00, 5),
('Redmi Pad Tablet',         'Xiaomi',     'Tech',      25000,  'rare',       2.00, 5),
('Nintendo Switch Lite',     'Nintendo',   'Gaming',    28000,  'ultra_rare', 1.00, 5),
('Apple Watch SE',           'Apple',      'Wearable',  45000,  'ultra_rare', 1.00, 3),
('GoPro Hero',               'GoPro',      'Camera',    55000,  'ultra_rare', 0.80, 3),
('iPad 10th Gen',            'Apple',      'Tech',      65000,  'ultra_rare', 0.50, 2),
('DJI Mini Drone',           'DJI',        'Tech',      80000,  'ultra_rare', 0.30, 2),
('iPhone 13',                'Apple',      'Smartphone',85000,  'legendary',  0.50, 2),
('iPhone 14',                'Apple',      'Smartphone',105000, 'legendary',  0.20, 1);

-- Box product pool assignments
INSERT INTO box_product_pool (box_id, product_id) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),
(2,9),(2,10),(2,11),(2,12),(2,13),
(3,14),(3,15),(3,16),(3,17),(3,18),(3,19),
(4,20),(4,21),(4,22),(4,23),(4,24),(4,25),
(5,26),(5,27),(5,28),(5,29),(5,30),(5,31),
(6,1),(6,2),(6,3),(6,4),(6,5),(6,6),(6,7),(6,8),
(6,9),(6,10),(6,11),(6,12),(6,13),(6,14),(6,15),
(6,16),(6,17),(6,18),(6,19),(6,20),(6,21),(6,22),
(6,23),(6,24),(6,25),(6,26),(6,27),(6,28),(6,29),
(6,30),(6,31),(6,32),(6,33);