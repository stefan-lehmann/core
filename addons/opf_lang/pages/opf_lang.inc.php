<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  languagefilter
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

// AUTOMATISCHES UPDATEN DER RELPACE-VARIABLEN
if ($function == cjoAddon::translate(4,'label_update_vars')) {
    cjoOpfLang::updateLangVars(TBL_OPF_LANG);
    $function = '';
}

// HINZUFÜGEN
if ($function == "add" || $function == "edit" ) {

    //Form
    $form = new cjoForm();
    $form->setEditMode(false);
    $form->onIsValid('cjoOpfLang::updateSettingsByForm');

    //Fields
    if ($function == 'edit') {
	    $fields['replace'] = new readOnlyField('replacename', cjoAddon::translate(4,'label_var'));
    }
    else {
	    $fields['replace'] = new textField('replacename', cjoAddon::translate(4,'label_var'), $readonly);
	    $fields['replace']->addValidator('notEmpty', cjoAddon::translate(4,'err_var'), false, false);
        $fields['replace']->activateSave(false);
    }
    $fields['name'] = new textAreaField('name', cjoAddon::translate(4,'label_name'), array('rows' => 5));
    $fields['name']->addValidator('notEmpty', cjoAddon::translate(4,'err_name'), false, false);
    $fields['name']->activateSave($function == 'edit');

	if ($function == 'add') $oid = '';

    //Add Fields:
    $section = new cjoFormSection(TBL_OPF_LANG, cjoAddon::translate(4,$function."_replacement"), array ('id' => $oid));
    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();
}

//LIST Ausgabe
$sql = "SELECT *, REPLACE(replacename, '[', '&#91;') AS replacename FROM ".TBL_OPF_LANG." WHERE clang=".cjoProp::getClang();
$list = new cjolist($sql, 'status DESC, replacename', 'ASC', 'clang', 40);

$add_button = cjoUrl::createBELink(
						    '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
							array('function' => 'add', 'clang' => $clang),
							$list->getGlobalParams(),
							'title="'.cjoI18N::translate("button_add").'"');

$cols['id'] = new resultColumn('id', $add_button);
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');
$cols['id']->delOption(OPT_ALL);

$cols['replace'] = new resultColumn('replacename', cjoAddon::translate(4,'label_var'));
$cols['replace']->setBodyAttributes('width="30%"');

$cols['name'] = new resultColumn('name', cjoAddon::translate(4,'label_name'));

$cols['status'] = new resultColumn('status', cjoAddon::translate(4,'label_used'));
$cols['status']->addCondition('status', '1', '<img src="img/silk_icons/accept.png" alt="true" />');
$cols['status']->addCondition('status', '0', '&nbsp;');
$cols['status']->setOptions(OPT_SORT);


$cols['edit'] = new editColumn();

if (cjoProp::getUser()->hasPerm('advancedMode[]')) {
    $cols['edit']->setHeadAttributes('colspan', 2);
    $cols['delete'] = new deleteColumn(array('function' => 'cjoOpfLang::deleteReplacement', 'id' => '%id%'));
}

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

$functions  = '<p style="text-align:center">'."\r\n".
              '		<input type="submit" name="function" value="'.cjoAddon::translate(4,'label_update_vars').'" />'."\r\n".
              '</p>'."\r\n";

$list->setVar(LIST_VAR_INSIDE_FOOT, $functions);

//Tabelle anzeigen
$list->show();
