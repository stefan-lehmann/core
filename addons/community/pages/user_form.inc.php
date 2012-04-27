<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
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

if (cjo_post('email','bool')) {
    $_POST['email'] = strtolower($_POST['email']);
}

if (cjo_post('cjoform_cancel_button','bool')) {
	cjoAssistance::redirectBE(array('group_id'=>$group_id));
}

$new_pw    = cjo_post('new_pw', 'string', cjoCommunityUser::generatePassword());
$set_login = cjo_request('set_login', 'int');

$used_inputs = array();
$valid_form = true;

$sql = new cjoSql();
$qry = "SELECT id, username, email, password, activation, status FROM ".TBL_COMMUNITY_USER." ORDER BY id";
$sql->setQuery($qry);

for ($i=0; $i < $sql->getRows(); $i++) {
	$id = $sql->getValue('id');

	if ($oid != $id) {
		$used_inputs['username'][$id] = $sql->getValue('username');
		$used_inputs['email'][$id] = $sql->getValue('email');
	}
	else {
		$user_typ 	= $sql->getValue('password');
		$activation = $sql->getValue('activation');
		$status 	= $sql->getValue('status');
	}
	$sql->next();
}

//Form
$form = new cjoForm();
$form->setEditMode($oid);
//$form->debug = true;

//Hidden Fields
$hidden['new_pw'] = new hiddenField('new_pw');
$hidden['new_pw']->setValue($new_pw);

$hidden['group_id'] = new hiddenField('group_id');
$hidden['group_id']->setValue($group_id);

$hidden['status'] = new hiddenField('status');
$hidden['c_status'] = new hiddenField('c_status');

$hidden['user_typ'] = new hiddenField('user_typ');
$hidden['user_typ']->setValue($user_typ);

if ($set_login) {
    $hidden['set_login'] = new hiddenField('set_login');
    $hidden['set_login']->setValue($set_login);
}
//Fields

if ($function != 'add' && $user_typ && !$set_login) {
    $labels['user_typ'] = '<img src="./img/silk_icons/key2.png" title="'.$I18N_10->msg('label_user_typ_password').'" alt="" /> '.$I18N_10->msg('label_user_typ_password');
    $fields['user_typ'] = new readOnlyField('groups[]', $I18N_10->msg('label_user_typ'), array('class' => 'large_item'));
    $fields['user_typ']->setValue($labels['user_typ'].$labels['set_login']);
}
else {
    $fields['user_typ'] = new selectField('user_typ', $I18N_10->msg('label_user_typ'), array('class' => 'large_item'), 'label_user_typ');
    $fields['user_typ']->addAttribute('size', '1');
    $fields['user_typ']->activateSave(false);

	if (!$set_login) {
        $fields['user_typ']->addOption($I18N_10->msg('label_user_typ_abo'), '0');
        $fields['user_typ']->addOption($I18N_10->msg('label_user_typ_password_create'), '1');
	}
	else {
        $fields['user_typ']->addOption($I18N_10->msg('label_user_typ_password_create'), '1');
        $fields['user_typ']->addOption($I18N_10->msg('label_user_typ_abo'), '0');
	}
}

if ($function == "add") {
	$fields['clang'] = new hiddenField('clang');
	$fields['clang']->setValue($clang);

	$fields['email'] = new textField('email', $I18N_10->msg('label_email'), array('class' => 'large_item'));
	$fields['email']->addValidator('notEmpty', $I18N_10->msg('msg_empty_email'));
	$fields['email']->addValidator('isEmail', $I18N_10->msg('msg_no_valid_email'));
	$fields['email']->addValidator('isNot', $I18N_10->msg('msg_email_in_use'), $used_inputs['email'],true);
}
else{
	$fields['email'] = new textField('email', $I18N_10->msg('label_email'), array('style' => 'font-weight: bold', 'class'=>'readonly', 'readonly' => 'readonly'));
}

if (($status && $user_typ) || $set_login) {

	$fields['username'] = new textField('username', $I18N_10->msg('label_username'));
	$fields['username']->addValidator('notEmpty', $I18N_10->msg('msg_empty_username'));
	$fields['username']->addValidator('isNot', $I18N_10->msg('msg_username_in_use'),$used_inputs['username'],true);

    $fields['password'] = new checkboxField('password', $I18N_10->msg('label_new_password'),  array('style' => 'width: auto;'));
    $fields['password']->addBox('<span class="hinweis">'. $I18N_10->msg('note_new_password').'</span>', md5($new_pw));
    $fields['password']->activateSave(false);

    if ($function == 'add' || $set_login == 2) {
        $fields['password']->addAttribute('checked', 'checked');
        $fields['password']->addAttribute('onclick', 'this.checked = true');
    }
}

$fields['headline1a'] = new readOnlyField('headline1a', '', array('class' => 'formheadline'));
$fields['headline1a']->setValue($I18N_10->msg('label_contact_personal'));

$fields['gender'] = new selectField('gender', $I18N_10->msg('label_gender'));
$fields['gender']->addAttribute('size', '1');
$fields['gender']->addAttribute('style', 'width: 130px;');
$fields['gender']->addValidator('notEmpty', $I18N_10->msg('err_notEmpty_gender'));
$fields['gender']->addOption('', '');

preg_match_all('/(?<=^|\|)([^\|]*)=([^\|]*)(?=\||$)/',
               $CJO['ADDON']['settings'][$mypage]['GENDER_TYPES'],
               $gender_types,
               PREG_SET_ORDER);

foreach($gender_types as $gender_type) {
	$fields['gender']->addOption($gender_type[2], $gender_type[1]);
}

$fields['firstname'] = new textField('firstname', $I18N_10->msg('label_firstname'));
$fields['firstname']->addValidator('notEmpty', $I18N_10->msg("msg_empty_firstname"));

$fields['name'] = new textField('name', $I18N_10->msg('label_name'));
$fields['name']->addValidator('notEmpty', $I18N_10->msg("msg_empty_name"));

$fields['birthdate'] = new datepickerField('birthdate', $I18N_10->msg("label_birthdate"), '', array('birthdate'));
$fields['birthdate']->addSettings("altFormat: 'yy-mm-dd', dateFormat: 'dd.mm.yy', yearRange: '1920:2020',buttonImage: 'img/silk_icons/calendar.png'", true);
$fields['birthdate']->addColAttribute('style', 'width: 37%');
$fields['birthdate']->setFormat('preg_replace',array('/([\d]{4})-([0-3]{1}[\d]{1})-([0-1]{1}[\d]{1})/','\3.\2.\1'));


$sel_group = cjoCommunityGroups::getSelectGroups($oid);
$sel_group->setSelected(cjo_post('groups', 'array'));
$fields['groups'] = new readOnlyField('groups[]', $I18N_10->msg('label_groups'));
$fields['groups']->setValue($sel_group->get());
$fields['groups']->addValidator('notEmpty', $I18N_10->msg('err_notEmpty_groups'));

$fields['status'] = new selectField('status', $I18N_10->msg('label_status'), array(), 'label_status');
$fields['status']->addAttribute('size', '1');
$fields['status']->addAttribute('style', 'width: 130px;');
$fields['status']->addOption($I18N->msg('label_status_true'), '1');
if ($function != "add" && !$set_login) {
	$fields['status']->addOption($I18N->msg('label_status_false'), '0');
	$fields['status']->addOption($I18N_10->msg('label_status_disabled'), '-1');
}

$fields['newsletter'] = new checkboxField('newsletter', $I18N_10->msg('label_newsletter'),  array('style' => 'width: auto;'));
$fields['newsletter']->setUncheckedValue();
$fields['newsletter']->addBox($I18N_10->msg('label_newsletter_active'), '1');
if ($function == "add")
	$fields['newsletter']->setDefault('1');

if (!$user_typ && !$set_login) {
	$fields['newsletter']->addAttribute('onclick', 'this.checked = true;');
	$fields['newsletter']->setValue('1');
}
// Kontakt privat ----------------------------------------------------------------------------------------------------------------------------

$fields['headline2'] = new readOnlyField('headline2', '', array('class' => 'formheadline slide'));
$fields['headline2']->setValue($I18N_10->msg('label_contact_private'));

$fields['street'] = new textField('street', $I18N_10->msg('label_street'));
$fields['plz'] = new textField('plz', $I18N_10->msg('label_plz'));
$fields['town'] = new textField('town', $I18N_10->msg('label_town'));
$fields['phone'] = new textField('phone', $I18N_10->msg('label_phone'));
$fields['mobile'] = new textField('mobile', $I18N_10->msg('label_mobile'));
$fields['email2'] = new textField('email2', $I18N_10->msg('label_email2'));

// Kontakt geschäftlich ----------------------------------------------------------------------------------------------------------------------------

$fields['headline3'] = new readOnlyField('headline3', '', array('class' => 'formheadline slide'));
$fields['headline3']->setValue($I18N_10->msg('label_contact_company'));

$fields['company_name'] = new textField('company_name', $I18N_10->msg('label_company_name'));
$fields['company_department'] = new textField('company_department', $I18N_10->msg('label_company_department'));
$fields['company_street'] = new textField('company_street', $I18N_10->msg('label_company_street'));
$fields['company_plz'] = new textField('company_plz', $I18N_10->msg('label_company_plz'));
$fields['company_town'] = new textField('company_town', $I18N_10->msg('label_company_town'));
$fields['company_phone'] = new textField('company_phone', $I18N_10->msg('label_company_phone'));
$fields['company_fax'] = new textField('company_fax', $I18N_10->msg('label_company_fax'));

// Infos ----------------------------------------------------------------------------------------------------------------------------

if ($function == 'add') {

	$fields['activation'] = new hiddenField('activation');
	$fields['activation']->setValue(1);

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

	$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
	$fields['headline1']->setValue($I18N_8->msg("label_info"));
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

//Add Fields:
$section = new cjoFormSection(TBL_COMMUNITY_USER, $I18N_10->msg('label_'.$function.'_user'), array ('id' => $oid));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);


if ($form->validate()) {
    
	$_email    = cjo_post('email','string');
	$_username = cjo_post('username','string');
	$_add_qry  = ($_username) ? "OR username LIKE '".$_username."'" : "";

	$sql = new cjoSql();
 	$qry = "SELECT id, email, username FROM ".TBL_COMMUNITY_USER." WHERE email LIKE '".$_email."' ".$add_qry;
	$sql->setQuery($qry);

	if ($sql->getRows() != 0) {

	   for ($i=0; $i<$sql->getRows(); $i++) {

	   		if ($function == 'add' &&
	   		    isset($_email) &&
	   		    $_email == $sql->getValue('email')) {

    			cjoMessage::addError($I18N_10->msg('msg_email_in_use'));
    			$fields['email']->addAttribute('class', 'invalid');
    			$form->valid_master = false;
    			unset($_email);
    		}

	   	    if (cjo_post('oid','int') != $sql->getValue('id') &&
	   	        isset($_username) &&
	   	        $_username == $sql->getValue('username')) {

    			cjoMessage::addError($I18N_10->msg('msg_username_in_use'));
    			$fields['username']->addAttribute('class', 'invalid');
    			$form->valid_master = false;
    			unset($_username);
    		}
    		$sql->next();
    	}

	}
}

$form->show();

if (!cjoMessage::hasErrors() && $form->validate()) {

    if ($function == "add")  $oid = $form->last_insert_id;

	$groups = cjo_post('groups', 'array');

	if (cjoCommunityGroups::updateGroups($oid, $groups)) {

    	cjoMessage::removeLastSuccess();

    	$data = array();
    	$data['username'] 	= cjo_post('username','string');
    	$data['name'] 	 	= cjo_post('name','string');
    	$data['firstname'] 	= cjo_post('firstname','string');
    	$data['status'] 	= cjo_post('status','int');
    	$data['gender'] 	= cjo_post('gender','string');
    	$data['email'] 		= strtolower(cjo_post('email','string'));

    	if (cjo_post('password', 'string') == md5($new_pw)) {
    		$data['new_pw'] = cjo_post('new_pw','string');

    		$update = new cjoSql();
            $update->setTable(TBL_COMMUNITY_USER);
            $update->setWhere("id='".$oid."'");
            $update->setValue("password", md5($new_pw));
            if ($update->update()) {
				cjoCommunityUser::sendNotification($data, 'SEND_PASSWORD_MSG');
			}
    	}
		else if ($user_typ && cjo_post('status','bool') && $activation == -1) {
			cjoCommunityUser::sendNotification($data, 'ACTIVATION_MSG');

    		$update = new cjoSql();
       		$update->setTable(TBL_COMMUNITY_USER);
            $update->setWhere("id='".$oid."' AND activation='-1'");
        	$update->setValue("activation", '1');
        	$update->update();
		}

    	if (cjo_post('cjoform_update_button', 'bool') && !cjoMessage::hasErrors()) {
    		cjoAssistance::redirectBE(array('group_id'=>$group_id, 'function'=>'edit', 'oid'=>$oid, 'msg' => 'msg_data_saved'));
    	}
		if (cjo_post('cjoform_save_button','bool')) {
			unset($function);
		}
	}
}

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function() {
		$('select#label_user_typ').change(function() {
			var selected = $(this).find('option:selected').val();

			if (selected == 1){
				location.href = '<?php echo cjoAssistance::createBEUrl(array('group_id'=>$group_id, 'clang'=>$clang, 'set_login'=>'2')); ?>';
			} else {
				location.href = '<?php echo cjoAssistance::createBEUrl(array('group_id'=>$group_id, 'clang'=>$clang, 'set_login'=>'')); ?>';
			}
		});
	});

/* ]]> */
</script>