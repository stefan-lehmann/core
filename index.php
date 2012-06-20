<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.6.0
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

error_reporting(E_ALL ^ E_NOTICE);
// ----- caching start für output filter

ob_start();

$CJO                = array();
$CJO['HTDOCS_PATH'] = '../';
$CJO['CONTEJO']     = true;
$CJO['PAGEPATH']    = '';
$cur_page           = array();
$cur_page['header'] = true;

require_once "./include/functions/function.cjo_mquotes.inc.php";
require_once './include/master.inc.php';

// ----------------- SETUP
if ($CJO['SETUP']){

    require_once $CJO['INCLUDE_PATH']."/functions/function.cjo_setup.inc.php";

	// ----------------- SET SETUP LANG
    $CJO['USER'] = false;
    $cur_page['header'] = true;
	$CJO['LANG'] = empty($lang) ? 'de' : $lang;
	$I18N = new i18n($CJO['LANG']);

	foreach ($I18N->getLocales() as $k => $l) {
		if (cjo_request($k,'string') == $l){
			$CJO['LANG'] = $l;
			$I18N = new i18n($CJO['LANG']);
		}
	}

	setlocale(LC_ALL,cjoAssistance::toArray($I18N->msg('setlocale'),','));

	$cur_page['name']    = $I18N->msg('title_setup');
	$cur_page['page']    = 'setup';
	$CJO['SERVERNAME']   = $CJO['SERVERNAME'] == '' ? 'CONTEJO' : $CJO['SERVERNAME'];
	$CJO['SERVER']       = $CJO['SERVER'] == '' ? $_SERVER['HTTP_HOST'] : $CJO['SERVER'];
}
else {

    $subpage = cjo_request('subpage', 'string', '');

	if ($CJO['USER'] != false) {
    	// --- addon page check
    	if (is_array($CJO['ADDON']['page'])) {

    		$match = array_search($page,$CJO['ADDON']['page']);

    		if ($match !== false){
    			// --- addon gefunden
    			$perm = $CJO['ADDON']['perm'][$match];
    			if ($CJO['ADDON']['status'][$page] == 1 && $CJO['USER']->hasAddonPerm($match,true)){

    				$parent_page = $CJO['ADDON']['menu'][$page];
    				$CJO['ADDON']['curr_subpage'][$page] = cjo_request('subpage', 'string');

    				if ($parent_page != 1) {

    					$match = array_search($parent_page,$CJO['ADDON']['page']);

    					if ($match !== false && $CJO['USER']->hasAddonPerm($match,true)) {
    						$subpage = $page;
    						$page = $parent_page;
    					}
    					else if ($CJO['USER']->hasPerm($parent_page.'[')) {
    						$subpage = $page;
    						$page = $parent_page;
    					}
    					else {
    						$parent_page = 1;
    					}
    				}

    				if ($CJO['ADDON']['menu'][$page]) {
    					$cur_page['header'] = false;
    					$cur_page['page'] = $page;
    					$CJO['PAGEPATH'] = $CJO['ADDON_PATH'].'/'.$cur_page['page'].'/pages/index.inc.php';
                        if (!file_exists($CJO['PAGEPATH']))
                            $CJO['PAGEPATH'] = $CJO['ADDON_CONFIG_PATH'].'/'.$cur_page['page'].'/pages/index.inc.php';
                        if (!file_exists($CJO['PAGEPATH'])) {
                            $cur_page['header'] = true;
                            $cur_page['page'] = '';
                            $CJO['PAGEPATH'] = '';
                            cjoMessage::addError($I18N->msg('msg_page_not_found'));
                        }
                        
    				}
    			}
    		}
    	}

    	$cur_page['popup'] = cjo_request('popup', 'boolean');
    	// ----- standard pages
    	if ($CJO['PAGEPATH'] == '') {
    		switch($page){
    			case 'addons':
    				if ($CJO['USER']->hasPerm('addons[]')) {
    					$cur_page['name'] = $I18N->msg('title_addons');
    					$cur_page['page'] = $page;
    				} break;

    			case 'specials':
    				if ($CJO['USER']->hasPerm('specials[')) {
    					$cur_page['name'] = $I18N->msg('title_specials');
    					$cur_page['page'] = $page;
    				} break;

    			case 'tools':
    				if ($CJO['USER']->hasPerm('tools[')) {
    					$cur_page['name'] = $I18N->msg('title_tools');
    					$cur_page['page'] = $page;
    				} break;

    			case 'users':
    				if ($CJO['USER']->hasPerm('users[')) {
    					$cur_page['name'] = $I18N->msg('title_user');
    					$cur_page['page'] = $page;
    				} break;

    			case 'medienpool':
    			case 'mediapool':
    			case 'media':
    				if ($CJO['USER']->isValueOf("rights",'media[') ||
    					$CJO['USER']->isAdmin()) {
    					$cur_page['name'] = $I18N->msg('title_media');
    					$cur_page['page'] = 'media';
    				} break;
    		}
    		if (empty($cur_page['page'])) {
    			$cur_page['name'] = $I18N->msg('title_edit');
    			$cur_page['page'] = 'edit';
    		}
    	}

    	new cjoSelectArticle($article_id);
        new cjoSelectMediaCat();
        new cjoSelectLang();
	}
}

if (($CJO['USER'] || $CJO['SETUP']) && cjo_get('msg', 'boolean')) {
	cjoMessage::addSuccess($I18N->msg(cjoAssistance::cleanInput(cjo_get('msg', 'string'))));
}
if (($CJO['USER'] || $CJO['SETUP']) && cjo_get('err_msg', 'boolean')) {
	cjoMessage::addError($I18N->msg(cjoAssistance::cleanInput(cjo_get('err_msg', 'string'))));
}
// ----- kein pagepath -> kein addon -> path setzen
if ($CJO['PAGEPATH'] == '') {
    $CJO['PAGEPATH'] = $CJO['INCLUDE_PATH'].'/pages/'.$cur_page['page'].'/_'.$cur_page['page'].'.inc.php';
}

// ----- ausgabe des includes
if ($cur_page['header']){
	require_once $CJO['INCLUDE_PATH'].'/layout/top.php';
}
include $CJO['PAGEPATH'];

if ($cur_page['header']) {
	require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';
}
cjoValidateEngine::cleanup();
cjoMessage::outputMessages();


// ----- caching end für output filter
$CONTENT = ob_get_contents();
while(@ob_end_clean());

// ----- inhalt endgueltig ausgeben
cjoClientCache::sendArticle(NULL, $CONTENT, 'backend', true);