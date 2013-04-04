CREATE TABLE IF NOT EXISTS `%TBL_30_EXTEND_META%` (
  `article_id` int(11) NOT NULL,
  `clang` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `createuser` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `createdate` int(11) NOT NULL
) ENGINE = MYISAM;