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

//Form
$form = new cjoForm();
$form->setEditMode(false);
//$form->debug = true;


$fields['login'] = new textField('login', cjoI18N::translate('label_login'), array('style' => 'font-weight: bold', 'class'=>'readonly', 'readonly' => 'readonly'));
$fields['login']->activateSave(false);

//Fields
$fields['name'] = new textField('name', cjoI18N::translate('label_name'));
$fields['name']->addValidator('notEmpty', cjoI18N::translate('msg_name_notEmpty'));
$fields['name']->addValidator('isNot', cjoI18N::translate('msg_name_inUse'),$used_inputs['names'],true);
$fields['name']->activateSave(false);

$fields['old_psw'] = new passwordField('old_psw', cjoI18N::translate('label_old_password'));
$fields['old_psw']->addValidator('notEmpty', cjoI18N::translate('msg_old_psw_notEmpty'));
$fields['old_psw']->activateSave(false);

$fields['new_psw'] = new passwordField('new_psw', cjoI18N::translate('label_new_password'));
$fields['new_psw']->addValidator('isLength', cjoI18N::translate('msg_new_psw_toShort'), array('min'=> 6));
$fields['new_psw']->addValidator('notEmpty', cjoI18N::translate('msg_new_psw_notEmpty'));
$fields['new_psw']->activateSave(false);

$fields['confirm_psw'] = new passwordField('confirm_psw', cjoI18N::translate('label_confirm_password'));
$fields['confirm_psw']->addValidator('notEmpty', cjoI18N::translate('msg_confirm_psw_notEmpty'));
$fields['confirm_psw']->activateSave(false);

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_update_button', cjoI18N::translate('button_update'), true, 'img/silk_icons/tick.png');
$fields['button']->needFullColumn(true);

//Add Fields:
$section = new cjoFormSection(TBL_USER, cjoI18N::translate('label_edit_user'), array ('user_id' => cjoProp::getUser()->getValue('user_id')));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {

    $posted                = array();
    $posted['name']        = cjo_post('name', 'string');
    $posted['old_psw']     = cjo_post('old_psw', 'string', true);
    $posted['new_psw']     = cjo_post('new_psw', 'string', true);
    $posted['confirm_psw'] = cjo_post('confirm_psw', 'string');

    if (md5($posted['old_psw']) != cjoProp::getUser()->getValue('psw')) {
        cjoMessage::addError(cjoI18N::translate('msg_data_not_saved'));
        cjoMessage::addError(cjoI18N::translate('msg_old_psw_notEqual'));
        $fields['old_psw']->addAttribute('class', 'invalid', true);
    }

    if ($posted['new_psw']  != $posted['confirm_psw']) {
        cjoMessage::addError(cjoI18N::translate('msg_data_not_saved'));
        cjoMessage::addError(cjoI18N::translate('msg_confirm_psw_notEqual'));
        $fields['confirm_psw']->addAttribute('class', 'invalid', true);
    }

    if (!cjoMessage::hasErrors()) {

        $update = new cjoSql();
        $update->setTable(TBL_USER);
        $update->setWhere("user_id = '".cjoProp::getUser()->getValue('user_id')."'");
        $update->setValue("name",$posted['name']);
        $update->setValue("psw",md5($posted['new_psw']));
    	$update->addGlobalUpdateFields();
        $status = $update->Update();

        if (!$status) {
            cjoMessage::addError($update->getError());
        }
        else {
            
            cjoExtension::registerExtensionPoint('USER_MY_ACCOUNT_UPDATED');
            
            if (cjo_post('cjoform_save_button','boolean')) {
        	   cjoUrl::redirectBE(array('msg'=>'msg_editor_updated'));
            }            
        }
    }
    else {
        $form->valid_master = false;
    }
}
$form->show(false);