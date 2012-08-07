<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  event_calendar
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

if (cjo_post('cjoform_export_button', 'bool')) {
    cjoEventImportExport::export();
}

if ($CJO_USER->isAdmin() && cjo_post('cjoform_delete_all_button', 'bool')) {

    $sql = new cjoSql();
    $sql->statusQuery("TRUNCATE TABLE ".TBL_16_EVENTS, $I18N_16->msg('msg_all_db_deleted'));
}


// IMPORT
$form = new cjoForm($mypage.'_'.$subpage.'_import');
$form->setApplyUrl();
$form->setEnctype('multipart/form-data');

$fields['import'] = new readOnlyField('import', $I18N_16->msg('label_csv'), array( 'style' => 'float: left;'));
$fields['import']->setValue('<input type="file" name="userfile" value="'.$_FILES['userfile']['tmp_name'].'" size="48" />');

//DATEI-EINSTELLUNGEN

$fields['headline2'] = new readOnlyField('headline2', '', array('class' => 'formheadline slide'));
$fields['headline2']->setValue($I18N_16->msg('label_file_settings'));

$fields['charset'] = new selectField('charset', $I18N_16->msg("label_charset"));
$fields['charset']->addAttribute('size', '1');

$charset = array('utf-8'=>'unicode (UTF-8)','iso'=>'ISO-8859-1');
foreach($charset as $key=>$value){
    $fields['charset']->addOption('&nbsp;'.$value,$key);
}

$fields['divider'] = new selectField('divider', $I18N_16->msg("label_divider"));
$fields['divider']->addAttribute('size', '1');
$fields['divider']->addAttribute('style', 'width: 90px;');

$divider = array(','=>',',';'=>';','|'=>'|','\t'=>'{TAB}','\x20'=>'{SPACE}');
foreach($divider as $key=>$value){
    $fields['divider']->addOption('&nbsp;'.$value,$key);
}

$fields['limit_start'] = new textField('limit_start', $I18N_16->msg('label_limit_start'));
$fields['limit_start']->setValue('1');
$fields['limit_start']->addValidator('isNumber', $I18N_16->msg("err_limit_start"));

$fields['limit_number'] = new textField('limit_number', $I18N_16->msg('label_limit_number'));
$fields['limit_number']->setHelp($I18N_16->msg('note_limit_number'));
$fields['limit_number']->addValidator('isNumber', $I18N_16->msg("err_limit_number"), true, true);


$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_import_button', $I18N_16->msg('button_import'), true, 'img/silk_icons/database_go.png');

//Add Fields:
$section= new cjoFormSection('', $I18N_16->msg('section_import'), array());

$section->addFields($fields);
$form->addSection($section);

if ($form->validate()) {
    
    if ($_FILES['userfile']['tmp_name'] != ""){
        cjoEventImportExport::import();
    }
    else {
        cjoMessage::addError($I18N_16->msg('file_not_found'));
        $fields['import']->addAttribute('class', 'invalid', 'join');
    }
}

$form->show(false);

// EXPORT
$form = new cjoForm($mypage.'_'.$subpage.'_export');
$form->setEnctype('multipart/form-data');
$form->debug = false;

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_export_button',$I18N_16->msg('button_export'), true, 'img/silk_icons/disk.png');

//Add Fields:
$section= new cjoFormSection('', $I18N_16->msg('section_export'), array());
$section->addField($fields['button']);
$form->addSection($section);

  //Show Form
$form->show(false);

if (!$CJO_USER->isAdmin()) return false;

// DELETE ALL
$form = new cjoForm($mypage.'_'.$subpage.'_delete');
$form->setEnctype('multipart/form-data');

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_delete_all_button',$I18N_16->msg('delete_all'), true, 'img/silk_icons/bin.png');
$fields['button']->setButtonAttributes('cjoform_delete_all_button', 'class="confirm"');

//Add Fields:
$section= new cjoFormSection('', $I18N_16->msg('section_delete_all'), array());
$section->addField($fields['button']);
$form->addSection($section);

$form->show(false);
