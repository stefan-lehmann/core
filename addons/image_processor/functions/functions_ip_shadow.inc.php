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

function ip_build_shadow_img($image,  $shadow, $rotate=0, $mime='') {

	global $CJO;

	$mypage 		  = 'image_processor';
	$background_color = $CJO['ADDON']['settings'][$mypage]['shadow']['background_color'];
	$shadow_color 	  = $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_color'];

	$shadow_r = hexdec(substr($shadow_color, 1, 2));
	$shadow_g = hexdec(substr($shadow_color, 3, 2));
	$shadow_b = hexdec(substr($shadow_color, 5, 2));


	if (!function_exists('imagefilter'))
		    include_once('functions_ip_imagefilter.inc.php');

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

function ip_drop_shadow_image($thumb, $width, $height, $mime){

	global $CJO;

	$mypage 		  = 'image_processor';
	$samples		  = $CJO['ADDON']['settings'][$mypage]['shadow']['samples'];
	$shadow_angle 	  = (int) $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_angle'];
	$shadow_size 	  = (int) $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_size'];
	$shadow_distance  = (int) $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_distance'];
	$shadow_color 	  = $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_color'];
	$background_color = $CJO['ADDON']['settings'][$mypage]['shadow']['background_color'];
	$border_width 	  = (int) $CJO['ADDON']['settings'][$mypage]['shadow']['border_width'];
	$border_color	  = $CJO['ADDON']['settings'][$mypage]['shadow']['border_color'];

	if ($shadow_color == $background_color ||
		$shadow_color == 'transparent'){
		return $thumb;
	}

	$rotate			  = 0;
	$shadow_x 		  = $shadow_size;
	$shadow_y 		  = $shadow_size;
	$dist_x 		  = $shadow_distance * cos(deg2rad($shadow_angle));
	$dist_y 		  = $shadow_distance * sin(deg2rad($shadow_angle));
	$out_x		 	  = $width;
	$out_y		  	  = $height;

	$shadow_x 	= $shadow_size;
	$shadow_y 	= $shadow_size;
	$dist_x 	= $shadow_distance * cos(deg2rad($shadow_angle+$rotate));
	$dist_y 	= $shadow_distance * sin(deg2rad($shadow_angle+$rotate));

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

	$image = ip_build_shadow_img($image, $shadow, $rotate, $mime);
	imagealphablending($image, true);
	imagesavealpha($image, true);
	 
	return $image;
}