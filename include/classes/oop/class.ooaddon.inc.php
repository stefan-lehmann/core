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
 * OOAddon class
 *
 * The OOAddon class provides methods to verify the availabilty of addons
 * @package 	contejo
 * @subpackage 	core
 */
class OOAddon {

    /**
     * Returns true if a addon is available
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isAvailable($addon) {
        return OOAddon::isInstalled($addon) && OOAddon::isActivated($addon);
    }

    /**
     * Returns true if a addon is avtivated
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isActivated($addon) {
        global $CJO;
        return isset( $CJO['ADDON']['status'][$addon]) && $CJO['ADDON']['status'][$addon] == 1;
    }

    /**
     * Returns true if a addon is activated or not.
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isInstalled($addon) {
        global $CJO;
        return isset( $CJO['ADDON']['install'][$addon]) && $CJO['ADDON']['install'][$addon] == 1;
    }

    /**
     * Returns true if a addon is a sytem addon.
     * @param string $addon name of the addon
     * @return boolean
     * @access public
     */
    public static function isSystemAddon($addon){
        global $CJO;
        return in_array($addon, $CJO['SYSTEM_ADDONS']);
    }

    /**
     * Returns the version of the addon.
     * @param string $addon name of the addon
     * @return string
     * @access public
     */
    public static function getVersion($addon, $default = null) {
        return OOAddon::getProperty($addon, 'version', $default);
    }

    /**
     * Returns the author of the addon.
     * @param string $addon name of the addon
     * @param string|null $default default value if author is not available
     * @return string
     * @access public
     */
    public static function getAuthor($addon, $default = null) {
        return OOAddon::getProperty($addon, 'author', $default);
    }

    /**
     * Returns the supportpage of the addon.
     * @param string $addon  name of the addon
     * @param string $default default value if supportpage is not available
     * @return string
     * @access public
     */
    public static function getSupportPage($addon, $default = null) {
        return OOAddon::getProperty($addon, 'supportpage', $default);
    }

    /**
     * Returns an array of all available addons.
     * @return string
     * @access public
     */
    public static function getAvailableAddons() {

        global $CJO;

        if (isset($CJO['ADDON']) && is_array($CJO['ADDON']) &&
            isset($CJO['ADDON']['status']) && is_array($CJO['ADDON']['status'])) {
            $addons = $CJO['ADDON']['status'];
        }
        else {
            $CJO['ADDON'] = array();
            $CJO['ADDON']['install'] = array();
            $CJO['ADDON']['status'] = array();
            $addons = array();
        }

        $avail = array();
        foreach ($addons as $addonName => $addonStatus) {
            if($addonStatus == 1)
            $avail[] = $addonName;
        }

        return $avail;
    }

    /**
     * Sets the property of an addon.
     * @param string $addon name of the addon
     * @param string $property name of the property
     * @param mixed $value new value of the property
     * @access public
     */
    public static function setProperty($addon, $property, $value) {

        global $CJO;

        if(!isset($CJO['ADDON'][$property]))
            $CJO['ADDON'][$property] = array();

        $CJO['ADDON'][$property][$addon] = $value;
    }

    /**
     * Returns the value of a addon property.
     * @param string $addon name of the addon
     * @param string $property name of the property
     * @param string $default default value if the property is not available
     * @access public
     */
    public static function getProperty($addon, $property, $default = null){

        global $CJO;

        return isset($CJO['ADDON'][$property][$addon]) ? $CJO['ADDON'][$property][$addon] : $default;
    }

    public static function enableMenuAddon($addons, $addonname, $menu = 0) {

    	global $CJO, $I18N;

    	if (!$CJO['CONTEJO']) return false;

    	if (OOAddon :: isInstalled($addonname)) {
    		if (isset ($menu)) {
    			$CJO['ADDON']['menu'][$addonname] = $menu;
    		} else {
    			$CJO['ADDON']['menu'][$addonname] = 0;
    		}
    		// regenerate Addons file
    		cjoGenerate::generateAddons($addons);
    	}
    	else {
    		cjoMessage::addError($I18N->msg("msg_addon_no_show_menu", $addonname));
    	}

        if (cjoMessage::hasErrors()){
    		return false;
    	}
    	return true;
    }

    public static function activateAddon($addons, $addonname) {

    	global $CJO, $I18N;

    	if (!$CJO['CONTEJO']) return false;

    	if (OOAddon :: isInstalled($addonname)) {
    		$CJO['ADDON']['status'][$addonname] = 1;

    		if ($CJO['ADDON']['menu'][$addonname] == 0) {
    		    $CJO['ADDON']['menu'][$addonname] = "addons";
    		}
    		// regenerate Addons file
    		cjoGenerate::generateAddons($addons);
    	}
    	else {
    		cjoMessage::addError($I18N->msg("msg_addon_no_activation", $addonname));
    	}

        if (cjoMessage::hasErrors()){
    		return false;
    	}

    	cjoMessage::addSuccess($I18N->msg("msg_addon_activated", $addonname));
        cjoExtension::registerExtensionPoint('ADDON_ACTIVATED', array('addon' => $addonname));      	
    	return true;
    }

    public static function deactivateAddon($addons, $addonname) {

    	global $CJO, $I18N;

    	if (!$CJO['CONTEJO']) return false;

    	$uninstall_file = $CJO['ADDON_PATH'].'/'.$addonname.'/uninstall.inc.php';

    	if (!cjoAssistance::isReadable($uninstall_file)){
    	    cjoMessage::flushErrors();
    		cjoMessage::addError($I18N->msg("msg_addon_uninstall_not_found"));
    		return false;
    	}

    	$CJO['ADDON']['status'][$addonname] = 0;
    	// regenerate Addons file
    	cjoGenerate::generateAddons($addons);

      	if (cjoMessage::hasErrors()){
    		return false;
    	}

    	cjoMessage::addSuccess($I18N->msg("msg_addon_deactivated", $addonname));
        cjoExtension::registerExtensionPoint('ADDON_DEACTIVATED', array('addon' => $addonname));     	
    	return true;
    }

    public static function installAddon($addons, $addonname) {

    	global $CJO, $I18N;

    	if (!$CJO['CONTEJO']) return false;

    	$addon_dir 		= $CJO['ADDON_PATH'].'/'.$addonname;
    	$addon_page_dir = $CJO['ADDON_CONFIG_PATH'].'/'.$addonname;
    	$install_file 	= $addon_dir."/install.inc.php";
    	$config_file 	= $addon_dir."/config.inc.php";

    	if (cjoAssistance::isWritable($addon_dir)) {
        	if (!file_exists($addon_page_dir)){
        		 mkdir($addon_page_dir, $CJO['FILEPERM']);
        	}
    	    cjoAssistance::isWritable($addon_page_dir);
    	}

    	if (cjoMessage::hasErrors()){
    		return false;
    	}

    	$handle = opendir($addon_dir);
    	while (false!==($item = readdir($handle))){

    		if($item == '.' || $item == '..' || $item == '.svn')  continue;

    		$from = $addon_dir."/".$item;
    		$to = $addon_page_dir."/".preg_replace('/\.bak$/i','.php', $item);

    		if (preg_match('/\.bak$/i',$item) &&
    			!file_exists($to)) {

				if (!@copy($from,$to)) {
					cjoMessage::addError($I18N->msg('msg_addon_bak_does_not_exist',
					                     cjoAssistance::absPath($from),
					                     cjoAssistance::absPath($to)));
				}
        		else {
        		    @chmod($to, $CJO['FILEPERM']);
        		}
    		}
    	}
    	closedir($handle);

        if (cjoMessage::hasErrors()){
    		return false;
    	}

    	if (cjoAssistance::isReadable($install_file)) {

    	    include_once $install_file;

    	    // Wurde das "install" Flag gesetzt, oder eine Fehlermeldung ausgegeben? Wenn ja, Abbruch
    		if (!OOAddon :: isInstalled($addonname) ||
    		    !empty ($CJO['ADDON']['installmsg'][$addonname])) {

    			$message_temp = $I18N->msg("msg_addon_no_install", $CJO['ADDON']['name'][$addonname]) . "<br/>";

    			if ($CJO['ADDON']['installmsg'][$addonname] == "") {
    				$message_temp .= $I18N->msg("msg_addon_no_reason");
    			} else {
    				$message_temp .= $CJO['ADDON']['installmsg'][$addonname];
    			}
    			cjoMessage::addError($message_temp);
    		}
    		else {
    			// check if config file exists
    			if (cjoAssistance::isReadable($config_file)) {
    				// skip config if it is a reinstall !
    				if (!OOAddon :: isActivated($addonname)) {
    					// if config is broken installation prozess will be terminated -> no install -> no errors in contejo
    					include_once $config_file;
    				}
    			}

    		    if (cjoMessage::hasErrors()){
            		return false;
            	}
            	cjoGenerate::generateAddons($addons);
    		}
    	}

        if (cjoMessage::hasErrors()){
    		return false;
    	}

    	cjoMessage::addSuccess($I18N->msg("msg_addon_installed"));
        cjoExtension::registerExtensionPoint('ADDON_INSTALLED', array('addon' => $addonname));
    	return true;
    }

    public static function uninstallAddon($addons, $addonname, $regenerate_addons = true) {

    	global $CJO, $I18N;

    	if (!$CJO['CONTEJO']) return false;

    	$addon_dir 		= $CJO['ADDON_PATH'].'/'.$addonname;
    	$addon_page_dir = $CJO['ADDON_CONFIG_PATH'].'/'.$addonname;
    	$uninstall_file = $addon_dir.'/uninstall.inc.php';

    	if (cjoAssistance::isReadable($uninstall_file)) {

    	    require_once $uninstall_file;

    		// Wurde das "uninstall" Flag gesetzt, oder eine Fehlermeldung ausgegeben? Wenn ja, Abbruch
    		if (OOAddon :: isInstalled($addonname) ||
    		    !empty($CJO['ADDON']['installmsg'][$addonname])) {

    			$message_temp = $I18N->msg('msg_addon_no_uninstall') . ' ';

    			if (empty ($CJO['ADDON']['installmsg'][$addonname])) {
    				$message_temp .= $I18N->msg('msg_addon_no_reason');
    			}
    			else {
    				$message_temp .= $CJO['ADDON']['installmsg'][$addonname];
    			}
    			cjoMessage::addError($message_temp);
    		} else {

    		    OOAddon :: deactivateAddon($addons, $addonname);

    			if (!cjoMessage::hasErrors() && $regenerate_addons) {
    				cjoGenerate::generateAddons($addons);
    			}
            }
    	}

        if (cjoMessage::hasErrors()) {
    		return false;
    	}

    	cjoMessage::addSuccess($I18N->msg("msg_addon_uninstalled", $addonname));
        cjoExtension::registerExtensionPoint('ADDON_UNINSTALLED', array('addon' => $addonname));                	
    	return true;
    }

    public static function deleteAddon($addons, $addonname) {

    	global $CJO, $ADDONS, $I18N;

    	if (!$CJO['CONTEJO']) return false;

    	// zuerst deinstallieren
    	OOAddon :: uninstallAddon($addons, $addonname, false);

    	if (!cjoMessage::hasErrors()) {
    		// bei erfolg, komplett löschen
    		cjoAssistance::deleteDir($CJO['ADDON_PATH'].'/'.$addonname, true);
    		cjoAssistance::deleteDir($CJO['ADDON_CONFIG_PATH'].'/'.$addonname, true);
    		// regenerate Addons file
    		cjoGenerate::generateAddons($addons);
    	}

    	if (cjoMessage::hasErrors()) {
            return false;
    	}

        $addonkey = array_search( $addonname, $ADDONS);
        unset($ADDONS[$addonkey]);

    	cjoMessage::addSuccess($I18N->msg("msg_addon_deleted"));
        cjoExtension::registerExtensionPoint('ADDON_DELETED', array('addon' => $addonname));      	
    	return true;
    }

    public static function readAddonsFolder($folder = false) {

    	global $CJO, $I18N, $ADDONS;

    	$ADDONS = $CJO['SYSTEM_ADDONS'];
    	$folder = !$folder ? $CJO['ADDON_PATH'].'/' : $folder;

    	foreach(glob($folder.'*') as $filename) {
    	    if (!is_dir($filename)) continue;
            $filename = str_replace($folder,'',$filename);
    	    if (in_array($filename,$CJO['SYSTEM_ADDONS'])) continue;
    	    $ADDONS[] = $filename;
    	}

    	$temp1 = @array_keys(array_flip($ADDONS));
        $temp2 = @array_keys($CJO['ADDON']['install']);

        $count1 = count(@array_diff($temp1, $temp2));
        $count2 = count(@array_diff($temp2, $temp1));

    	if ($count1 > 0 || $count2 > 0) {
    		return cjoGenerate::generateAddons($ADDONS);
    	}
    }
}