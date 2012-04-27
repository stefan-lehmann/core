<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  phpmailer
 * @version     2.6.0
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


$sql = new cjoSql;
$sql->setQuery("SELECT * FROM ".TBL_20_MAIL_SETTINGS." WHERE id='".$oid."'");
$status = $sql->getValue('status');

// STATUS ÄNDERN
if ($function == 'status') {
    $update = new cjoSql();
    $update->setTable(TBL_20_MAIL_SETTINGS);
    $update->setWhere("id='".$oid."'");
    $update->setValue("status",$mode);
    $update->Update($I18N_20->msg("msg_status_updated"));
    unset($function);
    unset($_GET['oid']);
}

if ($function == 'delete' &&
    $status != -1) {

   $sql->statusQuery("DELETE FROM ".TBL_20_MAIL_SETTINGS." WHERE id='".$oid."'",
                     $I18N_20->msg("msg_setting_deleted"));
    unset($function);
    unset($oid);
}


// HINZUFÜGEN
if ($function == "add" || $function == "edit") {

	//Form
    $form = new cjoForm();
    $form->setEditMode($oid);
    $form->debug = false;

    $fields['from_name'] = new textField('from_name', $I18N_20->msg('label_from_name'));
    $fields['from_name']->addValidator('notEmpty', $I18N_20->msg('msg_err_miss_from_name'));

    $fields['from_email'] = new textField('from_email', $I18N_20->msg('label_from_email'));
    $fields['from_email']->addValidator('notEmpty', $I18N_20->msg('msg_err_miss_from_email'));
    $fields['from_email']->addValidator('isEmail', $I18N_20->msg('msg_err_no_email'), true);

    $fields['bcc'] = new textField('bcc', $I18N_20->msg('label_bcc'));
    $fields['bcc']->addValidator('isEmail', $I18N_20->msg('msg_err_no_email'), true);
    $fields['bcc']->setNote($I18N_20->msg('note_separate_by_comma'));

    $fields['footer'] = new textAreaField('footer', $I18N_20->msg('label_footer'));

    $fields['priority'] = new selectField('priority', $I18N_20->msg('label_priority'));
    $fields['priority']->addAttribute('size', '1');
    $fields['priority']->addAttribute('style', 'width: 10%');    
    $fields['priority']->setNote($I18N_20->msg('note_priority'));
    $fields['priority']->setDefault(3);
    for ($i=1; $i<=5; $i++) {
        $fields['priority']->addOption($i, $i);
    }

    $fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline first'));
    $fields['headline1']->setValue($I18N_20->msg('label_connection_settings'));

    $fields['mailer'] = new selectField('mailer', $I18N_20->msg('label_mailer'));
    $fields['mailer']->addAttribute('size', '1');
    $fields['mailer']->addAttribute('style', 'width: 10%');     
    $fields['mailer']->addOption('smtp', 'smtp');
    $fields['mailer']->addOption('mail', 'mail');
    $fields['mailer']->addOption('sendmail', 'sendmail');
    $fields['mailer']->addValidator('notEmpty', '');

    $fields['host'] = new textField('host', $I18N_20->msg('label_host'));

    $onclick = 'if(this.checked != true) $(\'input[name=password],input[name=username]\').val(\'\');';

    $fields['smtp_auth'] = new checkboxField('smtp_auth', '&nbsp;', array('onclick' => $onclick));
    $fields['smtp_auth']->setUncheckedValue();
    $fields['smtp_auth']->addBox($I18N_20->msg('msg_smtp_auth'), '1');

    $fields['username'] = new textField('username', $I18N_20->msg('label_username'));
    $fields['password'] = new passwordField('password', $I18N_20->msg('label_password'));


    $fields['headline2'] = new readOnlyField('headline2', '', array('class' => 'formheadline first'));
    $fields['headline2']->setValue($I18N_20->msg('label_default_mailer'));

	$fields['status'] = new checkboxField('status', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['status']->addBox($I18N_20->msg('label_default_mailer'), '-1');
	$fields['status']->activateSave(false);

    if ($status == "-1") {
	    $fields['status']->addAttribute('disabled','disabled');
    }

    /**
     * Do not delete translate values for i18n collection!
     * [translate: label_add_setting]
     * [translate: label_edit_setting]
     */
    $section = new cjoFormSection(TBL_20_MAIL_SETTINGS, $I18N_20->msg("label_".$function."_setting"), array ('id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {

        $oid = $function == "add" ? $form->last_insert_id : cjo_post('oid', 'int');
        $status = cjo_post('status', 'int');

        if ($status == -1 || $oid == 1) {
            $update = new cjoSql();
            $update->setTable(TBL_20_MAIL_SETTINGS);
            $update->setWhere("status='-1'");
            $update->setValue("status", "1");
            $update->Update();

            $update->flush();
            $update->setTable(TBL_20_MAIL_SETTINGS);
            $update->setWhere("id='".$oid."'");
            $update->setValue("status", '-1');
            $update->Update();
        }

        if (cjo_post('cjoform_save_button', 'bool') || $function == "add") {
            $function = '';
        }
    }
}

if ($function == '') {

    //LIST Ausgabe
    $list = new cjolist("SELECT * FROM ".TBL_20_MAIL_SETTINGS, 'id', 'ASC', 100);

    $cols['icon'] = new staticColumn('<img src="img/silk_icons/connect.png" alt="" />',
                                     cjoAssistance::createBELink(
                                     			  '<img src="img/silk_icons/add.png" alt="'.$I18N_20->msg("label_add_setting").'" />',
                                                   array('function' => 'add', 'oid' => ''),
                                                   $list->getGlobalParams(),
                                                  'title="'.$I18N_20->msg("label_add_setting").'"'));

    $cols['icon']->setHeadAttributes('class="icon"');
    $cols['icon']->setBodyAttributes('class="icon"');
    $cols['icon']->delOption(OPT_SORT);

    $cols['id'] = new resultColumn('id', $I18N->msg("label_id"));
    $cols['id']->setHeadAttributes('class="icon"');
    $cols['id']->setBodyAttributes('class="icon"');

    $cols['from_name'] = new resultColumn('from_name', $I18N_20->msg("label_from_name"));
    $cols['from_name']->addOption(OPT_SORT);

    $cols['from_email'] = new resultColumn('from_email', $I18N_20->msg("label_from_email"));
    $cols['from_email']->addOption(OPT_SORT);

    $cols['host'] = new resultColumn('host', $I18N_20->msg("label_host"));
    $cols['host']->addOption(OPT_SORT);

    $cols['mailer'] = new resultColumn('mailer', $I18N_20->msg("label_mailer"));
    $cols['mailer']->addOption(OPT_SORT);

    $cols['smtp_auth'] = new resultColumn('smtp_auth', $I18N_20->msg("label_smtp_auth"));
    $cols['smtp_auth']->addCondition('smtp_auth', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
    $cols['smtp_auth']->addCondition('smtp_auth', 0, '&nbsp;');
    $cols['smtp_auth']->addOption(OPT_SORT);


    // Bearbeiten link
    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
    $cols['edit']->setHeadAttributes('colspan="3"');
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

    // Condition für Feld STATUS
    $inaktiv = '<img src="img/silk_icons/eye_off.png" title="'.$I18N_20->msg("label_setting_do_online").'" alt="'.$I18N_20->msg("label_setting_offline").'" />';
    $aktiv   = '<img src="img/silk_icons/eye.png" title="'.$I18N_20->msg("label_setting_do_offline").'" alt="'.$I18N_20->msg("label_setting_online").'" />';

    $cols['status'] = new staticColumn('status', NULL);
    $cols['status']->setBodyAttributes('width="16"');
    $cols['status']->setBodyAttributes('style="border-left: none;"');
    $cols['status']->setBodyAttributes('class="cjo_status"');
    $cols['status']->addCondition('status', '1', $aktiv, array ('function' => 'status', 'mode' => 0, 'oid' => '%id%'));
        $cols['status']->addCondition('status', '0', $inaktiv, array ('function' => 'status', 'mode' => 1, 'oid' => '%id%'));
        $cols['status']->addCondition('status', '-1', '&nbsp;');

    // Lösch link
    $img = '<img src="img/silk_icons/bin.png" alt="'.$I18N->msg("button_delete").'" title="'.$I18N->msg("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
	$cols['delete']->setBodyAttributes('width="60"');
	$cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));
    $cols['delete']->addCondition('status', '-1', '&nbsp;');

    //Spalten zur Anzeige hinzuf?gen
    $list->addColumns($cols);

    //Tabelle anzeigen
    $list->show(false);
}