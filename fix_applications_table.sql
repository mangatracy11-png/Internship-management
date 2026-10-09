-- Fix applications table - add created_at column
USE defenseproject;

ALTER TABLE `applications` ADD COLUMN IF NOT EXISTS `created_at` timestamp DEFAULT CURRENT_TIMESTAMP;

-- If table doesn't exist, create it
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intern_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Applications table fixed/created!' as status;

