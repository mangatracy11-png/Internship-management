CREATE TABLE IF NOT EXISTS `meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supervisor_id` int(11) NOT NULL,
  `intern_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `meeting_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `meeting_type` enum('virtual','in-person') NOT NULL,
  `location` varchar(500) NOT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_supervisor` (`supervisor_id`),
  KEY `idx_intern` (`intern_id`),
  KEY `idx_date` (`meeting_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
