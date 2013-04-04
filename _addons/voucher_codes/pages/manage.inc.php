<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  voucher_codes
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

if (cjo_post('cjoform_export_button', 'bool')) {
    vc_export_redemptions(cjo_post('event_id','int'));
}

if (cjo_post('cjoform_delete_all_button', 'bool') &&
	$CJO_USER->isAdmin()) {
    $sql = new cjoSql();
    $sql->statusQuery("TRUNCATE TABLE ".TBL_17_VOUCHER, $I18N_17->msg('msg_all_db_deleted'));
}

// IMPORT
$form = new cjoForm($mypage.'_'.$subpage.'_import');
$form->setEnctype('multipart/form-data');

$fields['codes'] = new textAreaField('codes', $I18N_17->msg('label_codes'));
$fields['codes']->addAttribute('rows', '5');
$fields['codes']->addAttribute('cols', '10');
$fields['codes']->addValidator('notEmpty', $I18N_17->msg("msg_err_codes_notEmpty"), false, false);
$fields['codes']->setNote($I18N_17->msg("note_separate_by_new_line"));

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_import_button',$I18N_17->msg('button_import'), true, 'img/silk_icons/database_go.png');

//Add Fields:
$section= new cjoFormSection('', $I18N_17->msg('section_import'), array());

$section->addFields($fields);
$form->addSection($section);

if ($form->validate()) {
    vc_import_codes();
}
$form->show(false);


// EXPORT
$form = new cjoForm($mypage.'_'.$subpage.'_export');
$form->setEnctype('multipart/form-data');

$qry = "SELECT
		 CONCAT(ev.title,
		 		' :: ',
		 		(DATE_FORMAT( FROM_UNIXTIME( ev.start_date ),'%d.%m.%y')),
		 		' (',
		 		(IFNULL((SELECT count(event_id) AS val FROM ".TBL_17_VOUCHER." WHERE event_id=ev.id GROUP BY event_id), 0)),
		 		')'
		 ) AS name,
		 ev.id
		 FROM ".TBL_16_EVENTS." ev
		 WHERE (ev.start_date - UNIX_TIMESTAMP(now())) > -86000
		 ORDER BY start_date";

$fields['event'] = new selectField('event_id', $I18N_17->msg("label_event"));
$fields['event']->addSqlOptions($qry);
$fields['event']->addAttribute('size', '1');

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_export_button',$I18N_17->msg('button_export'), true, 'img/silk_icons/disk.png');

//Add Fields:
$section= new cjoFormSection('', $I18N_17->msg('section_export'), array());
$section->addFields($fields);
$form->addSection($section);

  //Show Form
$form->show(false);

// DELETE ALL
if ($CJO_USER->isAdmin()) {

    $form = new cjoForm($mypage.'_'.$subpage.'_delete');
    $form->setApplyUrl('index.php?page='.$mypage.'&subpage='.$subpage);
    $form->setEnctype('multipart/form-data');

    $fields['button'] = new buttonField();
    $fields['button']->addButton('cjoform_delete_all_button',$I18N_17->msg('delete_all'), true, 'img/silk_icons/bin.png');
    $fields['button']->setButtonAttributes('cjoform_delete_all_button', 'class="confirm"');

    //Add Fields:
    $section= new cjoFormSection('', $I18N_17->msg('section_delete_all'), array());
    $section->addField($fields['button']);
    $form->addSection($section);

    $form->show(false);
}