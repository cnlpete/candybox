DROP TABLE IF EXISTS `%SQL_PREFIX%gallery_albums`;

CREATE TABLE `%SQL_PREFIX%gallery_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `content` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `%SQL_PREFIX%gallery_files`;

CREATE TABLE `%SQL_PREFIX%gallery_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `author_id` int(115) NOT NULL,
  `file` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(4) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'jpg',
  `date` datetime NOT NULL,
  `content` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

