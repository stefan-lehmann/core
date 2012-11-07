<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  piwik
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

class cjoPiwikExtension {

    static $mypage = 'piwik';
    
    public static function replaceVars($params) {

        global $CJO;

        $content = $params['subject'];
        
        if (strpos($content,'PIWIK_TRACKING_CODE[]') !== false) {  
            preg_match('/(?<=<title>)[^<]+(?=<\/title>)/i', $content, $title);
            $content = str_replace('PIWIK_TRACKING_CODE[]', self::generatePiwikCode($title[0]), $content);
        }
        if (preg_match('/PIWIK_NL_TRACKING_PIXEL\[([^\]]*)\]/',$content,$matches)) {
            $goal = (int) $matches[1] > 0 ? (int) $matches[1] : 1;      
            $content = str_replace($matches[0], self::generateNewsletterPixel($goal), $content);
        }   
        return $content;
    }
    
    public static function generatePiwikCode($action_name='') {
        
        global $CJO;
        
        $custom_url = false;
        if (cjo_get('html5video','bool') && OOAddon::isAvailable('html5video')) {
            $custom_url = cjoHtml5Video::getPiwikURl();
        }
        
        if ($custom_url) {
            $custom_url = 'piwikTracker.setCustomUrl(\''.$custom_url.'\');';
        }
        
        $piwik_url = preg_replace('/^https?:\/\//','',$CJO['ADDON']['settings'][self::$mypage]['URL'].'/');
        $piwik_url = preg_replace('/[^:]\/{2,}/','/', $piwik_url);
        $pixel_url = self::generateUrl(array('action_name'=> $action_name, 'url' => $custom_url), '&');     
        
        return '<!-- Piwik -->'.
               self::getSessionTrackRequest().
               '<script type="text/javascript">/* <![CDATA[ */ '.
               'var pkBaseURL = (("https:" == document.location.protocol) ? "https://'.$piwik_url.'" : "http://'.$piwik_url.'");'.
               'document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E")); /* ]]> */'.
               '</script><script type="text/javascript">/* <![CDATA[ */ '.
               'var piwik_tracked = false; '.
               'function trackPiwik(variables, track_again) { '.
               '    if (window.location.protocol == undefined || !window.location.protocol.match(/^http/))  return false;'.  
               '    if (typeof track_again == "undefined" && piwik_tracked == true)  return false;'. 
               '    try {'.
               '        var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", '.$CJO['ADDON']['settings'][self::$mypage]['IDSITE'].');'.
               '        if (typeof variables != "undefined") {'.
               '            for (var i=0;i<variables.length;i++) {'.
               '                piwikTracker.setCustomVariable((i+1), variables[i].name, variables[i].value, variables[i].type);'.
               '                if (i>=4) break;'.
               '            }'.
               '        }'.
               '        piwikTracker.setDownloadExtensions("'.$CJO['ADDON']['settings'][self::$mypage]['TRACK_AS_DOWNLOADS'].'" );'.
               '        piwikTracker.setDownloadClasses("'.$CJO['ADDON']['settings'][self::$mypage]['DOWNLOAD_CLASS'].'" );'.        
               '        piwikTracker.trackPageView();'.
               '        piwikTracker.enableLinkTracking();'.
               '        '.$custom_url.
               '    } catch( err ) {}'.
               '    piwik_tracked = true;'.
               '} '.
               'setTimeout(function() {trackPiwik(); },1000);'.               
               '/* ]]> */'.
               '</script><noscript><p><img src="'.$pixel_url.'" style="border:0" alt="" /></p></noscript>'.
               '<!-- End Piwik Tracking Tag -->';
    }
    
    public static function generateNewsletterPixel($goal=1) {
        
        global $CJO;
        
        $article_id = $CJO['ARTICLE_ID'];
        $article    = OOArticle::getArticleById($article_id);

        if (!OOArticle::isValid($article)) return false;
        
        $url = cjoRewrite::setServerUri(false, false).cjoRewrite::setServerPath();

        return sprintf('<img src="%scjo_piwik/%s/%s/%%user_id%%/%s/%s" width="1" height="1" alt="" border="0">',
                        $url,
                        $article_id,
                        (int) $goal,                        
                        md5($CJO['INSTNAME']),
                        $CJO['ADDON']['settings'][self::$mypage]['EMAIL_CAMPAIGN_FILENAME']);
    }

    public static function setSessionTrackRequest() {
        
        global $CJO;
        
        parse_str(cjo_server('QUERY_STRING', 'string'), $params);
        
        $url = parse_url(cjoRewrite::getUrl(cjo_get('article_id','cjo-article-id')));
        $replace = !empty($params['pk_campaign']) ? $params['pk_campaign'] : 'cjo_piwik';
        $url['path']  = preg_replace('/\/[^\/]+\//', '/'.$replace.'/', $url['path'], 1);
        $url['query'] = !empty($url['query']) ? '?'.$url['query'] : '';

        $params['url']   = $url['scheme'].'://'.$url['host'].$url['path'].$url['query'];
        if (isset($params['pk_clicked'])) {
            $params['cvar'] = '{"1":["NL clicked","User-ID: '.$params['pk_clicked'].'"]}';
            $params['pk_clicked'] = null;
        }
        
        cjo_set_session('piwik_track_session', self::generateUrl($params));
        
        $anchor = '';
        if (!empty($params['cjo_anchor'])) {
            $anchor = $params['cjo_anchor'];
            $params['cjo_anchor'] = null;
        }
        
        $params['url'] = null;
        $params['cvar'] = null;
        cjoAssistance::redirectFE(cjo_get('article_id','cjo-article-id'), false, $params, $anchor);
    }

    public static function getSessionTrackRequest() {
        
        global $CJO;
        
        if (!cjo_session('piwik_track_session' ,'bool')) return false;
        
        $url = cjo_session('piwik_track_session' ,'string');
        cjo_unset_session('piwik_track_session');
        
        return '<img src="'.$url.'" width="1" height="1" alt="" border="0" />';
    }       
    
    public static function redirectEmailPixelTracking() {
        
        global $CJO;
        $path    = pathinfo(cjo_server('REQUEST_URI','string'));
        $dirname = preg_replace('#^.*\/cjo_piwik\/#','',$path['dirname']);
        $get     = explode('/',$dirname);
        $params  = array();

        $article = OOArticle::getArticleById($get[0]);

        if (!OOArticle::isValid($article) ||
            $path['basename'] != $CJO['ADDON']['settings'][self::$mypage]['EMAIL_CAMPAIGN_FILENAME'] || 
            $get[3] != md5($CJO['INSTNAME'])) return false;

        $CJO['ARTICLE_ID'] = $article->getId();
        
        $params['action_name'] = 'Images loaded';
        $params['rand']        = $get[3];   
        $params['cvar']        = '{"1":["NL Images Loaded","User-ID: '.$get[2].'"]}';

        // Alle OBs schließen
        while (ob_get_level() > 0){ ob_end_clean(); };

        header ('HTTP/1.1 301 Moved Permanently');
        header ('Location: '.self::generateUrl($params, '&'));
        exit();
    }
    
    private static function generateUrl($get_params=array(), $separator=false) {
        
        global $CJO;
        
        $set_params = array();
        
        $set_params['idsite']          = !empty($get_params['idsite'])
                                       ? $get_params['idsite'] 
                                       : (string) $CJO['ADDON']['settings'][self::$mypage]['IDSITE'];
                                       
        $set_params['pk_campaign']     = !empty($get_params['pk_campaign'])
                                       ? $get_params['pk_campaign'] 
                                       : cjo_request('pk_campaign', 'string');
                                       
        $set_params['pk_kwd']          = !empty($get_params['pk_kwd'])
                                       ? $get_params['pk_kwd'] 
                                       : cjo_request('pk_kwd', 'string');
                                       
        $set_params['idgoal']          = !empty($get_params['idgoal'])
                                       ? $get_params['idgoal'] 
                                       : cjo_request('idgoal', 'int', '');
                                       
        $set_params['_cvar']           = !empty($get_params['_cvar'])
                                       ? $get_params['_cvar'] 
                                       : cjo_request('_cvar', 'string', '');
                                       
        $set_params['cvar']            = !empty($get_params['cvar'])
                                       ? $get_params['cvar'] 
                                       : cjo_request('cvar', 'string', '');
                                       
        $set_params['action_name']     = !empty($get_params['action_name'])
                                       ? $get_params['action_name'] 
                                       : cjo_request('action_name', 'string', 'No Name');                                  
                                                                      
        $set_params['url']             = !empty($get_params['url'])
                                       ? $get_params['url'] 
                                       : cjo_server('REQUEST_URI','string', cjoRewrite::getUrl($CJO['ARTICLE_ID']));
                                       
        $set_params['urlref']          = !empty($get_params['urlref'])
                                       ? $get_params['urlref'] 
                                       : cjo_server('HTTP_REFERER','string', '');                                      
                
        $set_params['cookie']          = isset($get_params['cookie'])
                                       ? $get_params['cookie'] 
                                       : cjo_server('HTTP_COOKIE', 'int', 0);
                                       
        $set_params['rec']             = isset($get_params['rec'])
                                       ? $get_params['rec'] 
                                       : 1;
                                       
        $set_params['revenue']         = isset($get_params['revenue'])
                                       ? $get_params['revenue'] 
                                       : 1;
                                       
        $set_params['rand']            = !empty($get_params['rand'])
                                       ? $get_params['rand'] 
                                       : rand(1, 99999999999999);                                      
        
        $set_params['h']               = ltrim(date('G',$_SERVER['REQUEST_TIME']), 0);
        $set_params['m']               = ltrim(date('i',$_SERVER['REQUEST_TIME']), 0);
        $set_params['s']               = ltrim(date('s',$_SERVER['REQUEST_TIME']), 0);
        
        if (!empty($set_params['cvar'])) {
            $set_params['idgoal'] = '';
        }

        foreach($set_params as $key=>$value) {
            if ($value == '') {
                unset($set_params[$key]);
            }
        }       
        if (empty($separator)) $separator = ini_get('arg_separator.output');
        
        $query = http_build_query($set_params, '', $separator);
        $url = $CJO['ADDON']['settings'][self::$mypage]['URL'].'/piwik.php?'.$query;
        return preg_replace('/[^:]\/{2,}/','/', $url);
    }
}


if ($CJO['CONTEJO']) return false;

cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoPiwikExtension::replaceVars');