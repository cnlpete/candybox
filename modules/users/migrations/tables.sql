DROP TABLE IF EXISTS `%SQL_PREFIX%users`;

CREATE TABLE `%SQL_PREFIX%users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `surname` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password_temporary` char(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `content` varchar(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `use_gravatar` tinyint(1) DEFAULT '0',
  `role` tinyint(1) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  `registration_ip` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `verification_code` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `verification_date` datetime DEFAULT NULL,
  `api_token` char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `api_token` (`api_token`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

