-- ============================================================
-- NexusBrew Coffee Shop - Database Schema & Sample Data
-- ============================================================
-- DevSecOps Project: Implementasi DevSecOps pada CI/CD Pipeline
-- Purpose: Demo application database for vulnerability scanning
-- ============================================================

CREATE DATABASE IF NOT EXISTS nexusbrew CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexusbrew;

-- ============================================================
-- Table: users
-- Note: This table EXISTS for demonstration only.
--       It is NOT used for authentication (login is hard-coded).
--       Storing plain-text passwords here is an INTENTIONAL
--       vulnerability (CWE-256: Unprotected Storage of Credentials)
--       detected by Semgrep.
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,  -- VULNERABILITY: Plain-text password storage
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- VULNERABILITY: Plain-text passwords inserted into database
-- CWE-256: Unprotected Storage of Credentials
-- Detected by: Semgrep
INSERT INTO users (username, email, password) VALUES
('admin',   'admin@nexusbrew.local', 'Admin123!'),   -- [VULN] plain-text password
('barista', 'barista@nexusbrew.local', 'coffee2024'); -- [VULN] plain-text password

-- ============================================================
-- Table: categories
-- ============================================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
('Hot Coffee'),
('Cold Brew'),
('Espresso'),
('Specialty Drinks'),
('Coffee Beans & Merchandise');

-- ============================================================
-- Table: products
-- ============================================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200) NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock       INT NOT NULL DEFAULT 0,
    category_id INT,
    image       VARCHAR(500),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Sample Coffee Products (images from Unsplash - royalty free)
INSERT INTO products (name, description, price, stock, category_id, image) VALUES
(
    'Signature Espresso',
    'A bold and rich single shot of our signature NexusBrew espresso blend. Crafted from 100% Arabica beans sourced from the highlands of Ethiopia, this espresso delivers a perfect balance of intensity and sweetness with a velvety crema on top.',
    38000, 100, 3,
    'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=600&q=80'
),
(
    'Classic Americano',
    'Espresso shots topped with hot water to produce a light layer of crema. The perfect everyday coffee for those who love a smooth, robust flavor without the heaviness of a latte. Our signature Americano is clean, bright, and endlessly satisfying.',
    35000, 150, 1,
    'https://images.unsplash.com/photo-1521302080334-4bebac2763a6?w=600&q=80'
),
(
    'Creamy Cappuccino',
    'A traditional Italian classic — equal parts espresso, steamed milk, and silky milk foam. Our cappuccino is crafted with care to achieve the perfect 1:1:1 ratio that makes every sip a warm embrace. Dusted with a hint of cinnamon.',
    45000, 120, 1,
    'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=600&q=80'
),
(
    'Caramel Macchiato',
    'Vanilla-flavored syrup meets freshly steamed milk and a bold espresso shot, finished with a rich caramel drizzle. Our Caramel Macchiato is the perfect harmony of sweet and bold — a true NexusBrew crowd favorite.',
    52000, 80, 4,
    'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600&q=80'
),
(
    'Cold Brew Concentrate',
    'Steeped for 18 hours in cold, filtered water, our Cold Brew Concentrate is smooth, low-acid, and incredibly refreshing. Twice as strong as regular coffee — dilute with water or milk to your preference. Best served over ice.',
    48000, 90, 2,
    'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=600&q=80'
),
(
    'Vanilla Sweet Cream Cold Brew',
    'Our famous slow-steeped cold brew topped with a silky sweet cream infused with Madagascar vanilla. The contrast of the bold cold brew and the delicate vanilla cream creates a truly indulgent experience in every sip.',
    58000, 60, 2,
    'https://images.unsplash.com/photo-1606791405792-1004f1718d0c?w=600&q=80'
),
(
    'Matcha Latte',
    'Premium ceremonial-grade matcha whisked into steamed oat milk with a touch of honey. Earthy, vibrant, and beautifully balanced. Our Matcha Latte is the perfect alternative for those seeking something unique and nourishing.',
    55000, 70, 4,
    'https://images.unsplash.com/photo-1515823662972-da6a2e4d3002?w=600&q=80'
),
(
    'Ethiopia Single Origin',
    'Light roast single-origin beans from the Yirgacheffe region of Ethiopia. Notes of blueberry, jasmine, and bergamot. Hand-sorted and small-batch roasted to preserve the natural terroir of this extraordinary origin. 250g bag.',
    120000, 45, 5,
    'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=600&q=80'
),
(
    'Sumatra Dark Roast',
    'Bold, earthy, and full-bodied. Our Sumatra Dark Roast features beans from the Mandheling region with a deep chocolate finish and low acidity. Perfect for those who love a rich, powerful cup of coffee. 250g bag.',
    110000, 55, 5,
    'https://images.unsplash.com/photo-1611854779393-1b2da9d400fe?w=600&q=80'
),
(
    'Iced Spanish Latte',
    'Sweetened condensed milk meets double espresso over ice — the beloved Spanish Latte is rich, indulgent, and perfectly caffeinated. A Southeast Asian café staple reimagined with our signature espresso blend.',
    50000, 85, 2,
    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80'
);
