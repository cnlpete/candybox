ALTER TABLE `%SQL_PREFIX%users` ADD `verification_date` DATETIME  NOT NULL  AFTER `verification_code`;
ALTER TABLE `%SQL_PREFIX%users` ADD `registration_ip` INT(11)  NOT NULL  AFTER `role`;
ALTER TABLE `%SQL_PREFIX%users` CHANGE `registration_ip` `registration_ip` VARCHAR(15)  NULL  DEFAULT '';
ALTER TABLE `%SQL_PREFIX%users` ADD `password_temporary` VARCHAR(32)  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NOT NULL  DEFAULT ''  AFTER `password`;