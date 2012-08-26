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

if (!$CJO['CONTEJO']) return false;

class cjoCommunityImportExport {

    public static function import() {
        
        @set_time_limit(180);
        @ini_set("memory_limit", "256M");

        global $CJO, $I18N_10;

        $settings              = array();
        $settings['mode']      = 'cm_import';
        $settings['form_name'] = 'community_imexport_import';
        
        if (cjo_post('cjo_form_name','string') == $settings['form_name']) {
            
            self::resetSession($settings['mode']);

            $settings['divider']        = stripslashes(cjo_post('divider'));
            $settings['limit_start']    = cjo_post('limit_start','int');
            $settings['limit_number']   = cjo_post('limit_number','int');
            $settings['groups']         = cjo_post('groups','array');
            $settings['clang']          = cjo_post('clang','string');
            $settings['ignore_updates'] = cjo_post('ignore_updates','bool');
            $settings['automate']       = cjo_post('automate','bool');
            $settings['import_file']    = $CJO['TEMPFOLDER'].'/community_cm_import_'.strftime('%Y%m%d%H%M%S',time());
            $settings['inserted']       = array('error' => 0, 'success' => 0);
            $settings['updated']        = array('error' => 0, 'success' => 0);
            $settings['ignored']        = 0;
            $settings['all_msg']        = array('error' => 0, 'success' => 0);
            
            if (!empty($_FILES['userfile']['tmp_name'])) {
                move_uploaded_file($_FILES['userfile']['tmp_name'], $settings['import_file']);
                @chmod($settings['import_file'], $CJO['FILEPERM']);
            }
        }
        elseif(cjo_session($settings['mode'],'bool')) {
           $settings = cjo_session($settings['mode'],'array',$settings);
        }
        else {   
            return false;
        }   
        if (!cjoAssistance::isReadable($settings['import_file'])) {
           self::resetSession($settings['mode']);
           return false; 
        }

        $data = file_get_contents($settings['import_file']);
        
        if (mb_detect_encoding($data, 'UTF-8', true) == false) {
            $data = utf8_encode($data);
        }

        self::normalizeLineEndings($data);

        preg_match_all('/^.*$/m', $data, $csv_data);

        // leere Datei
        if (!is_array($csv_data[0])){
            cjoMessage::addError($I18N_10->msg('err_empty_csv_document'));
            return false;
        }
        else {
            $columns = array_shift($csv_data[0]);
            $columns = explode($settings['divider'],$columns);
        }
        
        $total = count($csv_data[0]);
        
        if ($settings['limit_number'] < 1 || $settings['limit_number'] > $total) $settings['limit_number'] = $total;

        $csv_data = array_slice($csv_data[0], $settings['limit_start']-1, $settings['limit_number']);

        // Falsches Trennzeichen
        if (!preg_match('/'.$settings['divider'].'(?=([^"]*"[^"]*")*(?![^"]*"))/', $csv_data[0], $temp)){
            cjoMessage::addError($I18N_10->msg('err_wrong_delimiter'));
            return false;
        }
        $sql = new cjoSql();

        foreach($csv_data as $data){

            if (self::isTimeOut($settings)) break;

            $curr = array();
            $data = explode($settings['divider'],$data);

            foreach($columns as $key=>$col){
                $col = self::prepareValues($col);
                $col = trim($col);
                $col = strtolower($col);
                $curr[$col] = self::prepareValues($data[$key]);
            }
            
            if (empty($curr['email']) || !cjoCommunityUser::validateEmail($curr['email'])) {
                $settings['inserted']['error']++;
                $settings['total']['error']++;
                continue;
            }
            else {
                $curr['email'] = strtolower($curr['email']);
            }
             
            $sql->flush();
            $qry = "SELECT id FROM ".TBL_COMMUNITY_USER." WHERE email LIKE '".$curr['email']."' LIMIT 1";
            $sql->setQuery($qry);

            if ($sql->getRows() > 0) {
                
                $curr['id'] =  $sql->getValue('id');
                        
                if ($settings['ignore_updates']) {
                    $settings['ignored']++;
                    $settings['total']['success']++;
                    $settings['limit_start']++;
                    continue;
                }    
                
                if (isset($curr['status']))      unset($curr['status']);
                if (isset($curr['activation']))  unset($curr['activation']);
                if (isset($curr['newsletter']))  unset($curr['newsletter']);
                if (isset($curr['login_tries'])) unset($curr['login_tries']);
                if (isset($curr['lasttrydate'])) unset($curr['lasttrydate']);
                
                if (isset($curr['gender']) && empty($curr['firstname']) && empty($curr['name'])) {
                    unset($curr['gender']);
                }
                if ($sql->getValue('firstname') != $curr['firstname']) {
                    $data['activation_key'] = crc32($curr['email'].$curr['firstname']);
                }
                
                $curr['newsletter']  = $sql->getValue('newsletter');
                
            }
            else {
                $curr['status']      = $curr['status'] == ''     ? 1 : $curr['status'];
                $curr['activation']  = $curr['activation'] == '' ? 1 : $curr['activation'];
                $curr['newsletter']  = $curr['newsletter'] == '' ? 1 : $curr['newsletter'];
                $curr['login_tries'] = 0;
                $curr['lasttrydate'] = 0;
                $curr['createuser']  = $CJO['USER']->getValue("name").' (Import)';
            }
            
            if (isset($curr['email2'])) {
                if (cjoCommunityUser::validateEmail($curr['email2'])) {
                    $curr['email2'] = strtolower($curr['email2']);
                }
                else {
                    unset($curr['email2']);
                }
            }
            
            $curr['clang']           = $settings['clang'];
            $curr['groups']          = $settings['groups'];
            $curr['updateuser']      = $CJO['USER']->getValue("name").' (Import)';
             
            if (cjoCommunityUser::updateUser($curr, $curr['status'], $sql->getRows())) {
                cjoMessage::removeLastSuccess();
                if ($sql->getRows() == 0) $settings['inserted']['success']++; else $settings['updated']['success']++;
                $settings['total']['success']++;
            }
            else {
                if ($sql->getRows() == 0) $settings['inserted']['error']++; else $settings['updated']['error']++;
                $settings['total']['error']++;
            }
            $settings['limit_start']++;

            if (self::isTimeOut($settings)) break;
        }
         
        if ($settings['automate'] &&
            $settings['limit_start'] < $settings['limit_number']) {
            self::printRestartScript($settings);
        }
        else {
           self::resetSession($settings['mode']);
        }

        cjoMessage::flushAllMessages();

        if ($settings['total']['success'] > 0) {
            cjoMessage::addSuccess($I18N_10->msg('accept_data_imported',
            ($settings['total']['success']+$settings['total']['error']),
            $settings['inserted']['success'],
            $settings['updated']['success'],
            $settings['ignored']));
        }
        if ($settings['total']['error'] > 0) {
            cjoMessage::addError($I18N_10->msg('error_data_imported',
            ($settings['total']['error']),
            $settings['inserted']['error'],
            $settings['updated']['error']));
        }
    }
    
    private static function getGroups(&$groups=null, $re_id=false){
        
        if ($groups === null) $groups = array();
        
        if ($re_id === false) {
            
            $temp = cjo_post('groups','array', array());
            if (empty($temp)) return false;
            
            foreach($temp as $group_id) {
                $groups[$group_id] = $group_id;
                self::getGroups($groups,$group_id);
            }
            return true;
        }
        
        $groups[$re_id] = $re_id;
           
    	$sql = new cjoSql();
    	$qry = "SELECT id FROM ".TBL_COMMUNITY_GROUPS." WHERE re_id='".$re_id."'";
        $temp = $sql->getArray($qry);

    	foreach ($temp as $group) {
    		if ($group['id'] == $re_id) continue;
    		self::getGroups($groups, $group['id']);
    	} 
    	
        return true;	
    }

    public static function export($separator = ";", $limit=1000) {

        global $CJO, $I18N_10, $I18N;

        ini_set("memory_limit", "256M");

        $settings              = array();
        $settings['mode']      = 'cm_export';
        $settings['form_name'] = 'community_imexport_export';

        if (cjo_post('cjo_form_name','string') == $settings['form_name']) {
            
            if (!self::getGroups($groups)) return false;
            
            self::resetSession($settings['mode']);
            
            $settings['last_id']      = 0;
            $settings['count']        = 0;            
            $settings['groups']       = $groups;      
            $settings['limit']        = cjo_post('limit','int', 0);
                      
            $settings['export_file']  = $CJO['TEMPFOLDER'].'/community_cm_export_'.strftime('%Y%m%d%H%M%S',time());
            $output = '';
        }
        elseif(cjo_session($settings['mode'],'bool')) {
            
           $settings = cjo_session($settings['mode'],'array',$settings);
           if (empty($settings['groups'])) {
              self::resetSession($settings['mode']);
              return false; 
           }
           
           if (file_exists($settings['export_file'])) {
               $output = file_get_contents($settings['export_file']);               
           }
           else {
                $settings['last_id']      = 0;
                $settings['count']        = 0;   
                $settings['limit']        = 0;                
                $settings['export_file']  = $CJO['TEMPFOLDER'].'/community_cm_export_'.strftime('%Y%m%d%H%M%S',time());
                $output = '';  
           }
        }
        else {   
            return false;
        } 

        if (cjo_get('finished','bool')) {
            self::resetSession($settings['mode']);
            
            if (cjo_get('finished','string') == $settings['export_file']) {
                ob_end_clean();
                $date_string = date("Y").'-'.date("m").'-'.date("d");
                $filename = "community_user_".$date_string."___".$CJO['CLANG'][$CJO['CUR_CLANG']].".csv";
                header("Content-type: plain/text; charset=".$I18N->msg("htmlcharset"));
                header("Content-Disposition: attachment; filename=$filename");
                echo $output;
                exit();
            }
            else {
                cjoMessage::addError($I18N_10->msg('error_data_exported'));
                return false;
            }
        }

        foreach($settings['groups'] as $group_id) {
            $group_where[] = "ug.group_id='".$group_id."'";
        }

        $limit_where = ' AND ';
        
        switch($settings['limit']) {
            case 1:  $limit_where .= "us.status='1'"; break;                       // label_online_users
            case 2:  $limit_where .= "us.status<>'1'"; break;                      // label_offline_users 
            case 3:  $limit_where .= "us.status<>'1' AND us.bounce>'3'"; break;       // label_bounced_users  
            case 4:  $limit_where .= "us.status<>'1' AND us.activation<>'1'"; break;  // label_not_activated_users   
            default: $limit_where = '';                                         // label_all_users
        }
        
        $sql = new cjoSql();

        do {
            $sql->flush();
            $qry = "SELECT DISTINCT us.* 
            		FROM ".TBL_COMMUNITY_UG." ug 
            	    LEFT JOIN ".TBL_COMMUNITY_USER." us
            	    ON ug.user_id=us.id
            		WHERE 
            			us.clang='".$CJO['CUR_CLANG']."' AND 
            			us.id > '".$settings['last_id']."' 
                        ".$limit_where."
            			AND (".implode(' OR ',$group_where).")
            		LIMIT ".$limit;    
            $data = $sql->getArray($qry);

            if ($sql->getRows() > 0) {

                if ($settings['count'] == 0) $data = array_merge(array(array_keys($data[0])),$data);
                foreach($data as $line) {
                    $settings['last_id'] = $line['id'];
                    $output .= str_replace(array("\r\n","\r","\n"), ' ',implode(';',$line))."\r\n";
                    $settings['count']++; 
                }
                if (self::isTimeOut($settings)) break;
            }
            if (self::isTimeOut($settings)) break;

        } while($sql->getRows() > 0);

        cjoGenerate::putFileContents($settings['export_file'], $output);      
         
        if ($sql->getRows() > 0) {
      
            self::printRestartScript($settings);
            
            cjoMessage::flushAllMessages();
            cjoMessage::addSuccess($I18N_10->msg('accept_data_export',$settings['count']));
        }
        else {
            
            cjoMessage::addSuccess($I18N_10->msg('accept_data_finished',$settings['count']));
            $settings['finished'] = $settings['export_file'];
            self::printRestartScript($settings);
        }
    }

    private static function prepareValues($value){

        $value = trim($value);
        $value = preg_replace('/"(.*)"/', '\1', $value );

        return $value;
    }

    private static function isTimeOut($settings) {

        $max_ext_time = ini_get('max_execution_time');

        if (empty($max_ext_time))
        $max_ext_time = (int) get_cfg_var('max_execution_time');

        $time_left = $max_ext_time - cjoTime::showScriptTime();

        if ($time_left < 8) {
            self::printRestartScript($settings);
            return true;
        }
        return false;
    }

    private static function printRestartScript($settings) {
        
        $params = array();
        $params['page']     = 'community'; 
        $params['subpage']  = 'imexport'; 
        $params['automate'] = '1';
        $params['clang']    = $settings['clang']; 
        $params['mode']     = $settings['mode'];
        
        if (isset($settings['finished'])) {
            $params['finished'] =  $settings['finished'];
        }
        
        cjo_set_session($settings['mode'], $settings);
        $url = cjoAssistance::createBEUrl($params);
        echo '<script type="text/javascript">/* <![CDATA[ */ $(function(){ cm_automateScript($(\'form[name="'.$settings['form_name'].'"]\'),\''.$url.'\'); }); /* ]]> */</script>';
    }
    
    private static function resetSession($name){
        global $CJO;
        foreach (cjoAssistance::toArray(glob($CJO['TEMPFOLDER'].'/community_'.$name.'*')) as $filename) {
            @unlink($filename);
        }
        cjo_set_session($name, null);
    }
    
    private static function normalizeLineEndings(& $s) {
        // Normalize line endings
        // Convert all line-endings to UNIX format
        $s = str_replace("\r\n", "\n", $s);
        $s = str_replace("\r", "\n", $s);
        // Don't allow out-of-control blank lines
        $s = preg_replace("/\n{2,}/", "\n", $s);
    }
}

