<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  html5video
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

class cjoHtml5Video {

    public static $mypage = 'html5video';    

    public static function getVideoLink($filename, $params=array(), $return_array=false) {

        global $CJO;

        $valid_params = array(self::$mypage, 'clang', 'width', 'height', 'autoplay', 'controls', 'poster');    
              
        $media = OOMedia::getMediaByName($filename);
        
        if (!OOMedia::isValid($media) || !file_exists($CJO['MEDIAFOLDER']."/".$file)) return false;
        
        $domain                = cjoRewrite::setServerUri();   
        $params[self::$mypage] = $media->getId();
        $params['clang']       = (!empty($params['clang']))  ? (int) $params['clang']  : $CJO['CUR_CLANG'];  
        $params                = self::toHtml5VideoParams($filename, $params);
        
        foreach($params as $key=>$param) {
            if (in_array($key, $valid_params, true)) continue;
            $params[$key] = NULL;
        }
        $params['filename'] = $filename;
        if (!empty($params['preload'])) (bool) $params['preload'];
        if (!empty($params['autoplay'])) (bool) $params['autoplay'];
        if (!empty($params['controls'])) (bool) $params['controls'];

        $params['url'] = cjoRewrite::getUrl($CJO['ARTICLE_ID'], $params['clang'], $params);

        return  ($return_array) 
                    ? $params
        			: '<a rel="videobox" href="'.$params['url'].'" name="%copyright%"
                          title="%title%" class="imagelink zoom video">%image%<span></span>
                          %description%</a>'."\r\n";
    }
        
    /**
     * Returns HTML 5 code for embeding a video.
     * @param string $filename
     * @param array $params
     * @return string html-code to display a media as an image or an icon
     * @example  echo Media::toHtml5Video(test.mp4, ' array('width'=>800, 'height'=> 600));
     * @access public
     */
    public static function toHtml5VideoParams($filename, $params=array()) {

        global $CJO, $I18N;

        $sources  = array(); 
        $media    = OOMedia::getMediaSetByName($filename);
        $codecs   = $CJO['ADDON']['settings'][self::$mypage]['CODECS'];
        $autoplay = (int) $CJO['ADDON']['settings'][self::$mypage]['DEFAULT_AUTOPLAY'];
                    
        if (!OOMedia::isValid($media['video'])) return array();
        
        if (OOMedia::isValid($media['image'])) {
            $default_width = $media['image']->getWidth();
            $default_height = $media['image']->getHeight(); 
            $poster = $media['image']->getFullPath();        
        }
        else {
            $default_width = $CJO['ADDON']['settings'][self::$mypage]['DEFAULT_WIDTH'];
            $default_height = $CJO['ADDON']['settings'][self::$mypage]['DEFAULT_HEIGHT']; 
            $poster = null;    
        }
        
        $params['width']    = (!empty($params['width'])) ?  (int) $params['width']  : $default_width;
        $params['height']   = (!empty($params['height'])) ? (int) $params['height'] : $default_height;   
        $params['preload']  = (!empty($params['preload']) && $params['preload'] != 'false')   ? 'auto' : 'metadata';      
        $params['controls'] = (!empty($params['controls']) && $params['controls'] != 'false') ? 'controls' : 'controls';
        
        if (!empty($params['autoplay'])) {
            $params['autoplay'] = ($params['autoplay'] != 'false' || $params['autoplay'] === true) ? 'autoplay' : null;
        } elseif (empty($autoplay)) {
            $params['autoplay'] =  'autoplay';
        } else {
            $params['autoplay'] = null;
        }
            
        if (!empty($params['poster']) && $params['poster'] != 'false') {
            $params['poster'] = (file_exists($params['poster'])) ?  $params['poster'] : $poster; 
        } else {
            $params['poster'] = null;
        }
        
        $params['title'] = $media['video']->getTitle();  
        $params['description'] = $media['video']->getDescription($params['clang']);
        $params['copyright'] = $media['video']->getCopyright(); 
  
        foreach($media as $temp) {
            $mime = $temp->getType();
            if (!OOMedia::isVideo($temp->getFileName(),true) || empty($codecs[$mime])) continue;
            $sources[$mime] = array($temp->getFullPath(), $codecs[$mime]);
        }
        
        $i = 0;  
        $params['sources'] = array();
        foreach($sources as $source) {
            $params['sources']['path'][$i] = $source[0];
            $params['sources']['type'][$i] = $source[1];
            $i++;
        }
        
        if (in_array($media['video']->getType(),array('video/x-flv','video/mp4'))) {
            $params['fallback_url'] = $media['video']->getFullPath();
        }
        
        $params['addon_path'] = $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage;

        return $params;
    }
    
    public static function processVideo($filename = false) {

        global $CJO;

        $content = '';
        $params  = array();
        
        $params['width']    = cjo_get('width', 'string');
    	$params['height']   = cjo_get('height', 'string');    	
        $params['preload']  = cjo_get('preload', 'string', 'none');  
        $params['autoplay'] = cjo_get('autoplay', 'string', null);         
        $params['controls'] = cjo_get('controls', 'string', true);
        $params['clang']    = cjo_get('clang', 'cjo-clang-id', 0);   
        $domain             = cjoRewrite::setServerUri();    

        if (!$filename) {
            $id = cjo_get(self::$mypage, 'int');
            $media = OOMedia::getMediaById($id);
        }
        else {
             $media = OOMedia::getMediaByName($filename);
        }

    	$tmpl_path = $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/video.'.$CJO['TMPL_FILE_TYPE'];
    	    	
    	$html_tmpl = new cjoHtmlTemplate($tmpl_path);       	   

    	if (!OOMedia::isValid($media)) {
    	    $html_tmpl->fillTemplate('ERROR', array(
    	    						 'NOT_FOUND_LOCATION' => cjoRewrite::getUrl($CJO['NOTFOUND_ARTICLE_ID'], $params['clang']),
    	                             'TITLE'              => 'Error'));
    	}
    	else {
    	    
    	    $params = self::toHtml5VideoParams($media->getFileName(), $params);
    	    
    	    $html_tmpl->fillTemplateArray('SOURCES', $params['sources']);
    	    unset($params['sources']);

    	    if (!empty($params['controls'])) $params['controls'] = ' controls="'.$params['controls'].'"';
    	    if (!empty($params['autoplay'])) $params['autoplay'] = ' autoplay="'.$params['autoplay'].'"';
    	    if (!empty($params['preload']))  $params['preload']  = ' preload="'.$params['preload'].'"';
    	    if (!empty($params['poster']))   $params['poster']   = ' poster="'.$params['poster'].'"';
    	    
    	    $params['title']      = $media->getTitle();
    	    $params['file_name']  = pathinfo($media->getFileName(),PATHINFO_FILENAME);
            $params['addon_path'] = $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage;
    	    $html_tmpl->fillTemplate('TEMPLATE', $params);   	    
    	}

    	$content = $html_tmpl->get(false);

        cjoClientCache::sendArticle(null, $content, 'frontend');
    }
    
    public static function getPiwikURl() {
        
        global $CJO;
        
        $id = cjo_get(self::$mypage, 'int');
        $media = OOMedia::getMediaById($id);
        
        if (!OOMedia::isValid($media)) return false;
        
        $url = cjoRewrite::setServerUri();
        return $url['scheme'].'://'.$url['host'].'/'.$CJO['MEDIAFOLDER'].'/'.pathinfo($media->getFileName(), PATHINFO_FILENAME);
    }
    
}
