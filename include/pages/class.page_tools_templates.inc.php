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

class cjoPageToolsTemplates extends cjoPage {
        
    public function setEdit() { 

        $this->fields['name'] = new textField('name', cjoI18N::translate("label_name"));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate("msg_name_notEmpty"), false, false);
    
        $labels = array('application_gallery',
                        'application_view_gallery_star',
                        'application_view_list2',
                        'application_view_tile',
                        'page_white',
                        'page_white_code_red',
                        'page_white_text');
        sort($labels);
    
        $this->fields['label'] = new selectField('label', cjoI18N::translate("label_icon"));
        $this->fields['label']->addOption('--','');
        foreach($labels as $label) {
                $this->fields['label']->addOption($label,$label);
        }
        $this->fields['label']->addAttribute('size', '1');
    
        $this->fields['active_hidden'] = new hiddenField('active');
        $this->fields['active_hidden']->setValue('0');
        $this->fields['active'] = new checkboxField('active', '&nbsp;',  array('style' => 'width: auto;'));
        $this->fields['active']->addBox(cjoI18N::translate("label_active"), '1');
    
        $this->fields['content'] = new codeField('content', cjoI18N::translate("label_input"));
    
        if (cjoProp::countCtypes() > 0) {
            $this->fields['ctypes'] = new selectField('ctypes', cjoI18N::translate("label_ctype_connection"));
            $this->fields['ctypes']->setMultiple();
            $this->fields['ctypes']->setValueSeparator('|');
    
            foreach(cjoProp::getCtypes() as $ctype_id) {
                $this->fields['ctypes']->addOption(cjoProp::getCtypeName($ctype_id),$ctype_id);
            }
            $this->fields['ctypes']->addAttribute('size', cjoProp::countCtypes()+1);
        } else {
            $this->fields['ctypes'] = new hiddenField('ctypes');
            $this->fields['ctypes']->setValue('0');
        }

        /**
         * Do not delete translate values for cjoI18N collection!
         * [translate: label_add_template]
         * [translate: label_edit_template]
         */
        $this->AddSection(TBL_TEMPLATES, cjoI18N::translate("label_".$this->function."_template"), array ('id' => $this->oid));
    }

    protected function getDefault() {
        
        $this->list = new cjoList("SELECT * FROM ".TBL_TEMPLATES, 'prior', 'ASC', '', 100);

        $this->cols['icon'] = new resultColumn('label',
                                               cjoUrl::createBELink(
                                                              '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("label_add_template").'" />',
                                                              array('function' => 'add', 'oid' => ''),
                                                              $this->list->getGlobalParams(),
                                                              'title="'.cjoI18N::translate("label_add_template").'"'),
                                                'sprintf',
                                                '<img src="img/silk_icons/%s.png" alt="true" />');
        
        $this->cols['icon']->addCondition('label', '', '<img src="img/silk_icons/layout.png" alt="true" />');
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_SORT);
        
        $this->cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
        $this->cols['id']->setHeadAttributes('class="icon"');
        $this->cols['id']->setBodyAttributes('class="icon"');
        
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_name").' ');
        
        $this->cols['prio'] = new prioColumn();
        
        $this->cols['status'] = new statusColumn('accept', NULL, false, cjoI18N::translate("label_active"));
        $this->cols['status']->addCondition('active', '1', '');
        $this->cols['status']->addOption(OPT_SORT);
        
        $this->cols['ctypes'] = new resultColumn('ctypes', cjoI18N::translate("label_ctype_connection"), 'replace_array', array(cjoProp::get('CTYPE'),'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
        $this->cols['ctypes']->setBodyAttributes('width="300"');
        
        $this->cols['edit'] = new editColumn();
        
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams());
        
        $this->list->addColumns($this->cols);
        $this->list->show(false);

    }

    public static function onFormSaveorUpdate($params) {
        
        $oid   = cjo_get('oid','int');
        
        if (self::isAddMode()) {
            $oid = $params['form']->last_insert_id;
            cjoAssistance::updatePrio(TBL_TEMPLATES,$oid,time());
        }

        self::setSaveExtention(array ("id" => $oid));
        cjoGenerate::generateTemplates($oid);
    }

    public static function onListDelete($id) {
        
        if ($id == '1') {
            cjoMessage::addError(cjoI18N::translate("msg_cant_delete_default_template"));
        }
        elseif ($id != '') {
    
            $sql = new cjoSql();
            $qry = "SELECT DISTINCT
                    a.id AS id,
                        a.clang AS clang,
                        a.name AS name,
                        t.name AS template_name
                   FROM ".TBL_ARTICLES." a
                   LEFT JOIN ".TBL_TEMPLATES." t
                   ON a.template_id = t.id
                   WHERE a.template_id='".$id."'";
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
                cjoMessage::addError(cjoI18N::translate("msg_cant_delete_template_in_use",
                                                $results[0]['template_name']).'<br />'.implode(' | ',$temp));
    
            if (!cjoMessage::hasErrors()) {
                $sql->flush();  
                $results = $sql->getArray("SELECT * FROM ".TBL_TEMPLATES." WHERE id='".$id."'");
                $sql->flush();
                if ($sql->statusQuery("DELETE FROM ".TBL_TEMPLATES." WHERE id='".$id."'",
                                  cjoI18N::translate("msg_template_deleted"))) {
                    cjoAssistance::updatePrio(TBL_TEMPLATES);
                    cjoExtension::registerExtensionPoint('TEMPLATE_DELETED', $results[0]);
                }
            }
        }
    }
}
