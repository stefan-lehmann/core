<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
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

class cjoCommentsConfig {

    static $mypage = 'comments';
      
    public static function copyConfig($params) {
    
        global $CJO, $I18N_7;
    
        $error = '';
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT * FROM ".TBL_COMMENTS_CONFIG." WHERE clang='0'");
        $fields = $sql->getFieldnames();
    
        $insert = new cjoSql();
        for ($i = 0; $i < $sql->getRows(); $i++) {
    
            $insert->flush();
            $insert->setTable(TBL_COMMENTS_CONFIG);
            foreach($fields as $key=>$value ) {
                if ($value == "id"){
                    continue;
                }
                if ($value == "clang"){
                    $insert->setValue("clang", $params['id']);
                    continue;
                }
                $insert->setValue($value, $sql->getValue($value));
            }
            $insert->Insert();
    
            if ($insert->getError() != '') {
                $error .= '<br />'.$insert->getError();
            }
            $sql->next();
        }
    
        if ($error != '') return $I18N_7->msg("err_config_db_copy", $error);
    }
    
    public static function deleteConfig($id, $clang=false) {
    
        global $CJO, $I18N_7;
        
        if ($clang === false) {
            $clang = cjo_get('clang','cjo-clang-id');
        }
        
        $sql = new cjoSql();
        $sql->setQuery("SELECT reference_article_id FROM ".TBL_COMMENTS_CONFIG." WHERE clang='".$clang."'");
        $ref_id = $sql->getValue('reference_article_id');
        
        if ($clang == 0 || $ref_id == -1) {
            cjoMessage::addError($I18N_7->msg("msg_cant_delete_default_setting"));
            return false;
        }
    
        if ($params['id'] == 0) return false;
        $sql = new cjoSql();
        $sql->setTable(TBL_COMMENTS_CONFIG);
        $sql->setWhere("id='".$id."'");
        return $sql->Delete();
    }
    
    public static function deleteConfigByLang($params) {
    
        global $CJO, $I18N_7;

        $clang = $params['id'];

        if ($clang == 0) { return false; }

        $sql = new cjoSql();
        $sql->setTable(TBL_COMMENTS_CONFIG);
        $sql->setWhere("clang='".$clang."'");
        return $sql->Delete();
    }
}