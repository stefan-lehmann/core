<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT
 *
 * PHP Version: 5.2.6+
 *
 * @package     Addon_channel_list
 * @subpackage  setup
 * @version     SVN: $Id: install.sql 1037 2010-11-17 13:47:55Z s_lehmann $
 *
 * @author      Stefan Lehmann <sl@contejo.com>
 * @copyright   Copyright (c) 2008-2010 CONTEJO. All rights reserved.
 * @link        http://contejo.com
 *
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

CREATE TABLE IF NOT EXISTS `%TBL_CHANNELLIST%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `short_name` varchar(20) NOT NULL,
  `packages` varchar(255) NOT NULL,
  `pay` int(1) NOT NULL DEFAULT '0',
  `hd` int(1) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `prior` int(11) NOT NULL DEFAULT '0',
  `media` varchar(255) NOT NULL,
  `video` varchar(255) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL DEFAULT '0',
  `updatedate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%TBL_CHANNELPACKAGES%` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(50) NOT NULL,
  `selectable` int(1) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `media` varchar(255) NOT NULL,
  `prior` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL DEFAULT '0',
  `updatedate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;