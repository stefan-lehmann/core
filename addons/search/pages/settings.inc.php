<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  search
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

//Form
$form = new cjoForm();
$form->setEditMode(true);
$form->debug = false;

$fields['search'] = new cjoLinkButtonField('SEARCH_ARTICLE_ID', $I18N_13->msg('label_search_article'));
$fields['search']->addValidator('notEmpty', $I18N_13->msg('msg_search_article_notEmpty'));
$fields['search']->activateSave(false);

$sel_exclude = clone($CJO['SEL_ARTICLE']);
$sel_exclude->resetDisabled();
$sel_exclude->resetSelected();
$sel_exclude->resetSelectedPath();
$sel_exclude->setName('EXCLUDE_ARTICLES[]');
$sel_exclude->setSelected($CJO['ADDON']['settings'][$mypage]['EXCLUDE_ARTICLES']);
$sel_exclude->setDisabled(0);
$sel_exclude->setStyle('');
$sel_exclude->setMultiple(true);
$sel_exclude->setSize(10);

$fields['exclude_articles'] = new readOnlyField('exclude_articles_container', $I18N_13->msg('label_exclude_articles'));
$fields['exclude_articles']->setValue($sel_exclude->_get());
$fields['exclude_articles']->setHelp($I18N_13->msg("note_exclude_articles"));
$fields['exclude_articles']->activateSave(false);

$fields['include_module'] = new selectField('INCLUDE_MODULES', $I18N->msg("label_modules"));
$fields['include_module']->addSqlOptions("SELECT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_MODULES." ORDER BY prior");
$fields['include_module']->addAttribute('size', 8);
$fields['include_module']->setMultiple(true);
$fields['include_module']->addValidator('notEmpty', $I18N_13->msg('msg_include_module_notEmpty'));
$fields['include_module']->setHelp($I18N_13->msg("note_include_module"));
$fields['include_module']->activateSave(false);

$fields['article_values'] = new selectField('ARTICLE_VALUES', $I18N_13->msg('label_article_search_index'));
$fields['article_values']->addAttribute('size', 5);
$fields['article_values']->setMultiple(true);
$fields['article_values']->setHelp($I18N_13->msg("note_search_values"));
$fields['article_values']->addOption($I18N->msg('label_name'), 'name');
$fields['article_values']->addOption($I18N->msg('label_meta_title'), 'title');
$fields['article_values']->addOption($I18N->msg('label_author'), 'author');
$fields['article_values']->addOption($I18N->msg('label_description'), 'description');
$fields['article_values']->addOption($I18N->msg('label_keywords'), 'keywords');
$fields['article_values']->addValidator('notEmpty', $I18N_13->msg('msg_article_search_index_notEmpty'));
$fields['article_values']->setHelp($I18N_13->msg("note_article_values"));
$fields['article_values']->activateSave(false);

$fields['slice_values'] = new selectField('SLICE_VALUES', $I18N_13->msg('label_slice_search_index'));
$fields['slice_values']->addAttribute('size', 8);
$fields['slice_values']->setMultiple(true);
$fields['slice_values']->setHelp($I18N_13->msg("note_search_values"));
$fields['slice_values']->addValidator('notEmpty', $I18N_13->msg('msg_slice_search_index_notEmpty'));
$fields['slice_values']->activateSave(false);

for($i = 1; $i < 20; $i++){
	$fields['slice_values']->addOption('CJO_VALUE['.$i.']', 'value'.$i);
}

$fields['update_button'] = new buttonField();
$fields['update_button']->addButton('cjoform_update_button',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');
$fields['update_button']->setButtonAttributes('cjoform_update_button', 'id="cjoform_update_button3"');

//Add Fields
$section = new cjoFormSection($CJO['ADDON']['settings'][$mypage], $I18N_13->msg("label_search_settings"), array());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {

	$config_file = $CJO['ADDON']['settings'][$mypage]['settings'];

    $_POST['SETUP'] = 'false';

	if (cjoGenerate::updateSettingsFile($config_file)) {

        $CJO['ADDON']['settings'][$mypage]['ARTICLE_VALUES'] = implode('|',cjo_post('ARTICLE_VALUES','array'));
        $CJO['ADDON']['settings'][$mypage]['SLICE_VALUES']   = implode('|',cjo_post('SLICE_VALUES','array'));
		cjoSearch::removeFulltextIndex();
		cjoSearch::addFulltextIndex();
	}
}

$form->show(false);
