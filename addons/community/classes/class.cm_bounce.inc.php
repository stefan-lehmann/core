<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
 * @version     2.6.0
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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

if (!$CJO['CONTEJO']) return false;

require_once $CJO['ADDON_PATH'].'/community/bounce/class.phpmailer-bmh.php';
require_once $CJO['ADDON_PATH'].'/community/bounce/phpmailer-bmh_rules.php';

class cjoCommunityBounce extends BounceMailHandler   {

    static  $mypage        = 'community';
    static  $soft_turns    = 6;
    private $bmh;
    private $succes_msg    = '';
    private $log           = array();
    
    public static function bounce($start=false,$mail_account=false,$move=true) {
        
        global $CJO, $I18N_10;

        if ($start != true && !self::hasSession()) return false;

        if (!isset($CJO['ADDON']['settings'][self::$mypage]['BOUNCE'])) {
            $this->removeSession();
            return false;
        }
        
        if (!function_exists('imap_open')) {
            cjoMessage::addError($I18N_10->msg('msg_no_imap_open'));
            self::removeSession();
            return false; 
        }
        
        $mail = new cjoPHPMailer();
        $mail->setAccount($CJO['ADDON']['settings'][self::$mypage]['BOUNCE_MAIL_ACCOUNT']);

        if (empty($mail->Username) || empty($mail->Password)) {
            cjoMessage::addError($I18N_10->msg('msg_no_vaild_mail_account'));
            self::removeSession();
            return false;
        }

        if ($start) self::startSession();

        $bmh = new cjoCommunityBounce();
        $bmh->action_function    = 'cjoCommunityBounce::updateUser'; // default is 'callbackAction'
        $bmh->verbose            = VERBOSE_QUIET; //VERBOSE_SIMPLE; //VERBOSE_REPORT; //VERBOSE_DEBUG; //VERBOSE_QUIET; // default is VERBOSE_SIMPLE
        $bmh->mailhost           = $mail->Host;
        $bmh->mailbox_username   = $mail->Username;
        $bmh->mailbox_password   = $mail->Password;
        $bmh->disable_delete     = $move;
        $bmh->moveSoft           = $move;
        $bmh->moveHard           = $move;
        $bmh->max_messages       = 500;

        if (!$bmh->openMailbox()) {
            cjoMessage::addError($bmh->error_msg);
            self::removeSession();
            return false;
        }
               
        if (!$bmh->processMailbox()) {
            self::printRestartScript();
        } 
        else {
            self::removeSession();
            self::printRestartScript(true);
        }
    }
    
    public static function updateUser($msgnum, $bounce_type, $email, $subject, $xheader, $remove, $rule_no=false, $rule_cat=false, $totalFetched=0) {

        $sql = new cjoSql();
		$qry = "SELECT 
					id, 
					status, 
					bounce, 
					(SELECT GROUP_CONCAT(ug.group_id) FROM ".TBL_COMMUNITY_UG." ug WHERE ug.user_id=id GROUP BY ug.user_id) AS groups
				FROM 
					".TBL_COMMUNITY_USER."
				WHERE email LIKE '".$email."'";
				
        $sql->setQuery($qry);

        if ($sql->getRows() > 0) {
                       
            $bounce = ($remove) ? self::$soft_turns : (int) $sql->getValue('bounce') + 2;

            $user_id = $sql->getValue('id');            
            $group_ids = $sql->getValue('groups');           
 
            $update = $sql;
            $update->flush();
            $update->setTable(TBL_COMMUNITY_USER);
            $update->setWhere(array('id'=>$user_id));
            $update->setValue('bounce',$bounce);
			$update->addGlobalUpdateFields('bounce');
            if ($bounce>=self::$soft_turns) {
                $update->setValue('status',0);
            }      
            if (!$update->Update()) {
            	$group_ids = '-1';
				$rule_cat = 'unmatching recipient';
            }
 		}
		else {
			$group_ids = '-1';
			$rule_cat = 'unmatching recipient';
		}
	
        foreach (cjoAssistance::toArray($group_ids,',') as $group_id) {

            $sql->flush();
            $sql->setTable(TBL_COMMUNITY_BOUNCE);
            $sql->setWhere(array('group_id'=>$group_id, 'rule_cat'=>$rule_cat));
            $sql->Select('count');
            $count = $sql->getValue('count');
            
            if ($sql->getRows() == 0) {
                $insert = $sql;
                $insert->flush();
                $insert->setTable(TBL_COMMUNITY_BOUNCE);
                $insert->setValue('group_id',$group_id);
                $insert->setValue('rule_cat',$rule_cat);
                $insert->setValue('count',1);
                $insert->Insert();
            }
            else {
                $update = $sql;
                $update->flush();
                $update->setTable(TBL_COMMUNITY_BOUNCE);
                $update->setWhere(array('group_id'=>$group_id,'rule_cat'=>$rule_cat));
                $update->setValue('count',$count+1);
                $update->Update();
            }
        }
        //self::isTimeOut();
        return true;
    }
    
    public static function updateUserTable() {
        
        global $CJO, $I18N_10;
        
        $sql = new cjoSql();

        if ($sql->setDirectQuery("ALTER TABLE `".TBL_COMMUNITY_USER."` ADD `bounce` TINYINT( 1 ) NOT NULL AFTER `activation_key`")) {

            $settings_file = $CJO['ADDON']['settings'][self::$mypage]['SETTINGS'];
            $content = file_get_contents($settings_file);
            $content = str_replace("// --- /DYN","$"."CJO['ADDON']['settings'][$"."mypage]['BOUNCE'] = \"1\";\r\n// --- /DYN", $content);
            $content = str_replace("// --- /DYN","$"."CJO['ADDON']['settings'][$"."mypage]['BOUNCE_MAIL_ACCOUNT'] = \"0\";\r\n\r\n// --- /DYN", $content);
            cjoGenerate::putFileContents($settings_file, $content);
            $CJO['ADDON']['settings'][self::$mypage] = "1";
        }
        else {
            cjoMessage::addWarning($I18N_10->msg('msg_bounce_install_incomplete'));
        }
    }    
    
    public function processMailbox($max=false) {
        $result = parent::processMailbox($max);
        if (!empty($this->succes_msg)) {
            cjoMessage::addSuccess($this->succes_msg);
        }
        return $result;
    }

    public function output($msg=false,$verbose_level=VERBOSE_SIMPLE) {
        
        if ($this->verbose >= $verbose_level) {
            if (empty($msg)) {
                cjoMessage::addError($this->error_msg);
            } else {
                $this->succes_msg .= $msg . $this->bmh_newline;
            }
        }
    }
    
    private static function isTimeOut() {

        $max_ext_time = ini_get('max_execution_time');

        if (empty($max_ext_time))
        $max_ext_time = (int) get_cfg_var('max_execution_time');

        $time_left = $max_ext_time - cjoTime::showScriptTime();

        if ($time_left < 8) {
            self::printRestartScript();
            return true;
        }
        return false;
    }
    
    private static function generateLogFile() {
        
        global $CJO;
        
        $path = $CJO['ADDON']['settings'][self::$mypage]['BOUNCED_PATH'];
        $date = strftime('%Y-%m-%d_%H-%M-%S', time());
        
        if (!file_exists($path)){
            @mkdir($path);
            @chmod($path, $CJO['FILEPERM']);
        }
        
        $sql = new cjoSql();
        
        $qry = "SELECT 
        			group_id, 
    			   (SELECT name FROM ".TBL_COMMUNITY_GROUPS." WHERE id=group_id LIMIT 1) as name, 
    			    rule_cat, 
    			    count 
        		FROM ".TBL_COMMUNITY_BOUNCE." 
        		ORDER BY group_id, rule_cat";
        
        $results = $sql->getArray($qry);
        
        $content = 'group_id;group_name;bounce_type;count';
        
        foreach($results as $result) {
            $content .= "\r\n".implode(';',$result);
        }
        
        return cjoGenerate::putFileContents($path.'/bounce_report_'.$date.'.csv', $content);
    }

    private static function printRestartScript($finished=false) {
        
        $params = array();
        $params['page']     = 'community'; 
        $params['subpage']  = 'user'; 
        
        if ($finished) $params['finished'] =  $finished;
        $url = cjoAssistance::createBEUrl($params);
        
        echo '<script type="text/javascript">/* <![CDATA[ */ $(function(){ cm_automateScript(\''.$url.'\'); }); /* ]]> */</script>';
    }
    
    private function addLogEntry() {
        
    }
    
    private static function hasSession() {
        $fieldnames = cjoSql::getFieldNames(TBL_COMMUNITY_BOUNCE);
        if (empty($fieldnames)) {
            cjoMessage::removeLastError();
            return false;
        }
        return true;
    }
    
    private static function startSession() {
        if (self::hasSession()) return true;
        $sql = new cjoSql();
        $sql->setQuery("CREATE TABLE ".TBL_COMMUNITY_BOUNCE." (`group_id` INT( 11 ) NOT NULL , `rule_cat` VARCHAR( 50 ) NOT NULL ,`count` INT( 11 ) NOT NULL) ENGINE = MYISAM;");
    }
    
    private static function removeSession() {
        if (!self::hasSession()) return true;
        
        if (self::generateLogFile()) {
            $sql = new cjoSql();
            $sql->setQuery("DROP TABLE ".TBL_COMMUNITY_BOUNCE);
        }
    }
}
