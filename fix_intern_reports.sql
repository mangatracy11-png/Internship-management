-- Fix intern_reports table structure
ALTER TABLE intern_reports ADD COLUMN IF NOT EXISTS title VARCHAR(255) DEFAULT NULL;
ALTER TABLE intern_reports ADD COLUMN IF NOT EXISTS content TEXT;
ALTER TABLE intern_reports ADD COLUMN IF NOT EXISTS file_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE intern_reports ADD COLUMN IF NOT EXISTS week_number INT DEFAULT NULL;
ALTER TABLE intern_reports ADD COLUMN IF NOT EXISTS status ENUM('pending','reviewed','feedback_sent') DEFAULT 'pending';

-- Ensure indexes
ALTER TABLE intern_reports ADD INDEX IF NOT EXISTS idx_intern (intern_id);
ALTER TABLE intern_reports ADD INDEX IF NOT EXISTS idx_supervisor (supervisor_id);
ALTER TABLE intern_reports ADD INDEX IF NOT EXISTS idx_status (status);

-- Report comments already created
