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

// AUTOMATISCHES UPDATEN DER RELPACE-VARIABLEN
if ($function == $I18N_4->msg('label_update_vars')) {
    cjoOpfLang::updateLangVars(TBL_OPF_LANG);
    $function = '';
}

// LÖSCHEN
if ($function == 'delete') {

	$sql = new cjoSql();
	$qry = "SELECT * FROM ".TBL_OPF_LANG." WHERE id='".$oid."'";
	$sql->setQuery($qry);
    $qry = "DELETE FROM ".TBL_OPF_LANG." WHERE replacename='".$sql->getValue('replacename')."'";
    $sql->flush();
    $sql->statusQuery($qry,$I18N_4->msg("values_deleted"));
    $function = '';
}

// HINZUFÜGEN
if ($function == "add" || $function == "edit" ) {

    //Form
    $form = new cjoForm();
    $form->setEditMode(false);
    //$form->debug = true;

    //Fields
    if ($function == 'edit') {
	    $fields['replace'] = new readOnlyField('replacename', $I18N_4->msg('label_var'));
    }
    else {
	    $fields['replace'] = new textField('replacename', $I18N_4->msg('label_var'), $readonly);
	    $fields['replace']->addValidator('notEmpty', $I18N_4->msg('err_var'), false, false);

    }
    $fields['name'] = new textAreaField('name', $I18N_4->msg('label_name'), array('rows' => 5));
    $fields['name']->addValidator('notEmpty', $I18N_4->msg('err_name'), false, false);

	if ($function == 'add') {
		$oid = '';
	    $fields['replace']->activateSave(false);
        $fields['name']->activateSave(false);
	}

    //Add Fields:
    $section = new cjoFormSection(TBL_OPF_LANG, $I18N_4->msg($function."_replacement"), array ('id' => $oid));
    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {
        
        if ($function == "add") {

            $replacename = cjo_post('replacename','string');
            $name = cjo_post('name','string');

            $sql = new cjoSql();
            $sql->setQuery("SELECT * FROM ".TBL_OPF_LANG." WHERE replacename= BINARY '".trim($replacename)."'");

            if ($sql->getRows() > 0) {
                cjoMessage::addError($I18N_4->msg("values_already_exists"));
            }
            else {
                foreach ($CJO['CLANG'] as $key=>$val) {
                    $insert = new cjoSql();
                    $insert->setTable(TBL_OPF_LANG);
                    $insert->setValue("name", trim($name));
                    $insert->setValue("replacename",'[translate: '.trim($replacename).']');
                    $insert->setValue("clang",$key);
                    $insert->insert();
                }
                cjoMessage::addSuccess($I18N_4->msg("values_saved"));
            }
        }
    }
}

//LIST Ausgabe
$sql = "SELECT *, REPLACE(replacename, '[', '&#91;') AS replacename FROM ".TBL_OPF_LANG." WHERE clang=".$clang;
$list = new cjolist($sql, 'status DESC, replacename', 'ASC', 'clang', 40);

$add_button = cjoAssistance::createBELink(
						    '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
							array('function' => 'add', 'clang' => $clang),
							$list->getGlobalParams(),
							'title="'.$I18N->msg("button_add").'"');

$cols['id'] = new resultColumn('id', $add_button);
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');
$cols['id']->delOption(OPT_ALL);

$cols['replace'] = new resultColumn('replacename', $I18N_4->msg('label_var'));
$cols['replace']->setBodyAttributes('width="30%"');

$cols['name'] = new resultColumn('name', $I18N_4->msg('label_name'));

$cols['status'] = new resultColumn('status', $I18N_4->msg('label_used'));
$cols['status']->addCondition('status', '1', '<img src="img/silk_icons/accept.png" alt="true" />');
$cols['status']->addCondition('status', '0', '&nbsp;');
$cols['status']->setOptions(OPT_SORT);

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'clang' => $clang, 'oid' => '%id%'));


if ($CJO['USER']->hasPerm('advancedMode[]')) {
    $cols['edit']->setHeadAttributes('colspan="2"');

    $img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
	$cols['delete']->setBodyAttributes('width="60"');
	$cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));
}

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

$functions  = '<p style="text-align:center">'."\n".
              '		<input type="submit" name="function" value="'.$I18N_4->msg('label_update_vars').'" />'."\n".
              '</p>'."\n";

$list->setVar(LIST_VAR_INSIDE_FOOT, $functions);

//Tabelle anzeigen
$list->show();
