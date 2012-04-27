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
 * @version     2.6.0
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

if ($mode == 'groups' && ($function == 'add' || $function == 'edit')) {

    $sql = new cjoSql();
    $sql->setQuery("SELECT * FROM ".TBL_USER." WHERE login REGEXP '^group_' AND user_id='".$oid."'");
    $default_name = substr($sql->getValue('login'), 6);
    $default_rights = $sql->getValue('rights');
    //Form
    $form = new cjoForm();
    $form->setEditMode(false);
    //$form->debug = true;

    $hidden['mode'] = new hiddenField('mode');
    $hidden['mode']->setValue($mode);

    //Fields
    $fields['name'] = new textField('name', $I18N->msg("label_group_name"));
    $fields['name']->setValue($default_name);
    $fields['name']->addValidator('notEmpty', $I18N->msg('msg_group_name_notEmpty'));
    $fields['name']->addValidator('isNot', $I18N->msg('msg_name_inUse'),$used_inputs['names'],true);

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

        if (strpos($sql->getValue("description"), '|'.$oid.'|') !== false)
            $sel_user->setSelected($sql->getValue("user_id"));

        $sql->next();
    }

    $fields['related_user'] = new readOnlyField('related_user', $I18N->msg("label_editors"));
    $fields['related_user']->setValue($sel_user->get());
    $fields['related_user']->activateSave(false);

    // Allgemeine Rechte ----------------------------------------------------------------------------------------------------------------------------

    $fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
    $fields['headline1']->setValue($I18N->msg("label_common"));

    $sel_all = new cjoSelect();
    $sel_all->setMultiple(true);
    $sel_all->setSize(count($CJO['PERM']));
    $sel_all->setName("userperm_all[]");
    $sel_all->setId("userperm_all");

    foreach($CJO['PERM'] as $key=>$perm) {

    	$curr_type = preg_replace('/\[\w*\]/', '', $perm);
    	if ($prev_type == $curr_type) {
           $nbsp = '&nbsp;| &rarr; ';
    	}
    	else {
    	   $nbsp = '';
           $prev_type = $curr_type;
    	}

    	$sel_all->addOption($nbsp.$I18N->msg($key),$perm);

        if (strpos($default_rights, '#'.$perm.'#') !== false)
            $sel_all->setSelected($perm);
    }

    $fields['perm_all'] = new readOnlyField('perm_all', $I18N->msg("label_backend_perm"));
    $fields['perm_all']->setValue($sel_all->get());
    $fields['perm_all']->activateSave(false);

    $sel_addons = new cjoSelect();
    $sel_addons->setMultiple(true);
    $sel_addons->setSize(count($CJO['PERMADDON']));
    $sel_addons->setName("userperm_addon[]");
    $sel_addons->setId("userperm_addon");

    foreach($CJO['PERMADDON'] as $key=>$perm) {

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

    $fields['perm_addon'] = new readOnlyField('perm_addon', $I18N->msg("label_addon_perm"));
    $fields['perm_addon']->setValue($sel_addons->get());
    $fields['perm_addon']->activateSave(false);

    // Zugriff auf Kategorien ----------------------------------------------------------------------------------------------------------------------------

    $fields['headline2'] = new readOnlyField('headline2', '', array('class' => 'formheadline slide'));
    $fields['headline2']->setValue($I18N->msg("label_articles"));

    $sql = new cjoSql();
    $sql->setQuery("SELECT * FROM ".TBL_ARTICLES." WHERE ( startpage=1 OR re_id=0 ) AND clang=0 ORDER BY prior");

    $sel_cat = new cjoSelect();
    $sel_cat->setMultiple(true);
    $sel_cat->showRoot($I18N->msg("label_rights_all").' '.$I18N->msg('label_article_root'), 'root');
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

    $fields['perm_cat'] = new readOnlyField('perm_cat', $I18N->msg("label_structure_perm"));
    $fields['perm_cat']->setValue($sel_cat->get());
    $fields['perm_cat']->activateSave(false);


    // Extra-Permissions setzen
    $sel_ctypes = new cjoSelect();
    $sel_ctypes->setMultiple(true);
    $sel_ctypes->setSize(count($CJO['CTYPE'])+1);
    $sel_ctypes->setName("userperm_ctypes[]");
    $sel_ctypes->setId("userperm_ctypes");
    
    $sel_ctypes->addOption($I18N->msg("label_rights_all").' '.strtoupper($I18N->msg("title_ctypes")),'all');

    if (strpos($default_rights, '#ctype[all]#') !== false) {
        $sel_ctypes->setSelected('all');
        $sel_ctypes->root_selected = true;
    }    
    
    foreach($CJO['CTYPE'] as $ctype_id=>$ctype_name) {
        $sel_ctypes->addOption('&nbsp;| &rarr; '.$ctype_name.' (ID='.$ctype_id.')',$ctype_id);
        if (strpos($default_rights, '#ctype['.$ctype_id.']#') !== false) {
            $sel_ctypes->setSelected($ctype_id);
        }
    }
    
    $fields['perm_ctypes'] = new readOnlyField('perm_ctypes', $I18N->msg("title_ctypes"));
    $fields['perm_ctypes']->setValue($sel_ctypes->get());
    $fields['perm_ctypes']->activateSave(false);
    
    // Zugriff auf Medienkategorien ----------------------------------------------------------------------------------------------------------------------------

    $fields['headline3'] = new readOnlyField('headline3', '', array('class' => 'formheadline slide'));
    $fields['headline3']->setValue($I18N->msg("label_media_perm"));

    $sql = new cjoSql();
    $sql->setQuery("SELECT * FROM ".TBL_FILE_CATEGORIES." ORDER BY name");

    $sel_media = new cjoSelect();
    $sel_media->setMultiple(true);
    $sel_media->showRoot($I18N->msg("label_rights_all").' '.$I18N->msg('label_media_root'), 'root');
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

    $fields['perm_media'] = new readOnlyField('perm_media', $I18N->msg("label_mediafolder"));
    $fields['perm_media']->setValue($sel_media->get());
    $fields['perm_media']->activateSave(false);

    // Zugriff auf Module und Templates ----------------------------------------------------------------------------------------------------------------------------

    $fields['headline4'] = new readOnlyField('headline4', '', array('class' => 'formheadline slide'));
    $fields['headline4']->setValue($I18N->msg("label_module_perm"));

    $sql = new cjoSql();
    $sql->setQuery("SELECT id, CONCAT(name,' (ID=',id,')') AS name FROM ".TBL_MODULES." ORDER BY prior");

    $sel_module = new cjoSelect();
    $sel_module->setMultiple(true);
    $sel_module->setName("userperm_modules[]");
    $sel_module->setId("userperm_modules");
    $sel_module->setSize($sql->getRows()+1);

    $sel_module->addOption($I18N->msg("label_rights_all").' '.strtoupper($I18N->msg("label_modules")),0);

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

    $fields['perm_module'] = new readOnlyField('perm_module', $I18N->msg("label_modules"));
    $fields['perm_module']->setValue($sel_module->get());
    $fields['perm_module']->activateSave(false);

// Zugriff auf Module und Templates ----------------------------------------------------------------------------------------------------------------------------

    $fields['headline4a'] = new readOnlyField('headline4a', '', array('class' => 'formheadline slide'));
    $fields['headline4a']->setValue($I18N->msg("title_templates"));

    $sql = new cjoSql();
    $sql->setQuery("SELECT id, CONCAT(name,' (ID=',id,')') AS name FROM ".TBL_TEMPLATES." ORDER BY prior");

    $sel_template = new cjoSelect();
    $sel_template->setMultiple(true);
    $sel_template->setName("userperm_templates[]");
    $sel_template->setId("userperm_templates");
    $sel_template->setSize($sql->getRows()+1);

    $sel_template->addOption($I18N->msg("label_rights_all").' '.strtoupper($I18N->msg("title_templates")),0);

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
  
    $fields['perm_template'] = new readOnlyField('perm_template', $I18N->msg("title_templates"));
    $fields['perm_template']->setValue($sel_template->get());
    $fields['perm_template']->activateSave(false);    
    
    
    $fields['headline5'] = new readOnlyField('headline5', '', array('class' => 'formheadline slide'));
    $fields['headline5']->setValue($I18N->msg("label_options"));

    // Extra-Permissions setzen
    $sel_ext = new cjoSelect();
    $sel_ext->setMultiple(true);
    $sel_ext->setSize(count($CJO['EXTPERM']));
    $sel_ext->setName("userperm_ext[]");
    $sel_ext->setId("userperm_ext");

    foreach($CJO['EXTPERM'] as $key=>$extperm) {
        $sel_ext->addOption($I18N->msg($key),$extperm);

        if (strpos($default_rights, '#'.$extperm.'#') !== false)
            $sel_ext->setSelected($extperm);
    }

    $fields['perm_ext'] = new readOnlyField('perm_ext', $I18N->msg("label_options"));
    $fields['perm_ext']->setValue($sel_ext->get());
    $fields['perm_ext']->activateSave(false);

    // Extra-Rechte ----------------------------------------------------------------------------------------------------------------------------

    $fields['headline6'] = new readOnlyField('headline6', '', array('class' => 'formheadline slide'));
    $fields['headline6']->setValue($I18N->msg("label_extra_perm"));

    $sel_extra = new cjoSelect();
    $sel_extra->setMultiple(true);
    $sel_extra->setSize(count($CJO['EXTRAPERM'])+1);
    $sel_extra->setName("userperm_extra[]");
    $sel_extra->setId("userperm_extra");

    if (isset($CJO['EXTRAPERM'])) {
        foreach($CJO['EXTRAPERM'] as $key=>$extraperm) {
            $sel_extra->addOption($I18N->msg($key), $extraperm);

            if (strpos($default_rights, '#'.$extraperm.'#') !== false)
                $sel_extra->setSelected($extraperm);
        }
    }
    $fields['perm_extra'] = new readOnlyField('perm_extra', $I18N->msg("label_extras"));
    $fields['perm_extra']->setValue($sel_extra->get());
    $fields['perm_extra']->activateSave(false);

    $fields['status'] = new hiddenField('status');
    $fields['status']->setValue('0');

	if ($function == 'add') {

   		$oid = '';

		$fields['createdate_hidden'] = new hiddenField('createdate');
		$fields['createdate_hidden']->setValue(time());

		$fields['createuser_hidden'] = new hiddenField('createuser');
		$fields['createuser_hidden']->setValue($CJO['USER']->getValue("name"));
	}
	else {

		$fields['updatedate_hidden'] = new hiddenField('updatedate');
		$fields['updatedate_hidden']->setValue(time());

		$fields['updateuser_hidden'] = new hiddenField('updateuser');
		$fields['updateuser_hidden']->setValue($CJO['USER']->getValue("name"));

		$fields['headline7'] = new readOnlyField('headline7', '', array('class' => 'formheadline slide'));
		$fields['headline7']->setValue($I18N->msg("label_info"));
		$fields['headline7']->needFullColumn(true);

		$fields['updatedate'] = new readOnlyField('updatedate', $I18N->msg('label_updatedate'), array(), 'label_updatedate');
		$fields['updatedate']->setFormat('strftime',$I18N->msg('dateformat_sort'));
		$fields['updatedate']->needFullColumn(true);

		$fields['updateuser'] = new readOnlyField('updateuser', $I18N->msg('label_updateuser'), array(), 'label_updateuser');
		$fields['updateuser']->needFullColumn(true);

		$fields['createdate'] = new readOnlyField('createdate', $I18N->msg('label_createdate'), array(), 'label_createdate');
		$fields['createdate']->setFormat('strftime',$I18N->msg('dateformat_sort'));
		$fields['createdate']->needFullColumn(true);

		$fields['createuser'] = new readOnlyField('createuser', $I18N->msg('label_createuser'), array(), 'label_createuser');
		$fields['createuser']->needFullColumn(true);
	}

    /**
     * Do not delete translate values for i18n collection!
     * [translate: label_add_group]
     * [translate: label_edit_group]
     */
    $section = new cjoFormSection(TBL_USER, $I18N->msg('label_'.$function.'_group'), array ('user_id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {

        $oid = ($function == "add") ? $form->last_insert_id : $oid;

        $posted                      = array();
        $posted['name']              = cjo_post('name', 'string');
        $posted['new_users']         = cjo_post('user', 'array');
        $posted['perm']['cat']       = cjo_post('userperm_cat', 'array');
        $posted['perm']['ctypes']    = cjo_post('userperm_ctypes', 'array');        
        $posted['perm']['media']     = cjo_post('userperm_media', 'array');
        $posted['perm']['modules']   = cjo_post('userperm_modules', 'array');
        $posted['perm']['template']  = cjo_post('userperm_templates', 'array');        
        $posted['perm']['templates'] = cjo_post('userperm_templates', 'array');
        $posted['perm']['all']       = cjo_post('userperm_all', 'array');
        $posted['perm']['addon']     = cjo_post('userperm_addon', 'array');
        $posted['perm']['ext']       = cjo_post('userperm_ext', 'array');
        $posted['perm']['extra']     = cjo_post('userperm_extra', 'array');

        // userperm_cat
        foreach ($posted['perm']['cat'] as $cat_id) {

        	if ($cat_id == '') continue;

        	if ($cat_id == 0) {
        	    $posted['perm']['cat']['write'][] = 0;
        	    continue;
        	}

            $cat = OOArticle::getArticleById($cat_id);
            if (!OOArticle::isValid($cat)) continue;
            $tree = $cat->getParentTree();

            $posted['perm']['cat']['write'][$cat->getId()] = $cat->getId();
            $posted['perm']['cat']['read'][$cat->getId()] = '';

            if (is_array($tree)) {
                foreach($tree as $key => $cat) {
                    if (!$posted['perm']['cat']['write'][$cat->getId()]) {
                        $posted['perm']['cat']['read'][$cat->getId()] = $cat->getId();
                    }
                }
            }
        }

		@array_walk($posted['perm']['cat']['write'], 'cjoUser::addRightsPrefix', 'csw');
		@array_walk($posted['perm']['cat']['read'], 'cjoUser::addRightsPrefix', 'csr');
		@array_walk($posted['perm']['ctypes'], 'cjoUser::addRightsPrefix', 'ctype');
		@array_walk($posted['perm']['modules'], 'cjoUser::addRightsPrefix', 'module');
		@array_walk($posted['perm']['templates'], 'cjoUser::addRightsPrefix', 'template');
        @array_walk($posted['perm']['media'], 'cjoUser::addRightsPrefix', 'media');

        $rights  = '#'.implode('#', $posted['perm']['all']).
				   '#'.implode('#', $posted['perm']['addon']).
				   '#'.implode('#', $posted['perm']['ext']).
				   '#'.implode('#', $posted['perm']['extra']).    
                   '#'.implode('#', $posted['perm']['ctypes']).      
				   '#'.implode('#', $posted['perm']['modules']).
                   '#'.implode('#', $posted['perm']['templates']).      
				   '#'.implode('#', cjoAssistance::toArray($posted['perm']['cat']['write'])).
				   '#'.implode('#', cjoAssistance::toArray($posted['perm']['cat']['read'])).
				   '#'.implode('#', $posted['perm']['media']).
				   '#';
        $rights  = preg_replace('/\#{1,}/','#',$rights);

        $update = new cjoSql();
        $update->setTable(TBL_USER);
        $update->setWhere("user_id='".$oid."'");
        $update->setValue("description",count($posted['new_users']));
        $update->setValue("login",'group_'.$posted['name']);
        $update->setValue("psw",md5(time()-1000));
        $update->setValue("status",'0');
        $update->setValue("login_tries","1000");
        $update->setValue("rights",$rights);
        $update->addGlobalUpdateFields();
        $update->Update();

        if ($update->getError() == '') {

            cjoMessage::addSuccess($I18N->msg("msg_group_updated"));

            // einzelne Benutzer updaten
            $sql = new cjoSql();
            $qry = "SELECT user_id, description FROM ".TBL_USER." WHERE login NOT REGEXP '^group_' AND description LIKE '%|".$oid."|%'";
            $old_users = $sql->getArray($qry);

            foreach($old_users as $cur_user) {

                if ($cur_user['user_id'] == '' || in_array($cur_user['user_id'],$posted['new_users'])) continue;

                $related_users[] = $cur_user['user_id'];
                $cur_user['description'] = str_replace('|'.$oid.'|','|',$cur_user['description']);

                $update->flush();
                $update->setTable(TBL_USER);
                $update->setWhere("user_id='".$cur_user['user_id']."'");
                $update->setValue("description",$cur_user['description']);
                $update->addGlobalUpdateFields();
                $update->Update();
            }

            foreach($posted['new_users'] as $cur_user_id) {

                if ($cur_user_id == '') continue;

                // neue Benutzer auslesen
                $sql = new cjoSql();
                $qry = "SELECT description FROM ".TBL_USER." WHERE login NOT REGEXP '^group_' AND user_id='".$cur_user_id."'";
                $sql->setQuery($qry);

                $description = array_diff(explode('|', $sql->getValue('description')), array(''));
                $description[] = $oid;
                $description = array_unique($description);

                $update->flush();
                $update->setTable(TBL_USER);
                $update->setWhere("user_id='".$cur_user_id."'");
                $update->setValue("description",'|'.implode('|',$description).'|');
                $update->addGlobalUpdateFields();
                $update->Update();

                if ($update->getError() == '') {
                    $related_users[] = $cur_user_id;
                }
            }
            if (is_array($related_users)) {
                foreach($related_users as $related_user) {
                    cjoUser::updateUserRights($related_user);
                }
            }
        }
        else{
            cjoMessage::addError($sql->getError());
        }
    }
    
    if ($function == "add") {
        cjoExtension::registerExtensionPoint('USER_GROUP_ADDED', $posted);
    }
    else {
        cjoExtension::registerExtensionPoint('USER_GROUP_UPDATED', $posted);
    }
                
    if (cjo_post('cjoform_save_button','boolean')) {
        unset($functions);
        unset($mode);
        unset($oid);
    }
    else {
        return;
    }
}

$qry = "SELECT
            a.*,
			(SELECT DISTINCT COUNT(*) FROM ".TBL_USER."
		     WHERE description LIKE CONCAT('%|',a.user_id,'|%')) AS users,
			SUBSTRING(a.login,7) AS groupname
        FROM ".TBL_USER." a
        WHERE a.login LIKE 'group_%'";

$list = new cjolist($qry, 'a.name', 'ASC', 'name', 100);
$list->debug = false;
$cols['icon'] = new staticColumn('<img src="img/silk_icons/group.png" alt="" />',
                                 cjoAssistance::createBELink(
                                 			  '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
                                               array('function' => 'add', 'mode' => 'groups', 'oid' => ''),
                                               $list->getGlobalParams(),
                                              'title="'.$I18N->msg("button_add").'"'));

$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_SORT);

$cols['user_id'] = new resultColumn('user_id', $I18N->msg("label_id"));
$cols['user_id']->setHeadAttributes('class="icon"');
$cols['user_id']->setBodyAttributes('class="icon"');

$cols['groupname'] = new resultColumn('groupname', $I18N->msg("label_group_name"));
$cols['groupname']->delOption(OPT_SORT);

$cols['users'] = new resultColumn('users', $I18N->msg("label_editors"));

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="2"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'mode' => 'groups', 'oid' => '%user_id%'));

$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'mode' => 'groups', 'oid' => '%user_id%'));

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

//Tabelle anzeigen
$list->show(false);