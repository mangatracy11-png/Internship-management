-- Fix messages FK for intern_id/supervisor_id compatibility
USE defenseproject;

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE messages DROP FOREIGN KEY messages_ibfk_1;
ALTER TABLE messages DROP FOREIGN KEY messages_ibfk_2;

ALTER TABLE messages ADD CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE messages ADD CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- Clean invalid data
DELETE FROM messages WHERE receiver_id NOT IN (SELECT id FROM users) OR sender_id NOT IN (SELECT id FROM users);

SELECT 'FK fixed, data cleaned' as status;

