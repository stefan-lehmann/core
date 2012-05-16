## CONTEJO Database Dump Version 2.6 [.0]
## Prefix cjo_


ALTER TABLE cjo_article CHANGE autor author VARCHAR( 255 ) NOT NULL;

ALTER TABLE cjo_template CHANGE date ctypes VARCHAR( 255 ) NOT NULL DEFAULT '0';
UPDATE cjo_template set ctypes = '|0|' WHERE active = 1;

ALTER TABLE `cjo_article` CHANGE `alias` `redirect` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `cjo_article` ADD `locked` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`; 
ALTER TABLE `cjo_article` CHANGE `locked` `admin_only` tinyint( 1 ) NOT NULL;

ALTER TABLE `cjo_20_mail_settings` CHANGE `footer` `footer` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL 