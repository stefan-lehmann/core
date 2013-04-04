CREATE TABLE IF NOT EXISTS `%TBL_OPF_LANG%` (
  `id` int(11) NOT NULL auto_increment,
  `clang` varchar(255) NOT NULL default '',
  `name` text NOT NULL,
  `replacename` varchar(255) NOT NULL default '',
  `status` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;