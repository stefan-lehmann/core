<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  log
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

class cjoLog {
    
    private static $addon = 'log';
    private static $cleaned = false;
    
    public static function writeLog($extension, $params) {

        global $CJO;

        if (!cjoProp::isBackend()) return false;
                
        $incl = $CJO['ADDON']['settings'][self::$addon]['SETTINGS']['INCL_EXTENSIONS'];
        $excl = $CJO['ADDON']['settings'][self::$addon]['SETTINGS']['EXCL_EXTENSIONS'];

        if (!preg_match('/'.$incl.'/', $extension, $matches1) ||
             preg_match('/'.$excl.'/', $extension, $matches2)) {
            return false;
        }
        
        if (is_object($CJO['USER'])) {
            $user_id = cjoProp::getUser()->getValue('user_id');
        } else if (isset($params['user_id'])) {
            $user_id = $params['user_id'];
        } else {
            return false;
        }

        if (isset($params['subject'])) unset($params['subject']);
        
        self::cleanUpLog();

        $insert = new cjoSql();
        $insert->setTable(TBL_27_LOG);
        $insert->setValue('extension', $extension);        
        $insert->setValue('params', serialize($params));   
        $insert->setValue('url', cjo_server('REQUEST_URI','string'));        
        $insert->setValue('user_id', $user_id);
        $insert->setValue('date', time());
        
        if (!$insert->Insert()) {
            $sql = new cjoSql();
            $sql->setDirectQuery("ALTER TABLE ".TBL_27_LOG." ADD `url` VARCHAR( 255 ) NOT NULL AFTER `params` ");
        }  
    }
    
    public static function updateArticleLockedByUser($article_id, $clang=false) {

        global $CJO;
        
        if ($clang === false) $clang = cjoProp::getClang();
        
        self::cleanUpLog();
        
        if (is_object($CJO['USER'])) {
            $user_id = cjoProp::getUser()->getValue('user_id');
        } else if (isset($params['user_id'])) {
            $user_id = $params['user_id'];
        } else {
            return false;
        }
        
        cjoSelectArticle::deleteCachedSelArticle();
        
        $sql = new cjoSql();
        $sql->setQuery("DELETE FROM ".TBL_27_LOG." WHERE extension='ARTICLE_LOCKED' AND params='".$article_id.'|'.$clang."'");
        $sql->flush();
        $sql->setTable(TBL_27_LOG);
        $sql->setValue('extension', 'ARTICLE_LOCKED');        
        $sql->setValue('params', $article_id.'|'.$clang);        
        $sql->setValue('user_id', $user_id);
        $sql->setValue('date', time());
        return $sql->Insert();  
    }
    
    public static function clearArticleLockedByUser($params) {
        $sql = new cjoSql();
        $sql->setQuery("DELETE FROM ".TBL_27_LOG." WHERE extension='ARTICLE_LOCKED'");
            
        cjoSelectArticle::deleteCachedSelArticle();
    }
    
    public static function prepareMessage($params) {
        
        global $CJO, $I18N;
        
        $key = md5(cjoI18N::translate('msg_edit_by_other_user_redirected'));
        

        if (!isset($CJO['MESSAGES']->errors[$key])) return;
        
        $user = cjoLogin::getUser(cjo_get('locked_user','int'));
        
        if (empty($user['name'])) return;
        
        $CJO['MESSAGES']->errors[$key] = cjoI18N::translate('msg_edit_by_user_redirected', $user['name']);
    }
    
    public static function isArticleLockedByUser($article_id, $clang = false) {

        global $CJO;
        
        if ($clang === false) $clang = cjoProp::getClang();
        
        if (!isset($CJO['ART'][$article_id]['locked'][$clang]))  {
        
            if (is_object($CJO['USER'])) {
                $user_id = cjoProp::getUser()->getValue('user_id');
            } else if (isset($params['user_id'])) {
                $user_id = $params['user_id'];
            } else {
                return false;
            }
            
            $sql = new cjoSql();
            $sql->setQuery("SELECT user_id, date FROM ".TBL_27_LOG." 
                            WHERE 
                            extension = 'ARTICLE_LOCKED' AND 
                            params = '".$article_id.'|'.$clang."' AND 
                            user_id != '".$user_id."' AND
                            date > '".(time()-600)."'
                            ORDER BY date DESC LIMIT 1");
            
            $CJO['ART'][$article_id]['locked'][$clang] = ($sql->getRows() < 1 || ($sql->getValue('date')+20*1000) < time()) ? false : $sql->getValue('user_id');  
        }
        return $CJO['ART'][$article_id]['locked'][$clang];
    }
    
    private static function cleanUpLog() {

        global $CJO;
        
        if (self::$cleaned) return;
        
        $lifetime = time() - ((int) $CJO['ADDON']['settings'][self::$addon]['SETTINGS']['LOG_LIFETIME'] * 3600);
        
        $delete = new cjoSql();
        $delete->setTable(TBL_27_LOG);
        $delete->setWhere('date < '.$lifetime);       
        $delete->Delete();
        
        self::$cleaned = true;
    } 
}