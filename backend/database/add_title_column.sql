-- Migration: Add title column to users table
-- This adds a title column (Mr, Mrs, Ms) to the users table

-- Check if column exists and add if it doesn't
-- Note: MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- Run this only if the column doesn't exist, or it will error

ALTER TABLE `users` 
ADD COLUMN `title` varchar(10) NULL 
AFTER `email`;

-- Verify the column was added
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME = 'users' 
-- AND COLUMN_NAME = 'title';

