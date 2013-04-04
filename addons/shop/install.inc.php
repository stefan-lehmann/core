<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.7.x
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
 * @copyright   Copyright (c) 2008-2012 CONTEJO. All rights reserved. 
 * @link        http://contejo.com
 *
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *  CONTEJO is free software. This version may have been modified pursuant to the
 *  GNU General Public License, and as distributed it includes or is derivative
 *  of works licensed under the GNU General Public License or other free or open
 *  source software licenses. See _copyright.txt for copyright notices and
 *  details.
 * @filesource
 */

require_once dirname(__FILE__).'/config.inc.php';
require_once dirname(__FILE__).'/classes/class.shop_extension.inc.php';

$addon = 'shop';

$install = new cjoInstall($addon);
if ($install->installResource()) {

    $dir = $CJO['ADDON_CONFIG_PATH'].'/'.$addon.'/theme';
	if (!file_exists($dir)) { mkdir($dir, cjoProp::getFilePerm()); }

    cjoFile::copyDir($CJO['ADDON_PATH'].'/'.$addon.'/themes/default',$dir);
    
    $dir = $CJO['ADDON_CONFIG_PATH'].'/'.$addon.'/img';
    if (!file_exists($dir)) { mkdir($dir, cjoProp::getDirPerm()); }

    cjoFile::copyDir($CJO['ADDON_PATH'].'/'.$addon.'/setup/img',$dir);    

    foreach($CJO['CLANG'] as $clang_id => $name) {
        if ($clang_id == 0) continue;
    	cjoShopExtension::copyConfig(array('id'=>$clang_id));
    }
    if (!cjoMessage::hasErrors()) cjoAddon::setProperty('install', true, $addon);
}