CREATE DATABASE IF NOT EXISTS photohost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE photohost;

CREATE TABLE IF NOT EXISTS photos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  original_filename VARCHAR(255) NOT NULL,
  stored_filename VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  file_size BIGINT UNSIGNED NOT NULL,
  width INT NULL,
  height INT NULL,
  title VARCHAR(255) NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;