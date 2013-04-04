CREATE TABLE IF NOT EXISTS `%TBL_IMG_CROP%` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `width` varchar(11) NOT NULL,
  `height` varchar(11) NOT NULL,
  `aspectratio` tinyint(2) NOT NULL default '0',
  `status` tinyint(2) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;


INSERT IGNORE INTO `%TBL_IMG_CROP%` (`id`, `name`, `width`, `height`, `aspectratio`, `status`, `createuser`, `updateuser`, `createdate`, `updatedate`) VALUES
(1, 'Resize 1', '800', '600', '1', 1, 'Contejo', 'Contejo', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, 'Resize 2', '400', '400', '1', -1, 'Contejo', 'Contejo', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, 'Resize 3', '108', '108', '1', 1, 'Contejo', 'Contejo', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, 'Resize 4', '180', '', '', 1, 'Contejo', 'Contejo', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, 'Resize 5', '160', '100', '1', 1, 'Contejo', 'Contejo', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());