<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  permalink
 * @version     2.6.2
 *
 * @author      Stefan Lehmann <sl@contejo.com>
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


$mypage = "permalink";

$I18N_31 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON
    
if (!OOAddon::isActivated('extend_meta')) {
    
    $url = cjoAssistance::createBEUrl(array('page' => 'addons'));
    $CJO['ADDON']['installmsg'][$mypage] = $I18N_31->msg('msg_err_extend_meta_not_present', $url);
} 
else if (!in_array('permalink',$CJO['ADDON']['settings']['extend_meta']['FIELDS']['name'])) {
    
    $url = cjoAssistance::createBEUrl(array('page' => 'extend_meta', 'subpage'=>'settings'));
    $CJO['ADDON']['installmsg'][$mypage] = $I18N_31->msg('msg_err_permalink_not_present', $url);
}
else {
    
    $CJO['ADDON']['install'][$mypage] = true;
}