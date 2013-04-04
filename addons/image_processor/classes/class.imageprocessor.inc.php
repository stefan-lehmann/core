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

class cjoImageProcessor {
    
    private static $addon = 'image_processor';
    
    public static function getImg ($filename,
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
    
    	$use_cached = false;
    
    	if ($fullpath == '') {
    		$fullpath = cjoUrl::media($filename);
    	}
    	if (!file_exists($fullpath)) {
    		return self::getErrorImage(true);
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
        					if (cjoAddon::getParameter('allowoverride|jpg-quality', self::$addon)) //nur wenn allowoverride
        					$args["jq"] = (int) $parts[1];
        					break;
        				case "brand_on_off" :
        					if (cjoAddon::getParameter('brand|allowoverride|brand_on_off', self::$addon)) //nur wenn allowoverride
        					$args["b"] = (int) ((bool) $parts[1]);
        					break;
        				case "brandimg" :
        					if (($args["b"] || cjoAddon::getParameter('brand|default|brand_on_off', self::$addon)) &&
        					 cjoAddon::getParameter('brand|allowoverride|brandimg', self::$addon)) //nur wenn allowoverride
        					$args["bimg"] = trim($parts[1]);
        					break;
        			}
    		    }
    		}
    	}
    
    
    	if (resizecache::is_conflict_memory_limit($fullpath) &&
    		$filename != cjoAddon::getParameter('brand|default|brandimg', self::$addon)) {
    
    		$file_ext = substr(strrchr($fullpath, "."), 1);
    		$file_ext = (OOMedia::isDocType($file_ext)) ? $file_ext : 'default';
    		return cjoUrl::backend('img/mime_icons/'.$file_ext.'.png');
    	}
    
    	$res_orig_size = cjoAddon::getParameter('res_orig|size', self::$addon);
    	$res_orig_quality = cjoAddon::getParameter('res_orig|jpg-quality', self::$addon);
    
    	//Resize Original
    	if (cjoAddon::getParameter('res_orig|on_off', self::$addon) &&
    	    $res_orig_size > 250 && $res_orig_quality > 50) {
    		if (self::resOrig($fullpath, $res_orig_size, $res_orig_quality) == 'RESIZED')
    		self::updateDB($filename);
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
    	$cacheImgpath = cjoUrl::mediaCache($cacheImage);
    	$error = false;
    	$mime = @array_pop(getimagesize($fullpath));
    
    	//Für schnelles laden von errorimgs
    	if (!file_exists($fullpath) || (
        	$mime != 'image/png' &&
        	$mime != 'image/gif' &&
        	$mime == 'image/jpg' &&
        	$mime == 'image/jpeg' &&
        	$mime == 'image/pjpeg')) {
    
    		$fullpath = self::getErrorImage(true);
    
    		$errorImage = "";
    		if ($y) $errorImage = "y".$y.$errorImage;
    		if ($x) $errorImage = "x".$x.$errorImage;
    		$errorImage = $errorImage."_error.jpg";
    		$cacheImgpath = cjoUrl::mediaCache($errorImage);
    	}
    
    	if (file_exists($cacheImgpath) &&
        	filemtime($fullpath) < filemtime($cacheImgpath)) {
    		//@touch($cacheImgpath);
    		return $cacheImgpath;
    	}
    
    	@set_time_limit(5000);
    	ini_set("memory_limit", "256M");
    
    	cjoTime::avoidTimeout(cjoAddon::translate(8,'msg_wait_while_generating_images'));
    
    	$usebrand = cjoAddon::getParameter('brand|default|brand_on_off', self::$addon);
    	if (isset ($args['b']) && cjoAddon::getParameter('brand|allowoverride|brand_on_off', self::$addon))
    	    $usebrand = $args['b'];
    
    	if (!$usebrand) {
    		$resizeObj = new resizecache();
    	}
    	else {
    		$resizeObj = new resizecache_brandimage();
    	}
    
    	$returnPath = $resizeObj->generate($fullpath, $cacheImgpath, $args);
    
    	$cachesize = cjoAddon::getParameter('cachesize', self::$addon) * (2*1024*1024);
    	$garbagecollector = new garbagecollector(cjoUrl::mediaCache($errorImage), $cachesize);
    	$garbagecollector->tidy();
    
    	@set_time_limit(60);
    	ini_set("memory_limit", "32M");
    
    	return $returnPath;
    }
    
    public static function initCropValues($size) {
    
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
    
    			$rect = self::calculateCrop($size, $set[$i], $sql->getValue('aspectratio'));
    
    			$crop[$i] = $set[$i]['id'].'_'.$set[$i]['name'].'|'.$rect[1].'|'.$rect[2].'|'.$rect[3].'|'.$rect[4];
    		}
    		$sql->next();
    	}
    	return $crop;
    }
    
    public static function calculateCrop($c_size, $t_size, $aspectratio = 1) {
    
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
    
    
    public static function unlinkCached($filename) {
    
    	//cachenamen generieren
    	$cached_name = str_replace(substr($filename, strrpos($filename, '.')), '', $filename);
    	$cached_name = substr(md5($cached_name), 0, 16);
    
    	// ---- Dateien aus dem Cache lesen
    	foreach (cjoAssistance::toArray(glob(cjoUrl::mediaCache($cached_name."*"))) as $file) {
    		@unlink($file);
    	}
    }
    
    public static function resOrig($fullpath, $size, $jpeg_qual = 90) {
    
    	cjoTime::avoidTimeout(cjoAddon::translate(8,'msg_wait_while_generating_images'));
    
    	if ($jpeg_qual < 50) $jpeg_qual = 50;
    	if ($size < 1024)    $size = 1024;
    	$Imgpath_temp = $fullpath.'.'.@getmypid();
    
    	if (!file_exists(cjoUrl::mediaCache()))
    	    if (!@mkdir(cjoUrl::mediaCache(), cjoProp::getDirPerm()))
    	        return "ERROR NOT ABLE TO CREATE CACHE DIRECTORY";
    
    	if (file_exists($fullpath) && !is_dir($fullpath)) {
    		$imageinfo = getimagesize($fullpath);
    		if ($imageinfo[0] > $size || $imageinfo[1] > $size) {
    			$resizeObj = new resizecache();
    			$args['a'] = false;
    			$args['jq'] = (int) $jpeg_qual;
    			$args['x'] = (int) $size;
    			$args['y'] = (int) $size;
    			if ($resizeObj->generate($fullpath, $Imgpath_temp, $args)) {
    				if (@unlink($fullpath)) {
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
    
    public static function updateDB($filename) {
    
    	$fullpath = cjoUrl::media($filename);
    
    	$curImageInfo = @getimagesize($fullpath);
    	$curFileSize = @filesize($fullpath);
    	$file = OOMedia::getMediaByName($filename);
    
    	if ($curImageInfo[0] != $file->_width || $curImageInfo[0] != $file->_height) {
    
    		$crop = self::initCropValues($curImageInfo);
    
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
    public static function avoid_timeout($time = 8) {
    	global $I18N_8;
    	cjoTime::avoidTimeout(cjoAddon::translate(8,'msg_wait_while_generating_images'), $time);
    }
    
    public static function auto_resOrig($params){
    
        $filename = $params['filename'];
        $path = ($params['path'] == '') ? cjoUrl::media() : $params['path'].'/';
        $filepath = $path.'/'.$filename;
    
        $res_orig_size = cjoAddon::getParameter('res_orig|size', self::$addon) * (2*1024);
        $res_orig_quality = cjoAddon::getParameter('res_orig|jpg-quality', self::$addon);
    
        //Resize Original
        if (cjoAddon::getParameter('res_orig|on_off', self::$addon) &&
            $res_orig_size > 100 && $res_orig_quality > 50){
            if (self::resOrig($filepath, $res_orig_size, $res_orig_quality) == 'RESIZED'){
                clearstatcache();
            }
            if (file_exists(cjoUrl::media($filename))){
                self::updateDB($filename);
            }
        }
    }
    
    private static function buildShadow($image,  $shadow, $rotate=0, $mime='') {
    
        $background_color = cjoAddon::getParameter('shadow|background_color', self::$addon);
        $shadow_color     = cjoAddon::getParameter('shadow|shadow_color', self::$addon);
    
        $shadow_r = hexdec(substr($shadow_color, 1, 2));
        $shadow_g = hexdec(substr($shadow_color, 3, 2));
        $shadow_b = hexdec(substr($shadow_color, 5, 2));
    
    
        if (!function_exists('imagefilter')) include_once cjoPath::addon(self::$addon, 'function/functions_ip_imagefilter.inc.php');
    
        imagefilter($shadow, IMG_FILTER_COLORIZE, $shadow_r, $shadow_g, $shadow_b);
    
        $background_r = hexdec(substr($background_color, 1, 2));
        $background_g = hexdec(substr($background_color, 3, 2));
        $background_b = hexdec(substr($background_color, 5, 2));
    
        imagealphablending($image, false);
    
        for ($theX=0;$theX<imagesx($image);$theX++){
            for ($theY=0;$theY<imagesy($image);$theY++){
    
                $rgb = imagecolorat($image,$theX,$theY);
                $R = ($rgb >> 16) & 0xFF;
                $G = ($rgb >> 8) & 0xFF;
                $B = $rgb & 0xFF;
    
                $rgb = imagecolorat($shadow,$theX,$theY);
                $a = $rgb & 0xFF;
                $a = 127-floor($a/2);
                $t = $a/128.0;
    
                if (stripos($mime, 'png') !== false) {
                    $myColour = imagecolorallocatealpha($image,$R,$G,$B,$a);
                }
                else {
                    $R = $R * (1.0 - $t) + $background_r * $t;
                    $G = $G * (1.0 - $t) + $background_g * $t;
                    $B = $B * (1.0 - $t) + $background_b * $t;
                    $myColour = imagecolorallocate($image,$R, $G, $B);
                }
                imagesetpixel($image, $theX, $theY, $myColour);
            }
        }
        return $image;
    }
    
    public static function dropShadow($thumb, $width, $height, $mime){
    
        $samples          = cjoAddon::getParameter('shadow|samples', self::$addon);
        $shadow_angle     = (int) cjoAddon::getParameter('shadow|shadow_angle', self::$addon);
        $shadow_size      = (int) cjoAddon::getParameter('shadow|shadow_size', self::$addon);
        $shadow_distance  = (int) cjoAddon::getParameter('shadow|shadow_distance', self::$addon);
        $shadow_color     = cjoAddon::getParameter('shadow|shadow_color', self::$addon);
        $background_color = cjoAddon::getParameter('shadow|background_color', self::$addon);
        $border_width     = (int) cjoAddon::getParameter('shadow|border_width', self::$addon);
        $border_color     = cjoAddon::getParameter('shadow|border_color', self::$addon);
    
        if ($shadow_color == $background_color ||
            $shadow_color == 'transparent'){
            return $thumb;
        }
    
        $rotate           = 0;
        $shadow_x         = $shadow_size;
        $shadow_y         = $shadow_size;
        $dist_x           = $shadow_distance * cos(deg2rad($shadow_angle));
        $dist_y           = $shadow_distance * sin(deg2rad($shadow_angle));
        $out_x            = $width;
        $out_y            = $height;
    
        $shadow_x   = $shadow_size;
        $shadow_y   = $shadow_size;
        $dist_x     = $shadow_distance * cos(deg2rad($shadow_angle+$rotate));
        $dist_y     = $shadow_distance * sin(deg2rad($shadow_angle+$rotate));
    
        $shadow_r = hexdec(substr($shadow_color, 1, 2));
        $shadow_g = hexdec(substr($shadow_color, 3, 2));
        $shadow_b = hexdec(substr($shadow_color, 5, 2));
    
        // ---- Border ----
        if ($border_width > 0) {
    
            $border_r = hexdec(substr($border_color, 1, 2));
            $border_g = hexdec(substr($border_color, 3, 2));
            $border_b = hexdec(substr($border_color, 5, 2));
    
            $colour = imagecolorallocate($thumb, $border_r, $border_g, $border_b);
    
            $x1 = 0;
            $y1 = 0;
            $x2 = $out_x - 1;
            $y2 = $out_y - 1;
    
            for($i = 0; $i < $border_width; $i++)
            {
                ImageRectangle($thumb, $x1++, $y1++, $x2--, $y2--, $colour);
            }
        }
    
        // ---- RGB ----
        $image = imagecreatetruecolor($out_x+$shadow_x,$out_y+$shadow_y);
        $colour = imagecolorallocate($image, $shadow_r, $shadow_g, $shadow_b);
        imagefilledrectangle($image, 0, 0, $out_x+$shadow_x, $out_y+$shadow_y, $colour);
        imagecopymerge($image, $thumb,
                      $shadow_x*0.5-$dist_x,
                      $shadow_y*0.5-$dist_y,
                      0,
                      0,
                      $out_x,
                      $out_y,
                      100);
    
        // ---- Shadow with alpha ----
        $shadow = imagecreatetruecolor($out_x+$shadow_x,$out_y+$shadow_y);
        imagealphablending($shadow, false);
        $colour = imagecolorallocate($shadow, 0, 0, 0);
        imagefilledrectangle($shadow, 0, 0, $out_x+$shadow_x, $out_y+$shadow_y, $colour);
    
        for ($i=0;$i<=$samples;$i++) {
            $t = ((1.0*$i)/$samples);
            $intensity = 255*$t*$t;
            $colour = imagecolorallocate($shadow, $intensity, $intensity, $intensity);
            $points = array(
                $shadow_x*$t,            $shadow_y,     // Point 1 (x, y)
                $shadow_x,               $shadow_y*$t,  // Point 2 (x, y)
                $out_x,                  $shadow_y*$t,  // Point 3 (x, y)
                $out_x+$shadow_x*(1-$t), $shadow_y,     // Point 4 (x, y)
                $out_x+$shadow_x*(1-$t), $out_y,  // Point 5 (x, y)
                $out_x,                  $out_y+$shadow_y*(1-$t),  // Point 6 (x, y)
                $shadow_x,               $out_y+$shadow_y*(1-$t),  // Point 7 (x, y)
                $shadow_x*$t,            $out_y   // Point 8 (x, y)
            );
            imagepolygon($shadow, $points, 8, $colour);
        }
        for ($i=0;$i<=$samples;$i++) {
            $t = ((1.0*$i)/$samples);
            $intensity = 255*$t*$t;
            $colour = imagecolorallocate($shadow, $intensity, $intensity, $intensity);
            imagefilledarc($shadow, $shadow_x-1, $shadow_y-1, 2*(1-$t)*$shadow_x, 2*(1-$t)*$shadow_y, 180, 268, $colour, IMG_ARC_PIE);
            imagefilledarc($shadow, $out_x, $shadow_y-1, 2*(1-$t)*$shadow_x, 2*(1-$t)*$shadow_y, 270, 358, $colour, IMG_ARC_PIE);
            imagefilledarc($shadow, $out_x, $out_y, 2*(1-$t)*$shadow_x, 2*(1-$t)*$shadow_y, 0, 90, $colour, IMG_ARC_PIE);
            imagefilledarc($shadow, $shadow_x-1, $out_y, 2*(1-$t)*$shadow_x, 2*(1-$t)*$shadow_y, 90, 180, $colour, IMG_ARC_PIE);
        }
    
        $colour = imagecolorallocate($shadow, 255, 255, 255);
        imagefilledrectangle($shadow, $shadow_x, $shadow_y, $out_x, $out_y, $colour);
        imagefilledrectangle($shadow,
                             $shadow_x*0.5-$dist_x,
                             $shadow_y*0.5-$dist_y,
                             $out_x+$shadow_x*0.5-1-$dist_x,
                             $out_y+$shadow_y*0.5-1-$dist_y,
                             $colour);
    
        $image = self::buildShadow($image, $shadow, $rotate, $mime);
        imagealphablending($image, true);
        imagesavealpha($image, true);
         
        return $image;
    }

    /*
     WARNING! Due to a known bug in PHP 4.3.2 this script is not working well in this version. The sharpened images get too dark. The bug is fixed in version 4.3.3.
    
     From version 2 (July 17 2006) the script uses the imageconvolution function in PHP version >= 5.1, which improves the performance considerably.
    
     Unsharp masking is a traditional darkroom technique that has proven very suitable for
     digital imaging. The principle of unsharp masking is to create a blurred copy of the image
     and compare it to the underlying original. The difference in colour values
     between the two images is greatest for the pixels near sharp edges. When this
     difference is subtracted from the original image, the edges will be
     accentuated.
    
     The Amount parameter simply says how much of the effect you want. 100 is 'normal'.
     Radius is the radius of the blurring circle of the mask. 'Threshold' is the least
     difference in colour values that is allowed between the original and the mask. In practice
     this means that low-contrast areas of the picture are left unrendered whereas edges
     are treated normally. This is good for pictures of e.g. skin or blue skies.
    
     Any suggenstions for improvement of the algorithm, expecially regarding the speed
     and the roundoff errors in the Gaussian blur process, are welcome.
     */
    
    public static function unSharp($img, $amount = 50, $radius = 0.6, $threshold = 0) {
    
        global $I18N_8;
    
        ///////////////////////////////////////////////////////////////////////////////////////////////
        ////
        ////           Unsharp Mask for PHP - version 2.0
        ////
        ////    Unsharp mask algorithm by Torstein Hänsi 2003-06.
        ////             thoensi_at_netcom_dot_no.
        ////               Please leave this notice.
        ////
        ///////////////////////////////////////////////////////////////////////////////////////////////
    
        // $img is an image that is already created within php using
        // imgcreatetruecolor. No url! $img must be a truecolor image.
    
        // Attempt to calibrate the parameters to Photoshop:
        if ($amount > 500)    $amount = 500;
        $amount = $amount * 0.016;
        if ($radius > 50)    $radius = 50;
        $radius = $radius * 1;
        if ($threshold > 255)    $threshold = 255;
    
        $radius = abs(round($radius));     // Only integers make sense.
        if ($radius == 0) return $img;
        $w = imagesx($img); $h = imagesy($img);
    
        $imgCanvas = imagecreatetruecolor($w, $h);
        $imgCanvas2 = imagecreatetruecolor($w, $h);
        $imgBlur = imagecreatetruecolor($w, $h);
        $imgBlur2 = imagecreatetruecolor($w, $h);
        imagecopy ($imgCanvas, $img, 0, 0, 0, 0, $w, $h);
        imagecopy ($imgCanvas2, $img, 0, 0, 0, 0, $w, $h);
    
    
        // Gaussian blur matrix:
        //
        //    1    2    1
        //    2    4    2
        //    1    2    1
        //
        //////////////////////////////////////////////////
    
        imagecopy      ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h); // background
    
        for ($i = 0; $i < $radius; $i++)    {
    
            cjoTime::avoidTimeout(cjoAddon::translate(8,'msg_wait_while_generating_images'));
    
            if (function_exists('imageconvolution')) { // PHP >= 5.1
                $matrix = array(
                array( 1, 2, 1 ),
                array( 2, 4, 2 ),
                array( 1, 2, 1 )
                );
                
                imageconvolution($imgCanvas, $matrix, 16, 0);
                
            } else {
    
                // Move copies of the image around one pixel at the time and merge them with weight
                // according to the matrix. The same matrix is simply repeated for higher radii.
    
                imagecopy      ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
                imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
                imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
    
                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
                imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
                imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
    
                imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
                imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);
    
                // During the loop above the blurred copy darkens, possibly due to a roundoff
                // error. Therefore the sharp picture has to go through the same loop to
                // produce a similar image for comparison. This is not a good thing, as processing
                // time increases heavily.
                imagecopy ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 20 );
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 16.666667);
                imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
                imagecopy ($imgCanvas2, $imgBlur2, 0, 0, 0, 0, $w, $h);
            }
        }
        //return $imgBlur;
    
        // Calculate the difference between the blurred pixels and the original
        // and set the pixels
        for ($x = 0; $x < $w; $x++)    { // each row
    
            cjoTime::avoidTimeout(cjoAddon::translate(8,'msg_wait_while_generating_images'));
    
            for ($y = 0; $y < $h; $y++)    { // each pixel
    
                $rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);
    
                $rgbBlur = ImageColorAt($imgCanvas, $x, $y);
    
                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);
    
                // When the masked pixels differ less from the original
                // than the threshold specifies, they are set to their original value.
                $rNew = (abs($rOrig - $rBlur) >= $threshold)
                ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                : $rOrig;
                $gNew = (abs($gOrig - $gBlur) >= $threshold)
                ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                : $gOrig;
                $bNew = (abs($bOrig - $bBlur) >= $threshold)
                ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                : $bOrig;
    
                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                    $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
                    ImageSetPixel($img, $x, $y, $pixCol);
                }
            }
        }
        return $img;
    }

    public static function getErrorImage($get_fullpath=false) {
        $error_img = cjoAddon::getParameter('error_img', self::$addon);
        return $get_fullpath === true ? cjoUrl::media($error_img) : $error_img;
    }

    public static function initAddon() {
        
        ini_set('gd.jpeg_ignore_warning', 1);
    
        $ip_cachedir = cjoPath::cache(cjoAddon::getParameter('cachedir', $addon, 'resized'));
        
        // CacheDir erstellen, falls nicht vorhanden
        if ($ip_cachedir && !file_exists($ip_cachedir)){
        
            if (!cjoProp::isSetup() && !mkdir($ip_cachedir, cjoProp::get('FILEPERM')) && cjoProp::isBackend()){
                cjoMessage::addError(cjoAddon::translate(8,"msg_no_cache_folder", cjoAddon::getProperty('name', $addon),$ip_cachedir));
            }
        }
    }
}