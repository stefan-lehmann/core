<?php
/**
 * This file is part of CONTEJO ADDON - CHANNEL LIST
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_channel_list
 * @subpackage 	pages
 * @version   	SVN: $Id: index.inc.php 1084 2010-11-24 12:37:42Z s_lehmann $
 *
 * @author 		Stefan Lehmann <sl@contejo.com>
 * @copyright	Copyright (c) 2008-2011 CONTEJO. All rights reserved.
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

$mypage    = 'channellist';
$oid       = cjo_request('oid', 'int', '');
$function  = cjo_request('function', 'string');

cjoSubPages::addPages( array(
                        array('tv', 
                              'title' => cjoAddon::translate(23,'tv_channels')),
                        array('radio', 
                              'title' => cjoAddon::translate(23,'radio_channels')),
                        array('packages', 
                              'title' => cjoAddon::translate(23,'packages')),
                        array('settings', 
                              'title' => cjoAddon::translate(23,'settings'))
                      ));

require_once cjoSubPages::getPagePath();