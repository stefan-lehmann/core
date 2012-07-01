<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  extend_meta
 * @version     2.6.2
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

class cjoExtendMeta {
    
    static $mypage = 'extend_meta';
    
    private static function getFields() {
        global $CJO;
        return $CJO['ADDON']['settings'][self::$mypage]['FIELDS'];
    }
    
    public static function addFormFields($params) {
        
        global $CJO;
        
        $fields = self::getFields();
        $article_id = $CJO['ARTICLE_ID'];
        $article = OOArticle::getArticleById($article_id);

        foreach($fields['name'] as $key=>$name) {
            
            if (empty($fields['field'][$key])) continue;
            
            if ($fields['field'][$key] == 'headlineField') {
                $params['fields'][$name] = new readOnlyField($name, '', array('class' => 'formheadline'));
                $params['fields'][$name]->setValue($fields['label'][$key]);
            }
            else if ($fields['field'][$key] == 'slideheadlineField') {
                $params['fields'][$name] = new readOnlyField($name, '', array('class' => 'formheadline slide'));
                $params['fields'][$name]->setValue($fields['label'][$key]);
            }
            elseif (class_exists($fields['field'][$key])) {
                
                $params['fields'][$name] =  new $fields['field'][$key]($name, $fields['label'][$key]);
                $params['fields'][$name]->ActivateSave(false) ;
                
                if (!empty($fields['empty'][$key])) {            
                    $params['fields'][$name]->addValidator('notEmpty', $fields['message'][$key], true);
                }
                
                if (!empty($fields['validator'][$key])) {
                    $params['fields'][$name]->addValidator($fields['validator'][$key], $fields['message'][$key],array('field2'=> $fields['compare_value'][$key]));
                }
                            
                if (!empty($fields['helptext'][$key])) {
                    $params['fields'][$name]->setHelp($fields['helptext'][$key]);
                }
                $params['fields'][$name]->setValue($article->getValue($name));
            }
        }
    }
    
    public static function saveFormFields($params) {
            
        global $CJO;
            
        $fields = self::getFields();
        $article_id = $CJO['ARTICLE_ID'];
        $clang = $CJO['CUR_CLANG']; 
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere(array('article_id'=>$article_id,'clang'=>$clang));
        $sql->Delete();
        
        foreach($fields['name'] as $key=>$name) {
            if (empty($name) || 
                $fields['field'][$key] == 'headlineField' || 
                $fields['field'][$key] == 'slideheadlineField' ) continue;
                
            $sql->flush();
            $sql->setTable(TBL_30_EXTEND_META);
            $sql->setValue('article_id', $article_id);
            $sql->setValue('name', $name);
            $sql->setValue('value', cjo_post($name, 'string'));
            $sql->addGlobalCreateFields();
            $sql->Insert();
        }
    }
    
    public static function removeField($name) {
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere(array('name'=>$name));
        $sql->Delete();
    }
    
    public static function generateMata($params) {
        
        global $CJO;

        $content = $params['subject'];
        $fields = self::getFields();
        $article_id = $params['article_id'];
        $clang = $params['clang'];
        $sql = new cjoSql();

        foreach($fields['name'] as $key=>$name) {
            
            if (empty($name) || 
                $fields['field'][$key] == 'headlineField' || 
                $fields['field'][$key] == 'slideheadlineField' ) continue;
                
            $sql->flush();
            $sql->setTable(TBL_30_EXTEND_META);
            $sql->setWhere(array('article_id'=>$article_id,'clang'=>$clang,'name'=>$name));
            $sql->Select();
            
            $value = ($sql->getRows() == 1) ? cjoAssistance::addSlashes($sql->getValue('value')) : '';
            
            $CJO['ART'][$article_id][$name][$clang] = $value;
        }
        return $content;
    }
    
    public static function generateContejoClassVars($params) {

        $vars = $params['subject'];            
        $fields = self::getFields();
        foreach($fields['name'] as $key=>$name) {

            if (empty($name) || 
                $fields['field'][$key] == 'headlineField' || 
                $fields['field'][$key] == 'slideheadlineField' ) continue;
            $vars[] = $name;
        }

        return $vars;
    }

    public static function deleteArticle($params) {
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere(array('article_id'=>$params['id']));
        $sql->Delete();
    }
    
    public static function copyArticle($params) {
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere('article_id='.$params['source_id'].' AND article_id !='.$params['target_id']);
        $sql->Select();
        
        if ($sql->getRows() == 0) return;
        
        $results = $sql->getArray();
        foreach($results as $result) {
            $result['article_id'] = $params['target_id'];
            $sql->flush();
            $sql->setTable(TBL_30_EXTEND_META);
            $sql->setValues($result);
            $sql->addGlobalCreateFields();
            $sql->Insert();
        }
    }
    
    public static function addClang($params) {
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere(array('clang'=>'0'));
        $sql->Select();
        
        if ($sql->getRows() == 0) return;
        
        $results = $sql->getArray();
        foreach($results as $result) {
            $result['clang'] = $params['id'];
            $sql->flush();
            $sql->setTable(TBL_30_EXTEND_META);
            $sql->setValues($result);
            $sql->addGlobalCreateFields();
            $sql->Insert();
        }
    }
    
    public static function deleteClang($params) {
        
        $sql = new cjoSql();
        $sql->setTable(TBL_30_EXTEND_META);
        $sql->setWhere(array('clang'=>$params['id']));
        $sql->Delete();
    }
}