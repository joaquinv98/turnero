CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- e.g., "1 Hora - 1 Niño"
    duration_minutes INT NOT NULL,
    child_count INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE,
    parent_phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS turns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_names TEXT NOT NULL, -- Stored as JSON or comma-separated for display if multiple kids in one turn logic, but usually linked via child_id if 1-to-1. 
    -- However, requirements say "1, 2 or 3 children" per turn. 
    -- To keep it simple as per requirements: "Se puede registrar más de un niño en un mismo turno".
    -- We will store the main child_id if applicable, or just names if quick entry.
    -- Let's use a JSON column for child_ids to be flexible, or just text for names if they are new.
    -- Better: Link to children table. But for simplicity of "Turno", let's link to a primary child or store names.
    -- Requirement: "Nombre y apellido de los niños".
    
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    duration_minutes INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('active', 'finished', 'overtime') DEFAULT 'active',
    created_by INT, -- User ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Junction table for children in a turn (since 1 turn can have multiple kids)
CREATE TABLE IF NOT EXISTS turn_children (
    turn_id INT NOT NULL,
    child_id INT NOT NULL,
    PRIMARY KEY (turn_id, child_id),
    FOREIGN KEY (turn_id) REFERENCES turns(id),
    FOREIGN KEY (child_id) REFERENCES children(id)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turn_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    type ENUM('cash', 'card', 'transfer', 'other') DEFAULT 'cash',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (turn_id) REFERENCES turns(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default pricing
INSERT INTO pricing (name, duration_minutes, child_count, price) VALUES 
('1 Hora - 1 Niño', 60, 1, 1500.00),
('2 Horas - 1 Niño', 120, 1, 2500.00),
('1 Hora - 2 Niños', 60, 2, 2800.00),
('2 Horas - 2 Niños', 120, 2, 4500.00);
