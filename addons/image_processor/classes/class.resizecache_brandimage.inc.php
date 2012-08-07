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

class resizecache_brandimage extends resizecache {

    public $brandimagepath;
    public $brandimagedata;

    public function resizecache_brandimage() {
        parent :: resizecache();
    }

    public function generate($fullpath, $cacheImgpath, $args) {
        global $CJO;
        $path = parent :: generate($fullpath, $cacheImgpath, $args);
        ((isset ($args['bimg']) && $this->settings['brand']['allowoverride']['brandimg'])) ? $this->brandimagepath = $CJO['MEDIAFOLDER']."/".$args['bimg'] : $this->brandimagepath = $CJO['MEDIAFOLDER']."/".$this->settings['brand']['default']['brandimg'];
        if ($this->checkbrandimage())
            $this->brand();
        return $path;
    }

    //proz: Ausdehnung in Prozent des Originalbildes
    //align: Ausrichtung zuerst horiz(l,r) dann vert(t,b) Bsp: rb
    //margin: Randabstand in Prozent des Originalbildes, wenn y=-1 dann pixelgleich
    public function brand() {
        //Logogröße berechnen
        $ratio = (($this->x * $this->y) / ($this->brandimagedata[0] * $this->brandimagedata[1])) * ($this->settings['brand']['size'] / 100);
        if ($ratio > 1 && $this->settings['brand']['resize'] == 0)
            $ratio = 1;
        $resizedbrand_x = floor(($this->brandimagedata[0] * $ratio));
        $resizedbrand_y = floor(($this->brandimagedata[1] * $ratio));

        if ($resizedbrand_x >= $this->settings['brand']['limit'] && $resizedbrand_y >= $this->settings['brand']['limit']) {
            //Logoposition berechnen
            $absmargin_x = floor(($this->x * ($this->settings['brand']['x_margin'] / 100)));
            if ($this->settings['brand']['y_margin'] == -1) {
                $absmargin_y = $absmargin_x;
            } else {
                $absmargin_y = floor(($this->y * ($this->settings['brand']['y_margin'] / 100)));
            }

            if ($this->settings['brand']['orientation'] { 0 }
                == "l") {
                $offset_x = $absmargin_x;
            }
            if ($this->settings['brand']['orientation'] { 0 }
                == "r") {
                $offset_x = $this->x - $absmargin_x - $resizedbrand_x;
            }
            if ($this->settings['brand']['orientation'] { 1 }
                == "t") {
                $offset_y = $absmargin_y;
            }
            if ($this->settings['brand']['orientation'] { 1 }
                == "b") {
                $offset_y = $this->y - $absmargin_y - $resizedbrand_y;
            }
            $image = call_user_func("imagecreatefrom".$this->types[$this->orgImageInfo[2]], $this->cacheImgpath);
            $brand = call_user_func("imagecreatefrom".$this->types[$this->brandimagedata[2]], $this->brandimagepath);
            $resizedbrand = imagecreatetruecolor($resizedbrand_x, $resizedbrand_y);
            imagealphablending($resizedbrand, true);
            $transCol = imagecolorallocatealpha($resizedbrand, 0, 0, 0, 127);
            imagefill($resizedbrand, 0, 0, $transCol);

            imagecopyresampled($resizedbrand, $brand, 0, 0, 0, 0, $resizedbrand_x, $resizedbrand_y, $this->brandimagedata[0], $this->brandimagedata[1]);
            imagecopy($image, $resizedbrand, $offset_x, $offset_y, 0, 0, $resizedbrand_x, $resizedbrand_y); // $this->settings['brand']['opacity']
            call_user_func("image".$this->types[$this->orgImageInfo[2]], $image, $this->cacheImgpath, $this->jpgquality);
            imagedestroy($image);
            imagedestroy($brand);
        }
    }

    public function checkbrandimage() {
        if (!file_exists($this->brandimagepath)) {
            return false;
        } else {
            $this->brandimagedata = getimagesize($this->brandimagepath);

            if (!$this->brandimagedata[2] || $this->brandimagedata[2] > 3)
                return false;
        }
        return true;
    }
}