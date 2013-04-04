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

class cjoPageToolsCatgroups extends cjoPage {
    
    protected function setAdd() {    
        
        $this->setEdit();
        
        $this->fields['group_id'] = new selectField('group_id', cjoI18N::translate("label_id"));
        $this->fields['group_id']->addAttribute('size', '1');
        for($c=1; $c <= 10; $c++) {
            foreach(self::getCatGroups() as $cat_group) {
                if ($c == $cat_group['group_id']) continue 2;
            }
            $this->fields['group_id']->addOption($c,$c);
        }

        $this->AddSection(TBL_ARTICLES_CAT_GROUPS,  cjoI18N::translate("label_add_catgroup"), array ('group_id' => ''));
    }   

    protected function setEdit() {

        $this->fields['group_id'] = new hiddenField('group_id');
    
        $this->fields['group_name'] = new textField('group_name', cjoI18N::translate("label_catgroup_name"));
        $this->fields['group_name']->addValidator('notEmpty', cjoI18N::translate("msg_catgroup_name_notEmpty"), false, false);
    
        $this->fields['group_structure'] = new textField('group_structure', cjoI18N::translate("label_catgroup_structure"));
        $this->fields['group_structure']->addValidator('notEmpty', cjoI18N::translate("msg_catgroup_structure_notEmpty"), false, false);
    
        $this->fields['group_style'] = new colorpickerField('group_style', cjoI18N::translate("label_catgroup_style"));
        $this->fields['group_style']->addValidator('notEmpty', cjoI18N::translate("msg_catgroup_style_notEmpty"), false, false);
    
        $this->AddSection(TBL_ARTICLES_CAT_GROUPS,  cjoI18N::translate("label_edit_catgroup"), array ('group_id' => $this->oid));
    }   
    
    public static function onFormSaveorUpdate($params) {
        
        $posted = array();
        $posted['group_id'] = cjo_post('group_id','string');        
        $posted['group_name'] = cjo_post('group_name','string');
        $posted['group_structure'] = cjo_post('group_structure','string');
        $posted['group_style'] = cjo_post('group_style','string');
        
        self::setSaveExtention($posted);
    }


    protected function getDefault() {

        $this->list = new cjoList("SELECT *, group_id AS id FROM ".TBL_ARTICLES_CAT_GROUPS,
                            "group_id",
                            '', 100);
        
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/flag_blue.png" alt="" />',
                                        cjoUrl::createBELink(
                                        		'<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                $this->list->getGlobalParams(),
                                                array('function' => 'add', 'oid' => ''),
                                                'title="'.cjoI18N::translate("button_add").'"'));
        
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_SORT);
        
        $this->cols['group_id'] = new resultColumn('group_id', cjoI18N::translate("label_id"));
        $this->cols['group_id']->setHeadAttributes('class="icon"');
        $this->cols['group_id']->setBodyAttributes('class="icon"');
        $this->cols['group_id']->delOption(OPT_SORT);
        
        $this->cols['group_name'] = new resultColumn('group_name', cjoI18N::translate("label_catgroup_name").' ');
        $this->cols['group_name']->delOption(OPT_SORT);
        
        $this->cols['group_structure'] = new resultColumn('group_structure', cjoI18N::translate("label_catgroup_structure").' ');
        $this->cols['group_structure']->delOption(OPT_SORT);
        
        $this->cols['group_style'] = new resultColumn('group_style', cjoI18N::translate("label_catgroup_style").' ', 'sprintf', '<span style="border: 1px solid #666; background-color: %1$s; display: block; width: 2em; height: 1em;" title="%1$s"></span>');
        $this->cols['group_style']->delOption(OPT_SORT);
        
        $this->cols['edit'] = new editColumn();
        
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams());
        
        $this->list->addColumns($this->cols);
        $this->list->show(false);
    }

    public static function onListDelete($id) { 
              
        if ($id == '1') {
            cjoMessage::addError(cjoI18N::translate("msg_catgroup_1_not_deleted"));
        }
        else {
            $sql = new cjoSql();
            $qry = "SELECT name, id
                    FROM ".TBL_ARTICLES."
                    WHERE re_id=0 AND cat_group='".$id."'";
    
            $results = $sql->getArray($qry);
    
            $temp = array();
            foreach ($results as $result) {
                $temp[] = cjoUrl::createBELink(
                                            '<b>'.$result['name'].'</b> (ID='.$result['id'].')',
                                            array('page' => 'edit',
                                                  'subpage' => 'settings',
                                                  'function' => '',
                                                  'oid' => '',
                                                  'article_id' => $result['id'],
                                                  'clang' => $result['clang']));
            }
    
            if (!empty($temp))
                cjoMessage::addError(cjoI18N::translate("msg_catgroup_still_used").'<br/>'.implode(' | ',$temp));
    
            if (!cjoMessage::hasErrors()) {
                $sql->flush();
                $sql->statusQuery("DELETE FROM ".TBL_ARTICLES_CAT_GROUPS." WHERE group_id = '".$id."' LIMIT 1",
                                  cjoI18N::translate("msg_catgroup_deleted"));
                $sql->statusQuery("UPDATE ".TBL_ARTICLES." SET cat_group = 1 WHERE cat_group = '".$id."'",
                                  cjoI18N::translate("msg_catgroup_deleted"));
    
                foreach(self::getCatGroups() as $group) {
                    if ($id == $group['group_id']) {
                        $deleted = $group;
                        break;
                    }
                }                  
                cjoExtension::registerExtensionPoint('CATGROUP_DELETED', $deleted);                              
            }
        }
    }

    public static function getCatGroups() {
        
        $sql = new cjoSql();
        $qry = "SELECT * FROM ".TBL_ARTICLES_CAT_GROUPS;
        return $sql->getArray($qry);
    }
}