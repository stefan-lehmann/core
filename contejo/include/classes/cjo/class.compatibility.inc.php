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

/**
 * @deprecated
 */
class HtmlTemplate extends cjoHtmlTemplate {
    /**
     * @deprecated
     */
    function get_slice_standard($curr_slice, $show_vars = false) {
        return parent::getSliceStandard($curr_slice, $show_vars = false);
    }
}

/**
 * @deprecated
 */
class article extends cjoArticle {

}

/**
 * @deprecated
 */
class select extends cjoSelect {
    /**
     * @deprecated
     */
    function select() {
        $this -> init();
    }

    /**
     * @deprecated
     */
    function multiple($multiple) {
        parent::setMultiple($multiple);
    }

    /**
     * @deprecated
     */
    function init() {
        parent::__construct();
    }

    /**
     * @deprecated
     */
    function set_name($name) {
        parent::setName($name);
    }

    /**
     * @deprecated
     */
    function set_selectextra($extra) {
        parent::setSelectExtra($extra);
    }

    /**
     * @deprecated
     */
    function set_id($id) {
        parent::setId($id);
    }

    /**
     * @deprecated
     */
    function get_select_id() {
        return parent::getSelectId();
    }

    /**
     * @deprecated
     */
    function set_label($label, $label_pos = 'left', $label_css = '') {
        parent::setLabel($label, $label_pos, $label_css);
    }

    /**
     * @deprecated
     */
    function get_label_css() {
        return parent::getLabelCss();
    }

    /**
     * @deprecated
     */
    function get_label() {
        return parent::getLabel();
    }

    /**
     * @deprecated
     */
    function set_style($style) {
        parent::setStyle($style);
    }

    /**
     * @deprecated
     */
    function show_root($root, $title = 'root') {
        parent::showRoot($root, $title);
    }

    /**
     * @deprecated
     */
    function set_size($size) {
        parent::setSize($size);
    }

    /**
     * @deprecated
     */
    function set_selected($selected) {
        parent::setSelected($selected);
    }

    /**
     * @deprecated
     */
    function reset_selected() {
        parent::resetSelected();
    }

    /**
     * @deprecated
     */
    function set_selected_path($path = '') {
        parent::setSelectedPath($path);
    }

    /**
     * @deprecated
     */
    function reset_selected_path() {
        parent::resetSelectedPath();
    }

    /**
     * @deprecated
     */
    function is_selected_path($value) {
        return parent::isSelectedPath($value);
    }

    /**
     * @deprecated
     */
    function set_disabled($disabled) {
        parent::setDisabled($disabled);
    }

    /**
     * @deprecated
     */
    function reset_disabled() {
        parent::resetDisabled();
    }

    /**
     * @deprecated
     */
    function add_titles($title, $re_id = 0, $key = false) {
        parent::addTitles($title, $re_id, $key);
    }

    /**
     * @deprecated
     */
    function get_title($key, $re_id = 0) {
        return parent::getTitle($key, $re_id);
    }

    /**
     * @deprecated
     */
    function add_option($name, $value, $id = 0, $re_id = 0, $title = '') {
        parent::addOption($name, $value, $id, $re_id, $title);
    }

    /**
     * @deprecated
     */
    function add_options($options = array()) {
        parent::addOptions($options);
    }

    /**
     * @deprecated
     */
    function add_sqloptions($query, $sqldebug = false) {
        parent::addSqlOptions($query, $sqldebug);
    }

    /**
     * @deprecated
     */
    function get_selected($seperator = ',') {
        return parent::getSelected($seperator);
    }

    /**
     * @deprecated
     */
    function out() {
        return parent::get();
    }
}

/**
 * @deprecated
 */
function cjo_getUrl($id = '', $clang = false, $params = '', $hash_string = '') {
    return cjoRewrite::getUrl($id, $clang, $params, $hash_string);
}

/**
 * @deprecated
 */
function cjo_to_array($value, $delimiter = '|') {
    return cjoAssistance::toArray($value, $delimiter);
}

/**
 * @deprecated
 */
function cjo_debug($array, $name = '', $color = 'pink', $convert = true) {
    cjoAssistance::debug($array, $name, $color, $convert);
}

/**
 * @deprecated
 */
function cjo_contains_multival($value, $string, $separator = '|') {
    return cjoAssistance::inMultival($value, $string, $separator);
}

/**
 * @deprecated
 */
function cjo_html2txt($value, $replace = ' ') {
    return cjoAssistance::htmlToTxt($value, $replace);
}

/**
 * @deprecated
 */
function clean_input($data) {
    return cjoAssistance::cleanInput($data);
}

/**
 * @deprecated
 */
function cjo_input_check_checked($value, $values = array(), $bool = true) {
    return cjoAssistance::setChecked($value, $values, $bool);
}

/**
 * @deprecated
 */
function cjo_input_check_disabled($value, $values = array(), $bool = true) {
    return cjoAssistance::setDisabled($value, $values, $bool);
}

/**
 * @deprecated
 */
function cjo_create_link($value, $global_params = array (), $local_params = array (), $tags = '') {
    return cjoAssistance::createBELink($value, $local_params, $global_params, $tags);
}

/**
 * @deprecated
 */
function cjo_create_url($global_params = array (), $local_params = array ()) {
    return cjoAssistance::createBEUrl($global_params, $local_params);
}

/**
 * @deprecated
 */
function cjo_copyDir($srcdir, $dstdir, $offset = '', $verbose = false) {
    return cjoAssistance::copyDir($srcdir, $dstdir, $offset, $verbose);
}

/**
 * @deprecated
 */
function is_online($article_id, $check_tree = true, $redirect = false) {
    return OOArticle::isOnline($article_id, $check_tree, $redirect);
}

/**
 * @deprecated
 */
function set_universal_navis($set = false) {

    $navi = new OONavigation();

    $new_set = array();

    if (is_array($set)) {
        foreach ($set as $key => $value) {

            switch($key) {
                case 'clang' :
                    $navi -> _clang = $value;
                    break;
                case 'current_id' :
                    $navi -> _curr_attr = $value;
                    break;
                case 'show_sub_levels' :
                    $navi -> _show_sub_levels = $value;
                    break;
                case 'active_only' :
                    $navi -> _active_only = $value;
                    break;
                case 'online_only' :
                    $navi -> _online_only = $value;
                    break;
                case 'active_path_only' :
                    $navi -> _active_path_only = $value;
                    break;
                case 'link_current_id' :
                    $navi -> _link_active_id = $value;
                    break;
                case 'level' :
                    $navi -> _level = $value;
                    break;
                case 'level_depth' :
                    $navi -> _level_depth = $value;
                    break;
                case 'disable_navi_names' :
                    $navi -> _disable_navi_names = $value;
                    break;
                default :
                    if (!preg_match('/^_/', $key))
                        $key = '_' . $key;
            }
            $new_set[$key] = $value;
        }
    }

    $navi -> genereateNavis($new_set);
    foreach ($navi->_structure as $navis) {
        foreach ($navis as $value) {
            $GLOBALS['navis'][$value] = $navi -> getNavi($value);
        }
    }
    $GLOBALS['navis']['default'] = $navi -> getNavi('default');
}

/**
 * @deprecated
 */
function create_lang_navi($substr_name = false) {
    $navi = new OONavigation();
    $navi -> genereateLangNavi($substr_name);
    return $navi -> getNavi('lang');
}

/**
 * @deprecated
 */
function create_breadcrumbs($article_id, $text = '', $root_name = 'Home') {
    $navi = new OONavigation($article_id);
    $navi -> genereateBreadCrumbs($text, $root_name);
    return $navi -> getNavi('breadcrumbs');
}

/**
 * @deprecated
 */
function next_pref_articles($article_id, $online = true) {
    $navi = new OONavigation($article_id);
    $navi -> linkPrefNextArticles();
    return $navi -> getNavi('nextprev');
}

/**
 * @deprecated
 */
function create_anchor_link($value) {
    return OONavigation::getAnchorLinkText($value);
}

/**
 * @deprecated
 */
function get_meta() {
    return false;
}

/**
 * @deprecated
 */
function get_ctype() {
    return false;
}

/**
 * @deprecated
 */
function get_back_to_list_link() {
    return false;
}

/**
 * @deprecated
 */
function cjo_register_extension() {
    return false;
}

/**
 * @deprecated
 */
function cjo_include_template($tmpl_id) {

    global $CJO;

    if (!file_exists($CJO['FOLDER_GENERATED_TEMPLATES'] . "/" . $tmpl_id . '.template')) {
        cjoGenerate::generateTemplates($tmpl_id);
    }
    include_once $CJO['FOLDER_GENERATED_TEMPLATES'] . "/" . $tmpl_id . '.template';
}

/**
 * @deprecated
 */
function show_article_infos($article_id, $CJO_EXT_VALUE) {
    return OOArticle::getArticleInfos($article_id, $CJO_EXT_VALUE);
}

if (!function_exists('mime_content_type')) {
    function mime_content_type($file) {
        ob_start();
        system('/usr/bin/file -i -b ' . realpath($file));
        $type = ob_get_clean();
        $parts = explode(';', $type);
        return trim($parts[0]);
    }
}

if (!function_exists('readSqlDump')) {
    function readSqlDump($file) {
        return cjoInstall::readSqlFile($file);
    }
}

if (!function_exists('PMA_splitSqlFile')) {
    function PMA_splitSqlFile(&$ret, $sql, $release) {
        return cjoInstall::splitSqlFile($ret, $sql, $release);
    }
}
/**
 * @deprecated
 */
function cjo_installAddon($file, $debug = false) {
    return cjoInstall::installDump($file, $debug);
}

/**
 * @deprecated
 */
function cjo_uninstallAddon($file, $debug = false) {
    return cjoInstall::installDump($file, $debug);
}

$CJO_USER                          = &$CJO["USER"];
$CJO_LOGIN                         = &$CJO["LOGIN"];
$article_id                        = &$CJO['ARTICLE_ID'];
$clang                             = &$CJO['CUR_CLANG'];
$CJO['SERVERDOMAIN']               = &$CJO['SERVER'];

$CJO['SETTINGS']['TMPL_FILE_TYPE'] = &$CJO['TMPL_FILE_TYPE'];
$CJO['SETTINGS']['LOGIN']          = &$CJO['LOGIN_ENABLED'];
$CJO['SETTINGS']['ONLINE_FROM_TO'] = &$CJO['ONLINE_FROM_TO_ENABLED'];

$CJO['HORIZONTAL_IMG_S']           = &$CJO['IMAGE_LIST_BUTTON']['1']['FROM'];
$CJO['HORIZONTAL_IMG_E']           = &$CJO['IMAGE_LIST_BUTTON']['1']['TO'];
$CJO['VERTICAL_IMG_S']             = &$CJO['IMAGE_LIST_BUTTON']['2']['FROM'];
$CJO['VERTICAL_IMG_E']             = &$CJO['IMAGE_LIST_BUTTON']['2']['TO'];
$CJO['IMG_SET']['BRAND_IMG']       = &$CJO['IMAGE_LIST_BUTTON']['BRAND_IMG'];
$CJO['IMG_SET']['FUNCTIONS']       = &$CJO['IMAGE_LIST_BUTTON']['FUNCTIONS'];
$CJO['IMG_SET']['IMAGEBOX']        = &$CJO['IMAGE_LIST_BUTTON']['IMAGEBOX'];
$CJO['IMG_SET']['FLASHBOX']        = &$CJO['IMAGE_LIST_BUTTON']['FLASHBOX'];
$CJO['IMG_SET']['FLVBOX']          = &$CJO['IMAGE_LIST_BUTTON']['FLVBOX'];
$CJO['IMG_SET']['GALLERY_LINK']    = &$CJO['IMAGE_LIST_BUTTON']['GALLERY_LINK'];
$CJO['IMG_SET']['INT_LINK']        = &$CJO['IMAGE_LIST_BUTTON']['INT_LINK'];
$CJO['IMG_SET']['EXT_LINK']        = &$CJO['IMAGE_LIST_BUTTON']['EXT_LINK'];
$CJO['IMG_SET']['STYLE']           = &$CJO['IMAGE_LIST_BUTTON']['STYLE'];
$CJO['MODUL_SET'][1]['FRONTEND']   = &$CJO['CONTEJO'];

if (!isset($CJO['CACHEFOLDER'])) {
    $CJO['CACHEFOLDER']            = $CJO['MEDIAFOLDER'];
}

if (!isset($CJO['MODREWRITE']['LINK_REDIRECT']) && isset($CJO['MODREWRITE']['LINK_ALIAS'])) {
    $CJO['MODREWRITE']['LINK_REDIRECT'] = &$CJO['MODREWRITE']['LINK_ALIAS'];
}

if (!isset($CJO['MODREWRITE']['ENABLED'])) {
    if ($CJO['MODREWRITE'] === true) {
        $CJO['MODREWRITE'] = array();
        $CJO['MODREWRITE']['ENABLED'] = true;
    } else {
        $CJO['MODREWRITE'] = array();
        $CJO['MODREWRITE']['ENABLED'] = false;
    }
}
