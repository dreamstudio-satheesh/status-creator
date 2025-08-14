-- Create database if not exists
CREATE DATABASE IF NOT EXISTS status_creator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE status_creator;

-- Laravel migration tables (created by Laravel)
CREATE TABLE migrations (
    id int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    migration varchar(255) NOT NULL,
    batch int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table (Laravel default + custom fields)
CREATE TABLE users (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) NOT NULL,
    email varchar(255) UNIQUE DEFAULT NULL,
    email_verified_at timestamp NULL DEFAULT NULL,
    password varchar(255) DEFAULT NULL,
    mobile varchar(20) UNIQUE DEFAULT NULL,
    google_id varchar(255) DEFAULT NULL,
    avatar varchar(255) DEFAULT NULL,
    subscription_type enum('free','premium') NOT NULL DEFAULT 'free',
    subscription_expires_at timestamp NULL DEFAULT NULL,
    daily_ai_quota int(11) NOT NULL DEFAULT 10,
    daily_ai_used int(11) NOT NULL DEFAULT 0,
    last_quota_reset date DEFAULT NULL,
    remember_token varchar(100) DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY users_mobile_index (mobile),
    KEY users_subscription_type_subscription_expires_at_index (subscription_type,subscription_expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Laravel Sanctum personal access tokens
CREATE TABLE personal_access_tokens (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tokenable_type varchar(255) NOT NULL,
    tokenable_id bigint(20) UNSIGNED NOT NULL,
    name varchar(255) NOT NULL,
    token varchar(64) NOT NULL UNIQUE,
    abilities text DEFAULT NULL,
    last_used_at timestamp NULL DEFAULT NULL,
    expires_at timestamp NULL DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type,tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Laravel cache table
CREATE TABLE cache (
    `key` varchar(255) NOT NULL PRIMARY KEY,
    value mediumtext NOT NULL,
    expiration int(11) NOT NULL,
    KEY cache_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
    `key` varchar(255) NOT NULL PRIMARY KEY,
    owner varchar(255) NOT NULL,
    expiration int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Laravel session table
CREATE TABLE sessions (
    id varchar(255) NOT NULL PRIMARY KEY,
    user_id bigint(20) UNSIGNED DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    user_agent text DEFAULT NULL,
    payload longtext NOT NULL,
    last_activity int(11) NOT NULL,
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Laravel job queue table
CREATE TABLE jobs (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    queue varchar(255) NOT NULL,
    payload longtext NOT NULL,
    attempts tinyint(3) UNSIGNED NOT NULL,
    reserved_at int(10) UNSIGNED DEFAULT NULL,
    available_at int(10) UNSIGNED NOT NULL,
    created_at int(10) UNSIGNED NOT NULL,
    KEY jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
    id varchar(255) NOT NULL PRIMARY KEY,
    name varchar(255) NOT NULL,
    total_jobs int(11) NOT NULL,
    pending_jobs int(11) NOT NULL,
    failed_jobs int(11) NOT NULL,
    failed_job_ids longtext NOT NULL,
    options mediumtext DEFAULT NULL,
    cancelled_at int(10) UNSIGNED DEFAULT NULL,
    created_at int(10) UNSIGNED NOT NULL,
    finished_at int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uuid varchar(255) NOT NULL UNIQUE,
    connection text NOT NULL,
    queue text NOT NULL,
    payload longtext NOT NULL,
    exception longtext NOT NULL,
    failed_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Themes table
CREATE TABLE themes (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name varchar(100) NOT NULL,
    slug varchar(100) NOT NULL UNIQUE,
    name_ta varchar(100) NOT NULL,
    description text DEFAULT NULL,
    icon varchar(255) DEFAULT NULL,
    color varchar(7) DEFAULT NULL,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    order_index int(11) NOT NULL DEFAULT 0,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY themes_slug_index (slug),
    KEY themes_is_active_order_index_index (is_active,order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Templates table
CREATE TABLE templates (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    theme_id bigint(20) UNSIGNED NOT NULL,
    title varchar(255) DEFAULT NULL,
    background_image varchar(500) DEFAULT NULL,
    quote_text text NOT NULL,
    quote_text_ta text NOT NULL,
    font_family varchar(100) NOT NULL DEFAULT 'Tamil',
    font_size int(11) NOT NULL DEFAULT 24,
    text_color varchar(7) NOT NULL DEFAULT '#FFFFFF',
    text_alignment enum('left','center','right') NOT NULL DEFAULT 'center',
    padding int(11) NOT NULL DEFAULT 20,
    is_premium tinyint(1) NOT NULL DEFAULT 0,
    is_featured tinyint(1) NOT NULL DEFAULT 0,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    usage_count int(11) NOT NULL DEFAULT 0,
    ai_generated tinyint(1) NOT NULL DEFAULT 0,
    image_caption text DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY templates_theme_id_index (theme_id),
    KEY templates_is_premium_is_featured_index (is_premium,is_featured),
    KEY templates_is_active_index (is_active),
    CONSTRAINT templates_theme_id_foreign FOREIGN KEY (theme_id) REFERENCES themes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User creations table
CREATE TABLE user_creations (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id bigint(20) UNSIGNED NOT NULL,
    template_id bigint(20) UNSIGNED DEFAULT NULL,
    image_url varchar(500) NOT NULL,
    custom_text text DEFAULT NULL,
    settings json DEFAULT NULL,
    is_ai_generated tinyint(1) NOT NULL DEFAULT 0,
    shared_count int(11) NOT NULL DEFAULT 0,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY user_creations_user_id_index (user_id),
    KEY user_creations_created_at_index (created_at),
    CONSTRAINT user_creations_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT user_creations_template_id_foreign FOREIGN KEY (template_id) REFERENCES templates (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscriptions table
CREATE TABLE subscriptions (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id bigint(20) UNSIGNED NOT NULL,
    plan_name varchar(100) NOT NULL,
    amount decimal(10,2) NOT NULL,
    currency varchar(3) NOT NULL DEFAULT 'INR',
    payment_id varchar(255) DEFAULT NULL,
    payment_method varchar(50) DEFAULT NULL,
    status enum('active','expired','cancelled') NOT NULL DEFAULT 'active',
    starts_at timestamp NOT NULL,
    expires_at timestamp NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY subscriptions_user_id_status_index (user_id,status),
    KEY subscriptions_expires_at_index (expires_at),
    CONSTRAINT subscriptions_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI generation logs table
CREATE TABLE ai_generation_logs (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id bigint(20) UNSIGNED DEFAULT NULL,
    template_id bigint(20) UNSIGNED DEFAULT NULL,
    prompt text DEFAULT NULL,
    response text DEFAULT NULL,
    model_used varchar(100) DEFAULT NULL,
    tokens_used int(11) DEFAULT NULL,
    cost decimal(10,6) DEFAULT NULL,
    status enum('success','failed') NOT NULL DEFAULT 'success',
    error_message text DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY ai_generation_logs_user_id_index (user_id),
    KEY ai_generation_logs_created_at_index (created_at),
    CONSTRAINT ai_generation_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT ai_generation_logs_template_id_foreign FOREIGN KEY (template_id) REFERENCES templates (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fonts table
CREATE TABLE fonts (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name varchar(100) NOT NULL,
    family varchar(100) NOT NULL,
    file_path varchar(255) DEFAULT NULL,
    is_tamil tinyint(1) NOT NULL DEFAULT 0,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY fonts_is_active_index (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE settings (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    key_name varchar(100) NOT NULL UNIQUE,
    value text DEFAULT NULL,
    type varchar(50) NOT NULL DEFAULT 'string',
    description text DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    KEY settings_key_name_index (key_name)
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
INSERT INTO users (name, email, mobile, password, subscription_type, daily_ai_quota, email_verified_at) VALUES
('Admin', 'admin@example.com', '+919876543210', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'premium', 1000, NOW());