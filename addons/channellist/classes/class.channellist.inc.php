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

class cjoChannelList {

    private static $mypage = 'channellist';
    private static $current = array();    
    private static $packages = array();      
    
    public static function getPosition($id){
        global $CJO;
        $x = 2000; 
        $y = 2000;
        $id = (int) $id;
        $num = $id <= 148 ? $id : $id-148;
        
        if ($num > 0)
            $x = ($CJO['ADDON']['settings'][self::$mypage]['offset_x'] * $num * -1) + $CJO['ADDON']['settings'][self::$mypage]['offset_x'];
            $y = ($CJO['ADDON']['settings'][self::$mypage]['offset_y'] * $num * -1) + $CJO['ADDON']['settings'][self::$mypage]['offset_y'];
        return $x.'px '.$y.'px';
    }
    
    public static function formatIcon($id){
        global $CJO;
        $css = 'background-position:'.self::getPosition($id);
        $class = $id <= 148 ? '' : ' sprite2';
        return '<div class="channel_preview_small'.$class.'" style="'.$css.'"></div>';
    }
    
    public static function formatPackages($package_ids){
        global $CJO;

        $packages = self::getPackagesArray();
        $results  = array();

        foreach(cjoAssistance::toArray($package_ids) as $id) {
            if ($packages[$id]) $results[] = $packages[$id]['name'];
        }
        return implode(',<br/>', $results);
    }
    
    public static function getPackageParams($package_id) {
        
        $packages = self::getPackagesArray();
        $package = $packages[$package_id];
        $package['url'] = './'.$package['symbol'];
        
        if (!empty($package['media'])) 
            $package['media'] = OOMedia::toThumbnail($package['media'],false,array('width'=> '96'));
            
        return $package;
    }
    public static function getPackageChannels($package_id) {
        
        global $CJO;

        $channels    = array();
        $output      = '';
        $now         = time();
        
        if (!$package_id) return false;
        
        $sql = new cjoSql();
        $qry = "SELECT *, CONCAT('tv') AS type FROM ".TBL_TV_CHANNELS." WHERE status=1 AND `packages` REGEXP '([:|:]|^)".$package_id."([:|:]|$)' AND online_from < '".$now."' AND online_to > '".$now."' ORDER BY prior LIMIT 6";
        $channels = $sql->getArray($qry);
        
        $sql->flush();
        $qry = "SELECT * FROM ".TBL_CHANNELPACKAGES." ORDER BY id";
        $packages = $sql->getArray($qry);
        
        foreach($channels as $key => $channel) {

            $output .= sprintf("\t".'<li class="%s" title="%s"><span style="%s"></span>%s</li>'."\r\n",
                                 self::getChannelListItemClass($channel, $packages), 
                                 $channel['name'],
                                 self::getChannelListItemStyle($channel),                                 
                                 $channel['name']);                                     
        }
        
        return ($output) ? sprintf('<ul class="vf_package_channels">%s</ul>'."\r\n", $output) : '';
    }   

    public static function getPackageSelect($name, $selected, $style = '') {

        global $CJO;

    	$select = new cjoSelect();
        $select->setMultiple(false);
        $select->setName($name);
        $select->setStyle($style);
        $select->setSize(1);
    	$select->setSelected($selected);
        $select->addOption('','');
    	$select->addSqlOptions("SELECT name, id FROM ".TBL_CHANNELPACKAGES." ORDER BY prior");
    	return $select->get();
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
    
    public static function getChannelList() {
        
        global $CJO;

        $channels    = array();
        $output      = '';
        $now         = time();
        
        preg_match ('/[^\/]*(?=\.\d+\.\d+\.html)/i', cjo_server('REQUEST_URI', 'string'), $matches);  
        $current_name = strtolower($matches[0]);
                    
        $sql = new cjoSql();
        $qry = "(SELECT *, CONCAT('tv') AS type FROM ".TBL_TV_CHANNELS." WHERE status=1 AND online_from < '".$now."' AND online_to > '".$now."') UNION 
                (SELECT *, CONCAT('radio') AS type FROM ".TBL_RADIO_CHANNELS." WHERE status=1 AND online_from < '".$now."' AND online_to > '".$now."') ORDER BY type DESC, prior";
        $results = $sql->getArray($qry);

        $sql->flush();
        $qry = "SELECT * FROM ".TBL_CHANNELPACKAGES." ORDER BY id";
        $packages = $sql->getArray($qry);

        foreach($results as $key => $result) {
            $channels[$key]                = $result;  
            $channels[$key]['parsed_name'] = cjoRewrite::parseArticleName($result['name']);  
            $channels[$key]['url']         = $channels[$key]['parsed_name'].'.'.$CJO['ARTICLE_ID'].'.'.$CJO['CUR_CLANG'].'.html';
            $channels[$key]['video']       = $channels[$key]['parsed_name'].'.'.$CJO['ARTICLE_ID'].'.'.$CJO['CUR_CLANG'].'.html';            
            $channels[$key]['is_package']  = false;
            
            if ($key == 0 ) {
                $default_channel = $channels[$key];
                $current_key = 0;
            }
            if ($current_name == $channels[$key]['parsed_name']) {
                self::$current = $channels[$key];
                $current_key = $key;  
                $trailer = self::getChannelTrailer();
            }
        }

        if (empty(self::$current)) {
            foreach($packages as $key => $package) {
                if ($current_name == $package['symbol']) {
                    self::$current = $packages[$key];
                    self::$current['is_package'] = true;
                    unset($current_key);
                    break;
                }
            }
        }

        if (empty(self::$current)) {
            self::$current = $default_channel;
        }
        if (!empty($channels[$current_key])) $channels[$current_key]['current'] = true;
        $total = count($channels);        

        foreach($channels as $key => $channel) {

            $output .= sprintf("\t".'<li class="%s"><a href="%s" style="%s" title="%s">%s</a></li>'."\r\n",
                                 self::getChannelListItemClass($channel,$packages),            
                                 $channel['url'],
                                 self::getChannelListItemStyle($channel),
                                 $channel['name'],
                                 $channel['name']);                                     
        }

        return sprintf('<div class="vf_channellist"><ul>%s</ul></div>%s'.
                       '<script type="text/javascript" src="%s"></script>%s'."\r\n",
                       $output,   
                       self::getChannelListFilter(),                            
                       $CJO['FRONTPAGE_PATH'].'/js/channellist.js',
                       self::getChannelTrailerScript($trailer));    
    }
    
    private static function getChannelListFilter() {
        
        global $CJO;

        $path       = pathinfo(cjo_server('REQUEST_URI','string'));
        $article    = OOArticle :: getArticleById($CJO['ARTICLE_ID']);
        $start_link = cjoRewrite::parseArticleName($article->getName()).'.'.$CJO['ARTICLE_ID'].'.'.$CJO['CUR_CLANG'].'.html';
        $now        = time();
        
        $qry        = "SELECT name, CONCAT(id,':',symbol,'.".$CJO['ARTICLE_ID'].".".$CJO['CUR_CLANG'].".html') AS value 
                        FROM ".TBL_CHANNELPACKAGES." p
                        WHERE symbol NOT LIKE 'highlights'
                        AND symbol NOT LIKE '%videothek'
                        AND (
                            selectable = 0 
                        OR (
                            SELECT id
                            FROM ".TBL_TV_CHANNELS."
                            WHERE packages = p.id
                            AND STATUS =1
                            AND online_from < ".$now."
                            AND online_to > ".$now."
                            LIMIT 1
                            )
                        OR (
                            SELECT id
                            FROM ".TBL_RADIO_CHANNELS."
                            WHERE packages = p.id
                            AND STATUS =1
                            AND online_from < ".$now."
                            AND online_to > ".$now."
                            LIMIT 1
                            )
                        )
                        ORDER BY prior";

        $select = new cjoSelect();
        $select->setMultiple(false);
        $select->setSize(1);            
        $select->setName('vf_channellist_filter');
        $select->setStyle('class="vf_channellist_filter"');
        $select->setSelectExtra('title="[translate: channelList_filter]"');
        
        $select->setSelected(cjo_cookie('VF_CHANNEL_FILTER','string'));
        $select->setDisabled('');        
        
        $select->addOption('[translate: filter_alles]','0:'.$start_link);
        $select->addOption('','');
        $select->addSqlOptions($qry); 
        
        $temp = '';
        if (is_array($select->options[0])) {
            foreach($select->options[0] as $key=>$option) {
                $temp = explode(':',$option[1]);
                $temp = trim($temp[1]);
                if (substr($temp, 0, 1) == '-') {
                    $select->setDisabled($option[1]);
                    $select->options[0][$key][0] = '';
                    continue;
                }

                if (isset($path['basename']) &&
                    !empty($temp) &&
                    strpos($path['basename'],$temp) !== false) {
                    $select->resetSelected();
                    $select->setSelected($option);
                }
            }
        }
    	return $select->get();
    }
    
    private static function getChannelTrailer($trailer = false){
        
        $now = time();
        
        if ($trailer === false) {
            for($i=1;$i<=2;$i++) {
                $video = self::getChannelTrailer($i);
                if ($video) break;
            }
            return $video;
        }

        if (empty(self::$current['video'.$trailer]) ||
            self::$current['video'.$trailer.'_online_from'] > $now ||
            self::$current['video'.$trailer.'_online_to'] < $now) return false;

            return cjoHtml5Video::getVideoLink(self::$current['video'.$trailer], 
                                                   array('autoplay' => 'true'), 
                                                   true);
        
        
    }
    
    private static function getChannelTrailerScript($trailer) {

        return $trailer ? sprintf("\r\n".
                                   '<script type="text/javascript">/*'.
                                   '<![CDATA[*/ var VF_CHANNEL_TRAILER = %s; /*]]>*/'.
                                   '</script>',
                                   json_encode($trailer))
                       : ''; 
    }
    
    private static function getChannelListItemClass(&$item, $packages) {
        
        $class = array();
        if ($item['current'])                       $class[] = 'current';
        if (!$item['pay'] && $item['type'] == 'tv') $class[] = 'package10';
        if ($item['pay'] && $item['type'] == 'tv')  $class[] = 'package11';        
        if ($item['hd'])                            $class[] = 'package12';        
        if ($item['packages'])                      $class[] = preg_replace('/^|\|/',' package', $item['packages']);
        
        $class[] = $item['type'];      
                                 
        return implode(' ', $class);
    }
    
    private static function getChannelListItemStyle(&$item, $type='sprite_small') {
        global $CJO;
        
        $file = $CJO['MEDIAFOLDER'].'/'.$CJO['ADDON']['settings'][self::$mypage][$item['type'].'_'.$type];
        
        if ($item['id'] > 148 ) $file = str_replace('sprite_', 'sprite2_', $file);
        
        if (!file_exists($file)) return false;
        list($width, $height, $type, $attr) = getimagesize($file);
        
        return sprintf('background-image:url(%s);background-position:%s;background-size:%spx %spx;',
                       $file, self::getPosition($item['id']), $width, $height);
    }
    
    private static function getChannelLogo() {
        return sprintf('<div class="vf_channellogo" style="%s" title="%s"></div>'."\r\n",   
                       self::getChannelListItemStyle(self::$current, 'sprite_big'),
                       self::$current['name']); 
    }
    
    private static function getPackageLogo() {
        
        $media =  self::$current['media'] ? OOMedia::toThumbnail(self::$current['media'],false,array('crop_num'=> '-')) : '';

        return sprintf('<div class="vf_packagelogo" ><a href="./%s" class="imagelink" title="%s">%s</a></div>'."\r\n",
                       self::$current['symbol'],
                       self::$current['name'],   
                       $media); 
    }

    private static function getChannelPackageLogo() {
        
        if (!self::$current['packages'] || self::$current['packages'] == 14) return false;
        
        $output = '<p class="vf_channelpackage"><span class="vf_channel_note">[translate: package_included_in]</span>';

        foreach(cjoAssistance::toArray(self::$current['packages']) as $id) {
            
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
    
    private static function getChannelTitle() {
        return self::$current['name'];
    }
    
    private static function getChannelText() {
        $text = self::$current['description'] ? self::$current['description'] : '[translate: no_channel_description]';
        return strpos($text,'<p>') !== false ? $text : '<p>'.$text.'</p>';
    }
    
    private static function getChannelGallery(){
        
        global $CJO;
        
        $medialist = cjoAssistance::toArray(self::$current['medialist'],',');
        if (empty($medialist)) return '';
        
        $params = array();
        //Thumbnail Größen-Einstellungen
        $params['img']['crop_auto'] = 1;
        $params['img']['width'] = 88;
        $params['img']['height'] = 88;
        
        //Einstellung für Bildunterschriften
        $params['des']['settings'] = "-";
        //Zoom-Image Größen-Einstellungen
        $params['fun']['zoom']['crop_num'] = 1;
        //Zoom-Image-Funktion einschalten
        $params['fun']['settings'] = 1;
        
        
        $content = '';
        $i = 0;
        foreach($medialist as $key=>$filename) {
             
            if (!file_exists($CJO['MEDIAFOLDER'].'/'.$filename)) continue;
            $image = OOMedia::toResizedImage($filename, $params, true, false);
            if (!$image) continue;
            
            $i++;
            if ($i == 5) {
                $i = 0;
                $content .= '</div><div>';
            }
            $content .= $image;
        }
        return $content;
    }

    public static function replaceCanonicalUrl($content) {
        
        global $CJO;
        
		if (strpos('rel="canonical"', $content) === false) return $content;
		
        if (!preg_match ('/[^\/]*(?=\.\d+\.\d+\.html)/i', cjo_server('REQUEST_URI', 'string'), $matches)) 
            return $content;  
        
        $current_name = strtolower($matches[0]);
        $url = $current_name.'.'.$CJO['ARTICLE_ID'].'.'.$CJO['CUR_CLANG'].'.html';
        
        return preg_replace('/<link[^>]+rel="canonical"[^>]+href="[^"]+"[^>]+>/i',
                                '<link rel="canonical" href="'.$url.'" />',
                                $content);
        
    }
    
    public static function replaceVars($params) {

    	global $CJO;

    	$content = $params['subject'];
    	$replace = array();

    	if (!$CJO['CONTEJO'] && strpos($content,'VF_CHANNELLIST[]') !== false) {
    		$replace['VF_CHANNELLIST[]'] = self::getChannelList();
    		$replace['VF_CHANNEL_LOGO[]'] =  !self::$current['is_package'] ? self::getChannelLogo() : self::getPackageLogo();
    		$replace['VF_CHANNEL_PACKAGE[]'] = self::getChannelPackageLogo();
    		$replace['VF_CHANNEL_TITLE[]'] = self::getChannelTitle();
    		$replace['VF_CHANNEL_TEXT[]'] = self::getChannelText();
    		$replace['VF_CHANNEL_GALLERY[]'] = self::getChannelGallery();
    		
    	    $search = array_keys($replace);
        	$content = str_replace($search, $replace, $content);       	
            
            $content = self::replaceCanonicalUrl($content);
    	}

    	return $content;
    }
    
}