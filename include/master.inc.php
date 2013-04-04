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

@session_start();
ini_set('arg_separator.input', '&amp;');
ini_set('arg_separator.output', '&amp;');
ini_set('pcre.backtrack_limit', 1000000);
ini_set('register_globals', 'off');
ini_set('default_charset', 'utf-8');

require_once dirname(__FILE__) . '/classes/cjo/class.cjo_path.inc.php';
cjoPath::init($CJO['HTDOCS_PATH'], $CJO['CONTEJO']);
require_once dirname(__FILE__) . '/classes/cjo/class.cjo_url.inc.php';
cjoUrl::init($CJO['HTDOCS_PATH'], $CJO['CONTEJO']);

require_once cjoPath::inc('classes/cjo/class.cjo_autoload.inc.php');

cjoAutoload::register();
cjoAutoload::addDirectory(cjoPath::inc('classes/cjo'));
cjoAutoload::addDirectory(cjoPath::inc('classes/oop'));
cjoAutoload::addDirectory(cjoPath::inc('classes/var'));
cjoAutoload::addDirectory(cjoPath::inc('classes/afc'));
cjoAutoload::addDirectory(cjoPath::inc('pages'));

cjoI18N::init();
    
if (cjoProp::isBackend()) {
    cjoMessage::init();
    cjoAutoload::addDirectory(cjoPath::inc('classes/afc/classes/form'));
    cjoAutoload::addDirectory(cjoPath::inc('classes/afc/classes/form/validate'));
    cjoAutoload::addDirectory(cjoPath::inc('classes/afc/classes/form/fields'));
    cjoAutoload::addDirectory(cjoPath::inc('classes/afc/classes/form/fields/cjo'));
    cjoAutoload::addDirectory(cjoPath::inc('classes/afc/classes/list'));
    cjoAutoload::addDirectory(cjoPath::inc('classes/afc/classes/list/toolbars'));
    cjoAutoload::addDirectory(cjoPath::inc('classes/afc/classes/list/columns'));
    require_once cjoPath::inc('classes/afc/functions/function_cjo_common.inc.php');
    require_once cjoPath::inc('classes/afc/functions/function_cjo_string.inc.php');    
}

require_once cjoPath::inc('functions/function.cjo_globals.inc.php');
require_once cjoPath::inc('classes/cjo/class.compatibility.inc.php');

cjoProp::loadFromFile(cjoPath::inc('master'));
cjoProp::loadFromFile(cjoPath::pageConfig('master'));
cjoProp::loadFromFile(cjoPath::pageConfig('databases'));
cjoProp::loadFromFile(cjoPath::pageConfig('ctypes'));
cjoProp::loadFromFile(cjoPath::pageConfig('clangs'));
cjoProp::loadFromFile(cjoPath::pageConfig('addons'));

cjoProp::set('SCRIPT_START_TIME', cjoTime::getCurrentTime());

define('TBL_ACTIONS', 			  cjoProp::getTable('action'));
define('TBL_ARTICLES', 			  cjoProp::getTable('article'));
define('TBL_ARTICLES_CAT_GROUPS', cjoProp::getTable('article_cat_groups'));
define('TBL_ARTICLES_SLICE', 	  cjoProp::getTable('article_slice'));
define('TBL_ARTICLES_TYPE',		  cjoProp::getTable('article_type'));
define('TBL_CLANGS',			  cjoProp::getTable('clang'));
define('TBL_FILES', 			  cjoProp::getTable('file'));
define('TBL_FILE_CATEGORIES', 	  cjoProp::getTable('file_category'));
define('TBL_MODULES_ACTIONS', 	  cjoProp::getTable('module_action'));
define('TBL_MODULES', 			  cjoProp::getTable('modultyp'));
define('TBL_TEMPLATES', 		  cjoProp::getTable('template'));
define('TBL_USER', 				  cjoProp::getTable('user'));

cjoAddon::loadAddons();

if (cjoProp::get('ONLY_FUNCTIONS')) return false;

cjoProcess::init();

require_once cjoPath::inc('authentication.inc.php');
require_once cjoPath::inc('frontend_auth.inc.php');