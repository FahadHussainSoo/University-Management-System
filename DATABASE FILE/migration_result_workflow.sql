-- ============================================================
-- Migration: Result Workflow (Draft -> Submit -> Approve/Return)
-- Run this AFTER the original uni.sql has been imported.
-- ============================================================

-- 1. Preserve the old flat result table instead of destroying data.
--    Old schema had no marks breakdown and no workflow status, so it
--    is not compatible with the new one. We keep it for reference only.
RENAME TABLE `result` TO `result_legacy`;

-- 2. Subjects now live in their own table (with credit hours for
--    credit-weighted CGPA) instead of being hard-coded in dropdowns
--    across five different PHP files.
CREATE TABLE `subjects` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `credit_hours` DECIMAL(3,1) NOT NULL DEFAULT 3.0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_subject_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `subjects` (`name`, `credit_hours`) VALUES
('DBMS', 3.0),
('DBMS Lab', 1.0),
('Mathematics', 4.0),
('Programming', 3.0),
('Programming Lab', 1.0),
('English', 4.0),
('Physics', 3.0),
('Chemistry', 3.0),
('Psychology', 3.0);

-- 3. New result table: component marks, computed totals/grade, and a
--    workflow status that gates who can edit what.
CREATE TABLE `result` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `st_id` INT(11) NOT NULL,
  `subject_id` INT(11) NOT NULL,
  `semester` VARCHAR(20) NOT NULL,
  `faculty_id` INT(11) NOT NULL,

  `attendance` DECIMAL(4,1) DEFAULT NULL,   -- out of 10
  `assignments` DECIMAL(4,1) DEFAULT NULL,  -- out of 10
  `quizzes` DECIMAL(4,1) DEFAULT NULL,      -- out of 10
  `project` DECIMAL(4,1) DEFAULT NULL,      -- out of 10
  `midterm` DECIMAL(4,1) DEFAULT NULL,      -- out of 20
  `internal_total` DECIMAL(5,1) DEFAULT NULL, -- out of 60, computed

  `final_exam` DECIMAL(4,1) DEFAULT NULL,   -- out of 40

  `total_marks` DECIMAL(5,1) DEFAULT NULL,  -- out of 100, computed
  `percentage` DECIMAL(5,2) DEFAULT NULL,   -- computed
  `letter_grade` VARCHAR(5) DEFAULT NULL,   -- computed
  `grade_point` DECIMAL(3,2) DEFAULT NULL,  -- computed, 0.00-4.00

  `status` ENUM('draft','submitted','returned','approved') NOT NULL DEFAULT 'draft',
  `remarks` VARCHAR(500) DEFAULT NULL,      -- admin's reason when returning

  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_result_entry` (`st_id`,`subject_id`,`semester`),
  KEY `idx_faculty` (`faculty_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_result_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`),
  CONSTRAINT `fk_result_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
