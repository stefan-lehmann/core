<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  extend_meta
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

$mypage = 'extend_meta'; // only for this file

$I18N_30 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // CREATE LANG OBJ FOR THIS ADDON

$CJO['ADDON']['addon_id'][$mypage]      = '30';
$CJO['ADDON']['page'][$mypage] 		    = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage] 		    = $I18N_30->msg($mypage);  // name
$CJO['ADDON']['perm'][$mypage] 		    = 'extend_meta[]'; // permission
$CJO['ADDON']['author'][$mypage] 	    = 'Stefan Lehmann';
$CJO['ADDON']['version'][$mypage] 	    = '1.0';
$CJO['ADDON']['compat'][$mypage] 	    = '2.6.2';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/'.$mypage;
$CJO['ADDON']['settings'][$mypage]['SETTINGS'] = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/settings.inc.php"; // settings file

if (!defined('TBL_30_EXTEND_META')) {
    define('TBL_30_EXTEND_META', $CJO['TABLE_PREFIX'].'30_extend_meta');
}

if ($CJO['ADDON']['status'][$mypage] != 1) return;

include_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
include_once $CJO['ADDON_PATH']."/".$mypage."/classes/class.extend_meta.inc.php";

$CJO['ADDON']['settings'][$mypage]['FIELDTYPES'] = array('cjoLinkButtonField',
                                                         'cjoMediaButtonField',
                                                         'cjoMediaCategoryButtonField',
                                                         'cjoMediaListField',
                                                         'cjoWYMeditorField',
                                                         'checkboxField',
                                                         'colorpickerField',
                                                         'datepickerField',
                                                         'passwordField',
                                                         'selectField',
                                                         'textAreaField',
                                                         'textField',
                                                         'headlineField',
                                                         'slideheadlineField');

$CJO['ADDON']['settings'][$mypage]['VALIDATORTYPES'] = array('isCCExpDate',
                                                             'isCCNum',
                                                             'isColor',
                                                             'isDate',
                                                             'isDateAfter',
                                                             'isDateBefore',
                                                             'isDateEqual',
                                                             'isDateOnOrAfter',
                                                             'isDateOnOrBefore',
                                                             'isEmail',
                                                             'isFileSize',
                                                             'isFileType',
                                                             'isFloat',
                                                             'isInt',
                                                             'isLength',
                                                             'isNot',
                                                             'isNumber',
                                                             'isPrice',
                                                             'isRegExp',
                                                             'isURL',
                                                             'notEmptyOrNull');

$CJO['ADDON']['settings'][$mypage]['FIELDS'] = cjoAssistance::toArray(json_decode(stripslashes($CJO['ADDON']['settings'][$mypage]['FIELDS'])));
 
foreach($CJO['ADDON']['settings'][$mypage]['FIELDS'] as $field=>$values) {
    foreach($values as $key=>$value) {
        $CJO['ADDON']['settings'][$mypage]['FIELDS'][$field][$key] = htmlspecialchars_decode($CJO['ADDON']['settings'][$mypage]['FIELDS'][$field][$key]);
    }
}
 
 
cjoExtension::registerExtension('META_FORM_INIT','cjoExtendMeta::addFormFields'); 
cjoExtension::registerExtension('META_FORM_VALID','cjoExtendMeta::saveFormFields'); 
cjoExtension::registerExtension('GENERATE_ARTICLE_META','cjoExtendMeta::generateMata'); 
cjoExtension::registerExtension('CONTEJO_CLASS_VARS_GENERATED','cjoExtendMeta::generateContejoClassVars');

if (!$CJO['CONTEJO']) return false;

cjoExtension::registerExtension('ARTICLE_DELETED','cjoExtendMeta::deleteArticle'); 
cjoExtension::registerExtension('ARTICLE_COPIED','cjoExtendMeta::copyArticle'); 
cjoExtension::registerExtension('CLANG_ADDED','cjoExtendMeta::addClang'); 
cjoExtension::registerExtension('CLANG_DELETED','cjoExtendMeta::deleteClang'); 







