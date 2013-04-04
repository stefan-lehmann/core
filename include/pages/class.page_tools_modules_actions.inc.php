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

class cjoPageToolsModulesActions extends cjoPageToolsModules {
     
   protected function setAdd() {
       
        $sql = new cjoSql();
        $qry = "SELECT * 
                FROM ".TBL_MODULES_ACTIONS." 
                WHERE module_id='".$this->oid."' AND 
                      action_id='".$this->params['action_id']."'";
        $sql->setQuery($qry);
        
        if ($sql->getRows() > 0) {
            self::onListDelete($this->oid, $this->params['action_id']);
            cjoMessage::removeLastSuccess();
            cjoMessage::addError(cjoI18N::translate("msg_action_allready_connected"));
        }
        else {
            cjoMessage::addSuccess(cjoI18N::translate("msg_action_connected"));
        }
        $sql->flush();
        $insert = $sql;
        $insert->setTable(TBL_MODULES_ACTIONS);
        $insert->setValue('module_id', $this->oid);
        $insert->setValue('action_id', $this->params['action_id']);
        if ($insert->insert()) {
            cjoExtension::registerExtensionPoint('MODULE_UPDATED', 
                                                 array('ACTION' => 'ACTION_ADDED',
                                                                   'moduletyp_id' => $this->oid,
                                                                   'action_id=' => $this->params['action_id']));  
        }
       $this->setEdit();
   }
    
    protected function setEdit() {
        $this->list = new cjoList("SELECT " .
                            "   a.id AS action_id, " .
                            "   a.name AS name, " .
                            "   CONCAT_WS(' | '," .
                            "       IF(a.prepost=1,'POST','PRE')," .
                            "       IF(a.sadd=1,'ADD',NULL)," .
                            "       IF(a.sedit=1,'EDIT',NULL)," .
                            "       IF(a.sdelete=1,'DELETE',NULL)" .
                            "   ) AS status " .
                            "FROM ".TBL_ACTIONS." a " .
                            "LEFT JOIN ".TBL_MODULES_ACTIONS." ma " .
                            "ON ma.action_id = a.id " .
                            "WHERE ma.module_id='".$this->oid."'",
                            "   name",
                            "ASC",
                            " ma.module_id",
                            100);

        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/lightning.png" alt="" />', '');
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_ALL);
        
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_actions"));
        $this->cols['name']->setHeadAttributes('colspan="3"');
        $this->cols['name']->setBodyAttributes('style="width: 60%"');
        $this->cols['name']->setParams(array ('page' => 'tools', 'subpage' => 'actions', 'function' => 'edit', 'oid' => '%action_id%'));
        $this->cols['name']->delOption(OPT_ALL);
        
        $this->cols['status'] = new resultColumn('status', NULL, 'sprintf', '%s');
        $this->cols['status']->delOption(OPT_ALL);
        
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams(array('oid' => $this->oid, 'action_id' => '%action_id%')), 
                                                 true, '<img src="img/silk_icons/cross.png" title="'.cjoI18N::translate("label_remove_action_from_module").'" alt="'.cjoI18N::translate("button_delete").'" />');

        $this->cols['delete']->setBodyAttributes('class="icon cjo_delete"');
  
        $this->list->addColumns($this->cols);
        
        $add_action_sel = new cjoSelect();
        $add_action_sel->setName("action_id");
        $add_action_sel->setSize(1);
        $add_action_sel->setStyle('width: 100%');
        $add_action_sel->setSelectExtra('title="'.cjoI18N::translate("label_add_action_to_module").'"');
        $add_action_sel->setSelectExtra('onchange="cjo.jconfirm(\''.cjoI18N::translate("label_add_action_to_module").'\', \'cjo.changeLocation\', [$(this).find(\':selected\').val()]);"');
        
        $sql = new cjoSql();
        $qry = "SELECT action_id as id FROM ".TBL_MODULES_ACTIONS." WHERE module_id='".$this->oid."'";
        $used_actions = $sql->getArray($qry);
        
        $sql->flush();
        $qry = "SELECT DISTINCT " .
                     "  id, name, " .
                     "  CONCAT_WS('|'," .
                     "      IF(prepost=1,'POST','PRE')," .
                     "      IF(sadd=1,'ADD',NULL)," .
                     "      IF(sedit=1,'EDIT',NULL)," .
                     "      IF(sdelete=1,'DELETE',NULL)" .
                     "  ) AS status " .
                     "FROM ".TBL_ACTIONS." " .
                     "ORDER BY name";
        $sql->setQuery($qry);
        
        if ($sql->getRows() > 0) {
            
            $add_action_sel->addOption(cjoI18N::translate("label_add_action"),0);
            $add_action_sel->setSelected(0);
            $count = 0;
            for ($i=0; $i<$sql->getRows(); $i++) {
                if (array_search(array('id'=>$sql->getValue("id")), $used_actions) === false) {
                    $count++;
                    $add_action_sel->addOption($sql->getValue("name").' ['.$sql->getValue("status").']',
                                               cjoUrl::createBEUrl(array('function'=>'add', 'action_id'=>$sql->getValue("id")), array(), '&amp;'));
                }
                $sql->next();
            }
            $after_data = $add_action_sel->get();
        }
        else {
            //$after_data = $sql->getRows() == 0 ? cjoI18N::translate("msg_no_actions_available") : cjoI18N::translate("msg_all_actions_connected");
            $after_data .= cjoUrl::createBELink(' <img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("label_add_action").'" /> '.cjoI18N::translate("label_add_action"),
                                               array('subpage'=>'actions', 'function' => 'add', 'oid' => ''),
                                               $this->list->getGlobalParams(),
                                               'title="'.cjoI18N::translate("button_add").'"');
        }
        
        $add_action = '<tr>' .
                      ' <td class="icon">&nbsp;</td>' .
                      ' <td colspan="2">'.$after_data.'</td>' .
                      ' <td class="icon">&nbsp;</td>'.
                      '</tr>';
        $this->list->setVar(LIST_VAR_AFTER_DATA, $add_action);
        
        $this->list->setVar(LIST_VAR_NO_DATA, cjoI18N::translate("msg_no_actions_connected_to_module"));
        
        $this->list->show(false);
        
    }
    
    protected function getDefault() {
        
    }

    public static function onListDelete($id) {
        
       $action_id = cjo_get('action_id','int');
         
       $sql = new cjoSql();
       if ($sql->statusQuery("DELETE FROM ".TBL_MODULES_ACTIONS." WHERE module_id='".$id."' AND action_id='".$action_id."'",
                              cjoI18N::translate("msg_action_deleted_from_modul"))) {
                          
            cjoExtension::registerExtensionPoint('MODULE_UPDATED', array('ACTION' => 'ACTION_REMOVED',
                                                                         'moduletyp_id' => $id,
                                                                         'action_id=' => $action_id));   
        }
    }
     
}

 

