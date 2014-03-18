DROP TABLE IF EXISTS `%SQL_PREFIX%logs`;

CREATE TABLE `%SQL_PREFIX%logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller_name` varchar(32) NOT NULL DEFAULT 'NOT NULL',
  `action_name` varchar(16) NOT NULL,
  `action_id` int(11) DEFAULT NULL,
  `time_start` datetime NOT NULL,
  `time_end` datetime NOT NULL,
  `user_id` int(11) DEFAULT '0',
  `result` tinyint(1)  NOT NULL  DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `%SQL_PREFIX%mails`;

CREATE TABLE `%SQL_PREFIX%mails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(128) DEFAULT NULL,
  `from_address` varchar(32) DEFAULT NULL,
  `from_name` varchar(32) DEFAULT NULL,
  `to_address` varchar(32) DEFAULT NULL,
  `to_name` varchar(32) DEFAULT NULL,
  `subject` varchar(128) NOT NULL,
  `message` text NOT NULL,
  `attachment` text,
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `%SQL_PREFIX%migrations`;

CREATE TABLE `%SQL_PREFIX%migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(100) DEFAULT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

