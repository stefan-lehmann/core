<?php
/**
 * This file is part of CONTEJO ADDON - CHANNEL LIST
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_channel_list
 * @subpackage 	classes
 * @version   	SVN: $Id: class.channellist.inc.php 1037 2010-11-17 13:47:55Z s_lehmann $
 *
 * @author 		Stefan Lehmann <sl@contejo.com>
 * @copyright	Copyright (c) 2008-2011 CONTEJO. All rights reserved.
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

class cjoFilmList {

    private static $mypage = 'channellist';
    private static $current = array();    
    private static $packages = array();      
    private static $article_id = 231;  
    
    public static function getPackageParams($package_id) {
        
        $packages = self::getPackagesArray();
        $package = $packages[$package_id];
        $package['url'] = './'.$package['symbol'];
        
        if (!empty($package['media'])) 
            $package['media'] = OOMedia::toThumbnail($package['media'],false,array('crop_num'=> '-'));
            
        return $package;
    }
    
    public static function getPackagesArray(){
        
        global $CJO;
        
        if (!empty(self::$packages)) return self::$packages;
        
        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_CHANNELPACKAGES." ORDER BY id";
        $packages = $sql->getArray($qry);

        self::$packages = array();

        foreach($packages as $package) {
            self::$packages[$package['id']] = $package;
        }
        
        return self::$packages;
    }   
    
    public static function getFilmList($package_filter=false, $article_id=false) {
        
        global $CJO;

        $article_id     = !$article_id ? self::$article_id : $article_id;
        $items          = array();
        $results        = array();
        $trailer        = false;
        $hd             = 0;
        $current_symbol = '';
        
        $sql = new cjoSql();
            
        if (preg_match ('/[^\/]*(?=\.\d+\.\d+\.html)/i', cjo_server('REQUEST_URI', 'string'), $matches)) {

            
            $qry = "SELECT id FROM ".TBL_CHANNELPACKAGES." WHERE symbol = '".strtolower($matches[0])."' LIMIT 1";
            $sql->setQuery($qry);           
            if ($sql->getRows() == 1) {
                $current_id = $sql->getValue('id');
                $current_symbol = strtolower($matches[0]);
            }
        } 

        $sql->flush();
        $qry = "SELECT * FROM ".TBL_CHANNELPACKAGES." WHERE symbol != 'hdpaket' ORDER BY prior";
        $packages = $sql->getArray($qry);

        foreach($packages as $package) {
            
            if ($package_filter != false && $package_filter != $package['id']) continue;
            
            if ($package_filter != false) $link = '<a href="contejo://'.$article_id.'" '.
                                                  'class="filmlist_link" rel="package'.$package['id'].'" '.
                                                  'title="[translate: filmlist_to_filmlist]">'.
                                                  '[translate: filmlist_to_filmlist]</a>';   
            
            
            $sql->flush();
            $qry = "SELECT * FROM ".TBL_FILM_LIST." WHERE `packages` REGEXP '([:|:]|^)".$package['id']."([:|:]|$)'";
            $temp = $sql->getArray($qry);

            if ($sql->getRows() > 0) $results[$package['id']] = $temp;
        }
        
        $unique = array();
        $counter = 0;
        foreach($results as $id => $package) {
            if (empty($current_symbol) && !isset($cookie)) $cookie = cjo_cookie('VF_FILMLIST_FILTER','int', $id);
            $counter2 = 0;
            foreach($package as $key => $result) {
                
                if (!$result['poster'] || 
                    !file_exists($CJO['MEDIAFOLDER'].'/mam/cover/'.$result['poster']) ||
                    isset($unique[$result['id']])) {
                    unset($package[$id][$key]);
                    continue;
                }
                $unique[$result['id']]          = true;
                $items[$counter]                = $result;     
                $items[$counter]['id']          = $result['id'];                               
                $items[$counter]['parsed_name'] = cjoRewrite::parseArticleName($result['title']);  
                $items[$counter]['url']         = './'. $items[$counter]['parsed_name'].'.'.self::$article_id.'.'.$CJO['CUR_CLANG'].'.html';         
                $items[$counter]['poster']      = $CJO['MEDIAFOLDER'].'/mam/cover/'.$result['poster'];
                $items[$counter]['trailer']     = glob($CJO['MEDIAFOLDER'].'/mam/trailer/'.$items[$counter]['parsed_name'].'.*');    
                
                if ($result['hdcontent']) $hd++;
                
                if (empty(self::$current) && $cookie == $id && $counter2 == 3 ) {
                    $default_item = $items[$counter];
                    $current_key = $counter;
                }
                
                if ($current_symbol == $items[$counter]['parsed_name']) {
                    self::$current = $items[$counter];
                    $current_key = $counter;  
                 }
                $counter2++;
                $counter++;
            }
        }
        
        if (empty(self::$current)) {
            self::$current = $default_item;
        }
        if (!empty($items[$current_key])) $items[$current_key]['current'] = true;
      
        $filter = $package_filter == false ? self::getFilmListFilter($results, $current_id, $hd) : '';

        return sprintf('<div class="vf_filmlist"><div class="vf_scrollouter"><div class="vf_scrollcontainer"><ul>%s</ul></div></div>%s</div>%s'.
                       '<script type="text/javascript" src="%s"></script>%s'."\r\n",
                       self::getListOutput($items), 
                       $link,   
                       $filter,                       
                       $CJO['FRONTPAGE_PATH'].'/js/filmlist.js',
                       self::getFilmListTrailerScript());    
    }
    
    private static function getFilmListFromArticle() {
        
        global $CJO;
        
        $items        = array();
        $results      = array();
        $trailer      = false;
        $counter      = 0;
        
        preg_match ('/[^\/]*(?=\.\d+\.\d+\.html)/i', cjo_server('REQUEST_URI', 'string'), $matches);  
        $current_symbol = strtolower($matches[0]);
        
        $parent = OOArticle::getArticleById($CJO['ARTICLE_ID']);
        
        if (!OOArticle::isValid($parent)) return false;
        
        foreach($parent->getChildren(true) as $article) {
            
            $slice = OOArticleSlice::getFirstSliceForArticle($article->getId());
            
            do {
                if (!$slice->getModulTyp() != '' ||
                    !$slice->getFile(1) || 
                    !file_exists($CJO['MEDIAFOLDER'].'/'.$slice->getFile(1)) ||
                        isset($unique[$slice->getId()])) {
                        continue;
                }
                    
                $unique[$slice->getId()]        = true;     
                $items[$counter]['id']          = $slice->getId();
                $items[$counter]['title']       = trim($slice->getValue(2));  
                $items[$counter]['subtitle']    = $slice->getValue(3) ? ' <span>'.$slice->getValue(3).'</span>'  : '';             
                $items[$counter]['parsed_name'] = cjoRewrite::parseArticleName(trim($slice->getValue(2)));  
                $items[$counter]['url']         = './'. $items[$counter]['parsed_name'].'.'.$parent->getId().'.'.$CJO['CUR_CLANG'].'.html';         
                $items[$counter]['poster']      = OOMedia::toThumbnail($slice->getFile(1), false, array('width'=>129,'height'=>194,'get_src'=>true,'crop_auto'=>true));
                $items[$counter]['trailer']              = array($slice->getFile(2));            
                $items[$counter]['is_package']           = false;
                $items[$counter]['summary_long']         = $slice->getValue(1);
                $items[$counter]['year']                 = $slice->getValue(4);
                $items[$counter]['actors_display']       = $slice->getValue(5);
                $items[$counter]['directors_display']    = $slice->getValue(6);
                $items[$counter]['producers_display']    = $slice->getValue(7);                         
                $items[$counter]['country_of_origin']    = $slice->getValue(8);
                $items[$counter]['display_run_time']     = $slice->getValue(9);
                $items[$counter]['copyright']            = $slice->getValue(10);
                
                if (empty(self::$current) && $counter == 3 ) {
                    $default_item = $items[$counter];
                    $current_key = $counter; 
                }
                
                if ($current_symbol == $items[$counter]['parsed_name']) {
                    self::$current = $items[$counter];
                    $current_key = $counter;  
                }
                $counter++;
                
                $slice = $slice->getNextSlice();
            } while (OOArticleSlice::isValid($slice));
        }
        
        if (empty(self::$current)) self::$current = $default_item;
        if (!empty($items[$current_key])) $items[$current_key]['current'] = true;   
        
        return sprintf('<div class="vf_filmlist"><div class="vf_scrollouter"><div class="vf_scrollcontainer"><ul>%s</ul></div></div></div>'.
               '<script type="text/javascript" src="%s"></script>%s'."\r\n",
               self::getListOutput($items),                    
               $CJO['FRONTPAGE_PATH'].'/js/filmlist.js',
               self::getFilmListTrailerScript());  
    }
    
    private static function getListOutput($items) {
        
        $output = '';

        if (!is_array($items)) return false;
        
        foreach($items as $key => $item) {    
            $output .= sprintf("\t".'<li id="item_%s"%s><a href="%s" title="%s"><span><img src="./page/img/blank.gif" alt="%s" /></span><strong>%s</strong></a></li>'."\r\n",
                                 $item['id'],
                                 self::getFilmListItemClass($item),            
                                 $item['url'],
                                 $item['title'],
                                 $item['poster'],
                                 //$item['title'],
                                 $item['title']);                                     
        }
 
        return $output;  
    }
    
    private static function getFilmListFilter($results, $current_id, $hd) {
        
        global $CJO;
        
        if (empty($current_id)) $current_id = cjo_cookie('VF_FILMLIST_FILTER','int');

        $select = new cjoSelect();
        $select->setMultiple(false);
        $select->setSize(1);            
        $select->setName('vf_filmlist_filter');
        $select->setStyle('class="vf_channellist_filter"');
        $select->setSelectExtra('title="[translate: filmlist_filter]"');
        
        $select->setSelected($current_id);
        $select->setDisabled('');        
        
        $select->addOption('[translate: filmlist_filter_all]','0');
        $select->addOption('','2000');
        $sql = new cjoSql();
        $qry = "SELECT name, id, symbol
                FROM ".TBL_CHANNELPACKAGES." 
                WHERE (symbol LIKE '%paket' 
                OR symbol LIKE 'highlights'
				OR symbol LIKE '%videothek'                
                OR symbol LIKE '-')                 
                ORDER BY prior";
        $packages = $sql->getArray($qry); 
        
        foreach($packages as $package) {
            if ($package['symbol'] == 'hdpaket') {
                if ($hd < 8) continue;
                $package['name'] = 'HD';
            }
            if ($package['symbol'] == '-') {
                $select->setDisabled($package['id']);
                $package['name'] = '';
            }
            elseif (count($results[$package['id']]) < 1 &&
                    $package['symbol'] != 'hdpaket') {
                continue;
            }
            $select->addOption($package['name'],$package['id']);
        }
    	return $select->get();
    }
    
    private static function getFilmListTrailerScript($json=false) {
        
        global $CJO;
        
        if (empty(self::$current['trailer'][0])) return '';
        
        $trailer = '';
        
        foreach(self::$current['trailer'] as $video) {
            if (!OOMedia::isVideo($video, true)) continue;
            $trailer = cjoHtml5Video::getVideoLink(pathinfo(realpath($video), PATHINFO_BASENAME), 
                                                   array('autoplay' => 'true', 'controls' => 'true'), 
                                                   true); 
            
            break;
        } 
        
        if ($json) return $trailer;

        return sprintf("\r\n".
                       '<script type="text/javascript">/*'.
                       '<![CDATA[*/  var VF_FILMLIST_TRAILER = %s; /*]]>*/'.
                       '</script>',
                       stripslashes(json_encode($trailer)));
                       
    }
    
    private static function getFilmListItemClass(&$item) {

        $class = array();
        if ($item['current'])            $class[] = 'current default';     
        if (!empty($item['trailer'][0])) $class[] = 'trailer';               
        if ($item['packages'])           $class[] = preg_replace('/^|\|/',' package', $item['packages']);
        
        $class[] = $item['type'];  
        $class = implode(' ', $class);          
        return !empty($class) ? '  class="'.$class.'"' : ''; 
    }
    
    private static function getChannelListItemStyle(&$item, $type='sprite_small') {
        global $CJO;
        return sprintf('background-image:url(%s);background-position:%s',
                       $CJO['MEDIAFOLDER'].'/'.$CJO['ADDON']['settings'][self::$mypage][$item['type'].'_'.$type],
                       self::getPosition($item['id']));
    }
    
    private static function getFilmCover() {
        global $CJO;
        return sprintf('<div class="vf_film_cover" title="%s"><img src="%s" alt="%s" />%s</div>'."\r\n",   
                       self::$current['title'],
                       self::$current['poster'],
                       self::$current['title'],
                       self::$current['copyright']); 
    }
    
    private static function getPackageLogo() {
        
        $media =  self::$current['media'] ? OOMedia::toThumbnail(self::$current['media'],false,array('crop_num'=> '-')) : '';

        return sprintf('<div class="vf_packagelogo" ><a href="./%s" class="imagelink" title="%s">%s</a></div>'."\r\n",
                       self::$current['symbol'],
                       self::$current['name'],   
                       $media); 
    }

    private static function getChannelPackageLogo() {
        
        $packages = cjoAssistance::toArray(self::$current['packages']);
        
        if (!self::$current['packages'] || in_array(14, $packages)) return '';

        $output = '<p class="vf_channelpackage">';

        foreach($packages as $id) {
            if ($id == 12) continue;
            $param = self::getPackageParams($id);
            $output .= sprintf('<a href="%s" class="package%s" title="%s">%s</a>'."\r\n",
                               $param['url'],
                               $id,
                               $param['name'],
                               $param['media']); 
        }
        $output .= '</p>';
        return $output;
    } 
    
    private static function getPackafeDetailCss() {
        
        global $CJO;
        
        $packages = self::getPackagesArray();
        
        $content  = '<style type="text/css"><!-- @media screen {';
        $content .= '#vf_content .vf_channellogo{width:170px;height:170px;margin: -10px auto 0}'."\r\n";
        $content .= '#vf_content .vf_channel_note{display: block;font-size: 85%; margin:0 0 -9px 3px; }'."\r\n";        

        foreach($packages as $id=>$package) {
            $ypos = 100*($id-1);
            $content .=  '#vf_content .vf_channelpackage.package'.$id.' {background-position: 0 -'.$ypos.'px;}'."\r\n"; 
        }  
        
        $content .= '} --></style>'."\r\n"; 
            
        return $content;
    }
    
    private static function getFilmTitle() {
        return !empty(self::$current['subtitle']) ? self::$current['title'].self::$current['subtitle'] : self::$current['title'];
    }
    
    private static function getFilmText() {
        $text = self::$current['summary_long'] ? self::$current['summary_long'] : '[translate: no_filmlist_description]';
        $text = strpos($text,'<p>') !== false ? $text : '<p>'.$text.'</p>';
        $text .= self::getFacts();
        
        return $text;
    }

    private static function getFacts() {
        
        $facts = array();
                
        $facts['filmlist_year']                 = self::$current['year']; 
        $facts['filmlist_actors_display']       = self::$current['actors_display'];
        $facts['filmlist_directors_display']    = self::$current['directors_display'];
        $facts['filmlist_producers_display']    = self::$current['producers_display'];                         
        $facts['filmlist_country_of_origin']    = self::$current['country_of_origin'];
        $facts['filmlist_display_run_time']     = self::$current['display_run_time'];
        $facts['filmlist_copyright']            = self::$current['copyright'];
        $facts = array_diff($facts, array(''));
        
        foreach($facts as $key=>$fact) {
            if (empty($fact)) { unset($facts[$key]); continue; }
            $facts[$key] = '<strong>[translate: '.$key.']:</strong> '.$fact;
        }

        return empty($facts) ? '' : '<p>'.implode('<br/>',$facts).'</p>';
    }
    
    private static function getHeadline() {
        return '[translate: filmlist_our_movies_at_vtv]';
    }
    
    private static function getCurrentItem() {
        
        global $CJO;
        
        $id = str_replace('item_','', cjo_get('ajax','string'));
        
        if (strlen($id) > 16) {
            
            $sql = new cjoSql();
            $qry = "SELECT * FROM ".TBL_FILM_LIST." WHERE `id`='".$id."' LIMIT 1";
            $results = $sql->getArray($qry);
            
            if ($sql->getRows() != 1) return false;
            
            self::$current                = $results[0];                              
            self::$current['parsed_name'] = cjoRewrite::parseArticleName($results[0]['title']);        
            self::$current['trailer']     = glob($CJO['MEDIAFOLDER'].'/mam/trailer/'.self::$current['parsed_name'].'.*');
            self::$current['poster']      = $CJO['MEDIAFOLDER'].'/mam/cover/'.$results[0]['poster'];  

        }
        else {
            
            $slice = OOArticleSlice::getArticleSliceById($id);
            
            if (!OOArticleSlice::isValid($slice)) return false;

            self::$current['id']          = $slice->getId();
            self::$current['title']       = trim($slice->getValue(2));  
            self::$current['subtitle']    = $slice->getValue(3) ? ' <span>'.$slice->getValue(3).'</span>'  : '';             
            self::$current['parsed_name'] = cjoRewrite::parseArticleName(trim($slice->getValue(2)));  
            self::$current['poster']      = OOMedia::toThumbnail($slice->getFile(1), false, array('width'=>129,'height'=>194,'get_src'=>true,'crop_auto'=>true));
            self::$current['trailer']              = array($slice->getFile(2));            
            self::$current['is_package']           = false;
            self::$current['summary_long']         = $slice->getValue(1);
            self::$current['year']                 = $slice->getValue(4);
            self::$current['actors_display']       = $slice->getValue(5);
            self::$current['directors_display']    = $slice->getValue(6);
            self::$current['producers_display']    = $slice->getValue(7);                         
            self::$current['country_of_origin']    = $slice->getValue(8);
            self::$current['display_run_time']     = $slice->getValue(9);
            self::$current['copyright']            = $slice->getValue(10);
        }

        return true;
    }
    
    public static function getJsonForCurrent() {

        if (self::getCurrentItem()) {
            $data = array('cover'    => self::getFilmCover(),
                          'package'  => self::getChannelPackageLogo(),
                          'title'    => self::getFilmTitle(),
                          'text'     => self::getFilmText(),
                          'trailer'  => self::getFilmListTrailerScript(true),            
                          'error'    => false);
            }
        else {
            $data = array('message' => 'Upps, es ist ein Fehler aufgetreten. Wir bitten um Entschuldigung',
                          'error'   => 1);
        } 
        
        cjoClientCache::sendArticle(false, addslashes(json_encode($data)), 'frontend', true);
    }
    
    public static function replaceVars($params) {

    	global $CJO;

    	$content = $params['subject'];
    	$replace = array();

    	if (!$CJO['CONTEJO'] && strpos($content,'VF_FILMLIST[') !== false) {
    	    
    	    if (preg_match('/VF_FILMLIST\[([^\]]*)\]/', $content, $matches)) {
    	        $replace[$matches[0]] = $CJO['ARTICLE_ID'] == 232 
    	                              ? self::getFilmListFromArticle() 
    	                              : self::getFilmList($matches[1]); 
    	    }
   		    
   		    if (strpos($content,'VF_FILMLIST_HEADLINE[]') !== false)   	    
    		    $replace['VF_FILMLIST_HEADLINE[]'] = self::getHeadline();
    		
    		if (strpos($content,'VF_FILMLIST_COVER[]') !== false)      
    		    $replace['VF_FILMLIST_COVER[]'] =  self::getFilmCover();
    		
    		if (strpos($content,'VF_FILMLIST_PACKAGE_LOGO[]') !== false)  
    		    $replace['VF_FILMLIST_PACKAGE_LOGO[]'] = self::getChannelPackageLogo();
    		
    		if (strpos($content,'VF_FILMLIST_TITLE[]') !== false)  
    		    $replace['VF_FILMLIST_TITLE[]'] = self::getFilmTitle();
    		    
    		if (strpos($content,'VF_FILMLIST_TEXT[]') !== false)  
    		    $replace['VF_FILMLIST_TEXT[]'] = self::getFilmText(); 
    		
    	    $search = array_keys($replace);
        	$content = str_replace($search, $replace, $content);       	
    	}
    	return $content;
    }
}