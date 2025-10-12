-- eventia_users.sql
-- Cleaned and optimized database setup for Eventia
-- Removed duplicate database and table definitions

CREATE DATABASE IF NOT EXISTS eventia_users;
USE eventia_users;

-- Users Table
CREATE TABLE IF NOT EXISTS tbl_users(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    zip VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    otp INT(11) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    otp_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Packages Table
CREATE TABLE IF NOT EXISTS tbl_packages(
    package_id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(255) NOT NULL,
    package_description TEXT,
    base_price DECIMAL(10, 2) NOT NULL,
    event_type VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services Table
CREATE TABLE IF NOT EXISTS tbl_services(
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(255) NOT NULL,
    service_description TEXT,
    base_price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100),
    customizable BOOLEAN DEFAULT FALSE,
    customization_options JSON,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Service details table
CREATE TABLE IF NOT EXISTS tbl_service_details(
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    detail_name VARCHAR(255) NOT NULL,
    price_min DECIMAL(10,2),
    price_max DECIMAL(10,2),
    FOREIGN KEY (service_id) REFERENCES tbl_services(service_id) ON DELETE CASCADE
);

-- Service features table
CREATE TABLE IF NOT EXISTS tbl_service_features(
    feature_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    feature_name VARCHAR(255) NOT NULL,
    FOREIGN KEY (service_id) REFERENCES tbl_services(service_id) ON DELETE CASCADE
);

-- Venues Table
CREATE TABLE IF NOT EXISTS tbl_venues(
    venue_id INT AUTO_INCREMENT PRIMARY KEY,
    venue_name VARCHAR(255) NOT NULL,
    venue_type VARCHAR(100) NOT NULL,
    capacity INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) DEFAULT 0.00,
    description TEXT,
    image_url VARCHAR(255),
    amenities TEXT,
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE IF NOT EXISTS tbl_bookings(
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_reference VARCHAR(50) NOT NULL UNIQUE,
    user_id INT,
    booking_type ENUM('self', 'guided') NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    package_id INT,
    package_name VARCHAR(255),
    package_price DECIMAL(10, 2),
    venue_type ENUM('own', 'rental') NOT NULL,
    venue_id INT NULL,
    venue_address TEXT,
    event_date DATE NOT NULL,
    event_time VARCHAR(50) NOT NULL,
    event_location VARCHAR(255) NOT NULL,
    full_address TEXT,
    
    -- Customer information
    contact_name VARCHAR(255) NOT NULL,
    contact_email VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(50) NOT NULL,
    alternate_phone VARCHAR(50),
    company_name VARCHAR(255),
    backup_email VARCHAR(255),
    preferred_contact ENUM('Any', 'Email', 'Phone', 'SMS') DEFAULT 'Any',
    
    guest_count INT,
    special_instructions TEXT,
    
    -- Payment
    payment_method ENUM('GCash', 'Bank Transfer', 'PayPal', 'Installment') NOT NULL,
    payment_status ENUM('pending', 'partial', 'paid', 'cancelled') DEFAULT 'pending',
    receipt_path VARCHAR(255),
    
    -- Status
    booking_status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) DEFAULT 0.00,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES tbl_users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (venue_id) REFERENCES tbl_venues(venue_id) ON DELETE SET NULL,
    FOREIGN KEY (package_id) REFERENCES tbl_packages(package_id) ON DELETE SET NULL
);

-- Booking Services Table
CREATE TABLE IF NOT EXISTS tbl_booking_services(
    booking_service_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_description TEXT,
    base_price DECIMAL(10, 2) DEFAULT 0.00,
    final_price DECIMAL(10, 2) DEFAULT 0.00,
    customization_details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES tbl_bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES tbl_services(service_id) ON DELETE CASCADE
);

-- Indexes for better performance
CREATE INDEX idx_booking_reference ON tbl_bookings(booking_reference);
CREATE INDEX idx_booking_email ON tbl_bookings(contact_email);
CREATE INDEX idx_booking_date ON tbl_bookings(event_date);
CREATE INDEX idx_booking_status ON tbl_bookings(booking_status);
CREATE INDEX idx_service_status ON tbl_services(status);
CREATE INDEX idx_package_status ON tbl_packages(status);
CREATE INDEX idx_venue_status ON tbl_venues(status);

-- Insert Sample Data for Packages
INSERT IGNORE INTO tbl_packages (package_name, package_description, base_price, event_type) VALUES
('Wedding Event Package', 'Complete wedding planning with catering, floral design, and full coordination.', 108190.00, 'Wedding'),
('Birthday Celebration Package', 'Perfect for kids or adults, with cake, decorations, and entertainment add-ons.', 50000.00, 'Birthday'),
('Corporate Event Package', 'Professional setup for seminars, product launches, or company parties.', 140500.00, 'Corporate'),
('Debut Package', 'Includes 18 roses & candles setup, photography, and custom stage backdrop.', 95000.00, 'Debut'),
('Christening Package', 'Includes catering for 50 guests, souvenirs, and floral setup.', 45000.00, 'Christening'),
('Anniversary Celebration', 'Romantic dinner setting with live music and custom themes.', 75000.00, 'Anniversary'),
('Holiday Party Package', 'Christmas or New Year party with buffet and entertainment options.', 85000.00, 'Holiday'),
('Graduation Party Package', 'Includes simple catering, tarpaulin, and sound system.', 35000.00, 'Graduation'),
('Engagement Party Package', 'Intimate gathering with floral arrangements and photography.', 65000.00, 'Engagement'),
('Reunion Event Package', 'Perfect for family or class reunions with buffet and photo booth.', 55000.00, 'Reunion');

-- Insert Sample Data for Services
INSERT IGNORE INTO tbl_services (service_name, service_description, base_price, category, customizable, customization_options) VALUES
('Catering Service', 'Professional food and beverage service for your event', 15000.00, 'Catering', TRUE, '{"options": {"guests": {"type": "number", "min": 50, "max": 200, "price_per_unit": 300}, "premium_menu": {"type": "boolean", "price": 8000}, "dessert_station": {"type": "boolean", "price": 5000}}}'),
('Decoration Setup', 'Transform your venue with beautiful decor and themes', 10000.00, 'Decorations', TRUE, '{"options": {"centerpieces": {"type": "number", "min": 5, "max": 20, "price_per_unit": 800}, "arch_decoration": {"type": "boolean", "price": 5000}, "premium_flowers": {"type": "boolean", "price": 3000}}}'),
('Photography & Videography', 'Capture your special moments professionally', 12000.00, 'Photography', TRUE, '{"options": {"hours": {"type": "number", "min": 4, "max": 12, "price_per_unit": 1875}, "photographers": {"type": "number", "min": 1, "max": 3, "price_per_unit": 5000}, "album": {"type": "boolean", "price": 8000}}}'),
('Sound System & Lights', 'Professional audio and lighting for perfect ambiance', 8000.00, 'Audio-Visual', FALSE, NULL),
('Entertainment', 'Keep your guests entertained throughout the event', 10000.00, 'Entertainment', FALSE, NULL),
('Event Coordination', 'Professional event management and coordination', 15000.00, 'Coordination', FALSE, NULL);

-- Insert Service Details
INSERT IGNORE INTO tbl_service_details (service_id, detail_name, price_min, price_max) VALUES
(1, 'Buffet Style', 15000, 35000),
(1, 'Plated Service', 25000, 50000),
(1, 'Cocktail Setup', 20000, 40000),
(2, 'Classic Theme', 10000, 25000),
(2, 'Modern Theme', 20000, 40000),
(2, 'Floral Arrangements', 5000, 20000),
(3, 'Basic Package', 12000, 25000),
(3, 'Premium Package', 30000, 50000),
(3, 'Drone Coverage', 8000, 15000),
(4, 'Basic Sound Setup', 8000, 15000),
(4, 'Full DJ Equipment', 15000, 25000);

-- Insert Service Features
INSERT IGNORE INTO tbl_service_features (service_id, feature_name) VALUES
(1, 'Menu Planning'), (1, 'Professional Staff'), (1, 'Food Safety Certified'),
(2, 'Theme Consultation'), (2, 'Setup & Teardown'), (2, 'Custom Designs'),
(3, 'Professional Equipment'), (3, 'Edited Photos/Videos'), (3, 'Online Gallery'),
(4, 'Professional Audio'), (4, 'Lighting Effects'), (4, 'Technician Support'),
(5, 'Professional Performers'), (5, 'Equipment Included'), (5, 'Music Coordination'),
(6, 'Timeline Management'), (6, 'Vendor Coordination'), (6, 'Day-of Supervision');

-- Insert Sample Data for Venues
INSERT IGNORE INTO tbl_venues (venue_name, venue_type, capacity, location, price, description, amenities) VALUES
('The Garden Pavilion', 'Open-air', 100, 'Quezon City', 15000.00, 'Beautiful outdoor garden venue perfect for intimate gatherings and garden weddings', 'Garden seating, Natural lighting, Outdoor stage, Parking space'),
('Grand Ballroom', 'Indoor Hall', 300, 'Makati City', 35000.00, 'Elegant indoor ballroom with crystal chandeliers and sophisticated ambiance', 'Air conditioning, Sound system, Stage, Bridal room, Parking'),
('Seaside View Hall', 'Beachfront', 150, 'Batangas', 25000.00, 'Stunning beachfront venue with panoramic ocean views', 'Beach access, Sunset view, Outdoor seating, Parking, Changing rooms'),
('Executive Conference Hall', 'Conference', 150, 'Taguig City', 40000.00, 'Modern conference facility with AV equipment', 'AV equipment, Conference tables, WiFi, Catering kitchen'),
('Rooftop Terrace', 'Rooftop', 100, 'Mandaluyong City', 45000.00, 'Panoramic city views with modern amenities', 'City views, Modern furniture, Bar setup, Lighting');

-- Insert Admin User
INSERT IGNORE INTO tbl_users (first_name, last_name, email, phone, city, zip, address, password, status) VALUES
('Admin', 'Eventia', 'admin@eventia.com', '09171234567', 'Manila', '1000', '123 Admin Street', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');