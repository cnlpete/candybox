ALTER TABLE `users` CHANGE `verification_date` `verification_date` DATETIME  NULL  DEFAULT NULL;
ALTER TABLE `users` CHANGE `password_temporary` `password_temporary` VARCHAR(32)  NULL  DEFAULT NULL;
ALTER TABLE `users` DROP `receive_newsletter`;