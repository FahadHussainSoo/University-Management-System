-- Run this ONCE in phpMyAdmin (select your "uni" database, go to SQL tab, paste and Go)
-- It removes the broken placeholder attendance rows created by the old buggy code
-- (rows with no real date/attendance value, shown as 0000-00-00).
-- This is safe to run even if you have none of these rows.

DELETE FROM `attn` WHERE `at_date` = '0000-00-00' OR `atten` = '';
