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

$sql = new cjoSql();
$sql->setDirectQuery("SELECT login FROM ".TBL_USER." WHERE login NOT LIKE 'group_%'");

$used_logins = array();
for ($i=0; $i < $sql->getRows(); $i++) {
    $used_logins[] = $sql->getValue('login');
    $sql->next();
}

if (cjo_post('add_no_user', 'bool') && $sql->getRows() > 0) {
        
    $data = file_get_contents($CJO['FILE_CONFIG_MASTER']);

    if ($data != '') {
        $data = preg_replace('/^(\$CJO\[\'SETUP\'\]\s*=\s*)(.*)(;.*?)$/imx', '$1false$3', $data);

        if (!cjoGenerate::putFileContents($CJO['FILE_CONFIG_MASTER'], $data)) {
            cjoMessage::addError( $I18N->msg("msg_config_master_no_perm"));
        }
    }
    else {
        cjoMessage::addError($I18N->msg("msg_config_master_does_not_exist"));
    }

    if (!cjoMessage::hasErrors()) {
        cjoGenerate::generateAll();
        header('Location: index.php');
        exit;
    }
}

//Form
$form = new cjoForm();
$form->setEditMode(false);
$form->debug = false;

//Hidden Fields
$hidden['prev_subpage'] = new hiddenField('prev_subpage');
$hidden['prev_subpage']->setValue('step6');

$hidden['lang'] = new hiddenField('lang');
$hidden['lang']->setValue($lang);

$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline'));
$fields['headline1']->setValue($I18N->msg("label_admin_settings"));

$fields['user_login'] = new textField('user_login', $I18N->msg("label_user_login"));
$fields['user_login']->addValidator('notEmpty', $I18N->msg("msg_user_login_notEmpty"));
$fields['user_login']->addValidator('isNot', $I18N->msg('label_user_login').' '.$I18N->msg('msg_allready_in_use'),$used_logins,true);

$fields['user_psw'] = new passwordField('user_psw', $I18N->msg("label_user_psw"));
$fields['user_psw']->addValidator('notEmpty', $I18N->msg("msg_user_psw_notEmpty"));

if (isset($_POST['user_login']))
    $fields['user_login']->setDefault($_POST['user_login']);
    
if (isset($_POST['user_psw']))
    $fields['user_psw']->setDefault($_POST['user_psw']);
    
if(count($used_logins) > 0) {
    $fields['add_no_user'] = new checkboxField('add_no_user', '&nbsp;',  array('style' => 'width: auto;'));
    $fields['add_no_user']->addBox($I18N->msg("label_add_no_user"), '1');
}

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_back_button',$I18N->msg("button_back"), true, 'img/silk_icons/control_play_backwards.png');
$fields['button']->addButton('cjoform_next_button',$I18N->msg("button_finish"), true, 'img/silk_icons/tick.png');

$fields['button']->setButtonAttributes('cjoform_next_button', ' style="color: green"');
//Add Fields:
$section = new cjoFormSection('', $I18N->msg("label_setup_".$subpage."_title"), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
    
    $user_login = cjo_post('user_login', 'string');
    $user_psw  = cjo_post('user_psw', 'string');

    if (!in_array($user_login, $used_logins)) {

        $insert = new cjoSql();
        $insert->setTable(TBL_USER);
        $insert->setValue("name", 'Administrator');
        $insert->setValue("login", $user_login);
        $insert->setValue("psw", md5($user_psw));
        $insert->setValue("rights", '#admin[]#');
        $insert->addGlobalCreateFields("setup");
        $insert->setValue("status", '-1');
        $insert->Insert();

        if ($insert->getError() != '') {

            //[translate: msg_setup_error_try_again]
            cjoAssistance::redirectBE(array('subpage' => 'step8',
                                            'lang' => $lang,
                                            'err_msg' => 'msg_setup_error_try_again'));
        }
    }
    
    if (!cjoMessage::hasErrors()) {

        $data = file_get_contents($CJO['FILE_CONFIG_MASTER']);

        if ($data != '') {
            $data = preg_replace('/^(\$CJO\[\'SETUP\'\]\s*=\s*)(.*)(;.*?)$/imx', '$1false$3', $data);

            if (!cjoGenerate::putFileContents($CJO['FILE_CONFIG_MASTER'], $data)) {
                cjoMessage::addError( $I18N->msg("msg_config_master_no_perm"));
            }
        }
        else {
            cjoMessage::addError($I18N->msg("msg_config_master_does_not_exist"));
        }
    }

    if (!cjoMessage::hasErrors()) {
        cjoGenerate::generateAll();
        header('Location: index.php');
        exit;
    }
}
$form->show(false);

?>

<script type="text/javascript">
/* <![CDATA[ */
	$(function() { $('input[name=user_login], input[name=user_psw]').val(''); });
/* ]]> */
</script>