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
 * i18n class
 *
 * The i18n class provides localisation for the backend.
 * @package 	contejo
 * @subpackage 	core
 */
class i18n {

    /**
     * all available language files
     * @var array
     */
    public $locales;

    /**
     * path to language files
     * @var string
     */
    public $path;

    /**
     * local var
     * @var string
     */
    public $locale;

    /**
     * search and replace values
     * @var array
     */
    public $text;

    /**
     * fallback language
     * @var string
     */
    public $fallback;

    /**
     * Constructor
     * @param string $locale must of the common form, eg. de_DE, en_US or just plain en, de.
     * @param string $path is where the language files are located
     * @param string $fallback_locale
     * @return i18n
     * @access public
     */
    public function __construct($locale, $path = false, $fallback_locale = 'de') {

        global $CJO;

        if ($path === false)  $path = $CJO['INCLUDE_PATH']."/lang";

        $this->path       = $path;
        $this->text       = array ();
        $this->locale     = $locale;
        $this->fallback   = $fallback_locale;
        $this->locales    = array ();
        $this->extendFromFile($path);
    }

    /**
     * Loads the texts from the given file.
     *
     * The filename must be of the form:
     * <locale>.lang (eg: de_DE.lang, en_US.lang, en_GB.lang)
     *
     * The file must be in the common property format:	 *
     * key = value
     *
     * # comments must be on one line
     *
     * values may contain placeholders for replacement of variables, e.g.
     * file_not_found = The file {0} could not be found.
     * there can be only 10 placeholders, {0} to {9}.
     * @return void
     * @access public
     */
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

    /**
     * Returns a message according to a key from the current locale
     * You can give up to 10 parameters for substitution.
     * @param string $key
     * @param string|int|float $p0
     * @param string|int|float $p1
     * @param string|int|float $p2
     * @param string|int|float $p3
     * @param string|int|float $p4
     * @param string|int|float $p5
     * @param string|int|float $p6
     * @param string|int|float $p7
     * @param string|int|float $p8
     * @param string|int|float $p9
     * @return string
     * @access protected
     */
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

    /**
     * Extends the seach and replace values.
     * @param array $replace_vars
     * @return void
     * @access public
     */
    public function extend($replace_vars = array()) {

        if (!is_array($replace_vars[$this->locale])) return;
        foreach($replace_vars[$this->locale] as $key=>$value){
            if($this->text[$key] != '') continue;
            $this->text[$key] = $value;
        }
    }

    /**
     * Find all defined locales in a searchpath
     * the language files must be of the form: <locale>.lang
     * e.g. de_de.lang or en_gb.lang
     * @param string $path
     * @return array
     * @access protected
     */
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

    /**
     * Searches and replaces translate strings.
     * @param string $content
     * @return string
     * @access public
     */
    public static function searchAndTranslate($params) {

        global $CJO, $I18N;

        $var_I18N = 'I18N';
        $search = 'translate';

        $content = (is_array($params)) ? $params['subject'] : $params;

        if ($CJO['CONTEJO']) list($content, $textareas) = cjoOutput::textareasToPlaceholders($content);

        if (!is_object($I18N)) $I18N = new i18n($CJO['LANG']);

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