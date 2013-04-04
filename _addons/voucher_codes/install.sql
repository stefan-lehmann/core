CREATE TABLE IF NOT EXISTS `%TBL_17_VOUCHER%` (
  `code` varchar(32) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `event_id` int(11) NOT NULL,
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM;