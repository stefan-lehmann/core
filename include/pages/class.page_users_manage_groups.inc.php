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

class cjoPageUsersManageGroups extends cjoPage {
     
    private $match_mode = 'groups';
     
    protected function setEdit() {
                
        if ($this->mode != $this->match_mode) return false;
        
        $sql = new cjoSql();
        $sql->setQuery("SELECT * FROM ".TBL_USER." WHERE login REGEXP '^group_' AND user_id='".$this->oid."'");
        $default_name = substr($sql->getValue('login'), 6);
        $default_rights = $sql->getValue('rights');

        $this->fields['name'] = new textField('name', cjoI18N::translate("label_group_name"));
        $this->fields['name']->setValue($default_name);
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate('msg_group_name_notEmpty'));
        $this->fields['name']->addValidator('isNot', cjoI18N::translate('msg_name_inUse'),$used_inputs['names'],true);
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT * FROM ".TBL_USER." WHERE login NOT REGEXP '^group_' ORDER BY user_id");
    
        // zugeordnete Redakteure
        $sel_user = new cjoSelect();
        $sel_user->setMultiple(true);
        $sel_user->setSize(($sql->getRows() > 10 ? 11 : $sql->getRows()+1));
        $sel_user->setName("user[]");
        $sel_user->setId("user");
    
        for ($i=0;$i<$sql->getRows();$i++) {
            $sel_user->addOption($sql->getValue("name"), $sql->getValue("user_id"));
    
            if (strpos($sql->getValue("description"), '|'.$this->oid.'|') !== false)
                $sel_user->setSelected($sql->getValue("user_id"));
    
            $sql->next();
        }
    
        $this->fields['related_user'] = new readOnlyField('related_user', cjoI18N::translate("label_editors"));
        $this->fields['related_user']->setValue($sel_user->get());
        $this->fields['related_user']->activateSave(false);
    
        // Allgemeine Rechte ----------------------------------------------------------------------------------------------------------------------------
    
        $this->fields['headline1'] = new headlineField(cjoI18N::translate("label_common"), true);
    
        $sel_all = new cjoSelect();
        $sel_all->setMultiple(true);
        $sel_all->setSize(count(cjoProp::get('PERM')) +1);
        $sel_all->setName("userperm_all[]");
        $sel_all->setId("userperm_all");
    
        foreach(cjoProp::get('PERM') as $key=>$perm) {
    
            $curr_type = preg_replace('/\[\w*\]/', '', $perm);
            if ($prev_type == $curr_type) {
               $nbsp = '&nbsp;| &rarr; ';
            }
            else {
               $nbsp = '';
               $prev_type = $curr_type;
            }
    
            $sel_all->addOption($nbsp.cjoI18N::translate($key),$perm);
    
            if (strpos($default_rights, '#'.$perm.'#') !== false)
                $sel_all->setSelected($perm);
        }
    
        $this->fields['perm_all'] = new readOnlyField('perm_all', cjoI18N::translate("label_backend_perm"));
        $this->fields['perm_all']->setValue($sel_all->get());
        $this->fields['perm_all']->activateSave(false);
    
        $sel_addons = new cjoSelect();
        $sel_addons->setMultiple(true);
        $sel_addons->setSize(count(cjoProp::get('PERMADDON')) +1);
        $sel_addons->setName("userperm_addon[]");
        $sel_addons->setId("userperm_addon");
    
        foreach(cjoProp::get('PERMADDON') as $key=>$perm) {
    
            $curr_type = preg_replace('/\[\w*\]/', '', $perm);
            if ($prev_type == $curr_type) {
               $nbsp = '&nbsp;| &rarr; ';
            }
            else {
               $nbsp = '';
               $prev_type = $curr_type;
            }
            $sel_addons->addOption($nbsp.$key,$perm);
    
            if (strpos($default_rights, '#'.$perm.'#') !== false)
                $sel_addons->setSelected($perm);
        }
    
        $this->fields['perm_addon'] = new readOnlyField('perm_addon', cjoI18N::translate("label_addon_perm"));
        $this->fields['perm_addon']->setValue($sel_addons->get());
        $this->fields['perm_addon']->activateSave(false);
    
        // Zugriff auf Kategorien ----------------------------------------------------------------------------------------------------------------------------
    
        $this->fields['headline2'] = new headlineField(cjoI18N::translate("label_articles"), true);
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT * FROM ".TBL_ARTICLES." WHERE ( startpage=1 OR re_id=0 ) AND clang=0 ORDER BY prior");
    
        $sel_cat = new cjoSelect();
        $sel_cat->setMultiple(true);
        $sel_cat->showRoot(cjoI18N::translate("label_rights_all").' '.cjoI18N::translate('label_article_root'), 'root');
        $sel_cat->setSize(($sql->getRows()>18 ? 20 : $sql->getRows()+2));
        $sel_cat->setName("userperm_cat[]");
        $sel_cat->setId("userperm_cat");
    
        if (strpos($default_rights, '#csw[0]#') !== false) {
            $sel_cat->setSelected(0);
            $sel_cat->root_selected = true;
        }
    
        for ($i=0;$i<$sql->getRows();$i++) {
    
            $sel_cat->addOption($sql->getValue("name"),
                                 $sql->getValue("id"),
                                 $sql->getValue("id"),
                                 $sql->getValue("re_id"));
    
            if (!$sel_cat->root_selected &&
                strpos($default_rights, '#csw['.$sql->getValue("id").']#') !== false)
                $sel_cat->setSelected($sql->getValue("id"));
            $sql->next();
        }
    
        $this->fields['perm_cat'] = new readOnlyField('perm_cat', cjoI18N::translate("label_structure_perm"));
        $this->fields['perm_cat']->setValue($sel_cat->get());
        $this->fields['perm_cat']->activateSave(false);
    
    
        // Extra-Permissions setzen
        $sel_ctypes = new cjoSelect();
        $sel_ctypes->setMultiple(true);
        $sel_ctypes->setSize(cjoProp::countCtypes()+2);
        $sel_ctypes->setName("userperm_ctypes[]");
        $sel_ctypes->setId("userperm_ctypes");
        
        $sel_ctypes->addOption(cjoI18N::translate("label_rights_all").' '.strtoupper(cjoI18N::translate("title_ctypes")),'all');
    
        if (strpos($default_rights, '#ctype[all]#') !== false) {
            $sel_ctypes->setSelected('all');
            $sel_ctypes->root_selected = true;
        }    
        
        foreach(cjoProp::getCtypes() as $ctype_id=>$ctype_name) {
            $sel_ctypes->addOption('&nbsp;| &rarr; '.$ctype_name.' (ID='.$ctype_id.')',$ctype_id);
            if (strpos($default_rights, '#ctype['.$ctype_id.']#') !== false) {
                $sel_ctypes->setSelected($ctype_id);
            }
        }
        
        $this->fields['perm_ctypes'] = new readOnlyField('perm_ctypes', cjoI18N::translate("title_ctypes"));
        $this->fields['perm_ctypes']->setValue($sel_ctypes->get());
        $this->fields['perm_ctypes']->activateSave(false);
        
        // Zugriff auf Medienkategorien ----------------------------------------------------------------------------------------------------------------------------
    
        $this->fields['headline3'] = new headlineField(cjoI18N::translate("label_media_perm"), true);
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT * FROM ".TBL_FILE_CATEGORIES." ORDER BY name");
    
        $sel_media = new cjoSelect();
        $sel_media->setMultiple(true);
        $sel_media->showRoot(cjoI18N::translate("label_rights_all").' '.cjoI18N::translate('label_media_root'), 'root');
        $sel_media->setSize(($sql->getRows()>18 ? 20 : $sql->getRows()+2));
        $sel_media->setName("userperm_media[]");
        $sel_media->setId("userperm_media");
    
        if (strpos($default_rights, '#media[0]#') !== false) {
            $sel_media->setSelected(0);
            $sel_media->root_selected = true;
        }
    
        for ($i=0;$i<$sql->getRows();$i++) {
            $sel_media->addOption($sql->getValue("name"), $sql->getValue("id"), $sql->getValue("id"), $sql->getValue("re_id"));
    
            if (!$sel_media->root_selected &&
                strpos($default_rights, '#media['.$sql->getValue("id").']#') !== false)
                $sel_media->setSelected($sql->getValue("id"));
    
            $sql->next();
        }
    
        $this->fields['perm_media'] = new readOnlyField('perm_media', cjoI18N::translate("label_mediafolder"));
        $this->fields['perm_media']->setValue($sel_media->get());
        $this->fields['perm_media']->activateSave(false);
    
        // Zugriff auf Module und Templates ----------------------------------------------------------------------------------------------------------------------------
    
        $this->fields['headline4'] = new headlineField(cjoI18N::translate("label_module_perm"), true);
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT id, CONCAT(name,' (ID=',id,')') AS name FROM ".TBL_MODULES." ORDER BY prior");
    
        $sel_module = new cjoSelect();
        $sel_module->setMultiple(true);
        $sel_module->setName("userperm_modules[]");
        $sel_module->setId("userperm_modules");
        $sel_module->setSize($sql->getRows()+2);
    
        $sel_module->addOption(cjoI18N::translate("label_rights_all").' '.strtoupper(cjoI18N::translate("label_modules")),0);
    
        if (strpos($default_rights, '#module[0]#') !== false) {
            $sel_module->setSelected(0);
            $sel_module->root_selected = true;
        }
    
        for ($i=0;$i<$sql->getRows();$i++) {
            $sel_module->addOption('&nbsp;| &rarr; '.$sql->getValue("name"),$sql->getValue("id"));
    
            if (!$sel_module->root_selected &&
                strpos($default_rights, '#module['.$sql->getValue("id").']#') !== false)
                $sel_module->setSelected($sql->getValue("id"));
    
            $sql->next();
        }
    
        $this->fields['perm_module'] = new readOnlyField('perm_module', cjoI18N::translate("label_modules"));
        $this->fields['perm_module']->setValue($sel_module->get());
        $this->fields['perm_module']->activateSave(false);
    
    // Zugriff auf Module und Templates ----------------------------------------------------------------------------------------------------------------------------
    
        $this->fields['headline4a'] = new headlineField(cjoI18N::translate("title_templates"), true);
    
        $sql = new cjoSql();
        $sql->setQuery("SELECT id, CONCAT(name,' (ID=',id,')') AS name FROM ".TBL_TEMPLATES." ORDER BY prior");
    
        $sel_template = new cjoSelect();
        $sel_template->setMultiple(true);
        $sel_template->setName("userperm_templates[]");
        $sel_template->setId("userperm_templates");
        $sel_template->setSize($sql->getRows()+2);
    
        $sel_template->addOption(cjoI18N::translate("label_rights_all").' '.strtoupper(cjoI18N::translate("title_templates")),0);
    
        if (strpos($default_rights, '#template[0]#') !== false) {
            $sel_template->setSelected(0);
            $sel_template->root_selected = true;
        }
        
        for ($i=0;$i<$sql->getRows();$i++) {
            $sel_template->addOption('&nbsp;| &rarr; '.$sql->getValue("name"),$sql->getValue("id"));
    
            if (!$sel_template->root_selected &&
                strpos($default_rights, '#template['.$sql->getValue("id").']#') !== false)
                $sel_template->setSelected($sql->getValue("id"));
    
            $sql->next();
        }
      
        $this->fields['perm_template'] = new readOnlyField('perm_template', cjoI18N::translate("title_templates"));
        $this->fields['perm_template']->setValue($sel_template->get());
        $this->fields['perm_template']->activateSave(false);    
        
        
        $this->fields['headline5'] = new headlineField(cjoI18N::translate("label_options"), true);
    
        // Extra-Permissions setzen
        $sel_ext = new cjoSelect();
        $sel_ext->setMultiple(true);
        $sel_ext->setSize(count(cjoProp::get('EXTPERM'))+1);
        $sel_ext->setName("userperm_ext[]");
        $sel_ext->setId("userperm_ext");
    
        foreach(cjoProp::get('EXTPERM') as $key=>$extperm) {
            $sel_ext->addOption(cjoI18N::translate($key),$extperm);
    
            if (strpos($default_rights, '#'.$extperm.'#') !== false)
                $sel_ext->setSelected($extperm);
        }
    
        $this->fields['perm_ext'] = new readOnlyField('perm_ext', cjoI18N::translate("label_options"));
        $this->fields['perm_ext']->setValue($sel_ext->get());
        $this->fields['perm_ext']->activateSave(false);
    
        if (cjoProp::get('EXTRAPERM')) {
            // Extra-Rechte ----------------------------------------------------------------------------------------------------------------------------
        
            $this->fields['headline6'] = new headlineField(cjoI18N::translate("label_extra_perm"), true);
        
            $sel_extra = new cjoSelect();
            $sel_extra->setMultiple(true);
            $sel_extra->setSize(count(cjoProp::get('EXTRAPERM'))+1);
            $sel_extra->setName("userperm_extra[]");
            $sel_extra->setId("userperm_extra");
        
            foreach(cjoProp::get('EXTRAPERM') as $key=>$extraperm) {
                $sel_extra->addOption(cjoI18N::translate($key), $extraperm);
    
                if (strpos($default_rights, '#'.$extraperm.'#') !== false)
                    $sel_extra->setSelected($extraperm);
            }
        
            $this->fields['perm_extra'] = new readOnlyField('perm_extra', cjoI18N::translate("label_extras"));
            $this->fields['perm_extra']->setValue($sel_extra->get());
            $this->fields['perm_extra']->activateSave(false);
        }
        $this->fields['status'] = new hiddenField('status');
        $this->fields['status']->setValue('0');

        $this->addUpdateFields();
        $this->section = new cjoFormSection(TBL_USER, cjoI18N::translate('label_edit_group'), array ('user_id' => $this->oid));
    }
    
    protected function getDefault() {

        if ($this->mode != $this->match_mode && $this->oid) return false;
        
        $qry = "SELECT
            a.*,
            (SELECT DISTINCT COUNT(*) FROM ".TBL_USER."
            WHERE description LIKE CONCAT('%|',a.user_id,'|%')) AS users,
            SUBSTRING(a.login,7) AS groupname
        FROM ".TBL_USER." a
        WHERE a.login LIKE 'group_%'";

        $this->list = new cjolist($qry, 'a.name', 'ASC', 'name', 100);
        $this->list->debug = false;
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/group.png" alt="" />',
                                         cjoUrl::createBELink(
                                                      '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                                       array('function' => 'add', 'mode' => 'groups', 'oid' => ''),
                                                       $this->list->getGlobalParams(),
                                                      'title="'.cjoI18N::translate("button_add").'"'));
        
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_SORT);
        
        $this->cols['user_id'] = new resultColumn('user_id', cjoI18N::translate("label_id"));
        $this->cols['user_id']->setHeadAttributes('class="icon"');
        $this->cols['user_id']->setBodyAttributes('class="icon"');
        
        $this->cols['groupname'] = new resultColumn('groupname', cjoI18N::translate("label_group_name"));
        $this->cols['groupname']->delOption(OPT_SORT);
        
        $this->cols['users'] = new resultColumn('users', cjoI18N::translate("label_editors"));
        

        $this->cols['edit'] = new editColumn(array ('function' => 'edit', 'mode' => $this->match_mode, 'oid' => '%user_id%'));
        
        $this->cols['delete'] = new deleteColumn($this->getDeleteColParams(array('id'=>'%user_id%')));

        $this->list->addColumns($this->cols);
        $this->list->show(false);

    }
     
}