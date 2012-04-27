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

//Form
$form = new cjoForm();
$form->setEditMode(false);
//$form->debug = true;

//Hidden Fields
$hidden['group_id'] = new hiddenField('group_id');
$hidden['group_id']->setValue($group_id);

//Fields
$fields['name'] = new textField('name', $I18N_10->msg('label_name'), array('class' => 'large_item'));
$fields['name']->addValidator('notEmpty', $I18N_10->msg('msg_empty_name'), false, false);

$fields['re_id'] = new hiddenField('re_id');
$fields['re_id']->setValue($group_id);

$fields['article_types'] = new selectField('article_types', $I18N_10->msg("label_select_article_types"));
$fields['article_types']->setMultiple();
$fields['article_types']->setValueSeparator('|');
$fields['article_types']->addSqlOptions("SELECT CONCAT(name,' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &rarr; ',description) AS name, type_id FROM ".TBL_ARTICLES_TYPE." WHERE type_id != '1' ORDER BY prior");
$fields['article_types']->addAttribute('size', '8');
$fields['article_types']->setValue(implode('|', cjoCommunityGroups::getArticleTypesOfGroup($oid)));
$fields['article_types']->activateSave(false);

if ($function == 'add') {

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
/**
 * Do not delete translate values for i18n collection!
 * [translate_10: label_add_group]
 * [translate_10: label_edit_group]
 */
$section = new cjoFormSection(TBL_COMMUNITY_GROUPS, $I18N_10->msg('label_'.$function.'_group'), array ('id' => $oid));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show();

if ($form->validate()) {
	cjoCommunityGroups::saveArticleTypesOfGroup();
	cjoAssistance::redirectBE(array('group_id' => $group_id, 'function' => ''));
}
