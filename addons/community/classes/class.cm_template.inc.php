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

class cjoCommunityTemplate {

    static $mypage = 'community';
    
    public static function loginUserDefaults() {
        global $CJO;
        $default_values = array('clang' => $CJO['CUR_CLANG']);
        $equal_values   = array();
        return array($equal_values, $default_values);  
    }    
    
    public static function loginUser($form_name) {
        
        global $CJO;

        if (!$CJO['USER']['LOGIN']) {
            $error = strip_tags($CJO['USER']['MESSAGES']);
            $error = array(md5($error) => $error);
            return array('errors' => $error, 'is_valid' => false);
        }
        else {

            $posted = cjo_request($form_name, 'array');
            $re_id  = cjo_request('re_id', 'cjo-article-id', $posted['re_id']);

            $article = OOArticle::getArticleById($re_id);

            if (!OOArticle::isValid($article)) $re_id = $CJO['START_ARTICLE_ID'];

            cjoAssistance::redirectFE($re_id);
        }
    }
    
    public static function activateUser($confirm_mail_text, $phpmailer) {

        global $CJO, $I18N_10;
         
        $replace = array();      
        $akey    = cjo_get('akey', 'int', 0, true);
        
        if (!$akey) {
            return $I18N_10->msg('msg_no_activation_key');             
        }
    
        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_COMMUNITY_USER." WHERE activation_key='".$akey."' LIMIT 1";
        $data = $sql->getArray($qry);
        $data = $data[0];

        if ($sql->getRows() == 0) {
            return $I18N_10->msg('msg_user_not_found');             
        }
        
        if ($data['activation'] == '0') {
    
            $update = new cjoSql();
            $update->setTable(TBL_COMMUNITY_USER);
            $update->setWhere("activation_key='".$akey."'");
    
            if (!empty($CJO['ADDON']['settings'][self::$mypage]['VERIFY_REGISTRATION'])) {
            	$update->setValue("status", '0');
            	$update->setValue("activation", '-1');
            }
            else {
                $update->setValue("activation", '1');                
            	$update->setValue("status", '1');
            }

            if (!$update->Update()) {
                return $update->getError();     
            }
        }
        if (!empty($CJO['ADDON']['settings'][self::$mypage]['VERIFY_REGISTRATION'])) {
    
    		$link =  cjoRewrite::getUrl(null, $CJO['CUR_CLANG'], array('page' => 'community',
    			        	  										   'subpage' => 'user',
    			        	  										   'function' => 'edit',
    			        	  										   'oid'=> $data['id'])) ;
	        $replace['%link%'] = str_replace('&amp;', '&', $link);

	        $userdata = '';
	        foreach($data as $field=>$value) {

	        	if ($field == 'password' ||
	        		$field == 'activation' ||
	        		$field == 'activation_key' ||
	        		$field == 'createuser' ||
	        		$field == 'createuser' ||
	        		$field == 'updateuser' ||
	        		$field == 'createdate' ||
	        		$field == 'updatedate' ||
	        		$field == 'status' ||
	        		empty($data[$field])) continue;
	        		
	        	if ($field == 'gender') $data[$field] = cjoCommunityUser::getTitle($data[$field]);	        		

	        	$userdata .= $I18N_10->msg('label_'.$field).': '.$data[$field]."\r\n";
	        }

	        $replace['%userdata%'] = $userdata;
	        
            $phpmailer->ClearReplyTos();
            $phpmailer->AddReplyTo = $data['email'];
            $phpmailer->FromName = $data['firstname'].' '.$data['name'];

    	    if (is_array($replace)) {
                foreach($replace as $key=>$val) {
                    $confirm_mail_text = str_replace($key, $val, $confirm_mail_text);
                }
            }

            $phpmailer->Body = $confirm_mail_text;
            $phpmailer->Send();
            $phpmailer->ClearReplyTos();
    	}

        return true;
    }
    
    public static function manageAccountGetDefaults() {
        
        global $CJO;
        
        $default_values = array();
        $equal_values   = array('username' => cjoCommunityUser::getUsedUserNames($CJO['USER']['ID']),
        						'email' => cjoCommunityUser::getUsedEmails($CJO['USER']['ID']));
        
        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_COMMUNITY_USER." WHERE id='".$CJO['USER']['ID']."' AND status = 1";
        $user = $sql->getArray($qry);

        if ($sql->getRows() == 1) {
        
            $equal_values['password'] = $user[0]['password'];
        
            foreach($user[0] as $field => $data) {
                if ($field == 'password') continue;
                if ($field == 'newsletter' && !$data) { continue;
                    $default_values[$field] = '';
                    continue;
                }
        	    $default_values[$field] = $data;
        	}
        }

        return array($equal_values, $default_values);         
    }
    
    public static function manageAccount($form_name) {
    
    		global $CJO, $sender_email;
    		
            $return = array();
    		$posted = cjo_post($form_name, 'array', array(), true);
    
    		$sql = new cjoSql;
    		$columns = $sql->showColumns(TBL_COMMUNITY_USER);

            $sql->flush();
            $qry = "SELECT email FROM ".TBL_COMMUNITY_USER." WHERE id='".$CJO['USER']['ID']."' AND status = 1";
            $sql->setQuery($qry);	
    
            if ($sql->getRows() != 1) {
                $return['message'] = '<p class="error">'.$I18N_10->msg('msg_user_not_found').'</p>';
                $return['is_valid'] = false;
                return;
            }
            
    		$sender_email = $sql->getValue('email');
    		
            $sql->flush();
    		$update = $sql;
    		$update->setTable(TBL_COMMUNITY_USER);    		
    		
    		foreach($columns as $column) {
    
    			if (isset($posted[$column['name']]) && $column['name'] != 'id') {
    			    if ($column['name'] == 'password') continue;
    				if (strpos($column['name'], 'email') !== false ) {
    					$posted[$column['name']] = strtolower($posted[$column['name']]);
    				}
    				$update->setValue($column['name'], $posted[$column['name']]);
    				$return['mail_replace']['%'.$column['name'].'%'] = $posted[$column['name']];
    			}
    		}
    
            $old_password 	= md5($posted['password']);
            $new_password 	= $_POST[$form_name]['new_password'];
    
            if (strlen($new_password) >= 5) {
    			$return['mail_replace']['%new_password%'] = $new_password;
    			$update->setValue("password",md5($new_password));
            }
    
    		$update->setValue("updatedate",time());
    		$update->setValue("updateuser",'Manage Account');
    		$update->setWhere("id='".$CJO['USER']['ID']."' AND password='".$old_password."' AND status = 1");

      		$return['is_valid'] = $update->Update();
    
    		return $return;
    }
    
    public static function confirmNewsletterSignIn() {
    
        global $CJO;
        
        if (cjo_get('akey', 'bool')){
        
            $user_id = cjo_get('uid', 'int', 0, true);
            $akey = cjo_get('akey', 'int', 0, true);
        
            return cjoCommunityUser::activateUser($user_id, $akey);
        }
    }   
    
    public static function registerUserDefaults() {
        $default_values = array();
        $equal_values   = array('username' => cjoCommunityUser::getUsedUserNames(),
        			 		    'sender_email' => cjoCommunityUser::getUsedLoginEmails());
        return array($equal_values, $default_values); 
    }  
    
    public static function registerUser($form_name) {

        global $CJO;

		$return = array();
		$posted = cjo_post($form_name, 'array', array(), true);
		$data   = array();

		$sql = new cjoSql();
     	$qry = "SELECT * FROM ".TBL_COMMUNITY_USER." WHERE email LIKE '".$posted['sender_email']."' LIMIT 1";
		$sql_data = $sql->getArray($qry);
        $sql_data = $sql_data[0];

        if ($sql->getRows() != 0) {

            if ($sql_data['password'] &&  
                $sql_data['username'] && 
                $sql_data['status'] == 1) {
                cjoMessage::addError($I18N_10->msg('msg_login_allready_enabled'));
                return false;               
                
            } 	            
            cjoCommunityUser::deleteUser($sql_data['id']);
        }   
        
        $sql->flush();
		$columns = $sql->showColumns(TBL_COMMUNITY_USER);

		foreach ($columns as $column) {

		    $field = $column['name'];
			if (isset($posted[$field])) {

				if (strpos($field, 'email') !== false ) {
					$posted[$field] = strtolower($posted[$field]);
				}
				if ($field == 'status' || $field == 'activation') continue;

				$data[$field] = $posted[$field];
				$return['mail_replace']['%'.$field.'%'] = $posted[$field];
			}
		}

		$password = $posted['password'];
        if ($posted['newsletter']) {
            $data['newsletter'] = 1;
        }
		$data['email']		= strtolower($posted['sender_email']);
		$data['password']	= md5($password);
		$data['createuser']	= 'Register User';
		$data['createdate']	= time();
		$data['clang'] 		= $CJO['CUR_CLANG'];
		$data['groups'] 	= $posted['groups'];
		$data['activation_key'] = crc32($data['email'].$data['firstname']);

        cjoCommunityUser::updateUser($data, 0);

        $user_id = $GLOBALS[$data['activation_key']];

	    $return['mail_replace']['%title%']     = cjoCommunityUser::getTitle($data['gender']);
        $return['mail_replace']['%username%']  = $data['username'];
        $return['mail_replace']['%firstname%'] = $data['firstname'];
        $return['mail_replace']['%name%'] 	   = $data['name'];
        $return['mail_replace']['%password%']  = $password;

        $return['mail_replace']['%confirm_link%'] = cjoRewrite::getUrl($CJO['ADDON']['settings'][self::$mypage]['ACTIVATE_USER'],
        												          $CJO['CUR_CLANG'],
        												          array('akey' => $data['activation_key'], 'uid' => $user_id)
        												          );
		$return['mail_replace']['%confirm_link%'] = str_replace('&amp;', '&', $return['mail_replace']['%confirm_link%']);

        $return['is_valid'] = ($user_id != '') ? true : false;

        return $return;
    }
    
    public static function signInNewsletterDefaults() {
    
        global $CJO;
        
        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_COMMUNITY_USER." WHERE id='".$CJO['USER']['ID']."' LIMIT 1";
        $sql->setQuery($qry);

        if ($sql->getRows() == 1) {
            cjoAssistance::redirectFE($CJO['ADDON']['settings'][self::$mypage]['MANAGE_ACCOUNT']);
        }
        
        $default_values = array();
        $equal_values   = array('sender_email' => cjoCommunityUser::getNewsletterEmails());
        return array($equal_values, $default_values); 
    }       
    
    public static function signInNewsletter($form_name) {

        global $CJO;
        
        $return = array();
        self::$mypage = 'community';
		$posted = cjo_post($form_name, 'array');

		$posted['email']	  = strtolower($posted['sender_email']);
        $posted['newsletter'] = 1;
        $posted['activation'] = 0;        
        $posted['createuser'] = 'Newsletter Sign_In';
		$posted['updateuser'] = 'Newsletter Sign_In';

		$posted['activation_key'] = crc32($posted['email'].$posted['firstname']);
        $user_id = cjoCommunityUser::enableNewsletter($posted);

        if (!$user_id) {
            $return['errors'] = cjoMessage::getErrors();
            $return['is_valid'] = false;
            return $return;
        }
        
        $confirm_link = cjoRewrite::getUrl($CJO['ADDON']['settings'][self::$mypage]['NL_CONFIRM'],
        								   $CJO['CUR_CLANG'],
        								   array('akey' => $posted['activation_key'], 'uid' => $user_id));

	    $return['mail_replace']['%title%']        = cjoCommunityUser::getTitle($posted['gender']);
        $return['mail_replace']['%firstname%'] 	  = $posted['firstname'];
        $return['mail_replace']['%name%'] 		  = $posted['name'];
		$return['mail_replace']['%confirm_link%'] = str_replace('&amp;', '&', $confirm_link);

		$return['is_valid'] = true;

        return $return;
    }
    
    public static function signOutNewsletterDefaults(&$form) {    

        global $CJO;
        
        self::signInNewsletterDefaults();
        
        $inst = (int) preg_replace('/\D/', '', $CJO['INSTNAME']);  
        $user_id = cjo_get('UID' ,'string');
        $user_key = cjo_get('USR' ,'string');         
        
        if ($user_id && $user_key) {
            $sql = new cjoSql();
            $qry = "SELECT * FROM ".TBL_COMMUNITY_USER." WHERE id='".($user_id-$inst)."' AND activation_key='".$user_key."' AND status='1' LIMIT 1";
            $data = $sql->getArray($qry);
            $data = $data[0];

            if ($sql->getRows() != 0 && cjoCommunityUser::disableNewsletter($data)) {
                $form->elements_in = array();
                $form->sender_email                = $data['email'];
                $form->mail_replace['%title%']     = cjoCommunityUser::getTitle($data['gender']);
                $form->mail_replace['%firstname%'] = $data['firstname'];
                $form->mail_replace['%name%']      = $data['name'];
                $form->is_valid = true;                
            }
        }
        return;
    }
    
    public static function signOutNewsletter($form_name) {
        
        global $CJO, $I18N_10;
        
        $return = array();

        $posted = cjo_post($form_name, 'array', array(), true);

        $return['is_valid'] = true;

        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_COMMUNITY_USER." WHERE email='".$posted['sender_email']."' AND status='1' LIMIT 1";
        $data = $sql->getArray($qry);
        $data = $data[0];

        if ($sql->getRows() == 0) {
            $return['is_valid'] = false;
            $return['has_errors'] = true;            
            $return['errors'][md5($error)] = $I18N_10->msg('msg_user_not_found');
            return $return;
        }

        cjoCommunityUser::disableNewsletter($data);

	    $return['mail_replace']['%title%']     = cjoCommunityUser::getTitle($data['gender']);
        $return['mail_replace']['%firstname%'] = $data['firstname'];
        $return['mail_replace']['%name%']      = $data['name'];

        if (!cjoMessage::hasErrors()) {
            $return['is_valid'] = true;
        } else {
            $return['errors'] = cjoMessage::getErrors();
            $return['is_valid'] = false;
        }
        return $return;
    } 
    
    public static function sendPasswordDefaults() {
        $default_values = array();
        $equal_values   = array('sender_email' => cjoCommunityUser::getUsedEmails());
        return array($equal_values, $default_values);        
    }
    
    public static function sendPassword($form_name) {

        global $CJO, $new_pwrd;

        $return = array();

        $new_pwrd = cjoCommunityUser::generatePassword();
        $return['mail_replace']['%new_password%'] = $new_pwrd;

        $return['sender_email'] = cjoAssistance::cleanInput($_REQUEST[$form_name]['sender_email']);

        $update = new cjoSql();
        $update->setTable(TBL_COMMUNITY_USER);
        $update->setWhere("email='".$return['sender_email']."'");
        $update->setValue("password",md5($new_pwrd));
        $update->setValue("updatedate",time());
        $update->setValue("updateuser",'Send new password');
        $return['is_valid'] = $update->Update();

        return $return;
    }
    
    public static function secureDownload() {
    
        global $CJO;

        $id    = cjo_get('file', 'string');
        $inst  = cjo_session('INST', 'int', $CJO['USER']['ID']);
        if (!$inst) $inst = (int) preg_replace('/\D/i','', $CJO['INSTNAME']);
        $media = OOMedia::getMediaById($id/$inst);

        if (!OOMedia::isValid($media)) return false;
        
        $filename = $media->getFileName();     
        $filepath = $media->getFullPath();
        $filesize = $media->getSize();
        $filetype = $media->getType();        
        
        if (!file_exists($filepath)) return false;
        
        $available_memory = preg_replace('/\D/', '', ini_get('memory_limit'));
        $expected_memory = $available_memory;
        
        $kb = 1024;         // kB
        $mb = 1024 * $kb;   // MB
        $gb = 1024 * $mb;   // GB

        if ($filesize > $mb && $filesize < $gb) {
            $expected_memory = round($filesize/$mb,0)*3;
            ini_set("memory_limit", $expected_memory."M") ; 
        }
        
        $available_memory = preg_replace('/\D/', '', ini_get('memory_limit'));

        if ($expected_memory > $available_memory) {
            header('location:'.$filepath);
            exit();
        }

        if ($fp = fopen ($filepath, "r")) {

            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");// always modified
            header("Cache-Control: no-store, no-cache, must-revalidate");// HTTP/1.1
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header("Content-Type: ".$filetype);
            
            if (!$media->isImage())
                header("Content-Disposition: attachment; filename=\"".$filename."\"");
        
            fpassthru ($fp);
            fclose($fp);
            exit;
        } 
        return false;
    }
}