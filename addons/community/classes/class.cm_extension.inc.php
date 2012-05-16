<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
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

class cjoCommunityExtension {

    static $mypage = 'community';
    
    public static function copyConfig($params) {

    	global $CJO, $I18N;

    	$file = $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/0.clang.inc.php';
    	$dest = $CJO['ADDON_CONFIG_PATH'].'/'.self::$mypage.'/'.$params['id'].'.clang.inc.php';

    	if (file_exists($file)) {
    		if (!copy($file, $dest)) {
    			cjoMessage::addError($I18N->msg("err_config_file_copy", $dest));
    		}
    		else {
    		   @chmod($dest, $CJO['FILEPERM']);
    		}
    	}
    }

    public static function replaceVars($params) {

    	global $CJO;

    	$content        = $params['subject'];

    	$nl_signin 	  	= $CJO['ADDON']['settings'][self::$mypage]['NL_SIGNIN'];
    	$nl_signout   	= $CJO['ADDON']['settings'][self::$mypage]['NL_SIGNOUT'];
    	$login_form  	= $CJO['ADDON']['settings'][self::$mypage]['LOGIN_FORM'];
    	$register_user  = $CJO['ADDON']['settings'][self::$mypage]['REGISTER_USER'];
    	$manage_account = $CJO['ADDON']['settings'][self::$mypage]['MANAGE_ACCOUNT'];
    	$send_password 	= $CJO['ADDON']['settings'][self::$mypage]['SEND_PASSWORD'];

    	if ($CJO['ARTICLE_ID'] != $nl_signin &&
    		strpos($content,'CM_NEWSLETTER_SIGNIN_FORM[]') !== false &&
    		OOArticle::isOnline($nl_signin)) {

    		$article = new cjoArticle($nl_signin);
    		$article->setCLang($CJO['CUR_CLANG']);
    		$content = str_replace('CM_NEWSLETTER_SIGNIN_FORM[]', $article->getArticle(), $content);
    	}

    	if ($CJO['ARTICLE_ID'] != $nl_signout &&
    		strpos($content,'CM_NEWSLETTER_SIGNOUT_FORM[]') !== false &&
    		OOArticle::isOnline($nl_signout)) {

    		$article = new cjoArticle($nl_signout);
    		$article->setCLang($CJO['CUR_CLANG']);
    		$content = str_replace('CM_NEWSLETTER_SIGNOUT_FORM[]', $article->getArticle(), $content);
    	}

    	if ($CJO['ARTICLE_ID'] != $login_form &&
    		strpos($content,'CM_LOGIN_FORM[]') !== false &&
    		OOArticle::isOnline($login_form)) {

    		$article = new cjoArticle($login_form);
    		$article->setCLang($CJO['CUR_CLANG']);
    		$content = str_replace('CM_LOGIN_FORM[]', $article->getArticle(), $content);
    	}

    	if ($CJO['ARTICLE_ID'] != $register_user &&
    		strpos($content,'CM_REGISTER_USER_FORM[]') !== false &&
    		OOArticle::isOnline($register_user)) {

    		$article = new cjoArticle($register_user);
    		$article->setCLang($CJO['CUR_CLANG']);
    		$content = str_replace('CM_REGISTER_USER_FORM[]', $article->getArticle(), $content);
    	}

    	if ($CJO['ARTICLE_ID'] != $manage_account &&
    		strpos($content,'CM_MANAGE_ACCOUNT_FORM[]') !== false &&
    		OOArticle::isOnline($manage_account)) {

    		$article = new cjoArticle($manage_account);
    		$article->setCLang($CJO['CUR_CLANG']);
    		$content = str_replace('CM_MANAGE_ACCOUNT_FORM[]', $article->getArticle(), $content);
    	}

    	if ($CJO['ARTICLE_ID'] != $send_password &&
    		strpos($content,'CM_SEND_NEW_PASSWORT_FORM[]') !== false &&
    		OOArticle::isOnline($send_password)) {

    		$article = new cjoArticle($send_password);
    		$article->setCLang($CJO['CUR_CLANG']);
    		$content = str_replace('CM_SEND_NEW_PASSWORT_FORM[]', $article->getArticle(), $content);
    	}

    	$content = str_replace('CM_NEWSLETTER_SIGNIN_FORM[]', '', $content);
    	$content = str_replace('CM_NEWSLETTER_SIGNOUT_FORM[]', '', $content);
    	$content = str_replace('CM_LOGIN_FORM[]', '', $content);
    	$content = str_replace('CM_REGISTER_USER_FORM[]', '', $content);
    	$content = str_replace('CM_MANAGE_ACCOUNT_FORM[]', '', $content);
    	$content = str_replace('CM_SEND_NEW_PASSWORT_FORM[]', '', $content);

    	return $content;
    }

    public static function replaceSafeDownloadPath($params){

    	global $CJO;

    	$content = $params['subject'];

    	if (($CJO['ART'][$CJO['ARTICLE_ID']]['type_id']['0'] != 1 &&
    	     $CJO['ART'][$CJO['ARTICLE_ID']]['type_id']['0'] != 'out' ) ||
    	    $CJO['ADDON']['settings'][self::$mypage]['SAFE_DOWNLOAD_ENABLED']) {

        	$inst    = cjo_session('INST', 'int', $CJO['USER']['ID']);
            if (!$inst) $inst = (int) preg_replace('/\D/i','', $CJO['INSTNAME']);

        	$safe_dl = $CJO['ADDON']['settings'][self::$mypage]['SAFE_DOWNLOAD'];
        	$cache_dir = str_replace('//','/', '/'.$CJO['ADDON']['settings']['image_processor']['cachedir'].'/');
        	$processed = array();

            preg_match_all('#(?<=href=")[^"]*'.$CJO['MEDIAFOLDER'].'/([^"]+)(?=")#i', $content, $files, PREG_SET_ORDER);

            foreach($files as $file) {

                if ($processed[$file[0]] || strpos($file[0], $cache_dir) !== false) continue;

                $media_obj = OOMedia::getMediaByName($file[1]);

                $processed[$file[0]] = 1;

                if (!OOMedia::isValid($media_obj)) continue;

                $url = cjo_getUrl($safe_dl, $CJO['CUR_CLANG'], array('file' => $media_obj->_id * $inst, 'ext' => '.'.$media_obj->_getExtension()));

                $content = str_replace($file[0], $url, $content);
            }
    	}
        return $content;
    }

    public static function redirectLogin($params) {

    	global $CJO;

    	$article    = $params['article'];
    	$article_id = $article->article_id;
    	$re_id      = cjo_request('re_id', 'cjo-article-id');
    	$type_id    = $article->type_id;
    	$re_array 	= array();
    	$login_form = $CJO['ADDON']['settings'][self::$mypage]['LOGIN_FORM'];

    	$tree_type_id = false;
    	
    	if ($article_id != $CJO['ADDON']['settings'][self::$mypage]['LOGOUT']) {
    		$re_array = array('re_id' => $article_id);
    	}
    	
        $tree = cjoAssistance::toArray($article->getValue('path').$article->article_id);

    	foreach($tree as $id) {
    	    $article = OOArticle::getArticleById($id);
    	    
    	    if ($article->isOffline()) {
    	        return;
    	    }
    	    
    	    if ($article->getTypeId() != 1 && !$tree_type_id) {
    	        $tree_type_id = $article->getTypeId();
    	    }
    	}

    	if ($tree_type_id == 1) return;
    
        if ($tree_type_id == 'contejo') {
            while (ob_get_level() > 0){ ob_end_clean(); };
            header('Location: ./core/');
            exit();
        }    	
    	
		if (!$CJO['USER']['LOGIN']  &&
		    $article_id != $login_form &&
		    $login_form) {
		        cjoAssistance::redirectFE($login_form, $CJO['CUR_CLANG'], $re_array);
		}
		elseif ($CJO['USER']['LOGIN'] && !$CJO['LOGOUT']) {

		    if (cjo_request('re_id', 'bool') && $re_id != $article_id) {
            	cjoAssistance::redirectFE($re_id);
            }

            $LOGIN = cjo_post('LOGIN', 'array');

            if ($LOGIN['re_id'] && $LOGIN['re_id'] != $article_id) {
            	cjoAssistance::redirectFE($LOGIN['re_id']);
            }
        }
    }
    
    public static function changeNewsletterUrls($params) {
        
        global $CJO;
        
        $article = OOArticle::getArticleById($CJO['ARTICLE_ID']);
        $campain = OOArticle::isValid($article) 
                 ? 'Newsletter-'.$article->getName()
                 : 'Newsletter-'.strftime('%Y%m%d',time());
        
        $params['path']                .= 'cjo_piwik/';
        $params['query']['pk_campaign'] = $campain;
        $params['query']['pk_kwd']      = $params['name'];      
        
        return $params;
    }
    
    public static function toHtml4($params) {
        global $CJO;
        return preg_replace('/\w*\/>/','>',$params['subject']);
    }
}

cjoExtension::registerExtension('CLANG_ADDED', 'cjoCommunityExtension::copyConfig');

if ($CJO['CONTEJO']) return false;

cjoExtension::registerExtension('ARTICLE_OFFLINE', 'cjoCommunityExtension::redirectLogin');
cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoCommunityExtension::replaceVars');
cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoCommunityExtension::replaceSafeDownloadPath');