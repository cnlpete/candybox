ALTER TABLE `%SQL_PREFIX%logs` CHANGE `section_name` `controller_name` VARCHAR(32)  NOT NULL  DEFAULT 'NOT NULL';