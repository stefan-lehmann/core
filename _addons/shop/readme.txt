Datenbank Updates to do's

1 einfügen tabelle TBL_21_DELIVERY_COSTS
2 löschen Spalte 'tax' in TBL_21_DELIVERER_DETAILS
3 einfügen Spalte 'tax' in TBL_21_DELIVERER

ALTER TABLE `cjo_21_deliverer` ADD `tax` FLOAT NOT NULL;
ALTER TABLE `cjo_21_deliverer_details` DROP `tax`;
CREATE TABLE IF NOT EXISTS `cjo_21_delivery_costs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_value` decimal(10,0) NOT NULL,
  `costs` float NOT NULL,
  `zone_id` int(11) NOT NULL,
  `tax` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM ;