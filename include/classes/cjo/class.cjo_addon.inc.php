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
 * self class
 *
 * The cjoAddon class provides methods to verify the availabilty of addons
 * @package 	contejo
 * @subpackage 	core
 */
class cjoAddon {

    /**
     * Returns true if a addon is available
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isAvailable($addon) {
        return self::isInstalled($addon) && self::isActivated($addon);
    }

    /**
     * Returns true if a addon is activated
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isActivated($addon) {
        return self::getProperty('status',$addon, false);
    }

    /**
     * Returns true if a addon is activated or not.
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isInstalled($addon) {
        return self::getProperty('install',$addon, false);
    }

    /**
     * Returns true if a addon is a sytem addon.
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isSystemAddon($addon){
        return in_array($addon, cjoProp::get('SYSTEM_ADDONS'));
    }

    /**
     * Returns the version of the addon.
     * @param string $addon name of the addon
     * @return string
     * @access public
     */
    public static function getVersion($addon, $default = null) {
        return self::getProperty($addon, 'version', $default);
    }

    /**
     * Returns the author of the addon.
     * @param string $addon name of the addon
     * @param string|null $default default value if author is not available
     * @return string
     * @access public
     */
    public static function getAuthor($addon, $default = null) {
        return self::getProperty($addon, 'author', $default);
    }

    /**
     * Returns the supportpage of the addon.
     * @param string $addon  name of the addon
     * @param string $default default value if supportpage is not available
     * @return string
     * @access public
     */
    public static function getSupportPage($addon, $default = null) {
        return self::getProperty($addon, 'supportpage', $default);
    }

    /**
     * Returns the value of a addon property.
     * @param string $property name of the property
     * @param string $addon name of the addon
     * @param string $default default value if the property is not available
     * @access public
     */
    public static function getProperty($property, $addon = false, $default = null){
        $addons = cjoProp::get('ADDON');
        if ($addon === false) return $addons[$property];
        return isset($addons[$property][$addon]) ? $addons[$property][$addon] : $default;
    }
    
    /**
     * Removes a addon property.
     * @param string $property name of the property
     * @param string $addon name of the addon
     * @access public
     */
    public static function removeProperty($property, $addon) {
        return cjoProp::remove('ADDON|'.$property.'|'.$addon);
    }
    
    /**
     * Sets the property of an addon.
     * @param string $property name of the property
     * @param mixed $value new value of the property
     * @param string $addon name of the addon
     * @access public
     */
    public static function setProperty($property, $value, $addon) {

        $addons = cjoProp::get('ADDON');
        if (!isset($addons[$property])) $addons[$property] = array();
        $addons[$property][$addon] = $value;
        return cjoProp::set('ADDON', $addons);
    }

    public static function setProperties($values, $addon) {
        
        if (!is_array($values)) return false;
        foreach($values as $property=>$value) {
            self::setProperty($property, $value, $addon);
        }
    }
    
    public static function setParameter($parameter, $value, $addon) {    
        return cjoProp::set('ADDON|settings|'.$addon.'|'.$parameter, $value);
    }
    
    public static function getParameter($parameter, $addon, $default = null) {
        return cjoProp::get('ADDON|settings|'.$addon.'|'.$parameter, $default);
    }    

    public static function readParameterFile($addon, $path=false) {
        if ($path === false) $path = cjoPath::addonAssets($addon,'settings');

        if (file_exists($path.'.config')) {
            $data = cjoFile::getConfig($path.'.config');
            if (is_array($data)) {
                self::setParameter('settings', $data, $addon);
                return $data;
            }
        }
        return array();
    }
    
    static public function saveParameterFile($addon, $data, $path=false) {
        if ($path === false) $path = cjoPath::addonAssets($addon,'settings');
        return cjoProp::saveToFile($path, $data);
    }        
    
    /**
     * Removes a addon paramter.
     * @param string $parameter name of the parameter
     * @param string $addon name of the addon
     * @access public
     */
    public static function removeParameter($parameter, $addon){
        return cjoProp::remove('ADDON|settings|'.$addon.'|'.$parameter);
    }
    
    /**
     * Returns the permissions of a addon .
     * @param string $addon name of the addon
     * @access public
     */
    public static function getPerm($addon){
        return cjoAssistance::toArray(self::getPermissions('perm', $addon));
    }
    
    /**
     * Returns true if the addon has permissions
     * @param string $addon name of the addon
     * @access public
     */
    public static function hasPerm($addon){
        $perm = self::getPerm($addon);
        return !empty($perm);
    }  

    /**
     * Returns a message according to a key from the current locale
     * You can give up to 10 parameters for substitution.
     * @param string $addon name of the addon
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
    public static function translate($addon, $key, $p0 = '', $p1 = '', $p2 = '', $p3 = '', $p4 = '', $p5 = '', $p6 = '', $p7 = '', $p8 = '', $p9 = '') {
        return cjoI18N::translateAddon($addon, $key, $p0, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9);
    }

    public static function enableMenuAddon($addon, $menu = 0) {

    	if (!cjoProp::isBackend()) return false;

    	if (self::isInstalled($addon)) {
    		self::setProperty('menu', $menu, $addon);
    		self::generateAddons();
    	}
    	else {
    		cjoMessage::addError(cjoI18N::translate("msg_addon_no_show_menu", $addon));
    	}
        return !cjoMessage::hasErrors();
    }

    public static function activateAddon($addon) {

    	if (!cjoProp::isBackend()) return false;

    	if (self::isInstalled($addon)) {
            self::setProperty('status', 1, $addon);
            
    		if (cjoAddon::getProperty('menu', $addon) == 0) {
                self::setProperty('menu', 'addons', $addon);
    		}
    		self::generateAddons();
    	}
    	else {
    		cjoMessage::addError(cjoI18N::translate("msg_addon_no_activation", $addon));
    	}

        if (cjoMessage::hasErrors()) return false;

        cjoExtension::registerExtensionPoint('ADDON_ACTIVATED_'.strtoupper($addon), array('addon' => $addon));
        cjoExtension::registerExtensionPoint('ADDON_ACTIVATED', array('addon' => $addon));  
           
        cjoMessage::addSuccess(cjoI18N::translate("msg_addon_activated", $addon));
         	
    	return true;
    }

    public static function deactivateAddon($addon) {

    	if (!cjoProp::isBackend()) return false;

    	$uninstall = cjoPath::addon($addon, 'uninstall.inc.php');

    	if (cjoAddon::isSystemAddon($addon)){
    	    cjoMessage::flushErrors();
    		cjoMessage::addError(cjoI18N::translate("msg_addon_uninstall_sytem_addon"));
    		return false;
    	}

        self::setProperty('status', 0, $addon);
    	self::generateAddons();

      	if (cjoMessage::hasErrors()) return false;

    	cjoMessage::addSuccess(cjoI18N::translate("msg_addon_deactivated", $addon));
        cjoExtension::registerExtensionPoint('ADDON_DEACTIVATED', array('addon' => $addon));     	
    	return true;
    }

    public static function installAddon($addon) {

    	if (!cjoProp::isBackend()) return false;
        
        $path = self::getAddonPath($addon);
        
        if ($path === false) {
            cjoI18N::translate("msg_addon_not_found");
            return false;
        }

        if (cjoInstall::installResource($addon) && self::getInstallMessages($addon)) {
            self::setProperty('install', 1, $addon);

            if (self::isActivated($addon)) self::initAddon($addon, $path);
            self::generateAddons();
            cjoExtension::registerExtensionPoint('ADDON_INSTALLED', array('addon' => $addon));            
        };

        if (!cjoMessage::hasErrors() && !cjoMessage::hasWarnings()) {
            cjoMessage::addSuccess(cjoI18N::translate("msg_addon_installed"));
            return true;
        }
        return false;
    }


    public static function uninstallAddon($addon) {

    	if (!cjoProp::isBackend()) return false;
        
        $path = self::getAddonPath($addon);
        
        if ($path === false) {
            cjoI18N::translate("msg_addon_not_found");
            return false;
        }
        
        self::deactivateAddon($addon);
            
        if (cjoMessage::hasErrors()) return false;
    	
    	cjoInstall::uninstallResource($addon);

        if (cjoMessage::hasErrors()) return false;
        
        self::setProperty('install', 0, $addon);
        self::generateAddons();

    	cjoMessage::addSuccess(cjoI18N::translate("msg_addon_uninstalled", $addon));
        cjoExtension::registerExtensionPoint('ADDON_UNINSTALLED', array('addon' => $addon));                	
    	return true;
    }

    public static function deleteAddon($addon) {

    	if (!cjoProp::isBackend()) return false;

    	self::uninstallAddon($addon);

    	if (!cjoMessage::hasErrors()) {
    		// bei erfolg, komplett löschen
    		//cjoAssistance::deleteDir(cjoPath::addon($addon), true);
    		//cjoAssistance::deleteDir(cjoPath::addonAssets($addon), true);
    		self::removeProperty('status', $addon);
            self::removeProperty('install', $addon);
            self::removeProperty('menu', $addon);
    		self::generateAddons();
    	}

    	if (cjoMessage::hasErrors()) return false;

    	cjoMessage::addSuccess(cjoI18N::translate("msg_addon_deleted"));
        cjoExtension::registerExtensionPoint('ADDON_DELETED', array('addon' => $addon));      	
    	return true;
    }

    public static function loadAddons() {

        $permaddon = array();

        foreach(self::getProperty('status') as $addon=>$status){

            $path = self::getAddonPath($addon);
            if ($path == false) continue;

            self::initAddon($addon, $path);

            if (!$status || !cjoAddon::getProperty('perm', $addon, false)) continue;
        
            foreach(cjoAssistance::toArray(cjoAddon::getProperty('perm',$addon)) as $key => $perm){
                if (empty($perm)) continue;
                $key = (is_numeric($key)) ? cjoAddon::getProperty('name',$addon) : $key;
                $permaddon[$key] = $perm;
            }
        }  
        cjoProp::set('PERMADDON', $permaddon);
        cjoExtension::registerExtensionPoint('ADDONS_INCLUDED');
    }

    private static function initAddon($addon, $path=false) {
        
        if (!self::isActivated($addon)) return false;
        
        $config = cjoFile::getConfig($path.$addon.'.config');    
            
        if (empty($config)) cjoMessage::addWarning(cjoI18N::translate('msg_addon_has_invalid_config', $addon));

        if (file_exists(cjoPath::addonAssets($addon).'settings.config')) {
            $settings = cjoAddon::readParameterFile($addon, cjoPath::addonAssets($addon,'settings'));
            if (is_array($config['settings'])) {
                $config['settings'] = array_merge($config['settings'], $settings);
            }
            else {
                $config['settings'] = $settings;
            }
        }
        
        if (empty($config)) return false;
        
        if (is_array($config['tables'])) {
            foreach($config['tables'] as $key=>$value) {
                $key = strtoupper($key);
                if (defined($key)) continue;
                define($key, cjoProp::getTable($value));
            }
        }

        cjoI18N::init((string) $config['addon_id'], cjoPath::addon($addon, 'lang'));

        if (empty($config['name']) && is_string($config['page'])) $config['name'] = self::translate($config['addon_id'], $config['page']);

        if (is_array($config['onsetup']) && $config['setup']) {
            
            if (!cjoProp::isBackend()) {
                cjoAddon::setProperty('status', $config['onsetup']['status'], $addon);
                return;
            }
            
            if (cjo_get('page','string') != $addon && cjo_get('subpage','string') != $config['onsetup']['subpage']) {
                $url = cjoUrl::createBEUrl(array('page' => $addon, 'subpage' => $config['onsetup']['subpage']));
                cjoMessage::addWarning(self::translate($addon, $config['onsetup']['subpage'], $url));
            }
        }
        
        if (cjoAddon::isActivated($addon)) {
  
            if (is_array($config['autoload'])) {
                foreach($config['autoload'] as $key=>$value) {
                    cjoAutoload::addDirectory(cjoPath::addon($addon, $value));
                }
            }
            if (is_array($config['extensions'])) {
                foreach($config['extensions'] as $key=>$value) {
                    if (!is_array($value)) continue;
                    foreach($value as $extention) {
                        if (isset($extention["environment"]) && $extention["environment"] == "frontend" && !cjoProp::isBackend()) continue; 
                        if (isset($extention["environment"]) && $extention["environment"] == "backend" && !!cjoProp::isBackend()) continue; 
                        cjoExtension::registerExtension($key, $extention["callback"]);
                    }
                }
            }

            if (isset($config['include']) && file_exists(cjoPath::addon($addon, $config['include']))) {
                include_once cjoPath::addon($addon, $config['include']);
            }
        }   

        self::setProperties($config, $addon);
    }

    private static function getAddonPath($addon){
         $path = cjoPath::addon($addon);
         if (!file_exists($path.$addon.'.config')) $path = cjoPath::addonAssets($addon);
         if (!file_exists($path.$addon.'.config')) $path = false;
         self::convertConfigFiles($addon);
         return $path;
    }

    private static function convertConfigFiles($addon) {
        
        if (cjoProp::get("SETUP") != true) return false;
        $convert1 = cjoAssistance::updateConfigFiles(cjoPath::addon($addon));
        $convert2 = cjoAssistance::updateConfigFiles(cjoPath::addonAssets($addon));  
        return $convert1 || $convert2;       
    }

    public static function readAddonsFolder($folder = false) {

    	$addons = cjoProp::get('SYSTEM_ADDONS');
    	$folder = !$folder ? cjoPath::base(cjoProp::get('ADDON_PATH')) : $folder;

    	foreach(glob('{'.cjoUrl::base(cjoProp::get('ADDON_PATH')).'/*,'.cjoUrl::base(cjoProp::get('ADDON_CONFIG_PATH')).'/*}',
    	             GLOB_ONLYDIR|GLOB_BRACE) as $file) {
    	                 
            $file = pathinfo($file,PATHINFO_BASENAME);

    	    if (in_array($file,cjoProp::get('SYSTEM_ADDONS'))) continue;
    	    $addons[$file] = $file;
    	}

        $addons = array_values($addons);

    	$temp1 = @array_keys(array_flip($addons));
        $temp2 = @array_keys(cjoAddon::getProperty('install'));

        $count1 = count(@array_diff($temp1, $temp2));
        $count2 = count(@array_diff($temp2, $temp1));
        

    	if ($count1 > 0 || $count2 > 0) {
    	    $generated = array();
            foreach($temp1  as $addon) {
                $generated["install"][$addon] = cjoAddon::getProperty("install", $addon, 0);
                $generated["status"][$addon] = cjoAddon::getProperty("status", $addon, 0);
                $generated["menu"][$addon] = cjoAddon::getProperty("menu", $addon, 0);
            }
            
            cjoProp::set('ADDON', array_merge(cjoProp::get('ADDON'), $generated));

    		return self::generateAddons();
    	}
    }

    private static function getInstallMessages($addon) {
        
        $status = true;
        $addon_id = self::getProperty('addon_id',$addon);
        $setup = self::getProperty('setup', $addon);

        if (empty($setup['message'])) return $status;
        
        foreach($setup['message'] as $message) {
            
            $text = cjoI18N::translateAddon($addon_id, $message[1], cjoAddon::getProperty('name',$addon));

            switch($message[0]) {
                case 'error':   cjoMessage::addError($text); break;
                case 'warning': cjoMessage::addWarning($text); break;
                case 'success': cjoMessage::addSuccess($text); break;
            }
            if (isset($message[2])) $status = (bool) $message[2];
        }

        return $status;
    }
    
    /**
     * Schreibt Addoneigenschaften in die addon config Datei 
     */
    public static function generateAddons() {

        return cjoProp::saveToFile(cjoPath::pageConfig('addons'), 
                                   array('ADDON' => array("install" => cjoAddon::getProperty("install"),
                                                          "status"  => cjoAddon::getProperty("status"),
                                                          "menu"    => cjoAddon::getProperty("menu"))),
                                    false);
    }
}