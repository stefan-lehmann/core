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

class resizecache {

    private static $addon = 'image_processor';
    public $settings;
    public $types;

    public $orgImgpath;
    public $orgImageInfo;
    public $cacheImgpath;

    public $jpgquality;
    public $crop_w;
    public $crop_h;
    public $crop_x;
    public $crop_y;
    public $x;
    public $y;
    public $resize;
    public $aspectratio;
    public $shadow;

    public function generate($fullpath, $cacheImgpath, $args) {

        $no_resize = false;
        $unsharp   = false;
        $image     = false;
        $this->orgImgpath = $fullpath;
        $this->cacheImgpath = $cacheImgpath;
        $this->checkOrgImage();

        // Alexo: Cropping
        $this->crop_x = (int)$args['crop_x'];
        $this->crop_y = (int)$args['crop_y'];

        $this->crop_w = ((int)$args['crop_w'] > 0 && (int)$args['crop_w'] < $this->orgImageInfo[0])
            ? (int)$args['crop_w']
            : $this->orgImageInfo[0];

        $this->crop_h = ((int)$args['crop_h'] > 0 && (int)$args['crop_h'] < $this->orgImageInfo[1])
            ? (int)$args['crop_h']
            : $this->orgImageInfo[1];

        $this->x = ((int)$args['x'] != 0) ? (int)$args['x'] : NULL;
        $this->y = ((int)$args['y'] != 0) ? (int)$args['y'] : NULL;

        // Bild schärfen, wenn nicht das Orginal resized wird (orgImgpath identisch mit cacheImgpath)
        // und Bild größer als 1 Mio Pixel
        if ($this->orgImgpath != $this->cacheImgpath && ($this->x*$this->y) < 1000000){
            $unsharp = true;
        }

        $this->shadow = (int)$args['shadow'];

        $resizeImageInfo    = $this->orgImageInfo;
        $resizeImageInfo[0] = $this->x;
        $resizeImageInfo[1] = $this->y;

        $this->resize      = isset($args['r']) ? (boolean) $args['r'] : (boolean) cjoAddon::getParameter('default|resize', self::$addon);
        $this->aspectratio = isset($args['a']) ? (boolean) $args['a'] : (boolean) cjoAddon::getParameter('default|aspectratio', self::$addon);
        $this->jpgquality  = isset($args['jq']) && cjoAddon::getParameter('allowoverride|jpg-quality', self::$addon)
                           ? $args['jq'] : cjoAddon::getParameter('default|jpg-quality', self::$addon);

        $this->orgImageInfo[0] = $this->crop_w;
        $this->orgImageInfo[1] = $this->crop_h;
        
        if (!$this->calculatesize()) $no_resize = true;

        $thumb = imagecreatetruecolor($this->x, $this->y);

        switch ($this->orgImageInfo['mime']) {

            case 'image/png':   imagealphablending($thumb, false);
                                imagesavealpha($thumb, true); 
                                $image = imagecreatefrompng($this->orgImgpath);
                                $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
                                imagefilledrectangle($image, 0, 0, $this->x, $this->y, $transparent);
                                $unsharp = false;
                                break;
                                
            case 'image/gif':	$image = imagecreatefromgif($this->orgImgpath);
                                $transp_index = imagecolortransparent($image);
                                if ($transp_index >= 0 && $transp_index < 255) {
                                    $transparent_color = @imagecolorsforindex($image, $transp_index);
                                    $transp_index = imagecolorallocate($thumb, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                                    imagefill($thumb, 0, 0, $transp_index);
                                    imagecolortransparent($thumb, $transp_index);
                                    $number_colors = imagecolorstotal($image);
                                    imagetruecolortopalette($thumb, TRUE, $number_colors);
                                }
                                $unsharp = false;
                                break;
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg': $image = imagecreatefromjpeg($this->orgImgpath); break;
            default:            return $this->orgImgpath;
        }

        if ($no_resize) {
            $thumb = $image;
            imagedestroy ($image);
        }
        else {
            if (function_exists('imagecopyresampled')) {
                //imagecopyresized(int dst_im, int src_im, int dstX, int dstY, int srcX, int srcY, int dstW, int dstH, int srcW, int srcH)
                imagecopyresampled ($thumb, $image, 0, 0, $this->crop_x, $this->crop_y, $this->x, $this->y, $this->crop_w, $this->crop_h);
            }
            else {
                imagecopyresized ($thumb, $image, 0, 0, $this->crop_x, $this->crop_y, $this->x, $this->y, $this->crop_w, $this->crop_h);
            }
            if ($unsharp) {
                $thumb = cjoImageProcessor::unSharp($thumb);
            }
        }

        if ($this->shadow && !self::is_conflict_memory_limit($this->orgImgpath)) {
            if (!cjoAddon::getParameter('shadow|background_color', self::$addon) ||
                cjoAddon::getParameter('shadow|background_color', self::$addon) == 'transparent'){
                $this->orgImageInfo['mime'] = 'image/png';
            }

            $thumb = cjoImageProcessor::dropShadow($thumb, $this->x, $this->y, $this->orgImageInfo['mime']);
        }

        switch ($this->orgImageInfo['mime']) {
            case 'image/png':   $image = imagepng($thumb, $this->cacheImgpath); break;
            case 'image/gif':   $image = imagegif($thumb, $this->cacheImgpath); break;
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg': $image = imagejpeg($thumb, $this->cacheImgpath, $this->jpgquality); break;
            default:            return $this->orgImgpath;
        }

        imagedestroy ($thumb);

        return $this->cacheImgpath;
    }

    public static function is_conflict_memory_limit($fullpath) {

        $imagesize = @getimagesize($fullpath);
        
        $width        = !empty($imagesize[0]) ? $imagesize[0] : 1;
        $height       = !empty($imagesize[1]) ? $imagesize[1] : 1;
        $channels     = !empty($imagesize['channels']) ? $imagesize['channels'] : 3;
        $bits         = !empty($imagesize['bits']) ? $imagesize['bits'] : 32;
        $memory_limit = ini_get('memory_limit');

        if (empty($memory_limit)) {
            $memory_limit = (int) get_cfg_var('memory_limit');
        }

        $memory_limit = ((int) $memory_limit) * pow(1024,2);
        $expected_memory = round(($width * $height * $channels * $bits / 8 + pow(2, 16)) * 1.65);

        return (($expected_memory/$memory_limit*100) < 60 || empty($imagesize[0])) ? false : true;
    }

    public function checkOrgImage(){

        $error = false;
        if (!file_exists($this->orgImgpath) || is_dir($this->orgImgpath)) $error = true;
        if (!$error) $this->orgImageInfo = getimagesize($this->orgImgpath);
        if (!$this->orgImageInfo[2] || $this->orgImageInfo[2] > 3) $error = true;

        if ($error) {

            $this->orgImgpath = cjoImageProcessor::getErrorImage(true);
            $errorImage = "";
            if($this->y) $errorImage = "y".$this->y.$errorImage;
            if($this->x) $errorImage = "x".$this->x.$errorImage;
            $errorImage = ($errorImage != "")
                        ? $errorImage."_".cjoImageProcessor::getErrorImage()
                        : cjoImageProcessor::getErrorImage();
            $this->cacheImgpath = cjoUrl::mediaCache($errorImage);
        }

        $this->orgImageInfo = @getimagesize($this->orgImgpath);
    }

    public function calculatesize(){

        if (empty($this->orgImageInfo[0]) ||
            empty($this->orgImageInfo[1])) return false;

        (!$this->x || preg_match('/^[0-9]{1,}$/', $this->x)) &&
        (!$this->y || preg_match('/^[0-9]{1,}$/', $this->y)) &&
        ($this->x || $this->y)
            ? true
            : $this->x = $this->orgImageInfo[0];

        !$this->x
            ? $this->x = floor ($this->y * $this->orgImageInfo[0] / $this->orgImageInfo[1])
            : $this->x;

        !$this->y
            ? $this->y = floor ($this->x * $this->orgImageInfo[1] / $this->orgImageInfo[0])
            : $this->y;

        if (!$this->aspectratio && $this->x && $this->y) {

            $temp_x = $this->y * $this->orgImageInfo[0] / $this->orgImageInfo[1];
            $temp_y = $this->x * $this->orgImageInfo[1] / $this->orgImageInfo[0];

            if ($temp_y > $this->y) {
                $this->x = ceil($temp_x);
            }
            elseif ($temp_x > $this->x) {
                $this->y = ceil($temp_y);
            }
        }
        if (!(($this->orgImageInfo[0] > $this->x || $this->orgImageInfo[1] > $this->y) ||
            (($this->orgImageInfo[0] < $this->x || $this->orgImageInfo[1] < $this->y) && $this->resize))) {
            $this->x = $this->orgImageInfo[0];
            $this->y = $this->orgImageInfo[1];
        }
        return true;
    }
}