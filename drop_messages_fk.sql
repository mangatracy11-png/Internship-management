ALTER TABLE messages DROP FOREIGN KEY messages_ibfk_1;
ALTER TABLE messages DROP FOREIGN KEY messages_ibfk_2;
ALTER TABLE messages DROP INDEX sender_id;
ALTER TABLE messages DROP INDEX receiver_id;
