<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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
require_once dirname(__FILE__).'/classes/class.cm_extension.inc.php';

$mypage = 'community';

$install = new cjoInstall($mypage);

if ($install->installResource()) {

    foreach($CJO['CLANG'] as $clang_id => $name) {
        if ($clang_id == 0) continue;
    	cjoCommunityExtension::copyConfig(array('id'=>$clang_id));
    }
    if (!cjoMessage::hasErrors()) $CJO['ADDON']['install'][$mypage] = 1;
}

//ALTER TABLE `cjo_10_community_archiv` ADD `mail_account` INT( 5 ) NOT NULL 