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

class cjoPageUsersManageUsers extends cjoPage {
     
    protected function setEdit() {
     
        $this->fields['name'] = new textField('name', cjoI18N::translate('label_name'));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate('msg_name_notEmpty'));
        $this->fields['name']->addValidator('isNot', cjoI18N::translate('msg_name_inUse'),$used_inputs['names'],true);

        $this->fields['login'] = new textField('login', cjoI18N::translate('label_login'), array('style' => 'font-weight: bold', 'class'=>'readonly', 'readonly' => 'readonly'));
        $this->fields['login']->activateSave(false);
    
        $this->fields['new_psw'] = new passwordField('new_psw', cjoI18N::translate('label_password'));
        $this->fields['new_psw']->addValidator('isLength', cjoI18N::translate('msg_new_psw_toShort'), array('min'=> 6));
        $this->fields['new_psw']->setHelp(cjoI18N::translate("note_psw_encrypted"));
        $this->fields['new_psw']->activateSave(false);
    
        $this->fields['status'] = new selectField('status', cjoI18N::translate('label_status'));
        $this->fields['status']->addAttribute('size', '1');
        $this->fields['status']->addOption(cjoI18N::translate("label_status_do_true"), '1');
        $this->fields['status']->addOption(cjoI18N::translate("label_status_do_false"), '0');
    
        $this->fields['perm_admin'] = new checkboxField('perm_admin', '&nbsp;');
        $this->fields['perm_admin']->setUncheckedValue();
        $this->fields['perm_admin']->addBox(cjoI18N::translate('label_editor_admin'), 'perm_admin');
        
        if ($this->oid <= 1) {
            $this->fields['perm_admin']->addAttribute('checked', 'checked');
            $this->fields['perm_admin']->addAttribute('onclick', 'this.checked = true');
        }
    
        if ($used_inputs['rights'][$this->oid] != '' && in_array('admin[]',$used_inputs['rights'][$this->oid])) {
            $this->fields['perm_admin']->addAttribute('checked', 'checked');
        }
    
        $this->fields['perm_admin']->activateSave(false);
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT SUBSTRING(login,7) AS group_name, user_id as group_id FROM ".TBL_USER." WHERE login REGEXP '^group_' ORDER BY name");
    
        if ($sql->getRows() != 0) {
        $sel_groups = new cjoSelect();
        $sel_groups->setMultiple(true);
        $sel_groups->setSize($sql->getRows()+1);
        $sel_groups->setName("groups[]");
        $sel_groups->setId("groups");
    
        for ($i=0;$i<$sql->getRows();$i++) {
            $sel_groups->addOption($sql->getValue("group_name"),$sql->getValue("group_id"));
    
            if (is_array($used_inputs['description'][$this->oid]) && in_array($sql->getValue("group_id"), $used_inputs['description'][$this->oid])) {
                $sel_groups->setSelected($sql->getValue("group_id"));
            }
        
            $this->fields['perm_rights'] = new readOnlyField('perm_rights', cjoI18N::translate("label_group"));
            $this->fields['perm_rights']->setValue($sel_groups->get());
            $this->fields['perm_rights']->activateSave(false);
            $sql->next();
        }
    
        $this->fields['perm_rights'] = new readOnlyField('perm_rights', cjoI18N::translate("label_group"));
        $this->fields['perm_rights']->setValue($sel_groups->get());
        $this->fields['perm_rights']->activateSave(false);
        }
        $sql->flush();
        $sql->setQuery("SELECT * FROM ".TBL_CLANGS." ORDER BY id");
    
        $sel_lang = new cjoSelect();
        $sel_lang->setMultiple(true);
        $sel_lang->setSize($sql->getRows()+1);
        $sel_lang->setName("lang[]");
        $sel_lang->setId("lang");
    
        for ($i=0;$i<$sql->getRows();$i++) {
            $sel_lang->addOption($sql->getValue("name"),$sql->getValue("id"));
    
            if (is_array($used_inputs['rights'][$this->oid]) && in_array('clang['.$sql->getValue("id").']', $used_inputs['rights'][$this->oid])) {
                $sel_lang->setSelected($sql->getValue("id"));
            }
            $sql->next();
        }
    
        $this->fields['perm_lang'] = new readOnlyField('perm_lang', cjoI18N::translate("label_lang_perm"));
        $this->fields['perm_lang']->setValue($sel_lang->get());
        $this->fields['perm_lang']->activateSave(false);
    
        $this->addUpdateFields();
        $this->section = new cjoFormSection(TBL_USER, cjoI18N::translate('label_edit_user'), array ('user_id' => $this->oid));
    }
     
    protected function getDefault() {
        
        $add_sql = array();
        foreach (cjoProp::getClangs() as $id =>$name) {
        
           $add_sql[] = "IF(FIND_IN_SET('clang[".$id."]', REPLACE(rights,'#',',')), '".cjoProp::getClangIso($id)."|','')";
           $add_sql[] = "IF(FIND_IN_SET('admin[]', REPLACE(rights,'#',',')), '".cjoProp::getClangIso($id)."|','')";
        }
        $add_sql = implode(', '."\r\n", $add_sql);
        
        $qry = "SELECT *,
                    IF(FIND_IN_SET('admin[]', REPLACE(rights,'#',',')), '1','0') as admin,
                    CONCAT(
                    ".$add_sql."
                    ) AS langs
                FROM
                    ".TBL_USER."
                WHERE
                    login NOT LIKE 'group_%'";
        
        $this->list = new cjolist($qry, 'name', 'ASC', 'name', 100);
        
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/user.png" alt="" />',
                                     cjoUrl::createBELink(
                                                  '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                   array('function' => 'add', 'mode' => 'user', 'oid' => ''),
                                                   $this->list->getGlobalParams(),
                                                  'title="'.cjoI18N::translate("button_add").'"')
                                    );
        
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->addCondition('admin', '1', '<img src="img/silk_icons/user_orange.png" alt="" title="'.cjoI18N::translate('label_editor_admin').'" />');
        $this->cols['icon']->addCondition('admin', '0', '<img src="img/silk_icons/user.png" alt="" />');
        $this->cols['icon']->delOption(OPT_SORT);
        
        $this->cols['user_id'] = new resultColumn('user_id', cjoI18N::translate("label_id"));
        $this->cols['user_id']->setHeadAttributes('class="icon"');
        $this->cols['user_id']->setBodyAttributes('class="icon"');
        
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_name"));
        
        $this->cols['login'] = new resultColumn('login', cjoI18N::translate("label_login"));
        
        $replace_groups = array();
        $sql = new cjoSql();
        $qry = "SELECT user_id, name FROM ".TBL_USER." WHERE login REGEXP '^group_'";
        $sql->setQuery($qry);
        for ($i=0;$i<$sql->getRows();$i++) {
            $user_id = $sql->getValue('user_id');
            $name = $sql->getValue('name');
            $replace_groups[$user_id] = $name;
            $sql->next();
        }
        $this->cols['group'] = new resultColumn('description', cjoI18N::translate("label_group"), 'replace_array', array($replace_groups,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
        
        $this->cols['langs'] = new resultColumn('langs', cjoI18N::translate("label_lang_perm"), 'call_user_func', array('cjoAssistance::convertToFlags', array('%s')));
        
        $this->cols['lasttrydate'] = new resultColumn('lasttrydate', cjoI18N::translate("label_last_login"), 'strftime', cjoI18N::translate("dateformat_sort"));
        $this->cols['lasttrydate']->setBodyAttributes('style="white-space:nowrap;"');
        
        $this->cols['login_tries'] = new resultColumn('login_tries', cjoI18N::translate("label_logintries"));
        $this->cols['login_tries']->setHeadAttributes('class="icon"');
        $this->cols['login_tries']->setBodyAttributes('class="icon"');
        
        $this->cols['login_tries']->addCondition('login_tries', array('<',cjoProp::get('MAXLOGINS')), '<span title="'.cjoI18N::translate("label_reset_login_tries").'">%s</span>', array ('function' => 'reset_tries', 'mode' => 'user', 'oid' => '%user_id%'));
        $this->cols['login_tries']->addCondition('login_tries', array('>=',cjoProp::get('MAXLOGINS')), '<b style="color:red" title="'.cjoI18N::translate("label_reset_login_tries").'">%s</b>', array ('function' => 'reset_tries', 'mode' => 'user', 'oid' => '%user_id%'));

        
        $this->cols['edit'] = new editColumn(array ('function' => 'edit', 'mode' => 'users', 'oid' => '%user_id%'));
        
        $this->cols['status'] = new statusColumn('key2', array('function' => 'cjoPageUsersManage::updateSatus', 'oid' => '%user_id%', 'mode' => 'users'));
        $this->cols['status']->addCondition('status', '1','', array('status'=>'0'));
        $this->cols['status']->addCondition('status', '0','', array('status'=>'1'));
        $this->cols['status']->addCondition('status', '-1','<img src="img/silk_icons/key2_start.png" title="" alt="'.cjoI18N::translate("label_admin").'" />');
  
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams(array('id'=>'%user_id%')));

        $this->list->addColumns($this->cols);
        $this->list->show(false);
    }
     
}