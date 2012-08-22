CREATE TABLE IF NOT EXISTS `%TBL_COMMENTS%` (
  `id` int(10) NOT NULL auto_increment,
  `article_id` int(10) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  `author` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(200) default NULL,
  `email` varchar(50) default NULL,
  `city` varchar(50) default NULL,
  `country` varchar(50) default NULL,
  `created` int(10) NOT NULL default '0',
  `reply` text NOT NULL,
  `clang` int(11) NOT NULL default '0',
  `md5_message` varchar(50) default NULL,
  `md5_ip` varchar(50) default NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_COMMENTS_B8%` (
  `token` VARCHAR(255) BINARY PRIMARY KEY,
  `count` VARCHAR(255)
) ENGINE=MyISAM;

INSERT IGNORE INTO `%TBL_COMMENTS_B8%` (`token`, `count`) VALUES
('bayes*dbversion', '2'),
('bayes*texts.ham', '2'),
('bayes*texts.spam', '2'),
('contejo', '2 0 090602'),
('love', '2 0 090602'),
('sex', '0 2 090602'),
('drugs', '0 2 090602');

CREATE TABLE IF NOT EXISTS `%TBL_COMMENTS_CONFIG%` (
  `id` int(11) NOT NULL auto_increment,
  `form_article_id` int(11) NOT NULL default '0',
  `reference_article_id` int(11) NOT NULL default '-1',
  `filter_comments_by` varchar(255) NOT NULL,
  `clang` int(11) NOT NULL default '0',
  `comment_function` int(11) NOT NULL default '1',
  `new_online_global` int(11) NOT NULL default '0',
  `list_typ` varchar(50) NOT NULL default 'visible',
  `short_comments` int(10) NOT NULL default '0',
  `short_comments_length` int(11) NOT NULL default '60',
  `allow_html_tags` int(11) NOT NULL default '0',
  `order_comments` varchar(10) default 'ASC',
  `oversize_length` int(11) NOT NULL default '0',
  `oversize_replace` varchar(255) NOT NULL default ' ',
  `no_entries_text` varchar(255) NOT NULL,
  `date_format` varchar(50) NOT NULL,
  `b8_autolearn` int(11) NOT NULL default '1',
  `b8_spam_border` varchar(11) default '0.8',
  `blacklist_0` text,
  `blacklist_1` text,
  `debuging` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

INSERT IGNORE INTO `%TBL_COMMENTS_CONFIG%` (`id`, `form_article_id`, `reference_article_id`, `filter_comments_by`, `clang`, `comment_function`, `new_online_global`, `list_typ`, `short_comments`, `short_comments_length`, `allow_html_tags`, `order_comments`, `oversize_length`, `oversize_replace`, `no_entries_text`, `date_format`, `b8_autolearn`, `b8_spam_border`, `blacklist_0`, `blacklist_1`, `debuging`) VALUES (1, -1, -1, '', 0, 1, 1, 'visible', 1, 160, 0, 'ASC', 30, '-', '', '%d. %b. %Y - %H:%M', '1', '0,5', 'sex\r\nsays my memory\r\ndrugs\r\nincest\r\nviagra\r\nmoney\r\ndiscount\r\nbuy\r\nteen\r\nfuck\r\npuss\r\ncash\r\narsch\r\nhref\r\nselect\r\ninsert\r\nupdate', '', 1);
