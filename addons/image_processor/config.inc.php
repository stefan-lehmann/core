<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  image_processor
 * @version     2.6.0
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

$mypage = 'image_processor';

$I18N_8 = new i18n($CJO['LANG'], $CJO['ADDON_PATH'].'/'.$mypage.'/lang');  // create lang obj for this addon

$CJO['ADDON']['addon_id'][$mypage] 	= '8';
$CJO['ADDON']['page'][$mypage] 		= $mypage;
$CJO['ADDON']['name'][$mypage] 		= $I18N_8->msg($mypage);   // name
$CJO['ADDON']['perm'][$mypage] 		= 'image_processor[]';
$CJO['ADDON']['author'][$mypage] 	= 'Stefan Lehmann ( based on imageResize Addon 0.3 code by Dennis Wenger - redaxo@bitpixel.de)';
$CJO['ADDON']['version'][$mypage] 	= '1.2';
$CJO['ADDON']['compat'][$mypage] 	= '2.2';
$CJO['ADDON']['support'][$mypage] 	= 'http://contejo.com/addons/image_processor';


$CJO['ADDON']['settings'][$mypage]['SETTINGS'] = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage.'/settings.inc.php';

if (!$CJO['SETUP'])
    include_once($CJO['ADDON']['settings'][$mypage]['SETTINGS']);

if (!defined('TBL_IMG_CROP')) {
    define('TBL_IMG_CROP', $CJO['TABLE_PREFIX'].'img_crop');
}

if ($CJO['ADDON']['status'][$mypage] != 1) return;

// ERWEITERTE EINSTELLUNGEN ///////////////////////////////////////////////////////////////////////////////////////////

/**
 * imagecreatefromjpeg() : gd-jpeg, libjpeg:
 * recoverable error: Premature end of JPEG
 *
 * To remove the error, insert: ini_set('gd.jpeg_ignore_warning', 1);
 * Doing that will cause gd2, php to ignore the error and
 * continue where it use to just fail and do nothing.
 *
 * @see http://worcesterwideweb.com/2008/03/17/php-5-and-imagecreatefromjpeg-recoverable-error-premature-end-of-jpeg-file/
 *
 */

ini_set('gd.jpeg_ignore_warning', 1);

// Include Funtions and Classes
include_once $CJO['ADDON_PATH'].'/'.$mypage.'/functions/functions_ip_image.inc.php';
include_once $CJO['ADDON_PATH'].'/'.$mypage.'/functions/functions_ip_unsharp.inc.php';
include_once $CJO['ADDON_PATH'].'/'.$mypage.'/functions/functions_ip_shadow.inc.php';

$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');

// CacheDir erstellen, falls nicht vorhanden
if ($CJO['ADDON']['settings'][$mypage]['cachedir'] != '' &&
	!file_exists($ip_cachedir)){

	if (!$CJO['SETUP'] && !mkdir($ip_cachedir, 0777) && $CJO['CONTEJO']){
	  	cjoMessage::addError($I18N_8->msg("msg_no_cache_folder", $CJO['ADDON']['name'][$mypage],$ip_cachedir));
	}
}

cjoExtension::registerExtension('MEDIA_ADDED', 'imageProcessor_auto_resOrig');
cjoExtension::registerExtension('MEDIA_SYNC', 'imageProcessor_auto_resOrig');
cjoExtension::registerExtension('MEDIA_UPDATED', 'imageProcessor_auto_resOrig');

function imageProcessor_auto_resOrig($params){

    global $CJO;
    $mypage = 'image_processor';

    $filename = $params['filename'];
    $path = ($params['path'] == '') ? $CJO['MEDIAFOLDER'] : $params['path'];
    $filepath = $path.'/'.$filename;

	$res_orig_size = $CJO['ADDON']['settings'][$mypage]['res_orig']['size'] * (2*1024);
	$res_orig_quality = $CJO['ADDON']['settings'][$mypage]['res_orig']['jpg-quality'];

    //Resize Original
    if ($CJO['ADDON']['settings'][$mypage]['res_orig']['on_off'] &&
        $res_orig_size > 100 && $res_orig_quality > 50){
        if (imageProcessor_resOrig($filepath, $res_orig_size, $res_orig_quality) == 'RESIZED'){
            clearstatcache();
        }
		if (file_exists($CJO['MEDIAFOLDER']."/".$filename)){
	        imageProcessor_updateDB($filename);
		}
    }
}