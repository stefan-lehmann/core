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
 * cjoI18N class
 *
 * The cjoI18N class provides localisation for the backend.
 * @package 	contejo
 * @subpackage 	core
 */

class cjoI18N {
    
    /**
     * all available language files
     * @var array
     */
    protected $locales;

    /**
     * path to language files
     * @var string
     */
    protected $path;

    /**
     * local var
     * @var string
     */
    protected $locale;

    /**
     * search and replace values
     * @var array
     */
    protected $text;

    /**
     * fallback language
     * @var string
     */
    protected $fallback;
    
    /**
     * global static translation object
     * @var string
     */
    public static $i18n;    

    /**
     * Constructor
     * @param string $locale must of the common form, eg. de_DE, en_US or just plain en, de.
     * @param string $path is where the language files are located
     * @param string $fallback_locale
     * @return cjoI18N
     * @access public
     */
    public function __construct($namespace='', $path=false, $locale=false, $fallback_locale='de') {

        if ($path === false)   $path = cjoPath::inc('lang');
        if ($locale === false) $locale = cjoProp::get('LANG');
        
        $this->namespace  = $namespace;
        $this->path       = $path;
        $this->text       = array ();
        $this->locale     = $locale;
        $this->fallback   = $fallback_locale;
        $this->locales    = array ();
        $this->extendFromFile($namespace, $path);
        $this->setlocal();
        
        return $this;
    }

    private function setlocal() {
        setlocale(LC_ALL,cjoAssistance::toArray($this->msg('','setlocale'),','));
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
     * @access protected
     */
    protected function extendFromFile($namespace, $path, $local=false) {

    	if ($local === false) $local = $this->locale;

        if (!is_dir($path)) return false;

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
                $key = !empty($namespace) ? $namespace.'_'.trim($matches[1]) : trim($matches[1]);
            	if (!empty($this->text[$key])) continue;
                $this->text[$key] = trim($matches[2]);
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
    public function msg($namespace, $key=null, $p0 = '', $p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '', $p6 = '', $p7 = '', $p8 = '', $p9 = '') {

        $new_key = !empty($namespace) ? $namespace.'_'.trim($key) : trim($key);
        
        if (!empty($namespace) && isset($this->text[$new_key])) {
            $msg = $this->text[$new_key];
        }
        elseif(isset($this->text[$key])) {
            $msg = $this->text[$key];
        }
        else {
            $msg = '[translate: '.$new_key.']';
        }

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
     * @access protected
     */
    protected function extend($replace_vars = array()) {

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
     * @access public
     */
    public function getLocales($path = false) {

        if ($path === false)  $path = $this->path;

        if (empty ($this->locales)) {
            $this->locales[] = glob($path.'/*.lang');
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

        $search = 'translate';

        $content = (is_array($params)) ? $params['subject'] : $params;

        if (cjoProp::isBackend()) list($content, $textareas) = cjoOutput::textareasToPlaceholders($content);

        preg_match_all('/\[translate_*(\d*)\s*\:\s*([^\]]*)\]/', $content, $matches, PREG_SET_ORDER);

        foreach($matches as $key=>$value) {
            if (!is_array($value)) continue;
            $replace = (empty($value[1])) ? self::translate($value[2]) : self::translateAddon($value[1], $value[2]);
            $content = str_replace($value[0], $replace, $content);
        }

        return (cjoProp::isBackend()) ? cjoOutput::placeholdersToTextareas($content, $textareas) : $content;
    }
    
    public static function init($namespace='', $path=false, $locale=false, $fallback_locale='de') {

        if (!is_string($namespace)) {
            throw new cjoException('Expecting $namespace to be string, but ' . gettype($namespace) . ' given!');
        }
        
        if (empty(self::$i18n)) {
            self::$i18n = new cjoI18N($namespace, $path, $locale, $fallback_locale);

        }
        else {
            self::$i18n->extendFromFile($namespace, $path);
        }
    }

    public static function reset() {
        self::$i18n = null;
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
     * @access public
     */
    public static function translate($key, $p0 = '', $p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '', $p6 = '', $p7 = '', $p8 = '', $p9 = '') {

        if (!is_string($key)) {
            throw new cjoException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }   

        return self::$i18n->msg('', $key, $p0, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9);
    }
    
    /**
     * Returns a message according to a key from the current locale
     * You can give up to 10 parameters for substitution.
     * @param string $namespace
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
     * @access public
     */
    public static function translateAddon($namespace, $key, $p0 = '', $p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '', $p6 = '', $p7 = '', $p8 = '', $p9 = '') {

        if (!is_string($namespace) && (string) $namespace != $namespace) {
            throw new cjoException('Expecting $namespace to be string, but ' . gettype($namespace) . ' given!');
        }
        if (!is_string($key)) {
            throw new cjoException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }           
        return self::$i18n->msg((string) $namespace, $key, $p0, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9);
    }    
}