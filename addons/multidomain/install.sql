CREATE TABLE IF NOT EXISTS `%TBL_MULTIDOMAIN%` (
  `id` int(11) NOT NULL auto_increment,
  `servername` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `error_email` varchar(255) NOT NULL,
  `root_article_id` int(11) NOT NULL,
  `start_article_id` int(11) NOT NULL,
  `notfound_article_id` int(11) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`domain`)
) ENGINE=MyISAM;