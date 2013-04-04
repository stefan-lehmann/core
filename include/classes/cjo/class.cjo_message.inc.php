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

if (!cjoProp::isBackend()) return false;

/**
 * cjoMessage class
 *
 * The cjoMessage class handles the output
 * of messages and notifications in the backend.
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoMessage {

    /**
     * global message var
     * @var object
     */
    public static $messages;

    /**
     * Container for error messages
     * @var array
     */
    private $errors;

    /**
     * Container for success messages
     * @var array
     */
    private $successes;

    /**
     * Container for warnings and notifications
     * @var array
     */
    private $warnings;

    /**
     * Constructor
     * @return void
     * @access public
     */
    public function __construct(){

        if (!isset(self::$messages) || !is_object(self::$messages)) {
            return $this;
        }
        return self::$messages;
    }

    /**
     * Adds an error message.
     * @param string $message
     * @return void
     * @access public
     */
    public static function addError($message){
        self::$messages->errors[md5($message)] = $message;
    }

    /**
     * Adds a success message.
     * @param string $message
     * @return void
     * @access public
     */
    public static function addSuccess($message){
        self::$messages->successes[md5($message)] = $message;
    }

    /**
     * Adds an warning message.
     * @param string $message
     * @return void
     * @access public
     */
    public static function addWarning($message){
        self::$messages->warnings[md5($message)] = $message;
    }

    /**
     * Returns true if an error message had been added before.
     * @return boolean
     * @access public
     */
    public static function hasErrors(){
        return !empty(self::$messages->errors);
    }

    /**
     * Returns true if a specific error message had been added before.
     * @param string $message
     * @return boolean
     * @access public
     */
    public static function hasError($message){
        foreach(self::$messages->errors as $error){
            if ($message == $error) return true;
        }
        return false;
    }

    /**
     * Returns true if a success message had been added before.
     * @return boolean
     * @access public
     */
    public static function hasSuccesses(){
        return !empty(self::$messages->successes);
    }

    /**
     * Returns true if a specific success message had been added before.
     * @param string $message
     * @return boolean
     * @access public
     */
    public static function hasSuccess($message){
        foreach(self::$messages->successes as $success){
            if ($message == $success) return true;
        }
        return false;
    }

    /**
     * Returns true if warning had been added before.
     * @return boolean
     * @access public
     */
    public static function hasWarnings(){
        return !empty(self::$messages->warnings);
    }

    /**
     * Returns true if a specific warning message had been added before.
     * @param string $message
     * @return boolean
     * @access public
     */
    public static function hasWarning($message){
        foreach(self::$messages->warnings as $warning){
            if ($message == $warning) return true;
        }
        return false;
    }
    
     /**
     * Resets all error messages.
     * @return void
     * @access public
     */
    public static function flushErrors() {
        self::$messages->errors = array();
    }

     /**
     * Resets all successe messages.
     * @return void
     * @access public
     */
    public static function flushSuccesses() {
        self::$messages->successes = array();
    }

     /**
     * Resets all warnings.
     * @return void
     * @access public
     */
    public static function flushWarnings() {
        self::$messages->warnings = array();
    }

     /**
     * Resets all errors, successes and warnings together.
     * @return void
     * @access public
     */
    public static function flushAllMessages() {
 
        self::$messages->flushErrors();
        self::$messages->flushSuccesses();
        self::$messages->flushWarnings();
    }

     /**
     * Removes the last error message from the
     * container and returns it.
     * @return string
     * @access public
     */
    public static function removeLastError() {
        return array_pop(self::$messages->errors);
    }

     /**
     * Removes the last success message from the
     * container and returns it.
     * @return string
     * @access public
     */
    public static function removeLastSuccess() {
        return array_pop(self::$messages->successes);
    }

     /**
     * Removes the last warning from the
     * container and returns it.
     * @return string
     * @access public
     */
    public static function removeLastWarning() {
        return array_pop(self::$messages->warnings);
    }

    /**
     * Returns the error message array.
     * @return array
     * @access public
     */
    public static function getErrors() {
        return is_array(self::$messages->errors) ? self::$messages->errors : array();
    }

    /**
     * Returns the success message array.
     * @return array
     * @access public
     */
    public static function getSuccesses() {
        return is_array(self::$messages->successes) ? self::$messages->successes : array();
    }

    /**
     * Returns the warnings array.
     * @return array
     * @access public
     */
    public static function getWarnings() {
        return is_array(self::$messages->warnings) ? self::$messages->warnings : array();
    }

    /**
     * Formates the messages.
     * @param array $messages
     * @return string
     * @access public
     */
    public static function formatMessages($messages) {

    	if (empty($messages)) return false;

    	$info = '';
    	$warning = '';
    	$error = '';

    	foreach ($messages as $message) {

    		$text = $message[0];
    		$type = strval($message[1]);

    		if ($text == '') continue;

    		switch ($type) {
    			case '0' :
    			case 'accept' :
    			case 'success' :
    				//$info .= $text;
    				$info .= '<p class="info">'.$text .'</p>';
    				break;
    			case '1' :
    			case 'warning' :
    				//$warning .= $text;
    				$warning .= '<p class="warning">'.$text.'</p>';
    				break;
    			case '2' :
    			case 'error' :
    				//$error .= $text;
    				$error .= '<p class="error">'.$text.'</p>';
    				break;
    		}
    	}
    	if ($info != '' || $warning != '' || $error != '')
    	return '<div class="statusmessage">'.$info.$warning.$error.'</div>';
    }

    /**
     * Writes all formated messages via output filter
     * extensionpoint or simply returns it.
     * @param boolean $output_filter
     * @return string|void
     * @access public
     */
    public static function outputMessages($output_filter=true) {

    	if (!is_object(self::$messages)) return false;
    	
    	$message_out = array();
        
        cjoExtension::registerExtensionPoint('MESSAGE_OUTPUT', array());
    	
    	//overwrite all messages, if db write access ist permitted (demo)
        if (isset(self::$messages->errors[md5(1142)])){
        	cjoMessage::flushAllMessages();
        	cjoMessage::addError(cjoI18N::translate('msg_deactivated_function'));
    	}

    	foreach (self::$messages->getErrors() as $message) {
            $message_out[] = array ($message,'error');
    	}

        foreach (self::$messages->getSuccesses() as $message) {
            $message_out[] = array ($message,'success');
    	}

        foreach (self::$messages->getWarnings() as $message) {
            $message_out[] = array ($message,'warning');
    	}

    	if ($output_filter) {
    		$formated_messages = cjoMessage::formatMessages($message_out, $output_filter);
    		if (trim($formated_messages) == '') return;
    		cjoProp::set('MESSAGES_FORMATED', $formated_messages);
    		cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoMessage::insertMessages');
    	}
    	else{
    		return cjoMessage::formatMessages($message_out,$output_filter);
    	}
    }

    /**
     * output filter function
     * @param array $params
     * @return string
     */
    public static function insertMessages($params) {
    	$content = preg_replace('/<div([^>]*)id="cjo_tabs"([^>]*)>/i',cjoProp::get('MESSAGES_FORMATED').'$0',$params['subject']);
    	$content = cjoExtension::registerExtensionPoint('OUTPUT_FILTER[MESSAGES_INSERTED]', $content);
    	cjoProp::remove('MESSAGES_FORMATED');
    	return $content;
    }
    
    public static function init() {
        self::$messages = new cjoMessage();
        self::flushAllMessages();
    }
}