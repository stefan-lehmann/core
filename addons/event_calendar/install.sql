CREATE TABLE IF NOT EXISTS `%TBL_16_EVENTS%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clang` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `short_description` text NOT NULL,
  `description` text NOT NULL,
  `keywords` text NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `online_from` int(11) NOT NULL,
  `online_to` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `attribute1` text NOT NULL,
  `attribute2` text NOT NULL,
  `attribute3` text NOT NULL,
  `attribute4` text NOT NULL,
  `attribute5` text NOT NULL,
  `attribute6` text NOT NULL,
  `attribute7` text NOT NULL,
  `attribute8` text NOT NULL,
  `attribute9` text NOT NULL,
  `attribute10` text NOT NULL,
  `createdate` int(11) NOT NULL DEFAULT '0',
  `updatedate` int(11) NOT NULL DEFAULT '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM