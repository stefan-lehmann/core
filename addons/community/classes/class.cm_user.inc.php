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
 * @version     2.7.x
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

class cjoCommunityUser {
    
    static $mypage = 'community';
    static $fieldnames = false;
    
    public static function activateUser($id, $akey) {

    	global $CJO;

    	if (empty($id)) return false;

    	$sql = new cjoSql();
    	$qry = "SELECT status, activation, activation_key, newsletter FROM ".TBL_COMMUNITY_USER." WHERE id='".$id."'";
    	$sql->setQuery($qry);

    	if ($sql->getRows() == 0) return false;
    	if ($sql->getValue('status') == 1 && $sql->getValue('activation') == 1 && $sql->getValue('newsletter') > 0) return true;
    	if ($sql->getValue('activation_key') != $akey) return false;

	    $sql->flush();
		$update = $sql;
		$update->setTable(TBL_COMMUNITY_USER);
		$update->setWhere("id='".$id."'");
		$update->setValue("status",1);
		$update->setValue("activation",1);
		$update->setValue("newsletter",1);		
		$update->setValue("updatedate",time());
		return $update->Update();
    }
    
    private static function getFieldNames() {
        if (!self::$fieldnames) {
            self::$fieldnames = '|'.implode('|',cjoSql::getFieldNames(TBL_COMMUNITY_USER)).'|';
        }
        return self::$fieldnames;
    }


    public static function updateUser(&$data, $status = 1, $update_type = -1) {

    	global $I18N_10, $CJO;

        $sql = new cjoSql();        

    	if ($update_type == -1) {
     		$qry = "SELECT id FROM ".TBL_COMMUNITY_USER." WHERE email LIKE '".$data['email']."'";
    		$sql->setQuery($qry);

    		$update_type = $sql->getRows();
    		$data['id'] = $sql->getValue('id');
    	    $sql->flush();
    	}
    	
    	if (isset($data['birthdate']))
    	    $data['birthdate'] = strftime("%Y-%m-%d", strtotime($data['birthdate']));

    	if (!isset($data['username'])) {
    	    $data['username'] = isset($data['login']) ? $data['login'] : '';    	    
    	}
    	    
    	if ($update_type == 0) {

    		$insert = $sql;
    		$insert->setTable(TBL_COMMUNITY_USER);

    		if (!$data['activation_key']) {
    		    $data['activation_key'] = crc32($data['email'].$data['firstname']);
    		}
    		
    		foreach($data as $key=>$val) {
    			if ($key == 'id')           continue;
    			if ($key == 'groups')       continue;
    			if ($key == 'absendermail') continue;
    			if ($key == 'sender_email') continue;
    			if ($key == 'submit')       continue;
    			if ($key == 'status')       continue;
    			if ($key == 'createdate')   continue;
    			if ($key == 'updatedate')   continue;
                if ($key == 'updateuser' && empty($val)) $val = $CJO['USER']->getValue("name");
                if ($key == 'createuser' && empty($val)) $val = $CJO['USER']->getValue("name");
    			if (strpos(self::getFieldNames(),'|'.$key.'|') === false) continue;
    			$insert->setValue($key, $val);
    		}
    		
    		$insert->setValue('status', $status);
    		$insert->setValue('createdate', time());
    		$insert->setValue('updatedate', time());

    		if (!$insert->Insert()) {
    		    cjoMessage::addError($I18N_10->msg('err_user_inserted', $data['email'], $insert->getError()));
    			return false;
    		}

    		$data['id'] = $insert->getLastId();
    		
			cjoMessage::addSuccess($I18N_10->msg('user_inserted', $data['email']));
			cjoCommunityGroups::updateGroups($data['id'], $data['groups']);
    	}
    	elseif ($update_type == 1) {

    		$update = $sql;
    		$update->setTable(TBL_COMMUNITY_USER);
    		$update->setWhere("email='".$data['email']."'");

    		if (is_object($CJO['USER']) || 
    		    ($CJO['USER']['ID'] && $data['id'] == $CJO['USER']['ID'])) {
    		
        		foreach ($data as $key=>$val) {
        			if ($key == 'id')           continue;
        			if ($key == 'groups')       continue;
        			if ($key == 'absendermail') continue;
        			if ($key == 'sender_email') continue;
        			if ($key == 'submit')       continue;
        			if ($key == 'createuser')   continue;
        			if ($key == 'createdate')   continue;
        			if ($key == 'status')       continue;
    			    if (strpos(self::getFieldNames(),'|'.$key.'|') === false) continue;        			
        			$update->setValue($key, $val);
        		}
    		}
                
            if (isset($data['newsletter']))
                $update->setValue('newsletter', $data['newsletter']);    			
    		
    		if (!$update->Update()) {
    			cjoMessage::addError($I18N_10->msg('err_user_updated', $data['email'], $update->getError()));
    			return false;
    		}

    		$update->flush();

			cjoMessage::addSuccess($I18N_10->msg('user_updated', $data['email']));

		    $sql = $update;
			$qry = "SELECT DISTINCT group_id FROM ".TBL_COMMUNITY_UG." WHERE user_id= '".$data['id']."'";
			$sql->setQuery($qry);

			if (!is_array($data['groups'])) {
			    $data['groups'] = array($data['groups']);
			}
			
			for($i=0; $i<$sql->getRows(); $i++) {
				$data['groups'][] = $sql->getValue('group_id');
				$sql->next();
			}		
			
			cjoCommunityGroups::updateGroups($data['id'], $data['groups'], $update_type);
    	}
    	else{
    		self::deleteUser($data['email']);
    		self::updateUser($data, $status, 0);
    	}

    	$GLOBALS[$data['activation_key']] = $data['id'];

    	return true;
    }

    public static function deleteUser($user_ids, $old_group_id=0) {

    	global $CJO, $I18N_10;

    	foreach(cjoAssistance::toArray($user_ids) as $user_id) {

    		if (strpos($user_id, '@') !== false) $user_id = self::getUserId($user_id);

    		$sql = new cjoSql();

    		foreach(cjoAssistance::toArray($user_id) as $id) {

    		    $sql->flush();
    			$sql->setQuery("SELECT group_id FROM ".TBL_COMMUNITY_UG." WHERE user_id='".$id."'");

    			if ($sql->getRows() <= 1) {
    				if ($old_group_id == $sql->getValue('group_id') &&
    				$sql->getValue('group_id') != '0') {
    					self::moveUser($id, 0, $old_group_id);

    				}
    				else {
    					cjoCommunityUserGroups::delete($id);
    					$sql->flush();
    					$sql->setQuery("DELETE FROM ".TBL_COMMUNITY_USER." WHERE id=".$id);
    					if($sql->getError() != '') $error .= '<br/>'.$sql->getError();
    				}
    			}
    			else{
    				cjoCommunityUserGroups::delete($id,$old_group_id);
    			}
    		}
    	}

    	if (!cjoMessage::hasErrors()) {
    	    cjoMessage::flushSuccesses();
    		cjoMessage::addSuccess($I18N_10->msg('accept_user_deleted'));
    		return true;
    	}

        cjoMessage::flushAllMessages();
    	cjoMessage::addError($I18N_10->msg('error_user_deleted').$error);
    	return false;
    }

    public static function copyUser($user_ids, $new_group_id) {

    	global $I18N_10;

    	foreach(cjoAssistance::toArray($user_ids) as $user_id) {

    		$sql = new cjoSql();
    		$sql->setQuery("SELECT * FROM ".TBL_COMMUNITY_UG." WHERE group_id='".$new_group_id."' AND user_id='".$user_id."'");

    		if ($sql->getRows() == 1) {
    			continue;
    		}
    		elseif ($sql->getRows() > 1) {
    			cjoCommunityUserGroups::delete($user_id,$new_group_id);
    			cjoCommunityUserGroups::add($user_id,$new_group_id);
    		}
    		else{
    			cjoCommunityUserGroups::add($user_id,$new_group_id);
    		}
    	}

    	if (cjoMessage::hasErrors()) {
    	    cjoMessage::flushErrors();
    	    cjoMessage::addError($I18N_10->msg('error_user_copy'));
    	    return false;
    	}
    	else {
    		cjoMessage::addSuccess($I18N_10->msg('accept_user_copy'));
    	    return true;
    	}
    }

    public static function moveUser($user_ids, $new_group_id, $old_group_id) {

    	global $CJO, $I18N_10;

    	if ($new_group_id == $old_group_id) return false;

    	$used_groupids = array();
    	$sql = new cjoSql();

    	foreach (cjoAssistance::toArray($user_ids) as $user_id) {

    		cjoCommunityUserGroups::delete($user_id,$old_group_id);

            $sql->flush();
    		$sql->setQuery("SELECT group_id FROM ".TBL_COMMUNITY_UG." WHERE group_id='".$new_group_id."' AND user_id='".$user_id."'");

    		if ($sql->getRows() == 0) {
    			cjoCommunityUserGroups::add($user_id,$new_group_id);
    		}
    	}

    	if (cjoMessage::hasErrors()) {
    	    $error = $I18N_10->msg('error_user_move').'<br/>'.cjoMessage::removeLastError();
    	    cjoMessage::flushErrors();
    	    cjoMessage::addError($error);
    		return false;
    	}
    	else {
    		cjoMessage::addSuccess($I18N_10->msg('accept_user_move'));
    		return true;
    	}
    }

    public static function changeUserStatus($user_id, $mode = 1) {

    	global $I18N_10;

    	$update = new cjoSql();
    	$update->setTable(TBL_COMMUNITY_USER);
    	$update->setWhere("id='".$user_id."'");

    	$sql = new cjoSql();
    	$sql->setQuery("SELECT * FROM ".TBL_COMMUNITY_USER." WHERE id='".$user_id."'");

    	if ($sql->getValue('activation') != '' &&
    		$sql->getValue('status') != '' &&
    		$mode == 1) {

    		$data = array();
    		$data['username'] 	= $sql->getValue('username');
    		$data['name'] 	 	= $sql->getValue('name'); // Name
    		$data['firstname'] 	= $sql->getValue('firstname'); // Vorname
    		$data['status'] 	= $sql->getValue('status'); // Status
    		$data['gender'] 	= $sql->getValue('gender'); // Anrede
    		$data['email'] 		= $sql->getValue('email'); // eMai

    		if ($sql->getValue('username') && $sql->getValue('password')){
    		    self::sendNotification($data, 'ACTIVATION_MSG');
    		}
    		
    		if (cjoMessage::hasErrors()) return false;
    		$update->setValue("activation",1);
    	}

    	$update->setValue("status",$mode);
    	$update->setValue("updatedate",time());
    	$update->update();

    	if ($update->getError() == '') {
    	    if (is_object($I18N_10))
    		    cjoMessage::addSuccess($I18N_10->msg('accept_status'));
    		return true;
    	}

        if (is_object($I18N_10))
    	    cjoMessage::addError($I18N_10->msg('error_status'));

    	return false;
    }

    public static function enableNewsletter(&$data) {

    	global $CJO, $I18N_10;

        $sql = new cjoSql();
     	$qry = "SELECT * FROM ".TBL_COMMUNITY_USER." WHERE email LIKE '".$data['email']."' LIMIT 1";
		$sql_data = $sql->getArray($qry);
        $sql_data = $sql_data[0];

        if ($sql->getRows() == 0) {
            if (self::updateUser($data) ) {
                return $GLOBALS[$data['activation_key']];
            }
            return false;
        }   
        
        if ($sql_data['status'] != 1 || 
            $sql_data['activation'] != 1) {

            if (self::deleteUser($sql_data['id']) && 
                self::updateUser($data, 0)) {
                return $GLOBALS[$data['activation_key']];
            }
            return false;
        } 
        
        if ($sql_data['newsletter'] == 1) {
            cjoMessage::addError($I18N_10->msg('msg_newsletter_allready_enabled'));
            return false;
        }              
        
        if ($sql_data['password'] && $sql_data['username']) {
                
             $article = OOArticle::getArticleById($CJO['ADDON']['settings'][self::$mypage]['MANAGE_ACCOUNT']);
                
             $link = (OOArticle::isValid($article)) ? $article->toLink() : $I18N_10->msg('label_manage_account_article');
                
             cjoMessage::addError($I18N_10->msg('msg_my_account_newsletter_enable', $link));
             return false;    
        }
        
        return false;
    }

    public static function disableNewsletter(&$data) {

    	global $CJO, $I18N, $I18N_10;

        if (!empty($data['password']) && !empty($data['username'])) {

             $article = OOArticle::getArticleById($CJO['ADDON']['settings'][self::$mypage]['MANAGE_ACCOUNT']);
                      
             $link = (OOArticle::isValid($article)) 
                   ? $article->toLink() 
                   : $I18N_10->msg('label_manage_account_article');
                
             cjoMessage::addError($I18N_10->msg('msg_my_account_newsletter_enable', $link));
             return false;    
        }    
        
        $inst = (int) preg_replace('/\D/', '', $CJO['INSTNAME']);

    	$update = new cjoSql();
        $update->setTable(TBL_COMMUNITY_USER);
    	$update->setWhere("id='".$data['id']."'");
    	$update->setValue("newsletter", 0);	
    	$update->setValue("status", 0);	    	
    	$update->setValue("updatedate",time());
    	return $update->Update();
    }

    public static function getUserId($email) {

    	$sql = new cjoSql();
    	$sql->setQuery("SELECT id FROM ".TBL_COMMUNITY_USER." WHERE email='".$email."'");

    	for ($i=0; $i<$sql->getRows(); $i++) {
    		$user_ids[] = $sql->getValue('id');
    		$sql->next();
    	}
    	return $user_ids;
    }

    public static function getUsedEmails($user_id = false) {

        global $CJO;

        if ($user_id !== false) $add_sql = "AND id != '".$user_id."'";

        $sql = new cjoSql();
        $qry = "SELECT DISTINCT email FROM ".TBL_COMMUNITY_USER." WHERE status = '1' ".$add_sql;
        $sql->setQuery($qry);

        $used = '|';
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $used .= $sql->getValue('email').'|';
            $sql->next();
        }

        return $used;
    }
    
    public static function getNewsletterEmails() {

        global $CJO;

        $sql = new cjoSql();
        $qry = "SELECT DISTINCT email FROM ".TBL_COMMUNITY_USER." WHERE status = '1' AND newsletter = '1' AND activation = '1'";
        $sql->setQuery($qry);

        $used = '|';
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $used .= $sql->getValue('email').'|';
            $sql->next();
        }

        return $used;
    }   
    
    public static function getUsedLoginEmails() {

        global $CJO;

        if ($user_id !== false) $add_sql = "AND id != '".$user_id."'";

        $sql = new cjoSql();
        $qry = "SELECT DISTINCT email FROM ".TBL_COMMUNITY_USER." WHERE status = '1' AND username != '' AND password != ''  ".$add_sql;
        $sql->setQuery($qry);

        $used = '|';
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $used .= $sql->getValue('email').'|';
            $sql->next();
        }

        return $used;
    }    

    public static function getUsedUserNames($user_id = false) {

        global $CJO;

        if ($user_id !== false) $add_sql = "AND id != '".$user_id."'";

        $sql = new cjoSql();
        $qry = "SELECT DISTINCT username FROM ".TBL_COMMUNITY_USER." WHERE status = '1' ".$add_sql;
        $sql->setQuery($qry);

        $used = '|';
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $used .= $sql->getValue('username').'|';
            $sql->next();
        }
        return $used;
    }

    public static function sendNotification($data, $msg_type, $mypage = false){

        global $CJO, $I18N_10;

        if (!$mypage) $mypage = self::$mypage;
        
        $message = array();

        // PHPMAILER VORBEREITEN
        $phpmailer = new cjoPHPMailer();
        $phpmailer->setAccount($CJO['ADDON']['settings'][$mypage]['MAIL_ACCOUNT']);
        $phpmailer->Subject = $CJO['ADDON']['settings'][$mypage]['SUBJECT'];
        $phpmailer->AddAddresses($data['email']);
        $phpmailer->IsHTML(false);

        if ($CJO['ADDON']['settings'][$mypage]['BCC'] != '')
            $phpmailer->AddBCC($CJO['ADDON']['settings'][$mypage]['BCC']);


        $repl = array('%title%' 	=> cjoCommunityUser::getTitle($data['gender']),
                      '%firstname%' => $data['firstname'],
                      '%name%' 		=> $data['name'],
                      '%username%' 	=> $data['username'],
        			  '%login%' 	=> $data['username'],
                      '%password%' 	=> $data['new_pw'],
                      '%email%' 	=> $data['email']);

        $phpmailer->Body  = str_replace(array_keys($repl), $repl, $CJO['ADDON']['settings'][$mypage][$msg_type]);
        $phpmailer->Body .= $CJO['ADDON']['settings'][$mypage]['MAIL_FOOTER'];

        $phpmailer->Send(true);

        if($phpmailer->IsError() > 0){
            cjoMessage::addError($mail->ErrorInfo);
            return false;
        }
        elseif (is_object($I18N_10)) {
            cjoMessage::addSuccess($I18N_10->msg('msg_info_mail_send', $data['email']));
            return true;
        }
    }

    // Generate a random password
    public static function generatePassword($length=8){

        $dummy	= array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'), array('-','.','_','?','+'));
        // shuffle array
        mt_srand((double)microtime()*1000000);

        for ($i = 1; $i <= (count($dummy)*2); $i++){
            $swap		  = mt_rand(0,count($dummy)-1);
            $tmp		  = $dummy[$swap];
            $dummy[$swap] = $dummy[0];
            $dummy[0]	  = $tmp;
        }
        // get password
        return substr(implode('',$dummy),0,$length);
    }

    public static function getTitle($gender) {
        
        global $CJO;
        
        $gender_types = $CJO['ADDON']['settings'][self::$mypage]['GENDER_TYPES'];
    	preg_match('/(?<='.$gender.'=).*?(?=\||$)/i', $gender_types, $match);
        return $match[0];
    }
    
    public static function getGenderSelect($type, $name='gender', $style = '', $mypage=false) {

    	global $CJO;    	
    	
        if (!$mypage) $mypage = self::$mypage;

    	if (!is_string($CJO['ADDON']['settings'][$mypage]['GENDER_TYPES'])) return false;

        preg_match_all('/(?<=^|\|)([^\|]*)=([^\|]*)(?=\||$)/',
                       $CJO['ADDON']['settings'][$mypage]['GENDER_TYPES'],
                       $gender_types,
                       PREG_SET_ORDER);

    	$select = new cjoSelect();
        $select->setMultiple(false);
        $select->setName($name);
        $select->setStyle($style);
        $select->setSize(1);
    	$select->setSelected($type);
        $select->addOption('','');

    	foreach($gender_types as $gender_type) {
    		$select->addOption($gender_type[2], $gender_type[1]);
    	}
    	return $select->get();
    }
    
    public static function validateEmail($value){
        return preg_match("/^([A-Z0-9._%+-])+@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4}|museum)$/imu", $value);
    }
}