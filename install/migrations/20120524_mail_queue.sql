CREATE TABLE `%SQL_PREFIX%mails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `from_address` varchar(32) DEFAULT NULL,
  `from_name` varchar(32) DEFAULT NULL,
  `to_address` varchar(32) DEFAULT NULL,
  `to_name` varchar(32) DEFAULT NULL,
  `subject` varchar(128) NOT NULL,
  `content` text NOT NULL,
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;