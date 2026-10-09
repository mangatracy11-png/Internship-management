-- FINAL COMPLETE FK FIX for Chat Messages
-- Run this ONCE on defenseproject DB

USE defenseproject;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Clean existing invalid messages (orphans)
DELETE FROM messages WHERE receiver_id NOT IN (SELECT id FROM users) OR sender_id NOT IN (SELECT id FROM users);

-- 2. Drop existing strict FKs (if any)
ALTER TABLE messages DROP FOREIGN KEY IF EXISTS messages_ibfk_1;
ALTER TABLE messages DROP FOREIGN KEY IF EXISTS messages_ibfk_2;
ALTER TABLE messages DROP INDEX IF EXISTS sender_id;
ALTER TABLE messages DROP INDEX IF EXISTS receiver_id;

-- 3. Add proper indexes
ALTER TABLE messages ADD INDEX idx_sender (sender_id);
ALTER TABLE messages ADD INDEX idx_receiver (receiver_id);

-- 4. Add new FKs: sender CASCADE, receiver SET NULL (allows orphans if user deleted)
ALTER TABLE messages ADD CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE messages ADD CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify
SELECT 'SUCCESS: FKs fixed. Invalid data cleaned.' as status;
SELECT COUNT(*) as remaining_messages FROM messages;
SELECT COUNT(*) as orphan_receiver FROM messages WHERE receiver_id NOT IN (SELECT id FROM users);

