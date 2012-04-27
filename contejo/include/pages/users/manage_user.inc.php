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

if ($mode == 'user' && ($function == 'add' || $function == 'edit')) {

    //Form
    $form = new cjoForm();
    $form->setEditMode(false);
    //$form->debug = true;

    $hidden['mode'] = new hiddenField('mode');
    $hidden['mode']->setValue($mode);

	//Fields
    $fields['name'] = new textField('name', $I18N->msg('label_name'));
    $fields['name']->addValidator('notEmpty', $I18N->msg('msg_name_notEmpty'));
    $fields['name']->addValidator('isNot', $I18N->msg('msg_name_inUse'),$used_inputs['names'],true);

    if ($function == "add") {
        $fields['login'] = new textField('login', $I18N->msg('label_login'), array('style' => 'font-weight: bold'));
        $fields['login']->addValidator('notEmpty', $I18N->msg('msg_login_notEmpty'));
        $fields['login']->addValidator('isNot', $I18N->msg('msg_login_inUse'),$used_inputs['login'],true);
    }
    else{
        $fields['login'] = new textField('login', $I18N->msg('label_login'), array('style' => 'font-weight: bold', 'class'=>'readonly', 'readonly' => 'readonly'));
        $fields['login']->activateSave(false);
    }

    $fields['new_psw'] = new passwordField('new_psw', $I18N->msg('label_password'));
    $fields['new_psw']->addValidator('isLength', $I18N->msg('msg_new_psw_toShort'), array('min'=> 6));
    $fields['new_psw']->setHelp($I18N->msg("note_psw_encrypted"));
    $fields['new_psw']->activateSave(false);

    $fields['status'] = new selectField('status', $I18N->msg('label_status'));
    $fields['status']->addAttribute('size', '1');
    $fields['status']->addOption($I18N->msg("label_status_do_true"), '1');
    $fields['status']->addOption($I18N->msg("label_status_do_false"), '0');

    $fields['perm_admin'] = new checkboxField('perm_admin', '&nbsp;');
    $fields['perm_admin']->setUncheckedValue();
    $fields['perm_admin']->addBox($I18N->msg('label_editor_admin'), 'perm_admin');
    if ($function == 'edit_user' &&  $oid <= 1) {
        $fields['perm_admin']->addAttribute('checked', 'checked');
        $fields['perm_admin']->addAttribute('onclick', 'this.checked = true');
    }

    if ($used_inputs['rights'][$oid] != '' && in_array('admin[]',$used_inputs['rights'][$oid])) {
        $fields['perm_admin']->addAttribute('checked', 'checked');
    }

    $fields['perm_admin']->activateSave(false);

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

        if (is_array($used_inputs['description'][$oid]) && in_array($sql->getValue("group_id"), $used_inputs['description'][$oid])) {
            $sel_groups->setSelected($sql->getValue("group_id"));
        }
    
        $fields['perm_rights'] = new readOnlyField('perm_rights', $I18N->msg("label_group"));
        $fields['perm_rights']->setValue($sel_groups->get());
        $fields['perm_rights']->activateSave(false);
        $sql->next();
    }

    $fields['perm_rights'] = new readOnlyField('perm_rights', $I18N->msg("label_group"));
    $fields['perm_rights']->setValue($sel_groups->get());
    $fields['perm_rights']->activateSave(false);
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

        if (is_array($used_inputs['rights'][$oid]) && in_array('clang['.$sql->getValue("id").']', $used_inputs['rights'][$oid])) {
            $sel_lang->setSelected($sql->getValue("id"));
        }
        $sql->next();
    }

    $fields['perm_lang'] = new readOnlyField('perm_lang', $I18N->msg("label_lang_perm"));
    $fields['perm_lang']->setValue($sel_lang->get());
    $fields['perm_lang']->activateSave(false);

   if ($function == 'edit') {

		$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
		$fields['headline1']->setValue($I18N->msg("label_info"));
		$fields['headline1']->needFullColumn(true);

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
	else { $oid = ''; }

    /**
     * Do not delete translate values for i18n collection!
     * [translate: label_add_user]
     * [translate: label_edit_user]
     */
    $section = new cjoFormSection(TBL_USER, $I18N->msg('label_'.$function.'_user'), array ('user_id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {
        
        $oid = ($function == "add") ? $form->last_insert_id : $oid;

        $posted               = array();
        $posted['name']       = cjo_post('name', 'string');
        $posted['groups']     = cjo_post('groups', 'array');
        $posted['new_psw']    = cjo_post('new_psw', 'string', true);
        $posted['lang']       = cjo_post('lang', 'array');
        $posted['perm_admin'] = cjo_post('perm_admin', 'boolean');


        $update = new cjoSql();
        $update->setTable(TBL_USER);
        $update->setWhere("user_id = '".$oid."'");

        if ($posted['new_psw'] != '') {
            $update->setValue("psw",md5($posted['new_psw']));
        }

        $update->setValue("description",'|'.@implode('|',$posted['groups']).'|');

        if ($function == "add") {
            $update->addGlobalCreateFields();
        }
		$update->addGlobalUpdateFields();
        $status = $update->Update();

        if (!$status) {
            cjoMessage::addError($update->getError());
        }
        else {
            cjoUser::updateUserRights($oid,$posted['lang'],$posted['perm_admin']);
            
            if (cjoMessage::hasSuccesses()) {
                
                if ($function == "add") {                
                    //[translate: msg_editor_saved]
                    $msg = 'msg_editor_saved';
                    cjoExtension::registerExtensionPoint('USER_ADDED', $posted);
                }
                else {
                    //[translate: msg_editor_updated] 
                    $msg = 'msg_editor_updated';                    
                    $posted['ACTION'] = 'USER_UPDATED';
                    cjoExtension::registerExtensionPoint('USER_UPDATED', $posted);
                }
                    
                if (cjo_post('cjoform_save_button','boolean')) {
        		      cjoAssistance::redirectBE(array('mode'=>'', 'function'=>'', 'oid'=>'', 'msg'=> $msg));
        		}
            }
        }
    }
    return;
}

$add_sql = array();
foreach ($CJO['CLANG_ISO'] as $id =>$iso) {

   $add_sql[] = "IF(FIND_IN_SET('clang[".$id."]', REPLACE(rights,'#',',')), '".$iso."|','')";
   $add_sql[] = "IF(FIND_IN_SET('admin[]', REPLACE(rights,'#',',')), '".$iso."|','')";
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

$list = new cjolist($qry, 'name', 'ASC', 'name', 100);

$cols['icon'] = new staticColumn('<img src="img/silk_icons/user.png" alt="" />',
                             cjoAssistance::createBELink(
                             			  '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
                                           array('function' => 'add', 'mode' => 'user', 'oid' => ''),
                                           $list->getGlobalParams(),
                                          'title="'.$I18N->msg("button_add").'"')
                            );

$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->addCondition('admin', '1', '<img src="img/silk_icons/user_orange.png" alt="" title="'.$I18N->msg('label_editor_admin').'" />');
$cols['icon']->addCondition('admin', '0', '<img src="img/silk_icons/user.png" alt="" />');
$cols['icon']->delOption(OPT_SORT);

$cols['user_id'] = new resultColumn('user_id', $I18N->msg("label_id"));
$cols['user_id']->setHeadAttributes('class="icon"');
$cols['user_id']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', $I18N->msg("label_name"));

$cols['login'] = new resultColumn('login', $I18N->msg("label_login"));

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
$cols['group'] = new resultColumn('description', $I18N->msg("label_group"), 'replace_array', array($replace_groups,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));

$cols['langs'] = new resultColumn('langs', $I18N->msg("label_lang_perm"), 'call_user_func', array('cjoAssistance::convertToFlags', array('%s')));

$cols['lasttrydate'] = new resultColumn('lasttrydate', $I18N->msg("label_last_login"), 'strftime', $I18N->msg("dateformat_sort"));
$cols['lasttrydate']->setBodyAttributes('style="white-space:nowrap;"');

$cols['login_tries'] = new resultColumn('login_tries', $I18N->msg("label_logintries"));
$cols['login_tries']->setHeadAttributes('class="icon"');
$cols['login_tries']->setBodyAttributes('class="icon"');

$cols['login_tries']->addCondition('login_tries', array('<',$CJO['MAXLOGINS']), '<span title="'.$I18N->msg("label_reset_login_tries").'">%s</span>', array ('function' => 'reset_tries', 'mode' => 'user', 'oid' => '%user_id%'));
$cols['login_tries']->addCondition('login_tries', array('>=',$CJO['MAXLOGINS']), '<b style="color:red" title="'.$I18N->msg("label_reset_login_tries").'">%s</b>', array ('function' => 'reset_tries', 'mode' => 'user', 'oid' => '%user_id%'));

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="3"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'mode' => 'user', 'oid' => '%user_id%'));

$aktiv = '<img src="img/silk_icons/key2.png" title="'.$I18N->msg("label_status_do_false").'" alt="'.$I18N->msg("label_status_true").'" />';
$inaktiv = '<img src="img/silk_icons/key2_off.png" title="'.$I18N->msg("label_status_do_false").'" alt="'.$I18N->msg("label_status_false").'" />';
$disabled = '<img src="img/silk_icons/key2_start.png" title="" alt="'.$I18N->msg("label_admin").'" />';
$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->addCondition('status', '1', $aktiv, array ('function' => 'status', 'mode' => 'user', 'status' => 0, 'oid' => '%user_id%'));
$cols['status']->addCondition('status', '0', $inaktiv, array ('function' => 'status', 'mode' => 'user', 'status' => 1, 'oid' => '%user_id%'));
$cols['status']->addCondition('status', '-1', $disabled, array ());

$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'mode' => 'user', 'oid' => '%user_id%'));

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

//Tabelle anzeigen
$list->show(false);

?>
<script type="text/javascript">
/* <![CDATA[ */
	$(function() { $('input[name=new_psw]').val(''); });
/* ]]> */
</script>