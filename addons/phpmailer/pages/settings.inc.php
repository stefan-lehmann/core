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
 * @version     2.7.x
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

if ($function == "add" || $function == "edit") {

    $form = new cjoForm();
    $form->setEditMode($oid);
    $form->onSaveOrUpdate('cjoPHPMailer::updateSettingsByForm');

    $fields['from_name'] = new textField('from_name', cjoAddon::translate(20,'label_from_name'));
    $fields['from_name']->addValidator('notEmpty', cjoAddon::translate(20,'msg_err_miss_from_name'));

    $fields['from_email'] = new textField('from_email', cjoAddon::translate(20,'label_from_email'));
    $fields['from_email']->addValidator('notEmpty', cjoAddon::translate(20,'msg_err_miss_from_email'));
    $fields['from_email']->addValidator('isEmail', cjoAddon::translate(20,'msg_err_no_email'), true);

    $fields['bcc'] = new textField('bcc', cjoAddon::translate(20,'label_bcc'));
    $fields['bcc']->addValidator('isEmail', cjoAddon::translate(20,'msg_err_no_email'), true);
    $fields['bcc']->setNote(cjoAddon::translate(20,'note_separate_by_comma'));

    $fields['footer'] = new textAreaField('footer', cjoAddon::translate(20,'label_footer'));

    $fields['priority'] = new selectField('priority', cjoAddon::translate(20,'label_priority'));
    $fields['priority']->addAttribute('size', '1');
    $fields['priority']->addAttribute('style', 'width: 10%');    
    $fields['priority']->setNote(cjoAddon::translate(20,'note_priority'));
    $fields['priority']->setDefault(3);
    for ($i=1; $i<=5; $i++) {
        $fields['priority']->addOption($i, $i);
    }

    $fields['headline1'] = new headlineField(cjoAddon::translate(20,'label_connection_settings'));

    $fields['mailer'] = new selectField('mailer', cjoAddon::translate(20,'label_mailer'));
    $fields['mailer']->addAttribute('size', '1');
    $fields['mailer']->addAttribute('style', 'width: 10%');     
    $fields['mailer']->addOption('smtp', 'smtp');
    $fields['mailer']->addOption('mail', 'mail');
    $fields['mailer']->addOption('sendmail', 'sendmail');
    $fields['mailer']->addValidator('notEmpty', '');

    $fields['host'] = new textField('host', cjoAddon::translate(20,'label_host'));

    $onclick = 'if(this.checked != true) $(\'input[name=password],input[name=username]\').val(\'\');';

    $fields['smtp_auth'] = new checkboxField('smtp_auth', '&nbsp;', array('onclick' => $onclick));
    $fields['smtp_auth']->setUncheckedValue();
    $fields['smtp_auth']->addBox(cjoAddon::translate(20,'msg_smtp_auth'), '1');

    $fields['username'] = new textField('username', cjoAddon::translate(20,'label_username'));
    $fields['password'] = new passwordField('password', cjoAddon::translate(20,'label_password'));

    $fields['headline2'] = new headlineField(cjoAddon::translate(20,'label_default_mailer'));

	$fields['status'] = new checkboxField('status', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['status']->addBox(cjoAddon::translate(20,'label_default_mailer'), '-1');
	$fields['status']->activateSave(false);

    if ($status == "-1") {
	    $fields['status']->addAttribute('disabled','disabled');
    }

    /**
     * Do not delete translate values for cjoI18N collection!
     * [translate: label_add_setting]
     * [translate: label_edit_setting]
     */
    $section = new cjoFormSection(TBL_20_MAIL_SETTINGS, cjoAddon::translate(20,"label_".$function."_setting"), array ('id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();
}


$list = new cjoList("SELECT * FROM ".TBL_20_MAIL_SETTINGS, 'id', 'ASC', 100);

$cols['icon'] = new staticColumn('<img src="img/silk_icons/connect.png" alt="" />',
                                 cjoUrl::createBELink(
                                 			  '<img src="img/silk_icons/add.png" alt="'.cjoAddon::translate(20,"label_add_setting").'" />',
                                               array('function' => 'add', 'oid' => ''),
                                               $list->getGlobalParams(),
                                              'title="'.cjoAddon::translate(20,"label_add_setting").'"'));

$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_SORT);

$cols['id'] = new resultColumn('id', cjoI18N::translate("label_id"));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');

$cols['from_name'] = new resultColumn('from_name', cjoAddon::translate(20,"label_from_name"));
$cols['from_name']->addOption(OPT_SORT);

$cols['from_email'] = new resultColumn('from_email', cjoAddon::translate(20,"label_from_email"));
$cols['from_email']->addOption(OPT_SORT);

$cols['host'] = new resultColumn('host', cjoAddon::translate(20,"label_host"));
$cols['host']->addOption(OPT_SORT);

$cols['mailer'] = new resultColumn('mailer', cjoAddon::translate(20,"label_mailer"));
$cols['mailer']->addOption(OPT_SORT);

$cols['smtp_auth'] = new resultColumn('smtp_auth', cjoAddon::translate(20,"label_smtp_auth"));
$cols['smtp_auth']->addCondition('smtp_auth', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
$cols['smtp_auth']->addCondition('smtp_auth', 0, '&nbsp;');
$cols['smtp_auth']->addOption(OPT_SORT);

$cols['edit'] = new editColumn();
$cols['edit']->setHeadAttributes('colspan', 3);

$cols['status'] = new statusColumn('eye', array('function' => 'cjoPHPMailer::statusMailSetting', 'id' => '%id%'));
$cols['status']->addCondition('status', '1', cjoAddon::translate(20,"label_setting_do_offline"), array ('status' => 0));
$cols['status']->addCondition('status', '0', cjoAddon::translate(20,"label_setting_do_online"), array ('status' => 1));
$cols['status']->addCondition('status', '-1', NULL);

$cols['delete'] = new deleteColumn(array('function' => 'cjoPHPMailer::deleteMailSetting', 'id' => '%id%'));
$cols['delete']->addCondition('status', '-1', '&nbsp;');

$list->addColumns($cols);
$list->show(false);