-- Complete attendance table for internship system
USE defenseproject;

-- Drop if exists
DROP TABLE IF EXISTS attendance;

-- Create attendance table
CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  intern_id INT NOT NULL,
  date DATE NOT NULL,
  check_in TIME,
  check_out
