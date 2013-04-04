<?php

/**
 * Utility class to generate absolute paths
 *
 * @author gharlan
 *
 * @package redaxo5
 */
class cjoPath {
    
    static protected $base, $backend;
    
    static public function init($htdocs, $backend) {
        if (self::$base != NULL) return false;
        self::$base = realpath($htdocs).DIRECTORY_SEPARATOR;
        self::$backend = 'core';
    }
    
    /**
     * Returns a base path
     */
    static public function base($file = '') {
        return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, self::$base.$file);
    }

    /**
     * Returns the path to the frontend
     */
    static public function frontend($file = '') {
        return self::base($file);
    }

    /**
     * Returns the path to the frontend-controller (index.php from frontend)
     */
    static public function frontendController() {
        return self::base('index.php');
    }

    /**
     * Returns the path to the backend
     */
    static public function backend($file = '') {
        return self::base(self::$backend.DIRECTORY_SEPARATOR.$file);
    }

    /**
     * Returns the path to the backend-controller (index.php from backend)
     */
    static public function backendController() {
        return self::backend('index.php');
    }

    /**
     * Returns the path to the media-folder
     */
    static public function media($file = '') {
        return self::base(cjoProp::get('MEDIAFOLDER').DIRECTORY_SEPARATOR.$file);
    }

    /**
     * Returns the path to the cache folder of the core
     */
    static public function cache($file = '') {
        return self::base('cache/'.str_replace('cache'.DIRECTORY_SEPARATOR, '', $file));
    }
    
    /**
     * Returns the path to the generated cache folder of the core
     */
    static public function generated($type, $file = '') {
        $subfolder = ($type == 'articles') ? 'FOLDER_GENERATED_ARTICLES' : 'FOLDER_GENERATED_TEMPLATES';
        return self::cache(cjoProp::get($subfolder).DIRECTORY_SEPARATOR.$file);
    }

    /**
     * Returns the path to the cache folder of the given addon.
     */
    static public function addonCache($addon, $file = '') {
        return self::cache(cjoProp::get('CACHEFOLDER').DIRECTORY_SEPARATOR.$addon.DIRECTORY_SEPARATOR.$file);
    }

    /**
     * Returns the path to the core
     */
    static public function inc($file = '') {
        return self::backend('include'.DIRECTORY_SEPARATOR.$file);
    }
    
    /**
     * Returns the path to the install folder
     */
    static public function install($file = '') {
        return self::base(cjoProp::get('INSTALL_PATH').DIRECTORY_SEPARATOR.$file);
    }  
    
    /**
     * Returns the base path to the folder of the given addon
     */
    static public function addon($addon, $file = '') {
        return self::base(cjoProp::get('ADDON_PATH').DIRECTORY_SEPARATOR.$addon.DIRECTORY_SEPARATOR.$file);
    }
    
    /**
     * Returns the path to the page folder 
     */
    static public function page($file = '') {
        return self::base(cjoProp::get('FRONTPAGE_PATH').DIRECTORY_SEPARATOR.$file);
    }
    
    /**
     * Returns the path to the include folder of the page
     */
    static public function pageConfig($file = '') {
        return self::base(cjoProp::get('PAGE_CONFIG').DIRECTORY_SEPARATOR.$file);
    }
    
    /**
     * Returns the path to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @see assets()
     */
    static public function addonAssets($addon, $file = '') {
        return self::base(cjoProp::get('ADDON_CONFIG_PATH').DIRECTORY_SEPARATOR.$addon.DIRECTORY_SEPARATOR.$file);
    }
    
    /**
     * Returns the path to the individual upload folder of the user
     */
    static public function uploads($file = '') {
        
        if (!cjoProp::isBackend() || !cjoProp::getUser()) return false;
        
        $path = self::base(cjoProp::get('UPLOADFOLDER').DIRECTORY_SEPARATOR.cjo_url_friendly_string(cjoProp::getUser()->getValue('login')));
    
        if (is_dir($path) || mkdir($path, cjoProp::getDirPerm())) return $file != '' ? $path.'/'.$file : $path;
    }
    
/**
     * Parses trough an directory and returns the containing
     * files and directories with their containing files as
     * a directory tree. Works recursive.
     *
     * @param $dir (string), default=false - the directory name
     * @param $tree (array(string)), default=empty array - the directory tree
     * @param $only_files (bool), default=true - displays only files if true
     * @param $limit (int), default=2 - the maximum depth of tree level
     * @param $level (int), default=1 - the tree starting level
     * @param $pattern (string), default=starts with a point - constructs the tree key for filename
     * @param $nbsp (string), default=&nbsp; - set a new line for every entry, tormatting matter
     * @param $arrow (string), default=&rarr - the directory level symbol
     */
    public static function parseDir($dir = false, $tree = array(), $only_files = true, $limit = 2, $level = 1, $pattern = '/.*/i', $nbsp = '&nbsp;|', $arrow = '&rarr;') {

        if (empty($dir))
            return array();

        $nbsp .= ($level == 1) ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|';

        $handle = opendir($dir);
        while (false !== ($item = readdir($handle))) {
            if (preg_match('/^\.+/',$item)) continue;

            if (is_dir($dir.DIRECTORY_SEPARATOR.$item) && $level < $limit) {

                if ($only_files == false && preg_match($pattern, $item, $name)) {
                    $name = $nbsp . $arrow . $name[0];
                    $tree[$name] = $dir.DIRECTORY_SEPARATOR.$item;
                }
                $tree = cjoAssistance::parseDir($dir.DIRECTORY_SEPARATOR. $item, $tree, $only_files, $limit, ($level + 1), $pattern, $nbsp);
            } elseif ($only_files == true && preg_match($pattern, $item, $name)) {
                $name = $nbsp . $arrow . $name[0];
                $tree[$name] = $dir.DIRECTORY_SEPARATOR.$item;
            }
        }
        closedir($handle);
        return $tree;
    }
    
    /**
     * Converts a relative path to an absolute
     *
     * @param string $relPath The relative path
     *
     * @return string Absolute path
     */
    static public function absolute($relPath) {
        $stack = array();

        // pfadtrenner vereinheitlichen
        $relPath = str_replace('\\', '/', $relPath);
        foreach (explode('/', $relPath) as $dir) {
            // Aktuelles Verzeichnis, oder Ordner ohne Namen
            if ($dir == '.' || $dir == '')
                continue;

            // Zum Parent
            if ($dir == '..')
                array_pop($stack);
            // Normaler Ordner
            else
                array_push($stack, $dir);
        }

        return implode('/', $stack);
    }

}
