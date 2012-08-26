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

function imageProcessor_getImg ($filename,
                                $x = null,
                                $y = null,
                                $resize = null,
                                $aspectratio = null,
                                $brand_on_off = null,
                                $brandimg = null,
                                $jpg_quality = null,
                                $crop_x = null,
                                $crop_y = null,
                                $crop_w = null,
                                $crop_h = null,
                                $shadow = null,
                                $fullpath = '') {

	global $CJO, $I18N_8;

	$mypage = "image_processor";
	$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');
	$use_cached = false;

	//settings laden, wenn noch nicht geladen
	if (!$CJO['ADDON']['settings'][$mypage]['cachedir'])
	require ($CJO['ADDON']['settings'][$mypage]['SETTINGS']);

	if ($fullpath == '') {
		$fullpath = $CJO['MEDIAFOLDER'].'/'.$filename;
	}
	if (!file_exists($fullpath)) {
		return $CJO['MEDIAFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['error_img'];
	}
	
	$size = @getimagesize($fullpath);

	if (!$shadow  && (
	    ($x < 1 && $y < 1) || 
	    ($x >= $size[0] && $y >= $size[1]) || 
	    ($x >= $size[0] && $y < 1) ||
	    ($x < 1 && $y >= $size[1]))) return $fullpath;

	//optionale Parameter entgegennehmen
	$numargs = func_num_args();
	if ($numargs > 3) {
		$arg_list = func_get_args();

		$args = array ();
		$arg_list = array_slice($arg_list, 3); //Die 3 parameter entfernen
		sort($arg_list); //sortieren, dass der namenaufbau immer gleich ist

		for ($i = 0; $i < $numargs; $i++) {
		    
		    if (isset($arg_list[$i])){
		        
			    $parts = explode("=", $arg_list[$i], 2);
		   
    			$parts[0] = !empty($parts[0]) ? trim($parts[0]) : '';
    			$parts[1] = !empty($parts[1]) ? trim($parts[1]) : '';
    			//für die boolschen Parameter kann 1 oder 0 (oder für 0 nichts) angegeben werden
    			switch ($parts[0]) {
    				case "resize" :
    					$args["r"] = (int) ((bool) $parts[1]);
    					break;
    				case "aspectratio" :
    					$args["a"] = (int) ((bool) $parts[1]);
    					break;
    				case "jpg-quality" :
    					if ($CJO['ADDON']['settings'][$mypage]['allowoverride']['jpg-quality']) //nur wenn allowoverride
    					$args["jq"] = (int) $parts[1];
    					break;
    				case "brand_on_off" :
    					if ($CJO['ADDON']['settings'][$mypage]['brand']['allowoverride']['brand_on_off']) //nur wenn allowoverride
    					$args["b"] = (int) ((bool) $parts[1]);
    					break;
    				case "brandimg" :
    					if (($args["b"] || $CJO['ADDON']['settings'][$mypage]['brand']['default']['brand_on_off']) &&
    					$CJO['ADDON']['settings'][$mypage]['brand']['allowoverride']['brandimg']) //nur wenn allowoverride
    					$args["bimg"] = trim($parts[1]);
    					break;
    			}
		    }
		}
	}

	if (!class_exists("resizecache"))
	    require_once($CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.resizecache.inc.php');

	if (resizecache :: is_conflict_memory_limit($fullpath) &&
		$filename != $CJO['ADDON']['settings']['image_processor']['brand']['default']['brandimg']) {

		$file_ext = substr(strrchr($fullpath, "."), 1);
		$file_ext = (OOMedia :: isDocType($file_ext)) ? $file_ext : 'default';
		return $CJO['BACKEND_PATH'].'/img/mime_icons/'.$file_ext.'.png';
	}

	$res_orig_size = $CJO['ADDON']['settings'][$mypage]['res_orig']['size'];
	$res_orig_quality = $CJO['ADDON']['settings'][$mypage]['res_orig']['jpg-quality'];

	//Resize Original
	if ($CJO['ADDON']['settings'][$mypage]['res_orig']['on_off'] &&
	    $res_orig_size > 250 && $res_orig_quality > 50) {
		if (imageProcessor_resOrig($fullpath, $res_orig_size, $res_orig_quality) == 'RESIZED')
		imageProcessor_updateDB($filename);
	}

	//cachenamen generieren
	$cacheFiletype = strrchr($filename, ".");
	$cacheImage = str_replace(substr($filename, strrpos($filename, '.')), '', $filename);
	$cacheImage = substr(md5($cacheImage), 0, 16);

	if (is_array($args)) {
		$params = "";
		foreach ($args as $key => $value) {
			$params .= $key.$value;
		}
		$cacheImage = $cacheImage.','.$params;
	}

	if ($y) $args['y'] = $y;
	if ($x) $args['x'] = $x;
	if ($crop_x) $args['crop_x'] = $crop_x;
	if ($crop_y) $args['crop_y'] = $crop_y;
	if ($crop_w) $args['crop_w'] = $crop_w;
	if ($crop_h) $args['crop_h'] = $crop_h;
	if ($shadow) {
		$args['shadow'] = $shadow;
		$s = ',s';
	} else {
	    $s = '';
	}

	$cacheImage = $cacheImage.','.$crop_w.','.$crop_h.','.$crop_x.','.$crop_y.','.$x.','.$y.$s.$cacheFiletype;

	//Prüfen, ob Bild gecached werden muss
	$cacheImgpath = $ip_cachedir.$cacheImage;
	$error = false;

	$mime = @array_pop(getimagesize($fullpath));

	//Für schnelles laden von errorimgs
	if (!file_exists($fullpath) || (
    	$mime != 'image/png' &&
    	$mime != 'image/gif' &&
    	$mime == 'image/jpg' &&
    	$mime == 'image/jpeg' &&
    	$mime == 'image/pjpeg')) {

		$fullpath = $CJO['MEDIAFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['error_img'];

		$errorImage = "";
		if ($y) $errorImage = "y".$y.$errorImage;
		if ($x) $errorImage = "x".$x.$errorImage;
		$errorImage = $errorImage."_error.jpg";
		$cacheImgpath = $ip_cachedir.$errorImage;
	}

	if (file_exists($cacheImgpath) &&
    	filemtime($fullpath) < filemtime($cacheImgpath)) {
		//@ touch($cacheImgpath);
		return $cacheImgpath;
	}

	@set_time_limit(5000);
	ini_set("memory_limit", "256M");

	cjoTime::avoidTimeout($I18N_8->msg('msg_wait_while_generating_images'));

	$usebrand = $CJO['ADDON']['settings'][$mypage]['brand']['default']['brand_on_off'];
	if (isset ($args['b']) && $CJO['ADDON']['settings'][$mypage]['brand']['allowoverride']['brand_on_off'])
	    $usebrand = $args['b'];

	if (!$usebrand) {
		if (!class_exists("resizecache"))
		require_once ($CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.resizecache.inc.php');
		$resizeObj = new resizecache();
	}
	else {
		if (!class_exists("resizecache"))
		require_once ($CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.resizecache.inc.php');
		if (!class_exists("resizecache_brandimage"))
		require_once ($CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.resizecache_brandimage.inc.php');
		$resizeObj = new resizecache_brandimage();
	}

	$returnPath = $resizeObj->generate($fullpath, $cacheImgpath, $args);
	//Load and run garbagecollector
	if (!class_exists("garbagecollector"))
	require_once ($CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.garbagecollector.inc.php');

	$cachesize = $CJO['ADDON']['settings'][$mypage]['cachesize'] * (2*1024*1024);
	$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');
	$garbagecollector = new garbagecollector($ip_cachedir, $cachesize);
	$garbagecollector->tidy();

	@set_time_limit(60);
	ini_set("memory_limit", "32M");

	return $returnPath;
}

function imageProcessor_initCropValues($size) {

	global $CJO;

	$qry = "SELECT * FROM ".TBL_IMG_CROP." ORDER BY id";
	$sql = new cjoSql();
	$sql->setQuery($qry);

	for ($i = 1; $i <= $sql->getRows(); $i++) {

		$crop[$i] = '';

		if ($sql->getValue('status') != 0){

			$set[$i]['id'] = $sql->getValue('id');
			$set[$i]['name'] = $sql->getValue('name');
			$set[$i]['width'] = $sql->getValue('width');
			$set[$i]['height'] = $sql->getValue('height');

			$rect = imageProcessor_calculateCrop($size, $set[$i], $sql->getValue('aspectratio'));

			$crop[$i] = $set[$i]['id'].'_'.$set[$i]['name'].'|'.$rect[1].'|'.$rect[2].'|'.$rect[3].'|'.$rect[4];
		}
		$sql->next();
	}
	return $crop;
}

function imageProcessor_calculateCrop($c_size, $t_size, $aspectratio = 1) {

	$rect       = array();
    $rect['x1'] = 0;
	$rect['y1'] = 0;
	$rect['x2'] = $c_size[0];
	$rect['y2'] = $c_size[1];

	if ($t_size['width'] <= $c_size[0] &&
    	$t_size['height'] <= $c_size[1] &&
    	!empty($t_size['width']) &&
    	!empty($t_size['height'])) {
	    
		if ($aspectratio) {
		    
		    $c_format = $c_size[0] / $c_size[1];
		    $t_format = $t_size['width'] / $t_size['height'];  
		    
			if ($t_format < $c_format) {
				$width = ($c_size[1] * $t_size['width']) / $t_size['height'];
				$rect['x1'] = round(($c_size[0] - $width) / 2);
				$rect['x2'] = round($width);
				$rect['y2'] = $c_size[1];
			}
			elseif ($t_format > $c_format) {
				$height = ($c_size[0] * $t_size['height']) / $t_size['width'];
				$rect['y1'] = round(($c_size[1] - $height) / 2);
				$rect['x2'] = $c_size[0];
				$rect['y2'] = round($height);
			}
		}
	}
	return array(1=>$rect['x1'], 2=>$rect['y1'], 3=>$rect['x2'], 4=>$rect['y2']);
}


function imageProcessor_unlinkCached($filename) {

	global $CJO;
	$mypage = "image_processor";

	//cachenamen generieren
	$cached_name = str_replace(substr($filename, strrpos($filename, '.')), '', $filename);
	$cached_name = substr(md5($cached_name), 0, 16);

	// ---- Dateien aus dem Cache lesen
	$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');
	foreach (cjoAssistance::toArray(glob($ip_cachedir.$cached_name."*")) as $file) {
		@ unlink($file);
	}
}

function imageProcessor_resOrig($fullpath, $size, $jpeg_qual = 90) {

	global $CJO, $I18N_8;
	$mypage = "image_processor";

	cjoTime::avoidTimeout($I18N_8->msg('msg_wait_while_generating_images'));

	if ($jpeg_qual < 50) $jpeg_qual = 50;
	if ($size < 1024)    $size = 1024;
	$Imgpath_temp = $fullpath.'.'.@getmypid();

	$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');
	if (!file_exists($ip_cachedir))
	    if (!@ mkdir($ip_cachedir, $CJO['FILEPERM']))
	        return "ERROR NOT ABLE TO CREATE CACHE DIRECTORY";

	if (file_exists($fullpath) && !is_dir($fullpath)) {
		$imageinfo = getimagesize($fullpath);
		if ($imageinfo[0] > $size || $imageinfo[1] > $size) {
			if (!class_exists("resizecache"))
			require_once ($CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.resizecache.inc.php');
			$resizeObj = new resizecache();
			$args['a'] = false;
			$args['jq'] = (int) $jpeg_qual;
			$args['x'] = (int) $size;
			$args['y'] = (int) $size;
			if ($resizeObj->generate($fullpath, $Imgpath_temp, $args)) {
				if (@ unlink($fullpath)) {
					rename($Imgpath_temp, $fullpath);
					return "RESIZED";
				}
			}
			return "ERROR";
		}
		return "IMAGE TO SMALL";
	}
	return "File DOES NOT EXIST";
}

function imageProcessor_updateDB($filename) {

	global $CJO;
	$mypage = "image_processor";
	$fullpath = $CJO['MEDIAFOLDER']."/".$filename;

	$curImageInfo = @ getimagesize($fullpath);
	$curFileSize = @ filesize($fullpath);
	$file = OOMedia :: getMediaByName($filename);

	if ($curImageInfo[0] != $file->_width || $curImageInfo[0] != $file->_height) {

		$crop = imageProcessor_initCropValues($curImageInfo);

		$update = new cjoSql();
		$update->setTable(TBL_FILES);
		$update->setWhere("filename='".$filename."'");
		$update->setValue("width", $curImageInfo[0]);
		$update->setValue("height", $curImageInfo[1]);
		$update->setValue("filesize", $curFileSize);

		$update->setValue("crop_1", $crop[1]);
		$update->setValue("crop_2", $crop[2]);
		$update->setValue("crop_3", $crop[3]);
		$update->setValue("crop_4", $crop[4]);
		$update->setValue("crop_5", $crop[5]);

		$update->update();
	}
}

/**
 * Compatibility
 *
 * @param (int) $time
 */
function imageProcessor_avoid_timeout($time = 8) {
	global $I18N_8;
	cjoTime::avoidTimeout($I18N_8->msg('msg_wait_while_generating_images'), $time);
}

/**
 * Replace function http_build_query()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.http-build-query
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1356 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('http_build_query')) {

	function http_build_query($formdata, $numeric_prefix = null) {
		// If $formdata is an object, convert it to an array
		if (is_object($formdata)) {
			$formdata = get_object_vars($formdata);
		}
		// Check we have an array to work with
		if (!is_array($formdata)) {
			user_error('http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.', E_USER_WARNING);
			return false;
		}
		// If the array is empty, return null
		if (empty ($formdata)) {
			return;
		}
		// Argument seperator
		$separator = ini_get('arg_separator.output');

		// Start building the query
		$tmp = array ();
		foreach ($formdata as $key => $val) {
			if (is_integer($key) && $numeric_prefix != null) {
				$key = $numeric_prefix.$key;
			}
			if (is_scalar($val)) {
				array_push($tmp, urlencode($key).'='.urlencode($val));
				continue;
			}
			// If the value is an array, recursively parse it
			if (is_array($val)) {
				array_push($tmp, __http_build_query($val, urlencode($key)));
				continue;
			}
		}
		return implode($separator, $tmp);
	}

	// Helper function
	function __http_build_query($array, $name) {
		$tmp = array ();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				array_push($tmp, __http_build_query($value, sprintf('%s[%s]', $name, $key)));
			}
			elseif (is_scalar($value)) {
				array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
			}
			elseif (is_object($value)) {
				array_push($tmp, __http_build_query(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
			}
		}
		// Argument seperator
		$separator = ini_get('arg_separator.output');

		return implode($separator, $tmp);
	}
}