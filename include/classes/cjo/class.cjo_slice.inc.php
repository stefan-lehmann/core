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

if (!$CJO['CONTEJO']) return false;

/**
 * cjoSlice class
 * The cjoSlice class includes the basic
 * article slice functionality.
 * @package     contejo
 * @subpackage  core
 */
class cjoSlice {

    public static function addSlice($slice){

        global $CJO, $I18N;

        if (!$CJO['CONTEJO'] ||
            $CJO['USER']->hasPerm('editContentOnly[]') ||
            !$CJO['USER']->hasCatPermWrite($slice['article_id'])) {
            cjoMessage::addError($I18N->msg("msg_no_permissions"));
            return false;
        }

        $slice['clang'] = (int) $slice['clang'];

        $article = OOArticle::getArticleById($slice['article_id'], $slice['clang']);

        if (!OOArticle::isValid($article)) {
            cjoMessage::addError($I18N->msg("msg_no_such_article"));
            return false;
        }

        if (!isset($slice['re_article_slice_id']) ||
            $slice['re_article_slice_id'] === '') {
            $slice['re_article_slice_id'] = self::getLastSliceId($slice['article_id'], $slice['clang']);
        }

        $insert = new cjoSql();
        $insert->setTable(TBL_ARTICLES_SLICE);

        $columns = self::getTableColumns();

        $insert->setValue('ctype', 0);
        $insert->setValue('clang', 0);

        foreach($slice as $key => $value) {
            if (!$columns[$key]) continue;
            if ($key == 'value20' && is_array($value)) $value = serialize($value);
            $insert->setValue($columns[$key], $value);
        }

        $insert->addGlobalCreateFields();
        $insert->addGlobalUpdateFields();
        $insert->Insert();

        if ($insert->getError() != '') {
            cjoMessage::addError($insert->getError());
            return false;
        }

        $slice['slice_id'] = $insert->getLastId();

        $sql = $insert;
        $sql->flush();
        $sql->setQuery("SELECT name FROM ".TBL_MODULES." WHERE id ='".$slice['modultyp_id']."' LIMIT 1");
        $modul_name = $sql->getValue('name', 0);


        $update = $sql;
        $update->flush();
        $update->setTable(TBL_ARTICLES_SLICE);
        $update->setWhere("re_article_slice_id='".$slice['re_article_slice_id']."' AND " .
                          "id!='".$slice['slice_id']."' AND " .
                          "article_id='".$slice['article_id']."' AND " .
                          "clang='".$slice['clang']."'");
        $update->setValue("re_article_slice_id",$slice['slice_id']);
        $update->Update($I18N->msg('msg_block_of_type_added', $article->getName(), $modul_name));
                
        cjoExtension::registerExtensionPoint('ARTICLE_UPDATED', array ('action' => 'SLICE_ADDED', 
                                                                       'id' => $slice['article_id'], 
                                                                       'clang' => $CJO['CUR_CLANG'],
                                                                       'slice' => $slice));         
        
        return $slice['slice_id'];
    }

    public static function getLastSliceId($article_id, $clang) {

        global $I18N;

        $sql = new cjoSql();
        $sql->setDirectQuery("SELECT  
                                r1.id AS `r1.id`, 
                                r1.re_article_slice_id AS `r1.re_article_slice_id` 
                             FROM ".TBL_ARTICLES_SLICE." r1
                             LEFT JOIN ".TBL_ARTICLES_SLICE." r2
                             ON r1.id=r2.re_article_slice_id
                             WHERE
                                r1.article_id='".$article_id."' AND
                                r1.clang='".$clang."' AND
                                r2.id is NULL;");

        return ($sql->getRows() == 1) ? $sql->getValue("r1.id") : 0;
    }

    public static function getTableColumns(){

        global $CJO;

        if (empty($CJO['ARTICLES_SLICE_COLUMNS'])) {

            $sql = new cjoSql();
            $columns = $sql->showColumns(TBL_ARTICLES_SLICE);

            foreach($columns as $column) {
                $CJO['ARTICLES_SLICE_COLUMNS'][$column['name']] = $column['name'];
            }
        }
        return $CJO['ARTICLES_SLICE_COLUMNS'];
    }

    public static function moveSliceUp($slice_id, $clang) {
        return self::moveSlice($slice_id, $clang, 'moveup');
    }

    /**
     * Verschiebt einen Slice nach unten
     * @param int $slice_id Id des Slices
     * @param int $clang    Id der Sprache
     * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
     */
    public static function moveSliceDown($slice_id, $clang) {
        return self:: moveSlice($slice_id, $clang, 'movedown');
    }

    /**
     * Verschiebt einen Slice
     * @param int    $slice_id  Id des Slices
     * @param int    $clang     Id der Sprache
     * @param string $direction Richtung in die verschoben werden soll
     * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
     */
    public static function moveSlice($slice_id, $clang, $direction) {

        global $CJO, $I18N;

        if (!$CJO['USER']->hasPerm("moveSlice[]")) {
            cjoMessage::addError($I18N->msg('msg_no_rights_to_this_function'));
            return false;
        }

        $sql = new cjoSql();
        $qry = "SELECT *
                FROM
                    ".TBL_ARTICLES_SLICE." sl
                LEFT JOIN
                    ".TBL_MODULES." md
                ON
                    sl.modultyp_id=md.id
                WHERE
                    sl.id='".$slice_id."' && clang='".$clang."'";

        $slice = $sql->getArray($qry);
        $slice = $slice[0];

        if ($sql->getRows() != 1) {
            // ------------- START: MODUL IST NICHT VORHANDEN
            cjoMessage::addError($I18N->msg('msg_module_not_found'));
            return false;
        }

        if (!$CJO['USER']->hasPerm("module[".$slice['modultyp_id']."]") &&
            !$CJO['USER']->hasPerm("module[0]")) {
            cjoMessage::addError($I18N->msg('msg_no_rights_to_this_function'));
            return false;
        }

        $sql->flush();
        $qry = "SELECT 
                    id, ctype, re_article_slice_id,
                     (SELECT id FROM  ".TBL_ARTICLES_SLICE."
                      WHERE 
                        article_id='".$slice['article_id']."' AND 
                        clang='".$clang."' AND
                        re_article_slice_id = sl.id 
                      LIMIT 1) AS next_article_slice_id
                FROM 
                    ".TBL_ARTICLES_SLICE." sl
                WHERE 
                    sl.article_id='".$slice['article_id']."' AND 
                    sl.clang='".$clang."'";
        $results = $sql->getArray($qry);

        if ($sql->getRows() == 0) {
            cjoMessage::addError($I18N->msg('msg_module_not_found'));
            return false;
        }
        
        $re = array();
        
        foreach ($results as $key => $value) {
            $re['slices'][$value['re_article_slice_id']] = $value['id'];
            $re['prev'][$value['id']] = $value['re_article_slice_id'];
            $re['next'][$value['id']] = $value['next_article_slice_id'];   
            $re['ctypes'][$value['id']] = $value['ctype'];   
        }
        
        $re['curr_id'] = $re['slices'][0];
        $re['last_id'] = 0; 
        $re['counter'] = 0;
        $temp = array();
        foreach ($results as $key => $value) {

            
            if ($re['ctypes'][$re['curr_id']] == $slice['ctype']) {

                $re['effected'][$re['curr_id']]['prev'] = $re['last_id'];
                
                if ($re['counter'] > 0)             
                    $re['effected'][$re['last_id']]['next'] = $re['curr_id'];      

                $re['last_id'] = $re['curr_id'];
                $re['counter']++;               
            }
            
            if ($re['counter'] == 0) $re['last_id'] = $re['curr_id'];
            
            
            if ($re['slices'][$re['curr_id']])
                $re['curr_id'] = $re['slices'][$re['curr_id']];             
        }
        
        $re['source']['id']   = $slice_id;
        $re['source']['prev'] = $re['prev'][$slice_id];     
        $re['source']['next'] = $re['next'][$slice_id];             

        if ($direction == "moveup" && 
            $re['effected'][$slice_id]['prev'] > 0 &&
            $re['counter'] > 1) {
            $re['target']['id'] = $re['effected'][$slice_id]['prev'];
            
        } elseif ($direction == "movedown" && 
            $re['effected'][$slice_id]['next'] > 0 &&
            $re['counter'] > 1) {   
            $re['target']['id'] = $re['effected'][$slice_id]['next'];
            
        } else {
            cjoMessage::addError($I18N->msg('msg_slice_moved_error'));
            return false;
        }       

        $re['target']['prev'] = $re['prev'][$re['target']['id']] == $slice_id 
                              ? $re['target']['id']
                              : $re['prev'][$re['target']['id']];   
                                        
        $re['target']['next'] = $re['next'][$re['target']['id']] == $slice_id
                              ? $re['target']['id']
                              : $re['next'][$re['target']['id']];    

        $update = new cjoSql();
        $update->setTable(TBL_ARTICLES_SLICE);
        $update->setWhere("id='".$re['source']['id']."'");
        $update->setValue("re_article_slice_id",$re['target']['prev']);
        $update->Update();
        
        $update->flush();
        $update->setTable(TBL_ARTICLES_SLICE);
        $update->setWhere("id='".$re['target']['id']."'");
        $update->setValue("re_article_slice_id",$re['source']['prev']);
        $update->Update();
        
        if ($re['source']['next'] > 0 && $re['source']['next'] != $re['target']['id']) {
            $update->flush();
            $update->setTable(TBL_ARTICLES_SLICE);
            $update->setWhere("id='".$re['source']['next']."'");
            $update->setValue("re_article_slice_id",$re['target']['id']);
            $update->Update();
        }
        
        if ($re['target']['next'] > 0 && $re['target']['next'] != $re['source']['id']) {
            $update->flush();
            $update->setTable(TBL_ARTICLES_SLICE);
            $update->setWhere("id='".$re['target']['next']."'");
            $update->setValue("re_article_slice_id",$re['source']['id']);
            $update->Update();
        }               

        cjoMessage::addSuccess($I18N->msg('msg_slice_moved'));
        cjoGenerate::generateArticle($slice['article_id']);
        
        cjoExtension::registerExtensionPoint('ARTICLE_UPDATED', array ('action' => 'SLICE_MOVED', 
                                                                       'id' => $slice['article_id'], 
                                                                       'clang' => $CJO['CUR_CLANG'],
                                                                       'slice' => $slice));   
        return true;
    }

    /**
     * Löscht einen Slice
     * @param int    $slice_id  Id des Slices
     * @return boolean TRUE bei Erfolg, sonst FALSE
     */
    public static function deleteSlice($slice_id) {

        global $I18N;

        $sql = new cjoSql();
        $qry = "SELECT *
               FROM ".TBL_ARTICLES_SLICE."
               WHERE id='".$slice_id."'";
        $slice = $sql->getArray($qry);
        $slice = $slice[0];        

        $sql->flush();
        $qry = "SELECT *
                FROM ".TBL_ARTICLES_SLICE."
                WHERE re_article_slice_id='".$slice_id."'";
        $sql->setQuery($qry);

        if ($sql->getRows()>0){
            $update = new cjoSql();
            $update->setTable(TBL_ARTICLES_SLICE);
            $update->setWhere("id='".$sql->getValue("id")."'");
            $update->setValue("re_article_slice_id", $slice['re_article_slice_id']);
            $update->Update();      
        }
        
        $sql->flush();
        $qry = "DELETE FROM
                ".TBL_ARTICLES_SLICE."
                WHERE id='".$slice_id."'";

        if ($sql->statusQuery($qry, $I18N->msg('msg_block_deleted'))) {
            
            cjoExtension::registerExtensionPoint('ARTICLE_UPDATED', array ('action' => 'SLICE_DELETED', 
                                                                           'id' => $slice['article_id'], 
                                                                           'clang' => $CJO['CUR_CLANG'],
                                                                           'slice' => $slice));   
            return true;
        }
        else {
            return false;   
        }
    }

    /**
     * Führt alle pre-save Aktionen eines Moduls aus
     * @param int    $module_id  Id des Moduls
     * @param string $function   Funktion/Modus der Aktion
     * @param array  $CJO_ACTION Array zum speichern des Status
     * @return array Ein Array welches eine Meldung sowie das gefüllte REX_ACTION-Array beinhaltet
     */
    public static function execPreSaveAction($module_id, $function, $CJO_ACTION) {

        global $CJO, $I18N;

        $sql = new cjoSql();
        $qry = "SELECT 
                    ac.action AS `ac.action`
                FROM
                    ".TBL_MODULES_ACTIONS." ma,
                    ".TBL_ACTIONS." ac
                WHERE
                    ma.action_id=ac.id AND
                    ma.module_id='".$module_id."'
                    ".cjoSlice::getActionModeSql(0, $function);

        $sql->setQuery($qry);

        for ($i=0;$i<$sql->getRows();$i++) {

            $iaction = $sql->getValue("ac.action");
            foreach ($CJO['VARIABLES'] as $obj) {
                $iaction = $obj->getACOutput($CJO_ACTION, $iaction);
            }

            eval ('?>' . $iaction);

            if (isset($CJO_ACTION['MSG']) && $CJO_ACTION['MSG'] != "" ) {
                cjoMessage::addError($CJO_ACTION['MSG']);
            }
            $sql->next();
        }

        if (!$CJO_ACTION['SAVE']) {

            if ($CJO_ACTION['MSG'] != ""){
                cjoMessage::addError($CJO_ACTION['MSG']);
            }
            elseif ($function == "delete") {
                cjoMessage::addError($I18N->msg("msg_unable_to_delete_slice"));
            }
            else {
                cjoMessage::addError($I18N->msg('msg_data_not_saved'));
            }
        }
        return $CJO_ACTION;
    }

    /**
     * Führt alle post-save Aktionen eines Moduls aus
     * @param int    $module_id  Id des Moduls
     * @param string $function   Funktion/Modus der Aktion
     * @param array  $CJO_ACTION Array zum speichern des Status
     *
     * @return string Eine Meldung
     */
    public static function execPostSaveAction($module_id, $function, $CJO_ACTION) {

        global $CJO, $I18N;

        $sql = new cjoSql();
        $qry = "SELECT 
                    ac.action AS `ac.action`
                FROM
                    ".TBL_MODULES_ACTIONS." ma,
                    ".TBL_ACTIONS." ac
                WHERE
                    ma.action_id=ac.id AND
                    ma.module_id='".$module_id."'
                    ".cjoSlice::getActionModeSql(1, $function);

        $sql->setQuery($qry);

        for ($i=0;$i<$sql->getRows();$i++) {

            $iaction = $sql->getValue("ac.action");
            foreach ($CJO['VARIABLES'] as $obj) {
                $CJO_ACTION = $obj->getACOutput($CJO_ACTION, $iaction);
            }

            eval ('?>' . $iaction);

            if (isset($CJO_ACTION['MSG']) && $CJO_ACTION['MSG'] != "" ) {
                cjoMessage::addError($CJO_ACTION['MSG']);
            }
            $sql->next();
        }

        if (!$CJO_ACTION['SAVE']) {

            if ($CJO_ACTION['MSG'] != ""){
                cjoMessage::addError($CJO_ACTION['MSG']);
            }
            elseif ($function == "delete") {
                cjoMessage::addError($I18N->msg("msg_unable_to_delete_slice"));
            }
            else {
                cjoMessage::addError($I18N->msg('msg_data_not_saved'));
            }
        }
        return $CJO_ACTION;
    }

    private static function getActionModeSql($prepost, $function) {

        switch($function){
            case 'edit':    return " AND ac.prepost='".$prepost."' AND ac.sedit='1'";
            case 'delete':  return " AND ac.prepost='".$prepost."' AND ac.sdelete='1'";
            default:        return " AND ac.prepost='".$prepost."' AND ac.sadd='1'";
        }
    }
}