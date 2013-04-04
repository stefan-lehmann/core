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
 * cjoLogin class
 *
 * The cjoLogin class handles the login process with sessions.
 * @package 	contejo
 * @subpackage 	core
 */
class cjoLogin {

    public $DB;
    public $session_duration;
    public $login_query;
    public $user_query;
    public $system_id;
    public $usr_login;
    public $usr_psw;
    public $logout;
    public $uid;
    public $USER;
    public $text;
    public $passwordfunction;
    public $user_table;

    /**
     * Constructor
     * @return void
     * @access public
     */
    public function __construct() {

        $this->DB = 1;
        $this->logout = false;
        $this->setSysID(cjo_get_sys_id());
        $this->setSessiontime(cjoProp::getSessionTime());
        $this->setPasswordFunction('md5');
    }

    /**
     * Sets the id of the used database
     * @param int $DB default is 1
	 * @return void
     * @access public
     */
    public function setSqlDb($DB) {
        $this->DB = $DB;
    }

    /**
     * Sets the id of the system to ensure unique sessions
     * @param string $system_id
	 * @return void
     * @access public
     */
    public function setSysID($system_id) {
        $this->system_id = $system_id;
    }

    /**
     * Sets the live time of a login session.
     * @param int $session_duration in milliseconds
	 * @return void
     * @access public
     */
    public function setSessiontime($session_duration) {
        $this->session_duration = $session_duration;
    }

    /**
     * Set login and password values for login.
     * @param string $usr_login the user name
     * @param string $usr_psw the password
	 * @return void
     * @access public
     */
    public function setLogin($usr_login, $usr_psw) {
        $this->usr_login = cjoAssistance::cleanInput($usr_login);
        $this->usr_psw = $this->encryptPassword(cjoAssistance::cleanInput($usr_psw));
    }

    /**
     * Logout of the current user.
     * @param boolean $logout true to process logout
	 * @return void
     * @access protected
     */
    public function setLogout($logout) {

        $user_id = cjo_session('UID', 'int', 0);
        
        if (cjo_cookie(session_name(),'bool')) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        @session_destroy();
        
        if ($user_id > 0) {
            cjoExtension::registerExtensionPoint('USER_LOGGED_OUT', array ("user_id" => $user_id));
        }
           
        $this->logout = $logout;
    }

    /**
     * Define a sql query to call the requested user table.
     * @param string $user_query a sql query
     * @return void
     * @access public
     * @example
     *
     * 		cjoProp::get('LOGIN')->setLoginquery("SELECT *
     *					 	      	  FROM ".TBL_USER."
     *						          WHERE user_id='USR_UID'");
     */
    public function setUserQuery($user_query) {
        $this->user_query = $user_query;
    }

    /**
     * Define a sql query to call the requested user from the user table.
     * @param string $login_query a sql query
	 * @return void
     * @access public
     * @example
     *
     * 		cjoProp::get('LOGIN')->setLoginquery("SELECT *
     *				  				   FROM ".TBL_USER."
     *					  			   WHERE login = '%USR_LOGIN%'
     *				  				   psw = '%USR_PSW%' AND
     *                       		       lasttrydate <'".(time()-cjoProp::get('RELOGINDELAY'))."' AND
     *                      		      (login_tries <= '".cjoProp::get('MAXLOGINS')."' OR
     * 								   lasttrydate <'".(time()- (12 * 60 * 60))."')");
     */
    public function setLoginQuery($login_query) {
        $this->login_query = $login_query;
    }

    /**
     * Set the user id.
     * @param int $uid
	 * @return void
     * @access public
     */
    public function setUserID($uid) {
        $this->uid = $uid;
    }

    /**
     * Set name of the user table.
     * @param string $user_table database table name
	 * @return void
     * @access public
     */
    public function setUserTable($user_table) {
        $this->user_table = $user_table;
    }

    /**
     * Checks if a user is loged in.
     * If logged out or login incorrect redirect to login page and
     * empty global user id CJO[UID][system_id]
     * If login done check session time
     * If everything is okay write global user id
     *
     * @return boolean returns true if login is correct
     * @access public
     */
    public function checkLogin() {

        $logged_in = false;
        $error = '';
        if (!$this->logout) {
            
            if ($this->usr_login != '') {

                $this->USER = new cjoLoginSQL($this->DB);

                $query = str_replace("%USR_LOGIN%", $this->usr_login, $this->login_query);
                $query = str_replace("%USR_PSW%", $this->usr_psw, $query);

                $this->USER->setQuery($query);

                if ($this->USER->getRows() == 1) {
                    
                    if ((int) $this->USER->getValue('status') == 0) {
                        $error = cjoI18N::translate("msg_user_disabled");
                    }
                    elseif (!cjoProp::isBackend() &&
                            (int) $this->USER->getValue('activation') != 1) {
                        $error = cjoI18N::translate("msg_user_not_activated");
                    }
                    else {
                        cjo_regenerate_session(); 
                        $logged_in = true;
                        cjo_set_session('UID', $this->USER->getValue($this->uid));
                        cjoExtension::registerExtensionPoint('USER_LOGGED_IN',
                                                             array ("user_id" => $this->USER->getValue($this->uid)));                          
                    }
                }
                else {

                    $this->USER->flush();
                    $this->USER->setQuery("SELECT login_tries, lasttrydate
                                           FROM ".$this->user_table."
                                           WHERE username='".$this->usr_login."'");

                    if ($this->USER->getError() != '') {
                        $this->USER->flush();
                        $this->USER->setQuery("SELECT login_tries, lasttrydate
                                              FROM ".$this->user_table."
                                              WHERE login='".$this->usr_login."'");
                    }

                    if ($this->USER->getValue('login_tries') >= cjoProp::get('MAXLOGINS') &&
                        $this->USER->getValue('lasttrydate') > (time()- (2 * 60 *60))) {
                        $error = cjoI18N::translate("msg_maxlogins", cjoProp::get('MAXLOGINS')); //$this->text[32];
                    }
                    elseif ($this->USER->getValue('lasttrydate') > (time()-cjoProp::get('RELOGINDELAY'))) {
                        $error = cjoI18N::translate("msg_relogindelay", cjoProp::get('RELOGINDELAY')); //$this->text[31];
                    }
                    else {
                        $error = cjoI18N::translate("msg_login_incorrect"); //$this->text[30];
                    }
                }
            }
            elseif (cjo_session('UID', 'bool')) {
                
                $this->USER = new cjoLoginSQL($this->DB);
                $query = str_replace("%USR_UID%", cjo_session('UID', 'int'), $this->user_query);

                $this->USER->setQuery($query);
                if ($this->USER->getRows() == 1) {
                    if ((cjo_session('ST', 'int') + $this->session_duration) > time()) {
                        $logged_in = true;
                        cjo_set_session('UID', $this->USER->getValue($this->uid));
                        cjo_set_session('INST', $this->USER->getValue($this->uid));                        
                    }
                    else {
                        $error = cjoI18N::translate("msg_session_over"); //$this->text[10];
                    }
                }
                else {
                    $error = cjoI18N::translate("msg_user_id_not_found"); //$this->text[20];
                }
            }
            else {
                $error = cjoI18N::translate("msg_please_login"); //$this->text[40];
            }
        }
        else {
            $error = cjoI18N::translate("msg_you_logged_out"); //$this->text[50];
        }

        if ($logged_in) {
            cjo_set_session('ST', time());
        }
        else {         
            cjoMessage::flushAllMessages();
            cjoMessage::addError($error);
            cjo_unset_session('UID'); 
            cjo_unset_session('ST');           
        }
        return $logged_in;
    }

    /**
     * Returns a specific value of the current user.
     * @param string $value
     * @return mixed
     * @access public
     */
    public function getValue($value) {
        return $this->USER->getValue($value);
    }

    /**
     * Returns the messages in order to let the user know, whats going on.
     * @return string
     * @access public
     */
    public function getMessages() {
        return cjoMessage::outputMessages(false);
    }

    /**
     * Reads the article type settings.
     * @return void
     * @access public
     */
    public static function getAtypes() {

        if (cjoProp::get('ATYPES')) return true;

        $sql = new cjoSql();
        $sql->setQuery("SELECT type_id, groups FROM ".TBL_ARTICLES_TYPE);

        for ($i = 0; $i < $sql->getRows(); $i++) {
            $type_id = $sql->getValue('type_id');
            $groups = explode('|',$sql->getValue('groups'));
            cjoProp::set('ATYPES|'.$type_id, $groups);
            $sql->next();
        }
    }

    /**
     * Sets the encryption.
     * @param string $pswfunc normaly md5 is used
     * @return void
     * @access public
     */
    public function setPasswordFunction($pswfunc) {
        $this->passwordfunction = $pswfunc;
    }

    /**
     * Returns the encrypted password.
     * The encryption is set by setPasswordFunction
     * @param string $psw
     * @return string the encrypted password
     * @access public
     */
    public function encryptPassword($psw) {
        if ($this->passwordfunction == "") return $psw;
        return call_user_func($this->passwordfunction,$psw);
    }
    
    /**
     * Returns the user by the user_id.
     * @param int $id
     * @return array
     * @access public
     */
    public static function getUser($user_id) {
        $sql = new cjoSql();
        $qry = "SELECT *  FROM ".TBL_USER." WHERE user_id='".$user_id."' LIMIT 1";
        $results = $sql->getArray($qry);
        return (isset($results[0])) ? $results[0] : $results;
    }   
    
    public static function isBackendLogin() {
        return (cjo_session('UID', 'bool', false, cjo_get_sys_id(true)) && 
               (cjo_session('ST', 'string', false, cjo_get_sys_id(true))) + cjoProp::getSessionTime() > time());
    }
    
	/**
     * Returns a string representation of this object
     * for debugging purposes.
     * @return string
     * @access public
     */
	public function __toString() {
		return 'cjoLogin class , usr_login: "'.$this->usr_login.'", logout: "'.$this->logout.'", passwordfunction: "'.$this->passwordfunction.'"'."<br/>\r\n";
	}
}