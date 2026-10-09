-- Create departments table for admin management
USE defenseproject;

CREATE TABLE IF NOT EXISTS `department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data
INSERT INTO `department` (name, description) VALUES
('Departments of Studies and Projects(Dep)', 'Focuses on research, development, and implementation of innovative IT projects for public sector enhancement., web applications, cybersecurity'),
('Department of Exploitation and Software (DEL)', 'Manages software exploitation, maintenance, and development of custom applications for government use., telecommunications'),
('Department of Telecomputing and Office Automation (DTP)', 'Handles telecomputing infrastructure, network management, and office automation solutions.'),
('Department of Applied Computing to Research and Teaching (DIRE)', 'Supports research institutions and educational facilities with advanced computing solutions and digital tools.');
('Department of Administrative and Finance Affairs (DAAF)', 'Manages administrative processes, financial systems, and resource allocation for IT projects.');

SELECT 'Departments table created/populated!' as status;


