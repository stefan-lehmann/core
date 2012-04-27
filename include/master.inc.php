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

@session_start();
ini_set('arg_separator.input', '&amp;');
ini_set('arg_separator.output', '&amp;');
ini_set('pcre.backtrack_limit', 1000000);
ini_set('register_globals', 'off');
ini_set('default_charset', 'utf-8');

if (!isset($CJO)) $CJO = array();
if (empty($CJO['GG'])) $CJO['GG'] = false;
$page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '';

$CJO['SETUP'] 				= true;
$CJO['VERSION'] 			= "2.6";
$CJO['RELEASE'] 			= "0";
$CJO['INCLUDE_PATH'] 		= $CJO['HTDOCS_PATH']."core/include";
$CJO['INSTALL_PATH']        = $CJO['INCLUDE_PATH']."/install";
$CJO['JQUERY_PATH']			= $CJO['HTDOCS_PATH']."core/js/jQuery";
$CJO['ADDON_PATH'] 		    = $CJO['HTDOCS_PATH']."core/addons";
$CJO['FRONTPAGE_PATH']		= $CJO['HTDOCS_PATH']."page";
$CJO['FILE_CONFIG_PATH']  	= $CJO['FRONTPAGE_PATH']."/include";
$CJO['ADDON_CONFIG_PATH'] 	= $CJO['FILE_CONFIG_PATH'];
$CJO['FILE_CONFIG_MASTER']  = $CJO['FILE_CONFIG_PATH']."/config_master.inc.php";
$CJO['PHP_VERSION']			= "5.3.1";
$CJO['SYSTEM_ADDONS']       = array('developer', 'html5video', 'image_processor', 'import_export', 'log', 'phpmailer', 'opf_lang', 'wymeditor');

// ----- CONFIG FILES
@include_once $CJO['FILE_CONFIG_MASTER'];
@include_once $CJO['FILE_CONFIG_DB'];
@include_once $CJO['FILE_CONFIG_CTYPES'];
@include_once $CJO['FILE_CONFIG_LANGS'];

// ----------------- KONSTANTEN DEFINIEREN
define('TBL_ACTIONS', 			  $CJO['TABLE_PREFIX'].'action');
define('TBL_ARTICLES', 			  $CJO['TABLE_PREFIX'].'article');
define('TBL_ARTICLES_CAT_GROUPS', $CJO['TABLE_PREFIX'].'article_cat_groups');
define('TBL_ARTICLES_SLICE', 	  $CJO['TABLE_PREFIX'].'article_slice');
define('TBL_ARTICLES_TYPE',		  $CJO['TABLE_PREFIX'].'article_type');
define('TBL_CLANGS',			  $CJO['TABLE_PREFIX'].'clang');
define('TBL_FILES', 			  $CJO['TABLE_PREFIX'].'file');
define('TBL_FILE_CATEGORIES', 	  $CJO['TABLE_PREFIX'].'file_category');
define('TBL_MODULES_ACTIONS', 	  $CJO['TABLE_PREFIX'].'module_action');
define('TBL_MODULES', 			  $CJO['TABLE_PREFIX'].'modultyp');
define('TBL_TEMPLATES', 		  $CJO['TABLE_PREFIX'].'template');
define('TBL_USER', 				  $CJO['TABLE_PREFIX'].'user');

// -----------------
if (!isset($category_id) || $category_id == "") $category_id = 0;
if (!isset($ctype) || $ctype == "") $ctype = 0;

// ----------------- TIMER
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_time.inc.php";

// ----------------- CJO PERMS

$CJO['PERM']['label_rights_media'] 				    = "media[]";                // [translate: label_rights_media]
$CJO['PERM']['label_rights_media_categories'] 	    = "media[categories]";      // [translate: label_rights_media_categories]
$CJO['PERM']['label_rights_addmedia'] 			    = "media[addmedia]";        // [translate: label_rights_addmedia]
$CJO['PERM']['label_rights_tools'] 				    = "tools[]";                // [translate: label_rights_tools]
$CJO['PERM']['label_rights_templates'] 			    = "tools[templates]";       // [translate: label_rights_templates]
$CJO['PERM']['label_rights_modules'] 			    = "tools[modules]";         // [translate: label_rights_modules]
$CJO['PERM']['label_rights_actions'] 			    = "tools[actions]";         // [translate: label_rights_actions]
$CJO['PERM']['label_rights_ctypes'] 			    = "tools[ctypes]";          // [translate: label_rights_ctypes]
$CJO['PERM']['label_rights_langs'] 				    = "tools[langs]";           // [translate: label_rights_langs]
$CJO['PERM']['label_rights_catgroups'] 			    = "tools[catgroups]";       // [translate: label_rights_catgroups]
$CJO['PERM']['label_rights_types'] 				    = "tools[types]";           // [translate: label_rights_types]
$CJO['PERM']['label_rights_users'] 				    = "users[]";                // [translate: label_rights_users]
$CJO['PERM']['label_rights_password']			    = "users[password]";        // [translate: label_rights_password]
$CJO['PERM']['label_rights_addon_admin'] 		    = "addons[]";               // [translate: label_rights_addon_admin]
$CJO['PERM']['label_rights_specials'] 			    = "specials[]";             // [translate: label_rights_specials]

$CJO['EXTPERM']['label_rights_advancedmode'] 	    = "advancedMode[]";         // [translate: label_rights_advancedmode]
$CJO['EXTPERM']['label_rights_moveslice'] 		    = "moveSlice[]";            // [translate: label_rights_moveslice]
$CJO['EXTPERM']['label_rights_copycontent'] 	    = "copyContent[]";          // [translate: label_rights_copycontent]
$CJO['EXTPERM']['label_rights_copyarticle'] 	    = "copyArticle[]";          // [translate: label_rights_copyarticle]
$CJO['EXTPERM']['label_rights_movearticle']         = "moveArticle[]";          // [translate: label_rights_movearticle]
$CJO['EXTPERM']['label_rights_deletearticle_tree']  = "deleteArticleTree[]";    // [translate: label_rights_deletearticle_tree]
$CJO['EXTPERM']['label_rights_publisharticle'] 	    = "publishArticle[]";       // [translate: label_rights_publisharticle]
$CJO['EXTPERM']['label_rights_setloginarticle']     = "setloginArticle[]";      // [translate: label_rights_setloginarticle]

$CJO['EXTRAPERM']['label_rights_only_edit']         = "editContentOnly[]";      // [translate: label_rights_only_edit]

// ----- standard variables
$CJO['VARIABLES'] = array();
$CJO['VARIABLES'][] = 'cjoVarValue';
$CJO['VARIABLES'][] = 'cjoVarLink';
$CJO['VARIABLES'][] = 'cjoVarArticle';
$CJO['VARIABLES'][] = 'cjoVarGlobals';
$CJO['VARIABLES'][] = 'cjoVarMedia';
$CJO['VARIABLES'][] = 'cjoVarMeta';
$CJO['VARIABLES'][] = 'cjoVarNavigation';
$CJO['VARIABLES'][] = 'cjoVarTemplate';
$CJO['VARIABLES'][] = 'cjoVarWYMeditor';

require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_client_cache.inc.php";

// ----------------- INCLUDE FUNCTIONS
if (isset($CJO['NOFUNCTIONS']) && !$CJO['NOFUNCTIONS']) return false;

// ----------------- CONTEJO INCLUDES
require_once $CJO['INCLUDE_PATH']."/classes/oop/class.oocontejo.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/oop/class.oonavigation.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/oop/class.ooarticle.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/oop/class.ooarticleslice.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/oop/class.oomediacategory.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/oop/class.oomedia.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/oop/class.ooaddon.inc.php";

require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_article.inc.php";
require_once $CJO['INCLUDE_PATH'].'/classes/cjo/class.cjo_htmltemplate.inc.php';
require_once $CJO['INCLUDE_PATH'].'/classes/cjo/class.cjo_modultemplate.inc.php';
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_install.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_sql.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_login.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_loginsql.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_assistance.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_formgenerator.inc.php";

require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_extension.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_exception.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_generate.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.i18n.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_media.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_message.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_output.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_rewrite.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_select.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_selectarticle.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_selectlang.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_selectmediacat.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_slice.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_subpages.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_template.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.cjo_user.inc.php";
require_once $CJO['INCLUDE_PATH']."/classes/cjo/class.compatibility.inc.php";

// ----------------- CONTEJO LANGOBJEKT
$I18N = new i18n($CJO['LANG']);
setlocale(LC_ALL,cjoAssistance::toArray($I18N->msg('setlocale'),','));

// ----- EXTRA CLASSES

if ($CJO['CONTEJO']) {
    require_once $CJO['INCLUDE_PATH']."/classes/afc/classes/form/class.cjo_form.inc.php";
    require_once $CJO['INCLUDE_PATH']."/classes/afc/classes/list/class.cjo_list.inc.php";
    require_once $CJO['INCLUDE_PATH']."/classes/afc/functions/function_cjo_common.inc.php";
    require_once $CJO['INCLUDE_PATH']."/classes/afc/functions/function_cjo_string.inc.php";
}

// ----- FUNCTIONS
require_once $CJO['INCLUDE_PATH']."/functions/function.cjo_globals.inc.php";

//if (isset($CJO['ONLY_FUNCTIONS']) && $CJO['ONLY_FUNCTIONS']) return false;

require_once $CJO['INCLUDE_PATH'].'/classes/var/class.cjo_vars.inc.php';

cjoUnregisterGlobals();
cjogetAdjustPath();
cjoSetFavicon();

$CJO['CUR_CLANG'] = cjo_request('clang', 'cjo-clang-id', $CJO['START_CLANG_ID']);

$CJO['ARTICLE_ID']
= (cjo_request('article_id', 'int') == 0)
? $CJO['START_ARTICLE_ID']
: cjo_request('article_id','cjo-article-id', $CJO['NOTFOUND_ARTICLE_ID']);

if ($CJO['CONTEJO']) {
    $CJO['ARTICLE_ID'] = cjo_request('article_id', 'cjo-article-id');
}

foreach($CJO['VARIABLES'] as $key => $value) {
    require_once $CJO['INCLUDE_PATH']."/classes/var/class.".
    strtolower(str_replace('cjoVar', 'cjo_var_', $value)).".inc.php";

    $CJO['VARIABLES'][$key] = new $value;
}

// ------ MESSAGE
new cjoMessage();

if (!empty($CJO['FILE_CONFIG_ADDONS'])) {
    include_once $CJO['FILE_CONFIG_ADDONS'];
}
//cjoUser::updateCatReadPermissions();
require_once $CJO['INCLUDE_PATH']."/authentication.inc.php";
require_once $CJO['INCLUDE_PATH']."/frontend_auth.inc.php";
require_once $CJO['INCLUDE_PATH']."/local.inc.php";


cjoSetIndividualUploadFolder();
cjoExtension::registerExtension('OUTPUT_FILTER','i18n::searchAndTranslate');

// ----------------- set to default
$CJO['NOFUNCTIONS'] = true;