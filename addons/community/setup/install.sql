CREATE TABLE IF NOT EXISTS `%TBL_COMMUNITY_ARCHIV%` (
  `id` int(11) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `article_id` int(11) NOT NULL default '0',
  `group_ids` varchar(255) NOT NULL,
  `prepared` int(11) NOT NULL default '0',
  `send` int(11) NOT NULL default '0',
  `error` int(11) NOT NULL default '0',
  `firstsenddate` int(11) NOT NULL default '0',
  `lastsenddate` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `user` varchar(255) NOT NULL,
  `clang` int(11) NOT NULL default '0',
  `reply_to` varchar(255) NOT NULL,
  `atonce` int(11) NOT NULL,
  `mail_account` int(5) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_COMMUNITY_GROUPS%` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `re_id` int(11) NOT NULL default '0',
  `type_ids` varchar(255) NOT NULL default '1',
  `createuser` varchar(255) NOT NULL default '',
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_COMMUNITY_PREPARED%` (
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL default '0',
  `clang` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `error` varchar(255)  NOT NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `%TBL_COMMUNITY_UG%` (
  `user_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0'
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_COMMUNITY_USER%` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `gender` varchar(30) NOT NULL,
  `birthdate` date NOT NULL,
  `street` varchar(255) NOT NULL default '',
  `plz` varchar(255) NOT NULL default '',
  `town` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `mobile` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `email2` varchar(255) NOT NULL default '',
  `company_name` varchar(255) NOT NULL default '',
  `company_department` varchar(255) NOT NULL default '',
  `company_operating_field` varchar(255) NOT NULL default '',
  `company_street` varchar(255) NOT NULL default '',
  `company_plz` varchar(255) NOT NULL default '',
  `company_town` varchar(255) NOT NULL default '',
  `company_phone` varchar(255) NOT NULL default '',
  `company_fax` varchar(255) NOT NULL default '',
  `value1` text NOT NULL,
  `value2` text NOT NULL,
  `value3` text NOT NULL,
  `value4` text NOT NULL,
  `value5` text NOT NULL,
  `value6` text NOT NULL,
  `value7` text NOT NULL,
  `value8` text NOT NULL,
  `value9` text NOT NULL,
  `value10` text NOT NULL,
  `newsletter` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `clang` tinyint(4) NOT NULL default '0',
  `lasttrydate` int(11) NOT NULL default '0',
  `login_tries` tinyint(4) NOT NULL default '0',
  `activation` tinyint(1) NOT NULL default '0',
  `activation_key` varchar(32) NOT NULL default '',
  `bounce` tinyint(1) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL default '',
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
