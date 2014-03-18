DROP TABLE IF EXISTS `%SQL_PREFIX%sessions`;

CREATE TABLE `%SQL_PREFIX%sessions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session` varchar(40) DEFAULT NULL,
  `ip` varchar(128) DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

