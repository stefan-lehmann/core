<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  oauth
 * @version     2.7.2
 *
 * @author      Stefan Lehmann <sl@contejo.com> inspired by Saran Chamling's (saaraan@gmail.com) Facebook Ajax Connect
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

$mypage = 'oauth'; 
    
$source = $CJO['ADDON_PATH'].'/'.$mypage.'/providers';
$destination = $CJO['ADDON_CONFIG_PATH'].'/'.$mypage;

foreach(glob($source.'/*') as $file) {
    if (!is_dir($file)) continue;
    $dir = str_replace($source, '', $file);
    if (!file_exists($destination.$dir)) {
        mkdir($destination.$dir, $CJO['FILEPERM']);
    }
    cjoAssistance::copyFile($file.'/settings.json', 
                            $destination.$dir.'/settings.json');   
}
   
cjoAssistance::copyFile($CJO['ADDON_PATH'].'/'.$mypage.'/oauth.js', 
                        $destination.'/oauth.js');

$CJO['ADDON']['install'][$mypage] = true;