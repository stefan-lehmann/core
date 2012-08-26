<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  upgrade
 * @version     2.7.x
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

class cjojUpgrade {
    
    
    public static function start() {
        self::upgradeHTMLTemplates();
    }
    
    private static function upgradeHTMLTemplates() {
        
        global $CJO;
        
        $path = $CJO['ADDON']['settings']['developer']['edit_path'].'/'.$CJO['TMPL_FILE_TYPE'].'/*.'.$CJO['TMPL_FILE_TYPE'];
        $replace = array();
        
        foreach(self::rglob($path) as $file) {
            
            $content = file_get_contents($file);
            
            $replace =             
             array('\{CJO_VALUE_(\d+)\}'            =>  'CJO_VALUE[$1]',
                   '\{VALUE_(\d+)\}'                =>  'VALUE[$1]', 
                   '\{CJO_MEDIALIST_(\d+)\}'        =>  'CJO_MEDIALIST[$1]', 
                   '\{CJO_MEDIALIST_BUTTON_(\d+)\}' =>  'CJO_MEDIALIST_BUTTON[$1]', 
                   '\{CJO_MEDIA_BUTTON_(\d+)\}'     =>  'CJO_MEDIA_BUTTON[$1]', 
                   '\{CJO_FILE_(\d+)\}'             =>  'CJO_MEDIA[$1]', 
                   '\{CONTEJO\}'                    =>  '[[CJO_IS_CONTEJO]]',  
                   '\{WYMEDITOR\}'                  =>  'CJO_WYMEDITOR[1]',
                   '\{HORIZONTAL_IMG\}'             =>  array('IMAGE_LIST_BUTTON[1]', 'CJO_IMAGE_LIST[1]' ),
                   '\{VERTICAL_IMG\}'               =>  array('IMAGE_LIST_BUTTON[2]', 'CJO_IMAGE_LIST[2]'),
                   '\{i18n_msg: *\'?(\w)\'?\}'      =>  '[translate: $1]',
                   '\{([^ |^\}]+)\}'                =>  '[[$1]]',
                   '###([^#]+)###'                  =>  '[translate: $1]');
            
             
            foreach($replace as $pattern=>$replacement){ 
                if (is_array($replacement)) {
                    $replacement = (preg_match('/input\.'.$CJO['TMPL_FILE_TYPE'].'$/i',$matches)) ? $replacement[0] : $replacement[1];
                }
                $content = preg_replace('/'.$pattern.'/', $replacement, $content);
                
            }
            rename($file, $file.'_'.strftime('%Y-%m-%d_%H-%m'));
            cjoGenerate::putFileContents($file,$content);
        }

                
    } 
    
    
    function rglob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)  {
            $files = array_merge($files, self::rglob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }
    
}