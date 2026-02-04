-- Sample Tracking System Database Schema
-- Database: sample_tracking

CREATE DATABASE IF NOT EXISTS sample_tracking 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sample_tracking;

-- ============================================
-- Table: users
-- Stores user accounts with role-based access
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `role` enum('Admin','Operator','Viewer') NOT NULL DEFAULT 'Viewer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: rfid_tags
-- Stores RFID tag UIDs and their status
-- ============================================
CREATE TABLE IF NOT EXISTS `rfid_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `idx_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: samples
-- Stores sample information with RFID tracking
-- ============================================
CREATE TABLE IF NOT EXISTS `samples` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sample_number` varchar(50) NOT NULL,
  `sample_type` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `person_name` varchar(100) NOT NULL,
  `collected_date` date NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `rfid_id` int(11) NOT NULL,
  `status` enum('pending','checked','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sample_number` (`sample_number`),
  KEY `rfid_id` (`rfid_id`),
  KEY `idx_sample_number` (`sample_number`),
  KEY `idx_status` (`status`),
  KEY `idx_sample_type` (`sample_type`),
  KEY `idx_category` (`category`),
  KEY `idx_collected_date` (`collected_date`),
  CONSTRAINT `samples_ibfk_1` FOREIGN KEY (`rfid_id`) REFERENCES `rfid_tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: audit_logs
-- Tracks all system actions for chain-of-custody
-- ============================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sample_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_sample_id` (`sample_id`),
  KEY `idx_timestamp` (`timestamp`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `audit_logs_ibfk_2` FOREIGN KEY (`sample_id`) REFERENCES `samples` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Create uploads directories (handled by PHP)
-- ============================================
-- public_html/uploads/avatars/ - User profile pictures
-- public_html/uploads/samples/ - Sample attachments (if needed)
