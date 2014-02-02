ALTER TABLE `%SQL_PREFIX%users` CHANGE `password` `password` VARCHAR(128)  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NOT NULL  DEFAULT '';
ALTER TABLE `%SQL_PREFIX%users` CHANGE `password_temporary` `password_temporary` CHAR(128)  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NULL  DEFAULT NULL;
ALTER TABLE `%SQL_PREFIX%users` CHANGE `api_token` `api_token` CHAR(32)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';
ALTER TABLE `%SQL_PREFIX%sessions` CHANGE `session` `session` VARCHAR(40)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `%SQL_PREFIX%sessions` CHANGE `ip` `ip` VARCHAR(128)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `%SQL_PREFIX%users` CHANGE `registration_ip` `registration_ip` VARCHAR(128)  CHARACTER SET utf8  COLLATE utf8_unicode_ci  NULL  DEFAULT '';
ALTER TABLE `%SQL_PREFIX%mails` CHANGE `ip` `ip` VARCHAR(128)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT NULL;
ALTER TABLE `%SQL_PREFIX%blogs` CHANGE `language` `language` CHAR(2)  CHARACTER SET utf8  COLLATE utf8_general_ci  NULL  DEFAULT 'en';
