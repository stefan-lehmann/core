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

error_reporting(E_ALL ^ E_NOTICE);
// ----- caching start für output filter

ob_start();

$CJO = array('HTDOCS_PATH' => '../', 'CONTEJO' => true, 'PAGE_HEADER' => true);

require_once './include/master.inc.php';

// ----------------- SETUP
if (cjoProp::isSetup()) {

    require_once cjoPath::inc('functions/function.cjo_setup.inc.php');

    cjoProp::set('USER', false);
    cjoProp::set('PAGE_HEADER', true);
    cjoProp::setPage('setup');
    cjoProp::set('PAGE_NAME', cjoI18N::translate('title_setup'));
	cjoProp::set('LANG', cjo_get('lang', 'string', 'de'));

	foreach (cjoI18N::getLocales() as $k => $l) {
		if (cjo_request($k,'string') == $l){
            cjoProp::set('LANG',  $l);
            cjoI18N::reset();
			cjoI18N::init(cjoProp::get('LANG'));
		}
	}
}
else {

    $page = cjo_request('page', 'string', '');
    $subpage = cjo_request('subpage', 'string', '');

	if (cjoProp::getUser() != false) {
    	// --- addon page check
    	if (cjoAddon::getProperty('page')) {

    		$match = array_search($page, cjoAddon::getProperty('page'));

    		if ($match !== false){

    			$perm = cjoAddon::getProperty('perm',$page);
    			if (cjoAddon::isActivated($page) && cjoProp::getUser()->hasAddonPerm($page,true)){

    				$parent_page = cjoAddon::getProperty('menu',$page);
    				cjoAddon::setProperty('curr_subpage', $subpage, $page);

    				if ($parent_page != 1) {

    					$match = array_search($parent_page,cjoAddon::getProperty('page'));

    					if ($match !== false && cjoProp::getUser()->hasAddonPerm($match,true)) {
    						$subpage = $page;
    						$page = $parent_page;
    					}
    					else if (cjoProp::getUser()->hasPerm($parent_page.'[')) {
    						$subpage = $page;
    						$page = $parent_page;
    					}
    					else {
    						$parent_page = 1;
    					}
    				}

    				if (cjoAddon::getProperty('menu',$page)) {
    				    
    					cjoProp::setPage($page);
    					cjoProp::set('PAGE_PATH', cjoPath::addon(cjoProp::getPage(),'pages/index.inc.php'));
                        
                        if (!file_exists(cjoProp::get('PAGE_PATH')))
                            cjoProp::set('PAGE_PATH', cjoPath::addonAssets(cjoProp::getPage(),'pages/index.inc.php'));
                        
                        if (!file_exists(cjoProp::get('PAGE_PATH'))) {
                            cjoProp::set('PAGE_HEADER', true);
                            cjoProp::remove('PAGE_PAGE');
                            cjoProp::remove('PAGE_PATH');
                            cjoMessage::addError(cjoI18N::translate('msg_page_not_found'));
                        }
                        
    				}
    			}
    		}
    	}

        cjoProp::set('PAGE_POPUP', cjo_request('popup', 'boolean', false));
    	// ----- standard pages
    	if (!cjoProp::get('PAGE_PATH')) {
    		switch($page){
    			case 'addons':
    				if (cjoProp::getUser()->hasPerm('addons[]')) {
    					cjoProp::set('PAGE_NAME', cjoI18N::translate('title_addons'));
    					cjoProp::setPage($page);
    				} break;

    			case 'specials':
    				if (cjoProp::getUser()->hasPerm('specials[')) {
    					cjoProp::set('PAGE_NAME', cjoI18N::translate('title_specials'));
    					cjoProp::setPage($page);
    				} break;

    			case 'tools':
    				if (cjoProp::getUser()->hasPerm('tools[')) {
    					cjoProp::set('PAGE_NAME', cjoI18N::translate('title_tools'));
    					cjoProp::setPage($page);
    				} break;

    			case 'users':
    				if (cjoProp::getUser()->hasPerm('users[')) {
    					cjoProp::set('PAGE_NAME', cjoI18N::translate('title_user'));
    					cjoProp::setPage($page);
    				} break;

    			case 'medienpool':
    			case 'mediapool':
    			case 'media':
    				if (cjoProp::getUser()->isValueOf("rights",'media[') ||
    					cjoProp::getUser()->isAdmin()) {
    					cjoProp::set('PAGE_NAME', cjoI18N::translate('title_media'));
    					cjoProp::setPage('media');
    				} break;
                default:
        			cjoProp::set('PAGE_NAME', cjoI18N::translate('title_edit'));
        			cjoProp::setPage('edit');
        		}
    	}

    	cjoSelectArticle::init();
        cjoSelectMediaCat::init();
        cjoSelectLang::init();
	}

    cjoProp::setSubpage($subpage);
}

if ((cjoProp::getUser() || cjoProp::isSetup()) && cjo_get('msg', 'boolean')) {
	cjoMessage::addSuccess(cjoI18N::translate(cjoAssistance::cleanInput(cjo_get('msg', 'string'))));
}
if ((cjoProp::getUser() || cjoProp::isSetup()) && cjo_get('err_msg', 'boolean')) {
	cjoMessage::addError(cjoI18N::translate(cjoAssistance::cleanInput(cjo_get('err_msg', 'string'))));
}
// ----- kein pagepath -> kein addon -> path setzen
if (!cjoProp::get('PAGE_PATH')) {
    cjoProp::set('PAGE_PATH', cjoPath::inc('pages/'.cjoProp::getPage().'/_'.cjoProp::getPage().'.inc.php'));
}
   
cjoSubPages::generatePage();

cjoValidateEngine::cleanup();
cjoMessage::outputMessages();

// ----- caching end für output filter
$CONTENT = ob_get_contents();
while(@ob_end_clean());

// ----- inhalt endgueltig ausgeben
cjoClientCache::sendArticle(NULL, $CONTENT, 'backend', true);