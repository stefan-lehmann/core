## CONTEJO Database Dump Version 2.6 [.0]
## Prefix cjo_


ALTER TABLE `cjo_article` CHANGE `alias` `redirect` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `cjo_article` ADD `locked` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`; 
ALTER TABLE `cjo_article` CHANGE `locked` `admin_only` tinyint( 1 ) NOT NULL 