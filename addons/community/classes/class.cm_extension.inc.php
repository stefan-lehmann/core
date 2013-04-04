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

class cjoCommunityExtension {

    static $addon = 'community';
    
    public static function copyConfig($params) {

    	$source = cjoPath::addonAssets(self::$addon,'0.clang.config');
    	$dest = cjoPath::addonAssets(self::$addon,$params['id'].'.clang.config');
        cjoFile::copyFile($source, $dest);
    }

    public static function replaceVars($params) {


    	$content        = $params['subject'];

    	$nl_signin 	  	= cjoAddon::getParameter('NL_SIGNIN', self::$addon);
    	$nl_signout   	= cjoAddon::getParameter('NL_SIGNOUT', self::$addon);
    	$login_form  	= cjoAddon::getParameter('LOGIN_FORM', self::$addon);
    	$register_user  = cjoAddon::getParameter('REGISTER_USER', self::$addon);
    	$manage_account = cjoAddon::getParameter('MANAGE_ACCOUNT', self::$addon);
    	$send_password 	= cjoAddon::getParameter('SEND_PASSWORD', self::$addon);

    	if (cjoProp::getArticleId() != $nl_signin &&
    		strpos($content,'CM_NEWSLETTER_SIGNIN_FORM[]') !== false &&
    		OOArticle::isOnline($nl_signin)) {

    		$article = new cjoArticle($nl_signin);
    		$article->setCLang(cjoProp::getClang());
    		$content = str_replace('CM_NEWSLETTER_SIGNIN_FORM[]', $article->getArticle(), $content);
    	}

    	if (cjoProp::getArticleId() != $nl_signout &&
    		strpos($content,'CM_NEWSLETTER_SIGNOUT_FORM[]') !== false &&
    		OOArticle::isOnline($nl_signout)) {

    		$article = new cjoArticle($nl_signout);
    		$article->setCLang(cjoProp::getClang());
    		$content = str_replace('CM_NEWSLETTER_SIGNOUT_FORM[]', $article->getArticle(), $content);
    	}

    	if (cjoProp::getArticleId() != $login_form &&
    		strpos($content,'CM_LOGIN_FORM[]') !== false &&
    		OOArticle::isOnline($login_form)) {

    		$article = new cjoArticle($login_form);
    		$article->setCLang(cjoProp::getClang());
    		$content = str_replace('CM_LOGIN_FORM[]', $article->getArticle(), $content);
    	}

    	if (cjoProp::getArticleId() != $register_user &&
    		strpos($content,'CM_REGISTER_USER_FORM[]') !== false &&
    		OOArticle::isOnline($register_user)) {

    		$article = new cjoArticle($register_user);
    		$article->setCLang(cjoProp::getClang());
    		$content = str_replace('CM_REGISTER_USER_FORM[]', $article->getArticle(), $content);
    	}

    	if (cjoProp::getArticleId() != $manage_account &&
    		strpos($content,'CM_MANAGE_ACCOUNT_FORM[]') !== false &&
    		OOArticle::isOnline($manage_account)) {

    		$article = new cjoArticle($manage_account);
    		$article->setCLang(cjoProp::getClang());
    		$content = str_replace('CM_MANAGE_ACCOUNT_FORM[]', $article->getArticle(), $content);
    	}

    	if (cjoProp::getArticleId() != $send_password &&
    		strpos($content,'CM_SEND_NEW_PASSWORT_FORM[]') !== false &&
    		OOArticle::isOnline($send_password)) {

    		$article = new cjoArticle($send_password);
    		$article->setCLang(cjoProp::getClang());
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

    	$content = $params['subject'];

    	if (($CJO['ART'][cjoProp::getArticleId()]['type_id']['0'] != 1 &&
    	     $CJO['ART'][cjoProp::getArticleId()]['type_id']['0'] != 'out' ) ||
    	    cjoAddon::getParameter('SAFE_DOWNLOAD_ENABLED', self::$addon)) {

        	$inst    = cjo_session('INST', 'int', cjoProp::getUser('user_id'));
            if (!$inst) $inst = cjoProp::getUniqueNumber();

        	$safe_dl = cjoAddon::getParameter('SAFE_DOWNLOAD', self::$addon);
        	$cache_dir = str_replace('//','/', '/'.cjoAddon::getParameter('cachedir', 'image_processor').'/');
        	$processed = array();

            preg_match_all('#(?<=href=")[^"]*'.cjoUrl::media().'/([^"]+)(?=")#i', $content, $files, PREG_SET_ORDER);

            foreach($files as $file) {

                if ($processed[$file[0]] || strpos($file[0], $cache_dir) !== false) continue;

                $media_obj = OOMedia::getMediaByName($file[1]);

                $processed[$file[0]] = 1;

                if (!OOMedia::isValid($media_obj)) continue;

                $url = cjo_getUrl($safe_dl, cjoProp::getClang(), array('file' => $media_obj->_id * $inst, 'ext' => '.'.$media_obj->_getExtension()));

                $content = str_replace($file[0], $url, $content);
            }
    	}
        return $content;
    }

    public static function redirectLogin($params) {

    	$article    = $params['article'];
    	$article_id = $article->article_id;
    	$re_id      = cjo_request('re_id', 'cjo-article-id');
    	$type_id    = $article->type_id;
    	$re_array 	= array();
    	$login_form = cjoAddon::getParameter('LOGIN_FORM', self::$addon);

    	$tree_type_id = false;
    	
    	if ($article_id != cjoAddon::getParameter('LOGOUT', self::$addon)) {
    		$re_array = array('re_id' => $article_id);
    	}
    	
        $tree = cjoAssistance::toArray($article->getValue('path').$article->article_id);

    	foreach($tree as $id) {
    	    $article = OOArticle::getArticleById($id);
    	    
    	    if ($article->isOffline()) return;
    	    
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
    	
		if (!cjoProp::getUser('user_id') &&
		    $article_id != $login_form &&
		    $login_form) {
		        cjoUrl::redirectFE($login_form, cjoProp::getClang(), $re_array);
		}
		elseif (cjoProp::getUser('user_id') && !cjoProp::get('LOGOUT')) {

		    if (cjo_request('re_id', 'bool') && $re_id != $article_id) {
            	cjoUrl::redirectFE($re_id);
            }

            $LOGIN = cjo_post('LOGIN', 'array');

            if ($LOGIN['re_id'] && $LOGIN['re_id'] != $article_id) {
            	cjoUrl::redirectFE($LOGIN['re_id']);
            }
        }
    }
    
    public static function changeNewsletterUrls($params) {
        
        $article = OOArticle::getArticleById(cjoProp::getArticleId());
        $campain = OOArticle::isValid($article) 
                 ? 'Newsletter-'.$article->getName()
                 : 'Newsletter-'.strftime('%Y%m%d',time());
        
        $params['path']                .= 'cjo_piwik/';
        $params['query']['pk_campaign'] = $campain;
        $params['query']['pk_kwd']      = $params['name'];      
        
        return $params;
    }
    
    public static function toHtml4($params) {
        return preg_replace('/\w*\/>/','>',$params['subject']);
    }
    
    public static function initAddon() {

        cjoAddon::setParameter('CLANG_CONF', cjoPath::addonAssets(self::$addon,cjoProp::getClang().'.clang.config'), self::$addon);
        cjoAddon::readParameterFile(self::$addon, cjoPath::addonAssets(self::$addon,cjoProp::getClang().'.clang'));
        
        if (!cjoProp::isBackend() && 
            cjoAddon::getParameter($addon, 'LOGOUT') == cjoProp::get('ARTICLE_ID')) {
            cjoProp::set('LOGOUT',true);
        }
    }
}
