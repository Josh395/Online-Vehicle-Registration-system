-- Table for valid NINs
CREATE TABLE IF NOT EXISTS valid_nins (
    nin VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    tin VARCHAR(20) NOT NULL
);
INSERT INTO valid_nins (nin, name, tin) VALUES
('11111111111111111111', 'Hamis Monyo', '100000001'),
('22222222222222222222', 'Ahmed Mdabu', '100000002'),
('33333333333333333333', 'Nicholas Haule', '100000003'),
('44444444444444444444', 'Rose Dario', '100000004'),
('55555555555555555555', 'Mellania Ngomeni', '100000005'),
('66666666666666666666', 'Blandina Msume', '100000006'),
('77777777777777777777', 'Theofil Daniel', '100000007'),
('88888888888888888888', 'Joshua Alexander', '100000008'),
('99999999999999999999', 'Rachel Msigala', '100000009'),
('12345678901234567890', 'Robert Dario', '100000010'),
('23456789012345678901', 'Ruth David', '100000011'),
('34567890123456789012', 'Anna Modest', '100000012'),
('45678901234567890123', 'Miriam Moses', '100000013'),
('56789012345678901234', 'Rene Peter', '100000014'),
('67890123456789012345', 'Magreth Anthony', '100000015'),
('78901234567890123456', 'Nuru Baraka', '100000016'),
('89012345678901234567', 'Kephlen Idriss', '100000017'),
('90123456789012345678', 'Cesilia Anthony', '100000018'),
('11223344556677889900', 'Tumaini Aron', '100000019'),
('22334455667788990011', 'Elisha Elias', '100000020');

-- Table for valid Passports
CREATE TABLE IF NOT EXISTS valid_passports (
    passport VARCHAR(9) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    tin VARCHAR(20) NOT NULL
);
INSERT INTO valid_passports (passport, name, tin) VALUES
('AA1111111', 'Hamis Monyo', '100000001'),
('BB2222222', 'Ahmed Mdabu', '100000002'),
('CC3333333', 'Nicholas Haule', '100000003'),
('DD4444444', 'Rose Dario', '100000004'),
('EE5555555', 'Mellania Ngomeni', '100000005'),
('FF6666666', 'Blandina Msume', '100000006'),
('GG7777777', 'Theofil Daniel', '100000007'),
('HH8888888', 'Joshua Alexander', '100000008'),
('II9999999', 'Rachel Msigala', '100000009'),
('JJ1234567', 'Robert Dario', '100000010'),
('KK2345678', 'Ruth David', '100000011'),
('LL3456789', 'Anna Modest', '100000012'),
('MM4567890', 'Miriam Moses', '100000013'),
('NN5678901', 'Rene Peter', '100000014'),
('OO6789012', 'Magreth Anthony', '100000015'),
('PP7890123', 'Nuru Baraka', '100000016'),
('QQ8901234', 'Kephlen Idriss', '100000017'),
('RR9012345', 'Cesilia Anthony', '100000018'),
('SS1122334', 'Tumaini Aron', '100000019'),
('TT2233445', 'Elisha Elias', '100000020');


-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    tin VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for valid TINs
CREATE TABLE valid_tins (
    tin VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);
INSERT INTO valid_tins (tin, name) VALUES
('100000001', 'Hamis Monyo'),
('100000002', 'Ahmed Mdabu'),
('100000003', 'Nicholas Haule'),
('100000004', 'Rose Dario'),
('100000005', 'Mellania Ngomeni'),
('100000006', 'Blandina Msume'),
('100000007', 'Theofil Daniel'),
('100000008', 'Joshua Alexander'),
('100000009', 'Rachel Msigala'),
('100000010', 'Robert Dario'),
('100000011', 'Ruth David'),
('100000012', 'Anna Modest'),
('100000013', 'Miriam Moses'),
('100000014', 'Rene Peter'),
('100000015', 'Magreth Anthony'),
('100000016', 'Nuru Baraka'),
('100000017', 'Kephlen Idriss'),
('100000018', 'Cecilia Anthony'),
('100000019', 'Tumaini Aron'),
('100000020', 'Elisha Elias');

-- Admin users table
CREATE TABLE admin_users (
    admin_id INT PRIMARY KEY AUTO_INCREMENT ,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('SUPER_ADMIN', 'STAFF') DEFAULT 'STAFF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    reference_number VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected') DEFAULT 'draft',
    registration_number VARCHAR(20),
    full_name VARCHAR(100),
    dob DATE,
    primary_phone VARCHAR(20),
    email VARCHAR(100),
    physical_address TEXT,
    id_type VARCHAR(50),
    id_number VARCHAR(50),
    vin VARCHAR(17),
    engine_number VARCHAR(50),
    make VARCHAR(50),
    model VARCHAR(50),
    year INT,
    vehicle_type VARCHAR(50),
    color VARCHAR(20),
    fuel_type VARCHAR(20),
    transmission VARCHAR(20),
    odometer INT,
    insurance_provider VARCHAR(100),
    policy_number VARCHAR(50),
    insurance_start DATE,
    insurance_expiry DATE,
    cover_type VARCHAR(30),
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_status ENUM('pending', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Uploads table
CREATE TABLE uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    file_type VARCHAR(50),
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    control_number VARCHAR(30) NOT NULL,
    owner_id INT NOT NULL,
    application_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('MobileMoney', 'Bank', 'Card') NOT NULL,
    status ENUM('Pending', 'Paid', 'Failed') DEFAULT 'Pending',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    application_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
);


-- (username: admin, password: admin123)
INSERT INTO admin_users (username, password_hash, role) VALUES
('admin', '$2y$10$4M/2Y.KMLbesNIfzpB1xsemEzMzMn1DldFEllHe23U/gFBvdoA7Oe', 'SUPER_ADMIN');


CREATE TABLE transfer_ownership (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_full_name VARCHAR(100) NOT NULL,
    seller_id VARCHAR(50) NOT NULL,
    seller_phone VARCHAR(30) NOT NULL,
    seller_email VARCHAR(100) NOT NULL,
    seller_address VARCHAR(255) NOT NULL,
    buyer_full_name VARCHAR(100) NOT NULL,
    buyer_id VARCHAR(50) NOT NULL,
    buyer_dob DATE NOT NULL,
    buyer_phone VARCHAR(30) NOT NULL,
    buyer_email VARCHAR(100) NOT NULL,
    buyer_address VARCHAR(255) NOT NULL,
    vehicle_reg_number VARCHAR(50) NOT NULL,
    vehicle_vin VARCHAR(50) NOT NULL,
    engine_number VARCHAR(50) NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year_manufacture INT NOT NULL,
    color VARCHAR(30) NOT NULL,
    odometer INT NOT NULL,
    sale_agreement VARCHAR(255) NOT NULL,
    prev_reg_card VARCHAR(255) NOT NULL,
    transfer_fee DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL,
    status VARCHAR(30) DEFAULT 'pending',
    reviewed_at DATETIME NULL,
    reviewed_by VARCHAR(100) NULL,
    rejection_reason VARCHAR(255) NULL
);
