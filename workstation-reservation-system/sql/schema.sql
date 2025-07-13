CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    avatar VARCHAR(255) DEFAULT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE workstations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    is_available BOOLEAN DEFAULT TRUE,
    status ENUM('idle', 'busy') DEFAULT 'idle',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    workstation_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'canceled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workstation_id) REFERENCES workstations(id) ON DELETE CASCADE
);

-- Add lab computers (48 left, 14 right, 8 bottom = 70 total)
INSERT INTO workstations (name) VALUES
  ('PC 1'), ('PC 2'), ('PC 3'), ('PC 4'), ('PC 5'), ('PC 6'), ('PC 7'), ('PC 8'),
  ('PC 9'), ('PC 10'), ('PC 11'), ('PC 12'), ('PC 13'), ('PC 14'), ('PC 15'), ('PC 16'),
  ('PC 17'), ('PC 18'), ('PC 19'), ('PC 20'), ('PC 21'), ('PC 22'), ('PC 23'), ('PC 24'),
  ('PC 25'), ('PC 26'), ('PC 27'), ('PC 28'), ('PC 29'), ('PC 30'), ('PC 31'), ('PC 32'),
  ('PC 33'), ('PC 34'), ('PC 35'), ('PC 36'), ('PC 37'), ('PC 38'), ('PC 39'), ('PC 40'),
  ('PC 41'), ('PC 42'), ('PC 43'), ('PC 44'), ('PC 45'), ('PC 46'), ('PC 47'), ('PC 48'),
  ('PC 49'), ('PC 50'), ('PC 51'), ('PC 52'), ('PC 53'), ('PC 54'), ('PC 55'), ('PC 56'),
  ('PC 57'), ('PC 58'), ('PC 59'), ('PC 60'), ('PC 61'), ('PC 62'), ('PC 63'), ('PC 64'),
  ('PC 65'), ('PC 66'), ('PC 67'), ('PC 68'), ('PC 69'), ('PC 70');

ALTER TABLE workstations ADD COLUMN status ENUM('idle', 'busy') DEFAULT 'idle' AFTER name;