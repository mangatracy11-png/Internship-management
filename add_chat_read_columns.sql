-- Add read tracking to messages table for chat.php
-- Safe: IF NOT EXISTS

USE defenseproject;

ALTER TABLE messages 
ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS read_at TIMESTAMP NULL;

-- Update existing unread messages
UPDATE messages SET is_read = 0 WHERE is_read IS NULL;

-- Verify
SELECT COUNT(*) as total_messages, 
       SUM(is_read) as read_messages, 
       COUNT(*) - SUM(is_read) as unread_messages 
FROM messages;
