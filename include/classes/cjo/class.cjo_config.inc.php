<?php

/**
 * Class for handling configurations.
 * The configuration is persisted between requests.
 *
 * @author staabm
 */
class cjoConfig {
    
    /**
    * Flag to indicate if the config was initialized
    * @var boolean
    */
    static private $initialized = false;   
    static public $data; 
    

    static public function set($namespace, $key, $value) {
        
        self::init();
        
        if (!is_string($namespace)) {
            throw new cjoException('cjoConfig: expecting $namespace to be a string, ' . gettype($namespace) . ' given!');
        }
        if (!is_string($key)) {
            throw new cjoException('cjoConfig: expecting $key to be a string, ' . gettype($key) . ' given!');
        }
        if (!isset(self::$data[$namespace]))
          self::$data[$namespace] = array();
    
        $existed = isset(self::$data[$namespace][$key]);
        if (!$existed || $existed && self::$data[$namespace][$key] !== $value) {
          // keep track of changed data
          if (!isset(self::$changedData[$namespace]))
            self::$changedData[$namespace] = array();
          self::$changedData[$namespace][$key] = $value;
    
          // since it was re-added, do not longer mark as deleted
          if (isset(self::$deletedData[$namespace]) && isset(self::$deletedData[$namespace][$key]))
            unset(self::$deletedData[$namespace][$key]);
    
          // re-set the data in the container
          self::$data[$namespace][$key] = $value;
          self::$changed = true;
        }
    
        return $existed;
    }

    /**
     * initilizes the rex_config class
     */
    static protected function init() {
        
        if (self::$initialized) return;
        self::$data = array();
        self::$initialized = true;
    }

    /**
     * load the config-data
     */
    static public function load($file) {
        global $CJO;
        self::init();
        self::$data = $CJO;
        $data = cjoFile::getConfig($file.'.config');

        if (is_array($data))
            self::$data = array_merge(self::$data,$data);

        $CJO = self::$data;
    }

    /**
     * load the config-data from a  config file
     *
     * @return Returns TRUE, if the data was successfully loaded from the config file, otherwise FALSE.
     */
    static private function loadFromFile($file) {

        self::init();

        $data = cjoFile::getConfig($file.'.config');
        if (is_array($data))
            self::$data = array_merge(self::$data,$data);
    }
    
    /**
     * save the config-data to a config file
     *
     * @return Returns TRUE, if the data was successfully saved to the config file, otherwise FALSE.
     */
    static public function saveToFile($file) {

        $config = cjoFile::getConfig($file.'.config');
        if (empty($config)) return false;
 
        foreach($config as $key=>$value) {
            if (!isset(self::$data[$key]) || gettype(self::$data[$key]) !== $config[$key]) continue;
            $config[$key] = self::$data[$key];
        }
        return self::save($file, $config);
    }    

    /**
     * save the config-data
     */
    static private function save($file, $data) {
        return cjoFile::putConfig($file.'.config',$data);
    }
}
