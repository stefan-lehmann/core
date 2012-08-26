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

if( !defined('IMG_FILTER_NEGATE') )         define('IMG_FILTER_NEGATE',         0);
if( !defined('IMG_FILTER_GRAYSCALE') )      define('IMG_FILTER_GRAYSCALE',      1);
if( !defined('IMG_FILTER_BRIGHTNESS') )     define('IMG_FILTER_BRIGHTNESS',     2);
if( !defined('IMG_FILTER_CONTRAST') )       define('IMG_FILTER_CONTRAST',       3);
if( !defined('IMG_FILTER_COLORIZE') )       define('IMG_FILTER_COLORIZE',       4);
if( !defined('IMG_FILTER_EDGEDETECT') )     define('IMG_FILTER_EDGEDETECT',     5);
if( !defined('IMG_FILTER_EMBOSS') )         define('IMG_FILTER_EMBOSS',         6);
if( !defined('IMG_FILTER_GAUSSIAN_BLUR') )  define('IMG_FILTER_GAUSSIAN_BLUR',  7);
if( !defined('IMG_FILTER_SELECTIVE_BLUR') ) define('IMG_FILTER_SELECTIVE_BLUR', 8);
if( !defined('IMG_FILTER_MEAN_REMOVAL') )   define('IMG_FILTER_MEAN_REMOVAL',   9);
if( !defined('IMG_FILTER_SMOOTH') )         define('IMG_FILTER_SMOOTH',         10);

if( !defined('IMG_FILTER_TINT') )           define('IMG_FILTER_TINT',           11);
if( !defined('IMG_FILTER_RASTER_LINES') )   define('IMG_FILTER_RASTER_LINES',   12);
if( !defined('IMG_FILTER_BAYER') )          define('IMG_FILTER_BAYER',          13);
if( !defined('IMG_FILTER_CONTURES') )       define('IMG_FILTER_CONTURES',       14);
if( !defined('IMG_FILTER_JITTERING') )      define('IMG_FILTER_JITTERING',      15);
if( !defined('IMG_FILTER_POLARIZE') )       define('IMG_FILTER_POLARIZE',       16);
if( !defined('IMG_FILTER_REFRACTOR') )      define('IMG_FILTER_REFRACTOR',      17);
if( !defined('IMG_FILTER_COLOR_THRESHOLD') )define('IMG_FILTER_COLOR_THRESHOLD',18);
if( !defined('IMG_FILTER_CARTOONIZE') )     define('IMG_FILTER_CARTOONIZE',     19);
if( !defined('IMG_FILTER_THRESHOLD') )      define('IMG_FILTER_THRESHOLD',      20);
if( !defined('IMG_FILTER_RASTERIZE') )      define('IMG_FILTER_RASTERIZE',      21);
if( !defined('IMG_FILTER_OLIFY') )          define('IMG_FILTER_OLIFY'  ,        22);
if( !defined('IMG_FILTER_PASTEL') )         define('IMG_FILTER_PASTEL',         23);
if( !defined('IMG_FILTER_WATERCOLOR') )     define('IMG_FILTER_WATERCOLOR',     24);
if( !defined('IMG_FILTER_ROTOZOOM') )       define('IMG_FILTER_ROTOZOOM',       25);
if( !defined('IMG_FILTER_MIRRORED_FRAME') ) define('IMG_FILTER_MIRRORED_FRAME', 26);
/**
 * Applies a filter to an image
 *  PHP 4.0.6 > imagecolorresolvealpha()
 *  PHP 4.0.6 > imagecopyresampled()
 *  PHP 4.2.0 > fmod()
 *
 *
 * IMG_FILTER_NEGATE:         Reverses all colors of the image.
 * IMG_FILTER_GRAYSCALE:      Converts the image into grayscale.
 * IMG_FILTER_BRIGHTNESS:     Changes the brightness of the image. Use arg1
 *                            to set the level of brightness.
 * IMG_FILTER_CONTRAST:       Changes the contrast of the image. Use arg1
 *                            to set the level of contrast.
 * IMG_FILTER_COLORIZE:       Like IMG_FILTER_GRAYSCALE, except you can
 *                            specify the color. Use arg1, arg2 and arg3 in
 *                            the form of red, blue, green. The range for
 *                            each color is 0 to 255.
 * IMG_FILTER_EDGEDETECT:     Uses edge detection to highlight the edges in the image.
 * IMG_FILTER_EMBOSS:         Embosses the image.
 * IMG_FILTER_GAUSSIAN_BLUR:  Blurs the image using the Gaussian method.
 * IMG_FILTER_SELECTIVE_BLUR: Blurs the image.
 * IMG_FILTER_MEAN_REMOVAL:   Uses mean removal to achieve a \"sketchy\" effect.
 * IMG_FILTER_SMOOTH:         Makes the image smoother. Use arg1 to set the
 *                            level of smoothness.
 *
 * IMG_FILTER_TINT            Like IMG_FILTER_COLORIZE except you can use
 *                            (float) arg4 to set the intensive factor
 *                            0.0-1.0. Default is 0.25.
 * IMG_FILTER_RASTER_LINES    Colored raster lines. Use (int) arg1 0-255 to
 *                            set the level of threshold. Default is 127.
 * IMG_FILTER_BAYER           RGB bayer pattern simulation.
 * IMG_FILTER_CONTURES        Black conture lines and white backround.
 *                            (int) $arg1: amount 0-100; (int) $arg2: line
 *                            width; (bool) $arg3: white background.
 * IMG_FILTER_JITTERING       Use (int) arg1 1-100 to set amount. Default is 3.
 * IMG_FILTER_POLARIZE
 * IMG_FILTER_REFRACTOR       Use (int) arg1 to set pattern size. Default is 20.
 * IMG_FILTER_COLOR_THRESHOLD Use (int) arg1 to set maximum colors. Default id 64.
 * IMG_FILTER_CARTOONIZE      Converst the image into cartoon like look.
 * IMG_FILTER_THRESHOLD       Convert the image B/W look. Use (int) arg1
 *                            0-255 to set threshold level. Default is 127.
 * IMG_FILTER_RASTERIZE       Rasterize the image. Use (int) arg1 2.. to
 *                            set cell size. Default is 127.
 * IMG_FILTER_OLIFY           Olify the image. Use (int) arg1 1-20 to set
 *                            amount. Default is 2.
 * IMG_FILTER_PASTEL          Convert the image drawed with pastels look.
 *                            Use (int) arg1 1-20 to set amount. Default is 2.
 * IMG_FILTER_WATERCOLOR      Convert the image painted with watercolors look.
 * IMG_FILTER_ROTOZOOM        Rotate and zoom. Use (float) arg1 0-360 to set
 *                            angle and (float) arg2 0.0-10.0 to set zoom,
 *                            otherwise defaults used(45/1.6).
 * IMG_FILTER_MIRRORED_FRAME  Draw frame over the image. Use (int) arg1 to
 *                            set borser width.
 *
 * @author ukjpriee@ukj.pri.ee ; http://ukj.pri.ee
 * @param resource $src_im
 * @param int $filtertype
 * @param mixed $arg1
 * @param mixed $arg2
 * @param mixed $arg3
 * @param mixed $arg4
 * @return bool
 */

function imagefilter( &$src_im, $filtertype=0, $arg1=0, $arg2=0, $arg3=0, $arg4=0 ) {

    if (!is_resource($src_im)) return 0;

    $srcsx = imagesx( $src_im ); $srcsy = imagesy( $src_im );

    switch( $filtertype ) {
        case IMG_FILTER_NEGATE: {

            for ($y = 0; $y<$srcsy;$y++){
                for ($x = 0; $x<$srcsx;$x++){
                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );
                    $color = imagecolorresolvealpha($src_im,
                    255 - $a['red'],255 - $a['green'],255 - $a['blue'], $a['alpha'] );
                    imagesetpixel ( $src_im, $x, $y, $color );

                }
            }
            break;
        }
        case IMG_FILTER_GRAYSCALE: {

            if($arg1>=256 or $arg1==0) {
                $arg1=256;
            }

            if($arg1<3)$arg1=2; $m=round(256/$arg1); // astme suurus

            for($y=0;$y<$srcsy;$y++) {
                for($x=0;$x<$srcsx;$x++) {
                    $a=imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );
                    $r = round( 0.299*$a['red'] + 0.587*$a['green'] + 0.114*$a['blue'] );

                    if( $arg1<3 ) {
                        if($r>=127) $r=255; else $r=0;
                    } else
                    // fmod() 4.2.0
                    $r = abs( $r - fmod($r,$m) );

                    imagesetpixel($src_im,$x,$y,
                    imagecolorresolvealpha($src_im,$r,$r,$r,$a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_BRIGHTNESS: {

            for ($y = 0; $y<$srcsy;$y++){
                for ($x = 0; $x<$srcsx;$x++){
                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );

                    $a['red']   += $arg1;
                    $a['green'] += $arg1;
                    $a['blue']  += $arg1;

                    if($a['red']  <0)$a['red']  =0; elseif($a['red']  >255)$a['red']  =255;
                    if($a['green']<0)$a['green']=0; elseif($a['green']>255)$a['green']=255;
                    if($a['blue'] <0)$a['blue'] =0; elseif($a['blue'] >255)$a['blue'] =255;

                    // PHP 4.0.6
                    $color = imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] );
                    imagesetpixel ( $src_im, $x, $y, $color );
                }
            }
            break;
        }
        case IMG_FILTER_CONTRAST: {

            $contrast =& $arg1;
            $contrast = (100.0-$contrast)/100.0;
            $contrast = $contrast*$contrast;

            for ($y = 0; $y<$srcsy;$y++){
                for ($x = 0; $x<$srcsx;$x++){
                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );

                    $a['red'] = $a['red']/255.0;
                    $a['red'] = $a['red']-0.5;
                    $a['red'] = $a['red']*$contrast;
                    $a['red'] = $a['red']+0.5;
                    $a['red'] = $a['red']*255.0;

                    $a['green'] = $a['green']/255.0;
                    $a['green'] = $a['green']-0.5;
                    $a['green'] = $a['green']*$contrast;
                    $a['green'] = $a['green']+0.5;
                    $a['green'] = $a['green']*255.0;

                    $a['blue'] = $a['blue']/255.0;
                    $a['blue'] = $a['blue']-0.5;
                    $a['blue'] = $a['blue']*$contrast;
                    $a['blue'] = $a['blue']+0.5;
                    $a['blue'] = $a['blue']*255.0;

                    if($a['red']  <0)$a['red']  =0; elseif($a['red']  >255)$a['red']  =255;
                    if($a['green']<0)$a['green']=0; elseif($a['green']>255)$a['green']=255;
                    if($a['blue'] <0)$a['blue'] =0; elseif($a['blue'] >255)$a['blue'] =255;

                    // PHP 4.0.6
                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'],$a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_COLORIZE: {

            $red    = $arg1;
            $green  = $arg2;
            $blue   = $arg3;

            for($y=0;$y<$srcsy;$y++) {
                for($x=0;$x<$srcsx;$x++) {

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );

                    $a['red']   += $red;
                    $a['green'] += $green;
                    $a['blue']  += $blue;

                    if($a['red']  <0)$a['red']  =0; elseif($a['red']  >255)$a['red']  =255;
                    if($a['green']<0)$a['green']=0; elseif($a['green']>255)$a['green']=255;
                    if($a['blue'] <0)$a['blue'] =0; elseif($a['blue'] >255)$a['blue'] =255;

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im, $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_EDGEDETECT: {

            $kernel = array (array( -1.0, 0.0, -1.0),
            array(  0.0, 4.0,  0.0),
            array( -1.0, 0.0, -1.0));
            ImageConvolution_($src_im, $kernel, 1, 127);
            break;
        }
        case IMG_FILTER_EMBOSS: {

            $kernel = array (array( 1.5, 0.0, 0.0),
            array( 0.0, 0.0, 0.0),
            array( 0.0, 0.0,-1.5));

            ImageConvolution_($src_im, $kernel, 1, 127);
            break;
        }
        case IMG_FILTER_GAUSSIAN_BLUR: {

            $kernel = array (array( 1.0, 2.0, 1.0),
            array( 2.0, 4.0, 2.0),
            array( 1.0, 2.0, 1.0));
            ImageConvolution_($src_im, $kernel, 16, 0);
            break;
        }
        case IMG_FILTER_SELECTIVE_BLUR: {

            /* We need the orinal image with each safe neoghb. pixel */
            $srcback = imagecreatetruecolor($srcsx, $srcsy);
            if ($srcback==NULL) return 0;
            ImageCopy($srcback, $src_im,0,0,0,0,$srcsx,$srcsy);

            $srcsxe=$srcsx-1;
            $srcsye=$srcsx-1;
            $a = array('red'=>0,'green'=>0,'green'=>0,'alpha'=>0);

            for ($y = 1; $y<$srcsye;$y++){
                for ($x = 1; $x<$srcsxe;$x++){
                    $a0 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x,$y ) );
                    $a1 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x+1,$y   ) );
                    $a2 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x+1,$y+1 ) );
                    $a3 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x  ,$y+1 ) );
                    $a4 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x-1,$y+1 ) );
                    $a5 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x-1,$y   ) );
                    $a6 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x-1,$y-1 ) );
                    $a7 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x  ,$y-1 ) );
                    $a8 = imagecolorsforindex($srcback, ImageColorAt($srcback, $x+1,$y-1 ) );

                    $a['red']   = (
                    $a0['red']   + $a1['red']   + $a2['red']   + $a3['red']   + $a4['red']   +
                    $a5['red']   + $a6['red']   + $a7['red']   + $a8['red']   )  /9;

                    $a['green'] = (
                    $a0['green'] + $a1['green'] + $a2['green'] + $a3['green'] + $a4['green'] +
                    $a5['green'] + $a6['green'] + $a7['green'] + $a8['green'] )  /9;

                    $a['blue']  = (
                    $a0['blue']  + $a1['blue']  + $a2['blue']  + $a3['blue']  + $a4['blue']  +
                    $a5['blue']  + $a6['blue']  + $a7['blue']  + $a8['blue']  )  /9;

                    $a['red']  =$a['red']  &255;
                    $a['green']=$a['green']&255;
                    $a['blue'] =$a['blue'] &255;

                    $color = imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a0['alpha'] );
                    imagesetpixel ( $src_im, $x, $y, $color );
                }
            }
            break;
        }
        case IMG_FILTER_MEAN_REMOVAL: {
            $kernel = array (array( -1.0, -1.0, -1.0),
            array( -1.0,  9.0, -1.0),
            array( -1.0, -1.0, -1.0));
            ImageConvolution_($src_im, $kernel, 1, 0);

            break;
        }
        case IMG_FILTER_SMOOTH: {
            $weight = $arg1;
            $kernel = array
            (array(1.0,1.0    ,1.0),
            array(1.0,$weight,1.0),
            array(1.0,1.0    ,1.0));
            ImageConvolution_($src_im, $kernel, $weight+8, 0);
            break;
        }
        case IMG_FILTER_TINT: {
            if($arg4==0) return TRUE;
            $red    = $arg1;
            $green  = $arg2;
            $blue   = $arg3;
            $intens = $arg4; if($intens==0)$intens = 0.25;
            if($intens<0.0)$intens=0.0;elseif($intens>1.0)$intens=1.0;

            for($y=0;$y<$srcsy;$y++) {
                for($x=0;$x<$srcsx;$x++) {

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );

                    $dif_r = abs($a['red']  -$red);
                    $dif_g = abs($a['green']-$green);
                    $dif_b = abs($a['blue'] -$blue);

                    if($a['red']  <$red)   $a['red']   += ($dif_r*$intens);
                    elseif($a['red']  >$red)   $a['red']   -= ($dif_r*$intens);
                    if($a['green']<$green) $a['green'] += ($dif_g*$intens);
                    elseif($a['green']>$green) $a['green'] -= ($dif_g*$intens);
                    if($a['blue'] <$blue)  $a['blue']  += ($dif_b*$intens);
                    elseif($a['blue'] >$blue)  $a['blue']  -= ($dif_b*$intens);

                    if($a['red']  <0)$a['red']  =0; elseif($a['red']  >255)$a['red']  =255;
                    if($a['green']<0)$a['green']=0; elseif($a['green']>255)$a['green']=255;
                    if($a['blue'] <0)$a['blue'] =0; elseif($a['blue'] >255)$a['blue'] =255;

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im, $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_RASTER_LINES: {
            $Threshold =& $arg1;
            if($Threshold==0)$Threshold=127;

            $black = imagecolorallocate( $src_im, 0, 0, 0 );
            $white = imagecolorallocate( $src_im , 255, 255, 255 );
            $tmp_sw=$black;
            for ($y=0; $y<$srcsy;$y+=2) {

                if($tmp_sw==$black) {
                    $tmp_sw=$white;
                    imageline( $src_im , 0, $y , $srcsx ,$y , $tmp_sw );
                }
                elseif($tmp_sw==$white) $tmp_sw=$black;

                for ($x=0; $x<$srcsx;$x++) {

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );
                    $i = round( 0.299*$a['red'] + 0.587*$a['green'] + 0.114*$a['blue'] );

                    if ( $i >= $Threshold ) $c=$white; elseif ( $i < $Threshold ) $c=$black;

                    imagesetpixel ( $src_im, $x, $y, $c );
                }
            }
            break;
        }
        case IMG_FILTER_BAYER: {
            for( $y=0; $y<$srcsy;$y++ ) {
                for( $x=0; $x<$srcsx;$x++ ) {
                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );

                    if (fmod($y,2)==0)
                    if (fmod($x,2)==0) $a['red']   = $a['blue']=0;
                    else               $a['green'] = $a['blue']=0;
                    else
                    if (fmod($x,2)==0) $a['red'] = $a['green']=0;
                    else               $a['red'] = $a['blue'] =0;

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha ( $src_im, $a['red'], $a['green'], $a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_JITTERING: {
            $amount = $arg1;
            if($amount==0)$amount = 3;
            elseif($amount>100)$amount=100;

            for ($y = 0; $y<$srcsy; ++$y) {
                for ($x = 0; $x<$srcsx;++$x) {

                    $nx=$x+(rand(0,$amount))*$amount;
                    $ny=$y+(rand(0,$amount))*$amount;
                    if($nx>=$srcsx)$nx=$x-rand(3,6);
                    if($ny>=$srcsy)$ny=$y-rand(3,6);

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $nx,$ny ) );

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_POLARIZE: {
            $Ye0 = $srcsy - 2;
            $Ye1 = $srcsy - 3;
            $Ye2 = $srcsy - 1;
            for ($y = 5; $y<$srcsy; $y += 4) {
                for ($x = 0; $x<$srcsx;++$x) {

                    if($y<$Ye0) {

                        $a = imagecolorsforindex($src_im,
                        ImageColorAt($src_im, $x-2,$y+2 ) );

                        $a['red']   -=30;
                        $a['green'] -=30;
                        $a['blue']  +=60;

                        if($a['red']  <0)$a['red']  =0; elseif($a['red']  >255)$a['red']  =255;
                        if($a['green']<0)$a['green']=0; elseif($a['green']>255)$a['green']=255;
                        if($a['blue'] <0)$a['blue'] =0; elseif($a['blue'] >255)$a['blue'] =255;

                        imagesetpixel ( $src_im, $x+2, $y-2,
                        imagecolorresolvealpha($src_im,
                        $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                    }

                    if($y<$Ye1) {

                        $a = imagecolorsforindex($src_im,
                        ImageColorAt($src_im, $x+1,$y+3 ) );

                        $a['red']   -=30;
                        $a['green'] +=60;
                        $a['blue']  -=30;

                        if($a['red']  <0)$a['red']  =0; elseif($a['red']  >255)$a['red']  =255;
                        if($a['green']<0)$a['green']=0; elseif($a['green']>255)$a['green']=255;
                        if($a['blue'] <0)$a['blue'] =0; elseif($a['blue'] >255)$a['blue'] =255;

                        imagesetpixel ( $src_im, $x, $y-2,
                        imagecolorresolvealpha($src_im,
                        $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                    }

                    if($y<$Ye2) {
                        $a = imagecolorsforindex($src_im,
                        ImageColorAt($src_im, $x+2,$y+1 ) );

                        $a['red']   +=60;
                        $a['green'] -=30;
                        $a['blue']  -=30;

                        if($a['red']  <0)$a['red']  =0; elseif($a['red']  >255)$a['red']  =255;
                        if($a['green']<0)$a['green']=0; elseif($a['green']>255)$a['green']=255;
                        if($a['blue'] <0)$a['blue'] =0; elseif($a['blue'] >255)$a['blue'] =255;

                        imagesetpixel ( $src_im, $x-2, $y-1,
                        imagecolorresolvealpha($src_im,
                        $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                    }
                }
            }
            break;
        }
        case IMG_FILTER_REFRACTOR: {
            $patternsize = $arg1;
            if($patternsize==0)$patternsize=20;
            elseif($patternize>$srcsx*2 or $patternize>$srcsy*2)$patternize = (($srcsx+$srcsy)/2)*2;

            $M = hypot($srcsx,$srcsy) / 2;

            for ($y = 0; $y<$srcsy;$y++ ) {
                for ($x = 0; $x<$srcsx;$x++) {

                    $d1 = $x + fmod($x,($patternsize/255)*$M/2);
                    $d2 = $y + fmod($y,($patternsize/255)*$M/2);

                    if ($d1 >= $srcsx-1) $d1 = $srcsx-1;
                    if ($d2 >= $srcsy-1) $d2 = $srcsy-1;

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $d1,$d2 ) );

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );

                }
            }
            break;
        }
        case IMG_FILTER_COLOR_THRESHOLD: {
            $level = ceil(sqrt($arg1));
            if($level==0)$level=12; if($level<3)$level=2; elseif($level>64)$level=64;

            ////////////
            if($srcsx>$srcsy) { $pw=$level; $ph=ceil(($srcsy*$pw)/$srcsx);
            } else { $ph=$level; $pw=ceil(($srcsx*$ph)/$srcsy); }

            $tmpim = @imagecreatetruecolor($pw, $ph);
            @imagecopyresampled($tmpim,$src_im, 0,0,0,0, $pw, $ph,$srcsx, $srcsy );

            $impc = array(); // indexes, truecolors
            $impidxa = array('r'=>array(),'g'=>array(),'b'=>array(),'a'=>array()); // index color presentations

            for ($y = 0; $y<$ph;$y++) {
                for ($x = 0; $x<$pw;$x++) {

                    $impidx = imagecolorsforindex($tmpim, ImageColorAt($tmpim, $x,$y ) );
                    if( !in_array($impidx['red'],$impidxa['r']) )  $impidxa['r'][] = $impidx['red'];
                    if( !in_array($impidx['green'],$impidxa['g']) )  $impidxa['g'][] = $impidx['green'];
                    if( !in_array($impidx['blue'],$impidxa['b']) ) $impidxa['b'][] = $impidx['blue'];
                    if( !in_array($impidx['alpha'],$impidxa['a']) ) $impidxa['a'][] = $impidx['alpha'];

                }
            }
            imagedestroy( $tmpim );
            asort($impidxa['r'], SORT_NUMERIC ); asort($impidxa['g'], SORT_NUMERIC );
            asort($impidxa['b'], SORT_NUMERIC ); asort($impidxa['a'], SORT_NUMERIC );
            //print_r( $impidxa );

            $chs = round( 256/pow(2, log($level,2)/3.125 ) );
            for ($y = 0; $y<$srcsy;$y++) {
                for ($x = 0; $x<$srcsx;$x++) {

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );

                    foreach($impidxa['b'] as $impidxa_cb){
                        if(  abs($impidxa_cb-$a['blue']) > abs(next($impidxa['b'])-$a['blue']) ) ;
                        else { $a['blue']=$impidxa_cb; break; }
                    }

                    foreach($impidxa['g'] as $impidxa_cg){
                        if(  abs($impidxa_cg-$a['green']) > abs(next($impidxa['g'])-$a['green']) ) ;
                        else { $a['green']=$impidxa_cg; break; }
                    }

                    foreach($impidxa['r'] as $impidxa_cr){
                        if(  abs($impidxa_cr-$a['red']) > abs(next($impidxa['r'])-$a['red']) ) ;
                        else { $a['red']=$impidxa_cr; break; }
                    }

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_CONTURES: {
            $amount = $arg1;
            if($amount<2)$amount=1; if($amount>100)$amount=100;
            $amount = abs(100-$amount);

            $a=$arg2; // line width
            if($a<2)$a=1; elseif($a>10)$a=10;

            $We = $srcsx -$a;
            $He = $srcsy -$a;
            $ax = $ay = $a;

            $black = imagecolorresolvealpha( $src_im, 0, 0, 0 ,0);

            $tmpim = @imagecreatetruecolor($srcsx,$srcsy);
            @imagecopy($tmpim,$src_im, 0,0,0,0,$srcsx,$srcsy); // oiget pidine

            $bw = (bool) $arg3; //white background
            if($bw) {
                $src_im = @imagecreatetruecolor($srcsx,$srcsy);
                $white = imagecolorresolve( $src_im, 255, 255, 255 );
                imagefill($src_im, 0,0,$white);
            }

            for ($y = 0; $y<$srcsy;$y++){
                $ax = $a;
                for ($x = 0; $x<$srcsx;$x++){

                    $rgb0 = array_sum( imagecolorsforindex($tmpim, ImageColorAt($tmpim, $x,$y ) ) );

                    if($y<$He) {
                        $rgb2 = array_sum( imagecolorsforindex($tmpim, ImageColorAt($tmpim, $x,$y+$ay ) ) );
                    }else {
                        $ay=0;
                        $rgb2 = array_sum( imagecolorsforindex($tmpim, ImageColorAt($tmpim, $x,$y-$ay ) ) );
                    }


                    if($x<$We) {
                        $rgb1 = array_sum( imagecolorsforindex($tmpim, ImageColorAt($tmpim, $x+$ax,$y ) ) );
                    } else {
                        $ax=0;
                        $rgb1 = array_sum( imagecolorsforindex($tmpim, ImageColorAt($tmpim,$x-$ax,$y ) ) );
                    }

                    $rgb3 = array_sum( imagecolorsforindex($tmpim, ImageColorAt($tmpim, $x+$ax,$y+$ay ) ) );

                    // BLACK LINE
                    if( abs( $rgb0 - ($rgb1+$rgb2+$rgb3)/3 ) > $amount ) imagesetpixel($src_im,$x,$y,$black);

                }
            }
            break;
        }
        case IMG_FILTER_CARTOONIZE: {
            imagefilter( $src_im, IMG_FILTER_COLOR_THRESHOLD, 24 );

            $level=96; //color threshold
            $chs = round( 256/pow(2, log($level,2)/3.125 ) );

            $amount=20; //color sensitivity
            $amount = abs(100-$amount);

            $a=4; // line width
            $ad=3;

            $We = $srcsx -5;
            $He = $srcsy -5;
            $WeHe = 0;

            $black = imagecolorresolvealpha( $src_im, 0, 0, 0 ,0);

            for ($y = 0; $y<$srcsy;$y++){
                for ($x = 0; $x<$srcsx;$x++){

                    $rgb = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );
                    $rgb0 = array_sum( $rgb );


                    if($x<$We) {
                        $rgb1 = array_sum( imagecolorsforindex($src_im, ImageColorAt($src_im, $x+$a,$y ) ) ); $WeHe=0;
                    }
                    else { $rgb1 = $rgb0; $WeHe=1; }

                    if($y<$He)
                    $rgb2 = array_sum( imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y+$a ) ) );
                    else { $rgb2 = $rgb0; $WeHe=1; }

                    if( $WeHe == 0 )
                    $rgb3 = array_sum( imagecolorsforindex($src_im, ImageColorAt($src_im, $x+$ad,$y+$ad ) ) );
                    else $rgb3 = $rgb0;

                    // BLACK LINE
                    if( abs( $rgb0 - ($rgb1+$rgb2+$rgb3)/3 ) > $amount ) imagesetpixel($src_im,$x,$y,$black);
                }
            }
            break;
        }
        case IMG_FILTER_THRESHOLD: {
            $Threshold = $arg1;
            if($Threshold==0)$Threshold=127;
            for ($y=0; $y<$srcsy;$y+=1) {
                for ($x=0; $x<$srcsx;$x++) {

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $x,$y ) );
                    $r = round( 0.299*$a['red'] + 0.587*$a['green'] + 0.114*$a['blue'] );
                    //if($r > 1) $r--;else $r=0;
                    if ( $r >= $Threshold ) $v=255; else $v=0;

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im, $v,$v,$v, $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_RASTERIZE: {
            $cell =(int) $arg1;
            if($cell<4)$cell=4;
            elseif($cell>=$srcsx && $cell>=$srcsy) {
                $tmpim = @imagecreatetruecolor(1,1);
                imagecopyresampled($tmpim,$src_im, 0,0,0,0, 1,1, $srcsx,$srcsy);
                imagefilledrectangle ($src_im,0,0,$srcsx,$srcsy,ImageColorAt($tmpim,0,0));
                imagedestroy( $tmpim );
                break;
            }
            $p1w = ceil( $srcsx / $cell );
            $p1h = ceil( $srcsy / $cell );
            $tmpim = @imagecreatetruecolor($p1w, $p1h);
            imagecopyresampled($tmpim,$src_im, 0, 0, 0, 0, $p1w, $p1h, $srcsx, $srcsy);
            imagecopyresized($src_im,$tmpim, 0, 0, 0, 0, $srcsx, $srcsy,$p1w, $p1h);
            imagedestroy( $tmpim );

            break;
        }
        case IMG_FILTER_OLIFY: {
            $amount = $arg1;
            if($amount<1)$amount = 2; elseif($amount>20)$amount = 20;

            for ($y = 0; $y<$srcsy; ++$y) {
                for ($x = 0; $x<$srcsx;++$x) {

                    $nx=$x-(rand(0,$amount))*$amount;
                    $ny=$y-(rand(0,$amount))*$amount;
                    if($nx>$x)$nx=$x-1; if($nx<3)$nx=$x+rand(-2,2);
                    if($ny>$y)$ny=$y-1; if($ny<3)$ny=$y+rand(-2,2);

                    if(($ny-$y)>5)$ny=$y+5;
                    if(($nx-$x)>5)$nx=$x+5;

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $nx,$ny ) );

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_PASTEL: {
            $amount = $arg1;
            if($amount<1)$amount = 2; elseif($amount>10)$amount = 10;
            $amount /= 2;
            for ($y = 0; $y<$srcsy; ++$y) {
                for ($x = 0; $x<$srcsx;++$x) {

                    $nx=$x-(rand(0-$amount,$amount));
                    $ny=$y-(rand(0-$amount,$amount));

                    if($nx>$x)$nx=$x+rand(0-$amount,$amount);
                    if($ny>$y)$ny=$y+rand(0-$amount-5,$amount);

                    if(($ny-$y)>5)$ny=$y+5;
                    if(($nx-$x)>5)$nx=$x+5;

                    $a = imagecolorsforindex($src_im, ImageColorAt($src_im, $nx,$ny ) );

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
        case IMG_FILTER_WATERCOLOR: {
            $tmpim = @imagecreatetruecolor($srcsx, $srcsy);

            $p1w = ( 90 * $srcsx ) /100;
            $p1h = ( 90 * $srcsy ) /100;
            @imagecopy($tmpim,$src_im, 0,0,0,0, $p1w, $p1h);

            imagefilter( $src_im, IMG_FILTER_COLOR_THRESHOLD, 28 );
            imagefilter( $src_im, IMG_FILTER_OLIFY, 1 );

            imagecopyresampled($tmpim,$src_im, 0, 0, 0, 0, $p1w, $p1h, $srcsx,$srcsy);
            imagecopyresampled($src_im,$tmpim, 0, 0, 0, 0,$srcsx,$srcsy,$p1w, $p1h);
            imagedestroy( $tmpim );
            break;
        }
        case IMG_FILTER_ROTOZOOM: {
            // rotozoom by Vincent 'MooZ' Cruz (vcruz@free.fr) (Artweaver, Lua scripting)
            $angle=$arg1;
            if($angle<=0 or $angle>=360)$angle=0;
            $angle=deg2rad($angle);

            $zoom=$arg2; $zoom = $zoom<1.0?1.0: $zoom>10.0?10.0: $zoom;

            $rx=cos($angle)*$zoom;
            $ry=sin($angle)*$zoom;

            $u0=0; $v0=0; $u1=0; $v1=0;

            $tmpim = @imagecreatetruecolor($srcsx,$srcsy);
            @imagecopy($tmpim,$src_im, 0,0,0,0,$srcsx,$srcsy);

            for ($y=0; $y<$srcsy;$y++) {

                $u1=$u0; $v1=$v0;

                for ( $x=0; $x<$srcsx;$x++ ) {
                    $u1=$u1+$rx;
                    $v1=$v1+$ry;

                    if($u1<0)
                    $u=$srcsx-fmod(abs($u1),$srcsx);
                    else
                    $u=fmod($u1,$srcsx);


                    if($v1<0)
                    $v=$srcsy-fmod(abs($v1),$srcsx);
                    else
                    $v=fmod($v1,$srcsy);

                    $a = imagecolorsforindex($tmpim, ImageColorAt($tmpim, $u,$v ) );

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );

                }
                $u0=$u0-$ry; $v0=$v0+$rx;
            }
            break;
        }
        case IMG_FILTER_MIRRORED_FRAME: {
            //mirrored_frame.lua
            $FrameSize = $arg1;
            $seeall = $arg2;
            if($FrameSize==0)$FrameSize=30;
            if($FrameSize > $srcsx/2 ) $FrameSize = $srcsx/2;
            if($FrameSize > $srcsy/2 ) $FrameSize = $srcsy/2;

            $tmpim = @imagecreatetruecolor($srcsx,$srcsy);
            @imagecopy($tmpim,$src_im, 0,0,0,0,$srcsx,$srcsy); // oiget pidine


            if( function_exists( 'imagerotate' )) {
                $src_im = imagerotate ( $src_im, 180,0 );
                return @imagecopyresampled($src_im,$tmpim, $FrameSize,$FrameSize,0,0,
                $srcsx-($FrameSize*2),$srcsy-($FrameSize*2),$srcsx,$srcsy);
            }

            for ($y=0; $y<$srcsy;$y++) {
                for ($x=0; $x<$srcsx;$x++) {

                    $insideframe=0;
                    if( ($x > $FrameSize) and ($x < $srcsx-$FrameSize-1) )
                    if( ($y > $FrameSize) and ($y < $srcsy-$FrameSize-1) )$insideframe=1;

                    if($insideframe==1)
                    $a = imagecolorsforindex($tmpim, ImageColorAt($tmpim, $x,$y ) );
                    else
                    $a = imagecolorsforindex($tmpim, ImageColorAt($tmpim, $srcsx-$x,$srcsy-$y ) );

                    imagesetpixel ( $src_im, $x, $y,
                    imagecolorresolvealpha($src_im,
                    $a['red'],$a['green'],$a['blue'], $a['alpha'] ) );
                }
            }
            break;
        }
    } //end switch
    return TRUE;
}