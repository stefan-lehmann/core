CREATE TABLE IF NOT EXISTS `%TBL_21_ATTRIBUTES%` (
  `id` int(11) NOT NULL auto_increment,
  `translate_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `createuser` varchar(30) NOT NULL,
  `updatedate` int(11) NOT NULL default '0',
  `updateuser` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_ATTRIBUTE_TRANSLATE%` (
  `id` int(11) NOT NULL auto_increment,
  `translate_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `clang` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

CREATE TABLE IF NOT EXISTS `%TBL_21_ATTRIBUTE_VALUES%` (
  `id` int(11) NOT NULL auto_increment,
  `offset` varchar(10) NOT NULL,
  `prior` int(2) NOT NULL,
  `translate_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_BASKET%` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL,
  `slice_id` int(11) NOT NULL,
  `session_id` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `attribute` varchar(50) NOT NULL,
  `md5_id` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_COUNTRY_ZONE%` (
  `id` int(11) NOT NULL auto_increment,
  `zone` varchar(20) NOT NULL,
  `countries` varchar(500) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_DELIVERER%` (
  `id` int(11) NOT NULL auto_increment,
  `deliverer` varchar(20) NOT NULL,
  `tax` float NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_DELIVERER_DETAILS%` (
  `id` int(11) NOT NULL auto_increment,
  `deliverer_zone_id` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `size_in_units` float NOT NULL,
  `costs` varchar(11) NOT NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_DELIVERER_ZONE%` (
  `id` int(11) NOT NULL auto_increment,
  `deliverer_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_ORDERS%` (
  `id` int(11) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `title` varchar(10) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `company` varchar(50) NOT NULL,
  `address1` text NOT NULL,
  `address2` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone_nr` varchar(30) NOT NULL,
  `delivery_method` varchar(30) NOT NULL,
  `pay_method` varchar(30) NOT NULL,
  `pay_data` varchar(50) NOT NULL,
  `pay_costs` varchar(10) NOT NULL,
  `total_price` varchar(10) NOT NULL,
  `delivery_cost` varchar(10) NOT NULL,
  `state` varchar(10) NOT NULL,
  `products` text NOT NULL,
  `birth_date` int(11) NOT NULL default '0',
  `session_id` varchar(50) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `createuser` varchar(30) NOT NULL,
  `updatedate` int(11) NOT NULL default '0',
  `updateuser` varchar(20) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_PACKUNITS%` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `sizefactor` int(11) NOT NULL,
  `article_slice_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_PAYMENT%` (
  `id` int(11) NOT NULL auto_increment,
  `payment_name` varchar(20) NOT NULL,
  `requirements` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_21_DELIVERY_COSTS%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_value` decimal(10,0) NOT NULL,
  `costs` float NOT NULL,
  `zone_id` int(11) NOT NULL,
  `tax` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;

