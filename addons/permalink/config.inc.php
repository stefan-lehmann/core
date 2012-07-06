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

$mypage = 'permalink'; // only for this file

$I18N_31 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON

$CJO['ADDON']['addon_id'][$mypage]      = '31';
$CJO['ADDON']['page'][$mypage] 		    = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		    = $I18N_31->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		    = 'permalink[]'; // permission
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann';
$CJO['ADDON']['version'][$mypage] 	    = '1.0';
$CJO['ADDON']['compat'][$mypage] 	    = '2.6.2';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/'.$mypage;

if ($CJO['ADDON']['status'][$mypage] != 1) return;

include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.permalink.inc.php";

if (!OOAddon::isActivated('extend_meta') || 
    !in_array('permalink',$CJO['ADDON']['settings']['extend_meta']['FIELDS']['name'])) {

    if (!$CJO['CONTEJO']) {
        $CJO['ADDON']['status'][$mypage] = 0;
        return;
    }
    
    if (!OOAddon::isActivated('extend_meta')) {
        $url = cjoAssistance::createBEUrl(array('page' => 'addons'));
        cjoMessage::addWarning($I18N_31->msg('msg_err_extend_meta_not_present', $url));
    } else {
        $url = cjoAssistance::createBEUrl(array('page' => 'extend_meta', 'subpage'=>'settings'));
        cjoMessage::addWarning($I18N_31->msg('msg_err_permalink_not_present', $url));
    }
}
 
cjoExtension::registerExtension('GET_ARTICLE_ID','cjoPermalink::getArticleId');
cjoExtension::registerExtension('GENERATE_URL','cjoPermalink::generatedUrl');










