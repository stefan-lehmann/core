CREATE TABLE IF NOT EXISTS `%TBL_20_MAIL_SETTINGS%` (
  `id` int(11) NOT NULL auto_increment,
  `from_name` varchar(255)NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `bcc` varchar(255) NOT NULL,
  `footer` text NOT NULL,
  `mailer` varchar(50) NOT NULL,
  `host` varchar(50) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '3',
  `smtp_auth` tinyint(2) NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '1',
  `createdate` int(11) NOT NULL DEFAULT '0',
  `updatedate` int(11) NOT NULL DEFAULT '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_20_MAIL_ARCHIV%` (
  `id` int(11) NOT NULL auto_increment,
  `sender` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `cc` varchar(255) NOT NULL,
  `bcc` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `error` text NOT NULL,
  `content_type` varchar(50) NOT NULL,
  `article_id` int(11) NOT NULL,
  `clang` int(11) NOT NULL,
  `send_date` int(11) NOT NULL,
  `remote_addr` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `request` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;