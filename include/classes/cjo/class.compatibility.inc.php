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
class cjoRewrite {

    public static function parseArticleName($name) {
        return cjo_url_friendly_string($name);
    }

    public static function getUrl($article_id = 0, $clang = false, $query_params = '', $hash = '') {
        return cjoUrl::getUrl($article_id, $clange, $query_params, $hash);
    }
    
    public static function setRewriteUrl($params){
        return cjoUrl::setRewriteUrl($params);
    }
    
    public static function setServerUri($forward = true, $return_array = true){
        return cjoUrl::setServerUri($forward, $return_array);
    }
}

class I18N {
    public $locales;
    public $path;
    public $locale;
    public $text;
    public $fallback;
    public function __construct($locale=false, $path = false, $fallback_locale = 'de') {

        global $CJO;

        if ($path === false)  $path = $CJO['INCLUDE_PATH']."/lang";

        $this->path       = $path;
        $this->text       = array ();
        $this->locale     = $locale;
        $this->fallback   = $fallback_locale;
        $this->locales    = array ();
        $this->extendFromFile($path);
    }
    
    public function extendFromFile($path, $local=false) {

        if ($local === false) $local = $this->locale;

        $filename = $path."/".$local.".lang";

        if (!is_readable($filename)) {
            $local = $this->fallback;
            $filename   = $path."/".$local.".lang";
            if (!is_readable($filename)) return false;
        }

        $f = fopen($filename, "r");
        while (!feof($f)) {
            $buffer = fgets($f, 4096);
            if (preg_match("/^\s*(\w*)\s*=\s*(.*)$/", $buffer, $matches)) {
                if (!empty($this->text[trim($matches[1])])) continue;
                $this->text[trim($matches[1])] = trim($matches[2]);
            }
        }
        fclose($f);
    }
    public function msg($key, $p0 = '', $p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '', $p6 = '', $p7 = '', $p8 = '', $p9 = '') {
        global $CJO;

        $msg = (isset ($this->text[$key])) ? $this->text[$key] : '[translate: '.$key.']';

        $patterns = array ('/\{0\}/', '/\{1\}/',
                           '/\{2\}/', '/\{3\}/',
                           '/\{4\}/', '/\{5\}/',
                           '/\{6\}/', '/\{7\}/',
                           '/\{8\}/', '/\{9\}/');

        $replacements = array ($p0, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9);

        return preg_replace($patterns, $replacements, $msg);
    }
    public function extend($replace_vars = array()) {

        if (!is_array($replace_vars[$this->locale])) return;
        foreach($replace_vars[$this->locale] as $key=>$value){
            if($this->text[$key] != '') continue;
            $this->text[$key] = $value;
        }
    }

    public function getLocales($path = false) {

        if ($path === false)  $path = $this->path;

        if (empty ($this->locales) && is_readable($path)) {
            $this->locales = array ();

            $handle = opendir($path);
            while ($file = readdir($handle)) {
                if ($file != "." && $file != ".." && $file != ".svn") {
                    if (preg_match("/^(\w+)\.lang$/", $file, $matches)) {
                        $this->locales[] = $matches[1];
                    }
                }
            }
            closedir($handle);
        }
        return $this->locales;
    }

    public static function searchAndTranslate($params) {

        global $CJO, $I18N;

        $var_I18N = 'I18N';
        $search = 'translate';

        $content = (is_array($params)) ? $params['subject'] : $params;

        if ($CJO['CONTEJO']) list($content, $textareas) = cjoOutput::textareasToPlaceholders($content);

        if (!is_object($I18N)) $I18N = new cjoI18N($CJO['LANG']);

        preg_match_all('/\[translate_*(\d*)\s*\:\s*([^\]]*)\]/', $content, $matches, PREG_SET_ORDER);

        foreach($matches as $key=>$value) {

            if (!is_array($value)) continue;
            $cur_I18N = (!empty($value[1])) ? $var_I18N.'_'.$value[1] : $var_I18N;
            global $$cur_I18N;

            if (!is_object($$cur_I18N)) continue;
            $replace = $$cur_I18N->msg($value[2]);
            $content = str_replace($value[0], $replace, $content);
        }

        return ($CJO['CONTEJO']) ? cjoOutput::placeholdersToTextareas($content, $textareas) : $content;
    }
}

/**
 * @deprecated
 */
class OOAddon extends cjoAddon {
    
}

/**
 * @deprecated
 */
function cjo_getUrl($id = '', $clang = false, $params = '', $hash_string = '') {
    return cjoUrl::getUrl($id, $clang, $params, $hash_string);
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
function cjo_a22_getDefaultGlobalParams() {
    return cjoUrl::getDefaultGlobalParams();
}

/**
 * @deprecated
 */
function cjo_create_link($value, $global_params = array (), $local_params = array (), $tags = '') {
    return cjoUrl::createBELink($value, $local_params, $global_params, $tags);
}

/**
 * @deprecated
 */
function cjo_create_url($global_params = array (), $local_params = array ()) {
    return cjoUrl::createBEUrl($global_params, $local_params);
}

/**
 * @deprecated
 */
function cjo_copyDir($srcdir, $dstdir, $offset = '', $verbose = false) {
    return cjoFile::copyDir($srcdir, $dstdir, $offset, $verbose);
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

function imageProcessor_getImg($filename,
                                $x = null,
                                $y = null,
                                $resize = null,
                                $aspectratio = null,
                                $brand_on_off = null,
                                $brandimg = null,
                                $jpg_quality = null,
                                $crop_x = null,
                                $crop_y = null,
                                $crop_w = null,
                                $crop_h = null,
                                $shadow = null,
                                $fullpath = '') {
   return cjoImageProcessor::getImg ($filename,
                                    $x,
                                    $y,
                                    $resize,
                                    $aspectratio,
                                    $brand_on_off,
                                    $brandimg,
                                    $jpg_quality,
                                    $crop_x,
                                    $crop_y,
                                    $crop_w,
                                    $crop_h,
                                    $shadow,
                                    $fullpath);              
}

function cjo_json_encode_utf8($arr) {
        
    if (version_compare(PHP_VERSION, '5.4.0') >= 0) return json_encode($arr, JSON_UNESCAPED_UNICODE);
    
    //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
    array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
    return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
}

function convertAddOnSettings() {
        
    $addons = file_get_contents(cjoProp::get('FILE_CONFIG_ADDONS'));
    $pattern = '/^(.*)(\/\/.---.DYN.*\/\/.---.\/DYN)(.*)$/sU';
    $settings = preg_match($pattern,$addons,$matches);
    
    if (!empty($matches[2])) {
        eval($matches[2]);
    }
    cjoProp::saveToFile(cjoPath::pageData('addons'), $CJO);
    cjoProp::loadFromFile(cjoPath::pageData('addons'));
    unlink(cjoProp::get('FILE_CONFIG_ADDONS'));
}

global $I18N, $CJO;
$I18N = new I18N();
$CJO = & cjoProp::$properties;

if (cjoProp::isBackend()) {
    cjoSelectArticle::init();
    $CJO['SEL_ARTICLE'] = & cjoSelectArticle::$sel_article;
    cjoSelectMediaCat::init();
    $CJO['SEL_MEDIA']   = & cjoSelectMediaCat::$sel_media;
    cjoSelectLang::init();
    $CJO['SEL_LANG']    = & cjoSelectLang::$sel_lang;
}
/*
$CJO_USER                          = &$CJO["USER"];
$CJO_LOGIN                         = &$CJO["LOGIN"];
$article_id                        = &$CJO['ARTICLE_ID'];
$clang                             = cjoProp::getClang();
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
$CJO['MODUL_SET'][1]['FRONTEND']   = cjoProp::isBackend();

if (!cjoProp::get('CACHEFOLDER')) {
    cjoProp::set('CACHEFOLDER', 'cache');
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
*/