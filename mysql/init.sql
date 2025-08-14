-- Create database if not exists
CREATE DATABASE IF NOT EXISTS status_creator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE status_creator;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    mobile VARCHAR(20) UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255),
    google_id VARCHAR(255),
    avatar VARCHAR(255),
    subscription_type ENUM('free', 'premium') DEFAULT 'free',
    subscription_expires_at TIMESTAMP NULL,
    daily_ai_quota INT DEFAULT 10,
    daily_ai_used INT DEFAULT 0,
    last_quota_reset DATE,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mobile (mobile),
    INDEX idx_email (email),
    INDEX idx_subscription (subscription_type, subscription_expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Themes table
CREATE TABLE IF NOT EXISTS themes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    name_ta VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    color VARCHAR(7),
    is_active BOOLEAN DEFAULT TRUE,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active_order (is_active, order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Templates table
CREATE TABLE IF NOT EXISTS templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    theme_id BIGINT UNSIGNED,
    title VARCHAR(255),
    background_image VARCHAR(500),
    quote_text TEXT NOT NULL,
    quote_text_ta TEXT NOT NULL,
    font_family VARCHAR(100) DEFAULT 'Tamil',
    font_size INT DEFAULT 24,
    text_color VARCHAR(7) DEFAULT '#FFFFFF',
    text_alignment ENUM('left', 'center', 'right') DEFAULT 'center',
    padding INT DEFAULT 20,
    is_premium BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    ai_generated BOOLEAN DEFAULT FALSE,
    image_caption TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE,
    INDEX idx_theme (theme_id),
    INDEX idx_premium_featured (is_premium, is_featured),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User creations table
CREATE TABLE IF NOT EXISTS user_creations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED,
    image_url VARCHAR(500) NOT NULL,
    custom_text TEXT,
    settings JSON,
    is_ai_generated BOOLEAN DEFAULT FALSE,
    shared_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscriptions table
CREATE TABLE IF NOT EXISTS subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'INR',
    payment_id VARCHAR(255),
    payment_method VARCHAR(50),
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    starts_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI generation logs table
CREATE TABLE IF NOT EXISTS ai_generation_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    template_id BIGINT UNSIGNED,
    prompt TEXT,
    response TEXT,
    model_used VARCHAR(100),
    tokens_used INT,
    cost DECIMAL(10, 6),
    status ENUM('success', 'failed') DEFAULT 'success',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fonts table
CREATE TABLE IF NOT EXISTS fonts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    family VARCHAR(100) NOT NULL,
    file_path VARCHAR(255),
    is_tamil BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    type VARCHAR(50) DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default themes
INSERT INTO themes (name, slug, name_ta, description, color, order_index) VALUES
('Love', 'love', 'காதல்', 'Romantic and love quotes', '#FF69B4', 1),
('Motivation', 'motivation', 'ஊக்கம்', 'Motivational and inspirational quotes', '#FFD700', 2),
('Sad', 'sad', 'சோகம்', 'Emotional and sad quotes', '#4169E1', 3),
('Friendship', 'friendship', 'நட்பு', 'Friendship quotes', '#32CD32', 4),
('Life', 'life', 'வாழ்க்கை', 'Life philosophy quotes', '#FF8C00', 5),
('Success', 'success', 'வெற்றி', 'Success and achievement quotes', '#9370DB', 6),
('Family', 'family', 'குடும்பம்', 'Family and relationship quotes', '#DC143C', 7),
('Morning', 'morning', 'காலை வணக்கம்', 'Good morning wishes', '#FFA500', 8),
('Night', 'night', 'இரவு வணக்கம்', 'Good night wishes', '#191970', 9),
('Festival', 'festival', 'பண்டிகை', 'Festival wishes and greetings', '#FF1493', 10);

-- Insert default fonts
INSERT INTO fonts (name, family, is_tamil) VALUES
('Latha', 'Latha', TRUE),
('Catamaran', 'Catamaran', TRUE),
('Mukta Malar', 'Mukta Malar', TRUE),
('Hind Madurai', 'Hind Madurai', TRUE),
('Roboto', 'Roboto', FALSE),
('Open Sans', 'Open Sans', FALSE),
('Montserrat', 'Montserrat', FALSE);

-- Insert default settings
INSERT INTO settings (key_name, value, type, description) VALUES
('free_daily_limit', '10', 'integer', 'Daily AI generation limit for free users'),
('premium_daily_limit', '100', 'integer', 'Daily AI generation limit for premium users'),
('max_upload_size_mb', '10', 'integer', 'Maximum upload size in MB'),
('watermark_enabled', 'true', 'boolean', 'Enable watermark on free user creations'),
('maintenance_mode', 'false', 'boolean', 'Enable maintenance mode'),
('openrouter_model', 'meta-llama/llama-3.2-3b-instruct:free', 'string', 'OpenRouter model to use'),
('caption_model', 'Salesforce/blip-image-captioning-base', 'string', 'Image captioning model');

-- Create admin user (password: admin123)
INSERT INTO users (name, email, mobile, password, subscription_type, daily_ai_quota) VALUES
('Admin', 'admin@example.com', '+919876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'premium', 1000);