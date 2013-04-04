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

class cjoPageToolsTypes extends cjoPage {
    
    protected function setEdit() {
        
        $this->fields['name'] = new textField('name', cjoI18N::translate("label_types_name"));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_types_name_notEmpty"), false, false);
    
        $this->fields['description'] = new textAreaField('description', cjoI18N::translate("label_types_description"), array('rows' => 2));
        $this->fields['description']->addValidator('notEmpty', cjoI18N::translate("msg_types_description_notEmpty"), false, false);
    
        $hidden['groups_hidden'] = new hiddenField('groups');
        $hidden['groups_hidden']->setValue('0');
    
        if (cjoAddon::isActivated('community')) {
    
            $sel_group = cjoCommunityGroups::getSelectGroups($oid);
            $sel_group->setSelected(cjo_post('groups', 'array'));
    
            if (cjo_post('cjo_form_name','string') == $this->form->getName()) {
                $group_ids = cjo_post('groups', 'array');
            }
            else {
                $sql = new cjoSql();
                $qry = "SELECT groups FROM ".TBL_ARTICLES_TYPE." WHERE type_id = '".$oid."'";
                $sql->setQuery($qry);
                $group_ids = cjoAssistance::toArray($sql->getValue('groups'));
            }
            foreach ($group_ids as $val) {
                $sel_group->setSelected($val);
            }
    
            $this->fields['groups'] = new readOnlyField('groups[]', cjoI18N::translate('label_groups'));
            $this->fields['groups']->setValue($sel_group->get());
            $this->fields['groups']->addValidator('notEmpty', cjoAddon::translate(10,'err_notEmpty_groups'));
        }
    
        $this->addUpdateFields();
        $this->AddSection(TBL_ARTICLES_TYPE, cjoI18N::translate('label_edit_types'), array ('type_id' => $this->oid));
    } 
    
    protected function getDefault() {
        
        $this->list = new cjoList("SELECT *, type_id as id FROM ".TBL_ARTICLES_TYPE,
                                  'prior', 'ASC', '', 50);

        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/lock.png" alt="" />',
                                                cjoUrl::createBELink(
                                                              '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                               array('function' => 'add', 'oid' => ''),
                                                               $this->list->getGlobalParams(),
                                                              'title="'.cjoI18N::translate("button_add").'"')
                                                );
    
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_SORT);
    
        $this->cols['type_id'] = new resultColumn('type_id', cjoI18N::translate("label_id"));
        $this->cols['type_id']->setHeadAttributes('class="icon"');
        $this->cols['type_id']->setBodyAttributes('class="icon"');
    
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_types_name").' ');
    
        $this->cols['description'] = new resultColumn('description', cjoI18N::translate("label_types_description").' ');
        $this->cols['description']->delOption(OPT_SORT);
    
        $this->cols['prio'] = new prioColumn('prior', cjoI18N::translate('label_prio'),'sprintf');
    
        $replace_groups = array();
          
        $sql = new cjoSql();
        $sql->setQuery("SELECT * FROM ".TBL_COMMUNITY_GROUPS);
        for ($i=0; $i<$sql->getRows(); $i++) {
            $id = $sql->getValue('id');
            $name = $sql->getValue('name');
            $replace_groups[$id] = $name;
            $sql->next();
        }
    
        $this->cols['groups'] = new resultColumn('groups', cjoI18N::translate('label_groups'), 'replace_array', array($replace_groups,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
        $this->cols['groups']->setBodyAttributes('width="300"');
        $this->cols['groups']->delOption(OPT_ALL);
    
        $this->cols['edit'] = new editColumn();    
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams());
    
        $this->list->addColumns($this->cols);
        $this->list->show(false);
    }    

    public static function onFormSaveorUpdate($params) {
        
        $oid   = cjo_get('oid','int');
        
        if (self::isAddMode()) {
            $oid = $params['form']->last_insert_id;
            cjoAssistance::updatePrio(TBL_ARTICLES_TYPE,$oid,time(),'type_id');
        }
        
        
        self::setSaveExtention(array ("id" => $oid));
        cjoGenerate::generateTemplates($oid);
    }
     
    public static function onListDelete($id) {
        
        $sql = new cjoSql();
        $qry = "SELECT
                    name, id, clang,
                    SUBSTRING_INDEX(SUBSTRING_INDEX(RTRIM(path), '|', -2), '|', 1) AS article_id
               FROM
                    ".TBL_ARTICLES."
               WHERE
                    type_id = '".$id."'";

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
            cjoMessage::addError(cjoI18N::translate("msg_types_still_used").'<br/>'.implode(' | ',$temp));

        if (!cjoMessage::hasErrors()) {
            $sql->flush();
            $result = $sql->getArray("SELECT * FROM ".TBL_ARTICLES_TYPE." WHERE type_id='".$id."'");
            $sql->flush();            
            if ($sql->statusQuery("DELETE FROM ".TBL_ARTICLES_TYPE." WHERE type_id = '".$id."' LIMIT 1",
                                  cjoI18N::translate("msg_types_deleted")) &&
                $sql->statusQuery("UPDATE ".TBL_ARTICLES." SET type_id = '1' WHERE type_id = '".$id."'",
                                  cjoI18N::translate("msg_types_deleted"))) {
                cjoExtension::registerExtensionPoint('ARTICLE_TYPE_DELETED', $result[0]); 
            }
        }
    }
}  