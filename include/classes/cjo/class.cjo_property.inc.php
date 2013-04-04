<?php

/**
 * REX base class for core properties etc.
 *
 * @author gharlan
 */
class cjoProp {

    const CONFIG_NAMESPACE = 'CJO';
    static private $initialized = false;  

    /**
     * Array of properties
     * @var array
     */
    static public $properties = array();

    static public function init() {
        if (self::$initialized) return;
        global $CJO;
        self::$properties = $CJO;
        self::$initialized = true;
        unset($CJO);
    }

    /**
     * @see cjoConfig::set()
     */
    static public function setConfig($key, $value) {
        return cjoConfig::set(self::CONFIG_NAMESPACE, $key, $value);
    }

    /**
     * @see cjoConfig::get()
     */
    static public function getConfig($key, $default = null) {
        return cjoConfig::get(self::CONFIG_NAMESPACE, $key, $default);
    }

    /**
     * @see cjoConfig::has()
     */
    static public function hasConfig($key) {
        return cjoConfig::has(self::CONFIG_NAMESPACE, $key);
    }

    /**
     * @see cjoConfig::remove()
     */
    static public function removeConfig($key) {
        return cjoConfig::remove(self::CONFIG_NAMESPACE, $key);
    }

    /**
     * Sets a property
     * @param string $key   Key of the property
     * @param mixed  $value Value for the property
     * @return boolean TRUE when an existing value was overridden, otherwise FALSE
     * @throws cjoException on invalid parameters
     */
    static public function set($key, $value) {
        
        self::init();
        if (!is_string($key) && !is_array($key)) {
            throw new cjoException('Expecting $key to be string or array, but ' . gettype($key) . ' given!');
        }
        $exists = false;
        $segments = is_array($key) ? $key : cjoAssistance::toArray($key);
        $properties =& self::$properties;
        foreach ($segments as $segment) {
            $exists = true;
            if (!isset($properties[$segment])) {
                $properties[$segment] = array();
                $exists = false;
            }
            $properties =& $properties[$segment];
        }
        $properties = $value;
        
        return $exists;
    }

    /**
     * Returns a property
     * @param string $key     Key of the property
     * @param mixed  $default Default value, will be returned if the property isn't set
     * @return the value for $key or $default if $key cannot be found
     * @throws cjoException on invalid parameters
     */
    static public function get($key, $default = null) {

        self::init();
        if (!is_string($key)) {
            throw new cjoException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        $data = null;
        foreach(cjoAssistance::toArray($key) as $key=>$property) {
            
            if ($key == 0) {
                if (!isset(self::$properties[$property])) return $default;
                $data = self::$properties[$property];
            }
            else {
                if (!isset($data[$property])) return $default;
                $data = $data[$property];
            }
        }
        return $data;
    }

    /**
     * Returns if a property is set
     * @param string $key Key of the property
     * @return boolean TRUE if the key is set, otherwise FALSE
     * @throws cjoException on invalid parameters
     */
    static public function has($key) {
        self::init();        
        return is_string($key) && isset(self::$properties[$key]);
    }

    /**
     * Removes a property
     * @param string $key Key of the property
     * @return boolean TRUE if the value was found and removed, otherwise FALSE
     * @throws cjoException on invalid parameters
     */
    static public function remove($key) {
        
        self::init();
        if (!is_string($key)) {
            throw new cjoException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        
        $data = array();
        foreach(cjoAssistance::toArray($key) as $key=>$property) {
            
            if ($key == 0) {
                if (!isset(self::$properties[$property])) return false;
                $data[0] = &self::$properties[$property];
            }
            else {
                if (!isset($data[$key-1][$property])) return false;
                $data[$key] = &$data[$key-1][$property];
            }
        }
        unset($data[$key]);
        return true;
    }

    /**
     * Returns if the setup is active
     * @return boolean
     */
    static public function isSetup() {
        return (boolean) self::get('SETUP', false);
    }

    /**
     * Returns if the environment is the backend
     * @return boolean
     */
    static public function isBackend() {
        return (boolean) self::get('CONTEJO', false);
    }

    /**
     * Returns if the debug mode is active
     * @return boolean
     */
    static public function isDebugMode() {
        return (boolean)self::get('debug', false);
    }

    /**
     * Returns if the safe mode is active
     * @return boolean
     */
    static public function isSafeMode() {
        return self::isBackend() && cjo_session('safemode', 'boolean', false);
    }

    
    static public function getDB($dbid) {
        return self::get('DB|'.$dbid, false);
    }  
    
    /**
     * Returns the current backend page
     * @return string
     */
    static public function getPage($default = null) {
        return self::get('PAGE_PAGE', $default);
    }
    
    /**
     * Sets the current backend page
     * @return string
     */
    static public function setPage($page) {
        return self::set('PAGE_PAGE', $page);
    }
    
    /**
     * Returns the current backend subpage
     * @return string
     */
    static public function getSubpage($default = null) {
        return self::get('PAGE_SUBPAGE', $default);
    }
    /**
     * Sets the current backend subpage
     * @return string
     */
    static public function setSubpage($subpage) {
        return self::set('PAGE_SUBPAGE', $subpage);
    }
     
    /**
     * Returns the current article id
     * @return string
     */
    static public function getArticleId($default = null) {
        if (self::get('ARTICLE_ID')) {
            return self::get('ARTICLE_ID');
        }
        elseif ($default === null) {
            $article_id = cjo_request('article_id','cjo-article-id', cjoProp::get('START_ARTICLE_ID', null));
            self::set('ARTICLE_ID', $article_id);
             return $article_id;
        }
        else {
            return (int) cjoProp::get($default, null);
        }
    }
    
    /**
     * Returns the ctype
     * @return string
     */
    static public function getCtype($default = 0) {
        if (self::get('CUR_CTYPE')) {
            return self::get('CUR_CTYPE');
        }
        else {
           $ctype = cjo_request('clang', 'cjo-ctype-id', 0);
           self::set('CUR_CTYPE', $ctype);
           return $ctype;
        }
    }
    
    /**
     * Returns the ctype name
     * @return string
     */
    static public function getCtypeName($ctype) {
        return self::get('CTYPE|'.$ctype, false);
    }
    
    /**
     * Returns the ctype
     * @return string
     */
    static public function isCtype($ctype) {
        return self::getCtypeName($ctype) !== false;
    }
    
    /**
     * Returns the current clangs
     * @return string
     */
    static public function getCtypes() {
        $ctypes = self::get('CTYPE');
        return array_keys($ctypes);
    }   
    
    /**
     * Returns the number ctypes
     * @return int
     */
    static public function countCtypes() {
        $ctypes = self::getCtypes();
        return count($ctypes);
    }  
    
    /**
     * Returns the current clang id
     * @return string
     */
    static public function getClang() {
        if (self::get('CUR_CLANG', false)) {
            return self::get('CUR_CLANG');
        }
        else {
           $clang = cjo_request('clang', 'int');
           self::set('CUR_CLANG', $clang);
           return $clang;
        }
    }
    
    /**
     * Returns the current clang name
     * @return string
     */
    static public function getClangName($id=false, $default = null) {
        if ($id === false) $id = self::getClang();
        $names = self::get('CLANG');
        return isset($names[$id]) ? $names[$id] : $default;
    } 
    
    /**
     * Returns the current clang iso
     * @return string
     */
    static public function getClangIso($id=false, $default = null) {
        if ($id === false) $id = self::getClang();
        $iso = self::get('CLANG_ISO');
        return isset($iso[$id]) ? $iso[$id] : $default;
    }    
      
    /**
     * Returns the current clangs
     * @return string
     */
    static public function getClangs() {
        $iso = self::get('CLANG_ISO');
        return array_keys($iso);
    }     

      
    /**
     * Returns the number clangs
     * @return int
     */
    static public function countClangs() {
        $clangs = self::getClangs();
        return count($clangs);
    }
           
    /**
     * Returns the instname of the contejo installation
     * @return string
     */
    static public function getInstname() {
        return self::get('INSTNAME');
    }
    
    /**
     * Returns a unique number based on the instname of the contejo installation
     * @return string
     */
    static public function getUniqueNumber() {
        return (int) preg_replace('/\D/i','', cjoProp::getInstname());
    }    
    
    /**
     * Returns the server name of the contejo installation
     * @return string
     */
    static public function getServerName() {
        return self::get('SERVERNAME', 'CONTEJO');
    } 
    
    /**     
     * Returns the http_host of the contejo installation
     * @return string
     */
    static public function getServer() {
        return self::get('SERVER', $_SERVER['HTTP_HOST']);
    } 
    
    /**
     * Returns the sessionduration of the contejo installation
     * @return string
     */
    static public function getSessionTime() {
        return self::get('SESSIONTIME');
    }

    /**
     * Returns the table prefix
     * @return string
     */
    static public function getTablePrefix() {
        return self::get('TABLE_PREFIX');
    }

    /**
     * Adds the table prefix to the table name
     * @param string $table Table name
     * @return string
     */
    static public function getTable($table) {
        if (!is_string($table)) {
            throw new cjoException('Expecting $table to be string, but ' . gettype($table) . ' given!');
        }
        return self::getTablePrefix().$table;
    }

    /**
     * Returns the temp prefix
     * @return string
     */
    static public function getTempPrefix() {
        return self::get('temp_prefix');
    }

    /**
     * Returns the current user
     * @return object
     */
    static public function getUser($value=false) {
        $user = self::get('USER');
        if ($value === false) return $user;
        return (cjoLoginSQL::isValid($user)) ? $user->getValue($value) : false;
    }

    /**
     * Returns the CONTEJO version
     * @param string $separator Separator between version, subversion and minorversion
     * @return string
     */
    static public function getVersion($separator = '.') {
        return self::get('VERSION') . $separator . self::get('RELEASE');
    }

    /**
     * Returns the title tag and if the property "use_accesskeys" is true, the accesskey tag
     * @param string $title Title
     * @param string $key   Key for the accesskey
     * @return string
     */
    static public function getAccesskey($title, $key) {
        if (self::get('use_accesskeys')) {
            $accesskeys = (array)self::get('accesskeys', array());
            if (isset($accesskeys[$key]))
                return ' accesskey="' . $accesskeys[$key] . '" title="' . $title . ' [' . $accesskeys[$key] . ']"';
        }

        return ' title="' . $title . '"';
    }

    /**
     * Returns the file perm
     * @return int
     */
    static public function getFilePerm() {
        return octdec((int)self::get('fileperm', 664));
    }

    /**
     * Returns the dir perm
     * @return int
     */
    static public function getDirPerm() {
        return octdec((int)self::get('dirperm', 775));
    }
    
    /**
     * load the config-data from a  config file
     *
     * @return Returns TRUE, if the data was successfully loaded from the config file, otherwise FALSE.
     */
    static public function loadFromFile($file) {
        self::init();
        $properties = cjoFile::getConfig($file.'.config');
        if (!is_array($properties)) {
            cjoMessage::addWarning(cjoI18N::translate('msg_loaded_empty_config', $file.'.config'));
            return false;
        }
        self::$properties = array_merge(self::$properties,$properties);
    }
    
    static private function testUpgrade($file) {
        
        if (is_readable($file.'.config')) return;

        $path = pathinfo($file);
        $path = glob($path['dirname'].'/*'.$path['filename'].'.inc.php');
        $old_vars = array();
        $old_vars = array_keys(get_defined_vars());
        
        include($path[0]);
        
        $data = array();
        foreach(array_keys(get_defined_vars()) as $var) {
            if (in_array($var, $old_vars) || 
                $var == 'php_errormsg' ||
                !is_array($$var)) continue;
 
            $data = array_merge($data, $$var);
        }

        self::save($file, $data);
    }
    
    /**
     * save the config-data to a config file
     *
     * @return Returns TRUE, if the data was successfully saved to the config file, otherwise FALSE.
     */
    static public function saveToFile($file, $data=false, $merge=true) {

        self::init();
        
        $properties = cjoFile::getConfig($file.'.config');

        if (empty($properties)) return false;        
        
        if ($data === false && $merge === true) $data = &self::$properties;
        
        if (!is_array($data)) return false;
        
            
        if ($merge === true) { 
            foreach($properties as $key=>$value) {
                if (!isset($data[$key]) || gettype($data[$key]) !== gettype($properties[$key])) continue;
                $properties[$key] = $data[$key];
            }
        } else {
            $properties = $data;
        }
        return self::save($file, $properties);
    }    

    /**
     * save the config-data
     */
    static private function save($file, $data) {
        return cjoFile::putConfig($file.'.config',$data);
    }
    
    /**
     * Gibt eine Fehlermeldung entsprechend der Datentyp validierung aus
     *
     * @param mixed zu prüfende Variable
     * @param string Datentype der Variable (ermitteln durch gettype)
     * @param string erwarteter Datentype
     * @param string Datei
     * @param string Zeilennummer
     * @access protected
     */
    private static function onErrorType($var, $type, $expected, $file=false, $line=false) {
    
        $position = '';
        $position .= $file ? "\r\n".$file : '';
        $position .= $line ? ' at Line: '.$line : '';
        $position .= "\r\n";
    
        switch ($expected) {
            case 'class' :
                throw new cjoException('Unexpected class for Object "'.$var.'"!'.$position);
            case 'subclass' :
                throw new cjoException('Class "'.$var.'" is not a valid subclass!'.$position);
                // filesystem-types
            case 'method' :
                throw new cjoException('Method "'.$var.'" not exists!'.$position);
            case 'dir' :
                throw new cjoException('Folder "'.$var.'" not found!'.$position);
            case 'file' :
                throw new cjoException('File "'.$var.'" not found!'.$position);
            case 'resource' :
                throw new cjoException('Var "'.$var.'" is not a valid resource!'.$position);
            case 'upload' :
                throw new cjoException('File "'.$var.'" is no valid uploaded file!'.$position);
            case 'readable' :
                throw new cjoException('Destination "'.$var.'" not readable!'.$position);
            case 'writable' :
                throw new cjoException('Destination "'.$var.'" not writable!'.$position);
            case 'callable' :
                if (is_array($var))
                $var = implode('::', $var);
                throw new cjoException('Function or Class "'.$var.'" not callable!'.$position);
    
            default :
                throw new cjoException('Unexpected type "'.$type.'" for "$'.$var.'"! Expecting type "'.$expected.'"'.$position);
        }
    }
    
    /**
     * Prüft die übergebene Variable auf einen bestimmten Datentyp
     * und bricht bei einem Fehler mit einer Meldung das Script ab.
     *
     * <code>
     * // Prüfung der Variable $url auf den type String
     * self::validateType($url, 'string', __FILE__, __LINE__);
     * </code>
     *
     * <code>
     * // Prüfung der Variable $param auf String ODER Array
     * self::validateType($param, array ('string', 'array'), __FILE__, __LINE__);
     * </code>
     *
     * <code>
     * // Prüfung von $file, ob die Datei existiert UND ob die Datei beschreibbar ist
     * self::validateType($file, array(array('file', 'readable')), __FILE__, __LINE__);
     * </code>
     *
     * @param mixed zu überprüfende Variable
     * @param mixed Kriterium das geprüft werden soll
     * @param string Datei
     * @param string Zeilennummer
     * @access public
     */
    public static function isValidType($var, $expected, $file=false, $line=false) {
        if (!self::validateType($var, $expected, $file, $line)) {
            self::onErrorType($var, gettype($var), $expected, $file, $line);
        }
    }  
    
    /**
     * Prüft die Übergebene Variable auf einen bestimmten Datentyp.
     * Diese Funktion verknüpft die übergebenen Kriterien mit logischen UND oder ODER.
     *
     * @param mixed zu überprüfende Variable
     * @param mixed Kriterium das geprüft werden soll
     * @param string Datei
     * @param string Zeilennummer
     * @return boolean true wenn die Variable $var allen Kriterien des Types $type entspricht, sonst false
     * @access protected
     */
    private static function validateType($var, $type, $file=false, $line=false) {
        if (is_array($type)) {
            foreach ($type as $_type) {
                if (is_array($_type)) {
                    foreach ($_type as $__type) {
                        // AND Opperator
                        // if one of the checks is NOT correct, return false
                        if (!self::checkVarType($var, $__type, $file, $line)) {
                            return false;
                        }
                    }
                } else {
                    // OR Opperator
                    // if one of the checks is correct, return true
                    if (self::checkVarType($var, $_type, $file, $line)) {
                        return true;
                    }
                }
            }
            return false;
        }
        elseif (is_string($type)) {
            if (self::checkVarType($var, $type, $file, $line)) {
                return true;
            }
        } else {
            self::onErrorType('type', gettype($type), 'array|string', __FILE__, __LINE__);
        }
        return false;
    }
    
    /**
     * Prüft die übergebene Variable auf einen bestimmten Datentyp
     *
     * @param mixed zu überprüfende Variable
     * @param mixed Kriterium das geprüft werden soll
     * @param string Datei
     * @param string Zeilennummer
     * @return bool true wenn die Variable $var dem Type $type entspricht, sonst false
     * @access protected
     */
    public static function checkVarType($var, $type, $file=false, $line=false) {
        switch ($type) {
            // simple-vartypes
            case 'boolean' :
                return is_bool($var);
            case 'integer' :
                return is_int($var);
            case 'double' :
                return is_double($var);
            case 'float' :
                return is_float($var);
            case 'scalar' :
                return is_scalar($var);
            case 'numeric' :
                return is_numeric($var);
            case 'string' :
                return is_string($var);
            case 'array' :
                return is_array($var);
                // object-types
            case 'NULL' :
            case 'null' :
                return is_null($var);
            case 'object' :
                return is_object($var);
            case 'class' :
                self::validateType($var, 'array', $file, $line);
                self::validateType($var[0], 'object', $file, $line);
                self::validateType($var[1], 'string', $file, $line);
                return is_a($var[0], $var[1]);
            case 'subclass' :
                self::validateType($var, 'array', $file, $line);
                self::validateType($var[0], 'object', $file, $line);
                self::validateType($var[1], 'string', $file, $line);
                return is_subclass_of($var[0], $var[1]);
                // filesystem-types
            case 'method' :
                self::validateType($var, 'array', $file, $line);
                self::validateType($var[0], 'object', $file, $line);
                self::validateType($var[1], 'string', $file, $line);
                return method_exists($var[0], $var[1]);
            case 'file' :
                return is_file($var);
            case 'dir' :
                return is_dir($var);
            case 'resource' :
                return is_resource($var);
            case 'upload' :
                return is_uploaded_file($var);
                // attributechecks
            case 'readable' :
                return is_readable($var);
            case 'writable' :
                return is_writable($var);
            case 'callable' :
                self::validateType($var, array ('string','array'), $file, $line);
                return is_callable($var);
            default :
                return false;
        }
    }  
}
