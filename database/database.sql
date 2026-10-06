CREATE DATABASE IF NOT EXISTS ar_tourism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ar_tourism;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    remember_token VARCHAR(255) DEFAULT NULL,
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_username (username),
    INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    value TEXT NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE destinations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    short_description TEXT,
    full_description TEXT,
    cover_image VARCHAR(255) DEFAULT NULL,
    location VARCHAR(200) DEFAULT NULL,
    latitude DECIMAL(10,6) DEFAULT NULL,
    longitude DECIMAL(10,6) DEFAULT NULL,
    map_url VARCHAR(255) DEFAULT NULL,
    category_id INT UNSIGNED DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_destinations_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_destinations_name (name),
    INDEX idx_destinations_category (category_id),
    INDEX idx_destinations_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attractions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(180) NOT NULL,
    short_description TEXT,
    full_description TEXT,
    main_image VARCHAR(255) DEFAULT NULL,
    gallery JSON DEFAULT NULL,
    youtube_url VARCHAR(255) DEFAULT NULL,
    website_url VARCHAR(255) DEFAULT NULL,
    google_maps_url VARCHAR(255) DEFAULT NULL,
    latitude DECIMAL(10,6) DEFAULT NULL,
    longitude DECIMAL(10,6) DEFAULT NULL,
    opening_hours VARCHAR(100) DEFAULT NULL,
    entry_information VARCHAR(150) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    display_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_attractions_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    CONSTRAINT fk_attractions_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_attractions_destination (destination_id),
    INDEX idx_attractions_status (status),
    INDEX idx_attractions_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attraction_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attraction_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    title VARCHAR(120) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attraction_images_attraction FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE CASCADE,
    INDEX idx_attraction_images_attraction (attraction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ar_posters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT,
    poster_image VARCHAR(255) DEFAULT NULL,
    target_file VARCHAR(255) DEFAULT NULL,
    target_status ENUM('NOT_COMPILED','READY') NOT NULL DEFAULT 'NOT_COMPILED',
    target_compiled_at DATETIME DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ar_posters_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ar_hotspots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poster_id INT UNSIGNED NOT NULL,
    attraction_id INT UNSIGNED DEFAULT NULL,
    label VARCHAR(120) NOT NULL,
    x DECIMAL(10,6) NOT NULL DEFAULT 0,
    y DECIMAL(10,6) NOT NULL DEFAULT 0,
    width DECIMAL(10,6) NOT NULL DEFAULT 0.2,
    height DECIMAL(10,6) NOT NULL DEFAULT 0.2,
    z_index INT NOT NULL DEFAULT 1,
    video_url VARCHAR(255) DEFAULT NULL,
    content_type ENUM('info','video','map') NOT NULL DEFAULT 'info',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ar_hotspots_poster FOREIGN KEY (poster_id) REFERENCES ar_posters(id) ON DELETE CASCADE,
    CONSTRAINT fk_ar_hotspots_attraction FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE SET NULL,
    INDEX idx_ar_hotspots_poster (poster_id),
    INDEX idx_ar_hotspots_attraction (attraction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(50) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_logs_user (user_id),
    INDEX idx_activity_logs_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key, value) VALUES
('site_name', 'AR Tourism Explorer'),
('site_tagline', 'Explore destinations with immersive AR stories'),
('contact_email', 'hello@artourism.local'),
('contact_phone', '+6012-3456789');
