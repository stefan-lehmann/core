<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  archive
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

$settings = $CJO['ADDON']['settings'][$mypage];
$settings['CATEGORIES'] = '|'.$settings['CATEGORIES'].'|';

//Form
$form = new cjoForm();
$form->setEditMode(false);
//$form->debug = true;

$sql = new cjoSql();
$sql->setQuery("SELECT * FROM ".TBL_ARTICLES." WHERE ( startpage=1 OR re_id=0 ) AND clang=0 ORDER BY prior");

$sel_cat = new cjoSelect();
$sel_cat->setMultiple(true);
$sel_cat->showRoot($I18N->msg("label_rights_all").' '.$I18N->msg('label_article_root'), 'root');
$sel_cat->setSize(($sql->getRows()>18 ? 20 : $sql->getRows()+2));
$sel_cat->setName("CATEGORIES[]");
$sel_cat->setId("categories");

if (strpos($settings['CATEGORIES'], '|0|') !== false) {
    $sel_cat->setSelected(0);
    $sel_cat->root_selected = true;
}
for ($i=0;$i<$sql->getRows();$i++) {
    $sel_cat->addOption($sql->getValue("name"),
    					 $sql->getValue("id"),
    					 $sql->getValue("id"),
    					 $sql->getValue("re_id"));

    if (!$sel_cat->root_selected &&
    	strpos($settings['CATEGORIES'], '|'.$sql->getValue("id").'|') !== false)
        $sel_cat->setSelected($sql->getValue("id"));
    $sql->next();
}

$fields['categories'] = new readOnlyField('categories', $I18N_28->msg("label_categories_select"));
$fields['categories']->setValue($sel_cat->get());
$fields['categories']->activateSave(false);

$fields['duration'] = new selectField('DURATION',  $I18N_28->msg("label_duration"));
$fields['duration']->addOptions(array(array($I18N_28->msg('label_immediately'),            0),
                                        array($I18N_28->msg('label_duration_week', 1),     7*60*60*24),
                                        array($I18N_28->msg('label_duration_weeks', 2),   14*60*60*24),
                                        array($I18N_28->msg('label_duration_month', 1),   30*60*60*24),
                                        array($I18N_28->msg('label_duration_months', 2),  60*60*60*24),
                                        array($I18N_28->msg('label_duration_months', 3),  90*60*60*24),
                                        array($I18N_28->msg('label_duration_months', 4), 120*60*60*24)
                                        ));
                                        
$fields['duration']->setMultiple(false);
$fields['duration']->addAttribute('size', '1', false);
$fields['duration']->activateSave(false);
                                        
$fields['disabled_hidden'] = new hiddenField('DISABLED');
$fields['disabled_hidden']->setValue('0');
$fields['disabled_hidden']->activateSave(false);
$fields['disabled'] = new checkboxField('DISABLED', '&nbsp;');
$fields['disabled']->addBox($I18N_28->msg('label_disable_archive'), '1');
$fields['disabled']->activateSave(false);
                                            
$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_update_button', $I18N->msg('button_update'), true, 'img/silk_icons/tick.png');
$fields['button']->needFullColumn(true);


//Add Fields
$section = new cjoFormSection($settings, $I18N_28->msg("label_basic_settings"), array());

$section->addFields($fields);
$form->addSection($section);
$form->show(false);

if ($form->validate()) {

	if (!cjoMessage::hasErrors()) {
    	if (cjoGenerate::updateSettingsFile($CJO['ADDON']['settings'][$mypage]['SETTINGS_FILE'])) {
    	    include $CJO['ADDON']['settings'][$mypage]['SETTINGS_FILE'];
    	    cjoArchive::archiveArticles();
    		cjoAssistance::redirectBE(array('msg' => 'msg_data_saved'));
    	}
    	else {
    	    cjoMessage::addError($I18N->msg('msg_data_not_saved'));
    	}
	}
}


