<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  event_calendar
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

global $CJO;
$mypage = "event_calendar";

$I18N_16 = new i18n($CJO['LANG'],$CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // create lang obj for this addon

$CJO['ADDON']['addon_id'][$mypage] 	    = '16';
$CJO['ADDON']['page'][$mypage] 		    = $mypage;
$CJO['ADDON']['name'][$mypage] 		    = $I18N_16->msg($mypage);
$CJO['ADDON']['perm'][$mypage] 		    = 'event_calendar[]';
$CJO['ADDON']['autor'][$mypage] 		= 'Stefan Lehmann 2010';
$CJO['ADDON']['version'][$mypage] 	    = '0.3';
$CJO['ADDON']['supportpage'][$mypage] 	= 'http://contejo.com/';
$CJO['ADDON']['compat'][$mypage] 	    = '2.2';
$CJO['ADDON']['support'][$mypage] 	    = 'http://contejo.com/addons/event_calendar';

$CJO['ADDON']['settings'][$mypage]['CLANG_CONF'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/'.$clang.'.clang.inc.php';

if (!defined('TBL_16_EVENTS')) {
    define('TBL_16_EVENTS', $CJO['TABLE_PREFIX'].'16_events');
}

if ($CJO['ADDON']['status'][$mypage] != 1) return;


$CJO['ADDON']['settings'][$mypage]['enabled_types'] = array (
                   array ($I18N_16->msg('label_times'), 'times'),
                   array ($I18N_16->msg('label_end_date'), 'end_date'),
                   array ($I18N_16->msg('label_event_article'), 'article'),
                   array ($I18N_16->msg('label_event_file'), 'file'),
                   array ($I18N_16->msg('label_short_description'), 'short_description'),
                   array ($I18N_16->msg('label_description'), 'description'),
                   array ($I18N_16->msg('label_keywords'), 'keywords'),
                   array ($I18N_16->msg('label_online_from_to'), 'online_from_to')
                   );

$CJO['ADDON']['settings'][$mypage]['list_types'] = array (
                   array ($I18N_16->msg('label_attribute_disabled'), ''),
                   array ($I18N_16->msg('label_attribute_text'), 'text'),
                   array ($I18N_16->msg('label_attribute_textarea'), 'textarea'),
                   array ($I18N_16->msg('label_attribute_wymeditor'), 'wymeditor'),
                   array ($I18N_16->msg('label_attribute_datepicker'), 'datepicker'),
                   array ($I18N_16->msg('label_attribute_time'), 'time'),
                   array ($I18N_16->msg('label_attribute_media'), 'media'),
                   array ($I18N_16->msg('label_attribute_article'), 'article'),
                   array ($I18N_16->msg('label_attribute_select'), 'select')
                   );

$CJO['ADDON']['settings'][$mypage]['date_input_formats'] = array (
                   array ($I18N_16->msg('label_example').' 20.01.2013', '%d.%m.%Y'),
                   array ($I18N_16->msg('label_example').' 01-20-2013', '%m-%d-%Y')
                   );

$CJO['ADDON']['settings'][$mypage]['date_output_formats'] = array (
                   array ($I18N_16->msg('label_example').' Mittwoch 20. Januar 2010', '%A %d. %B %Y'),
                   array ($I18N_16->msg('label_example').' Mittwoch 20.01.2010', '%A %d.%m.%Y'),
                   array ($I18N_16->msg('label_example').' Mi 20. Januar 2010', '%a %d. %B %Y'),
                   array ($I18N_16->msg('label_example').' Mi 20. Jan. 2010', '%a %d. %b. %Y'),
                   array ($I18N_16->msg('label_example').' Mi 20. Jan.', '%a %d. %b.'),
                   array ($I18N_16->msg('label_example').' Mi 20.01.2010', '%a %d.%m.%Y'),
                   array ($I18N_16->msg('label_example').' Mi 20.01.10', '%a %d.%m.%y'),
                   array ($I18N_16->msg('label_example').' Mi 20.01.', '%a %d.%m.'),
                   array ($I18N_16->msg('label_example').' 20. Januar 2010', '%d. %B. %Y'),
                   array ($I18N_16->msg('label_example').' 20. Jan. 2010', '%d. %b. %Y'),
                   array ($I18N_16->msg('label_example').' 20.01.2010', '%d.%m.%Y'),
                   array ($I18N_16->msg('label_example').' 20.01.10', '%d.%m.%y'),
                   array ($I18N_16->msg('label_example').' January, 20 2010', '%B, %d %Y'),
                   array ($I18N_16->msg('label_example').' Jan, 20 2010', '%b, %d %Y'),
                   array ($I18N_16->msg('label_example').' Wensday 01-20-2010', '%A %m-%d-%Y'),
                   array ($I18N_16->msg('label_example').' We 01-20-2010', '%a %m-%d-%Y'),
                   array ($I18N_16->msg('label_example').' 01-20-10', '%m-%d-%y'),
                   array ($I18N_16->msg('label_example').' 01-20-2010', '%m-%d-%Y'),
                   array ($I18N_16->msg('label_example').' 01-20-10', '%m-%d-%y'),
                   array ($I18N_16->msg('label_example').' 01/20/2010', '%m-%d-%Y'),
                   array ($I18N_16->msg('label_example').' 01/20/10', '%m-%d-%y')
                   );               

$CJO['ADDON']['settings'][$mypage]['available_search_fields'] = array (
                   array ($I18N_16->msg('label_title'), 'title'),
                   array ($I18N_16->msg('label_short_description'), 'short_description')
                   );

if (cjoAssistance::inMultival('description', $enabled_fields))
    $CJO['ADDON']['settings'][$mypage]['available_search_fields'][] = array ($I18N_16->msg('label_description'), 'description');

if (cjoAssistance::inMultival('keywords', $enabled_fields))
    $CJO['ADDON']['settings'][$mypage]['available_search_fields'][] = array ($I18N_16->msg('label_keywords'), 'keywords');

                   
    
// Include Funtions and Classes
include_once $CJO['ADDON']['settings'][$mypage]['CLANG_CONF'];
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.event_calendar.inc.php';
require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.event_imexport.inc.php';

if(!$CJO['CONTEJO']){
	cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoEventCalendar::replaceVars');
}
else {
    cjoExtension::registerExtension('CLANG_ADDED', 'cjoEventCalendar::copyConfig');
}
