<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  image_processor
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

// STATUS ÄNDERN
if ($function == 'status' && $oid != '') {

    $update = new cjoSql();
    $update->setTable(TBL_IMG_CROP);
    $update->setWhere("id='".$oid."'");
    $update->setValue("status",$mode);
    $update->Update($I18N_8->msg("msg_crop_num_status_updated"));
    unset($function);
}

if ($function == 'edit' || $function == 'add') {

	$sql = new cjoSql();
	$qry = "SELECT * FROM ".TBL_IMG_CROP." WHERE id=".$oid;
	$sql->setQuery($qry);
	$status = $sql->getValue('status');

	//Form
	$form = new cjoForm();
    $form->setEditMode($oid);
    $form->debug = false;

	//Fields
	$fields['note'] = new readOnlyField('note', '');
	$fields['note']->setNote($I18N_8->msg('note_reset_cropsettings'), 'class="warning" style="position: relative; display:block; margin: 5px 0 0 200px!important"');
	$fields['note']->activateSave(false);
	$fields['note']->needFullColumn(true);

	$fields['name'] = new textField('name', $I18N_8->msg('label_name'));
	$fields['name']->addValidator('notEmpty', $I18N_8->msg('msg_empty_name'));
	$fields['name']->needFullColumn(true);

	$fields['status_hidden'] = new hiddenField('status');
	$fields['status_hidden']->setValue($status);

	$fields['status'] = new checkboxField('status', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['status']->addBox($I18N_8->msg('label_default_crop'), '-1');
	$fields['status']->needFullColumn(true);

	$fields['width'] = new textField('width', $I18N_8->msg('label_size'));
	$fields['width']->addValidator('isRange', $I18N_8->msg('msg_wrong_width'), array('low' => '0', 'high' => 10000));
	$fields['width']->addAttribute('style', 'width: 50px; float: left;');
	$fields['width']->addAttribute('maxlength', '4');
	$fields['width']->setNote('&times;', 'style="width: auto;"');

	$fields['height'] = new textField('height', null);
	$fields['height']->addValidator('isRange', $I18N_8->msg('msg_wrong_height'), array('low' => '0', 'high' => 10000));
	$fields['height']->addAttribute('style', 'width: 50px');
	$fields['height']->addAttribute('maxlength', '4');
	$fields['height']->setNote($I18N_8->msg('label_pixel_bxh'));

	$fields['clear2'] = new readOnlyField(null, null);
	$fields['clear2']->addColAttribute('class', 'hide_me');
	$fields['clear2']->needFullColumn(true);

	$fields['aspectratio'] = new checkboxField('aspectratio', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['aspectratio']->addBox($I18N_8->msg('label_aspectratio_crop'), '1');
	$fields['aspectratio']->setUncheckedValue();
	$fields['aspectratio']->needFullColumn(true);

	if ($function == 'add') {

		$oid = '';

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
		$fields['headline1']->setValue($I18N->msg("label_info"));
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

	if ($status == -1 || $_POST['status'] == -1) {

		$fields['status'] = new readOnlyField('label_default_crop', null, array('style'=>'margin-left: 200px;'));
		$fields['status']->setValue($I18N_8->msg('label_default_crop'));
		$fields['status']->needFullColumn(true);
	}

	//Add Fields:
	$section = new cjoFormSection(TBL_IMG_CROP, '', array ('id' => $oid), array('30%', '70%'));

	$section->addFields($fields);
	$form->addSection($section);
	$form->addFields($hidden);
	$form->show();

	if ($form->validate()) {
	    
		if (cjo_post('status','string') == '-1') {
			$update = new cjoSql();
			$update->setTable(TBL_IMG_CROP);
			$update->setWhere("id!='".$oid."' AND status = '-1'");
			$update->setValue("status", '1');
			$update->Update($I18N->msg('msg_data_saved'));
		}

		cjoMedia::resetAllMedia($oid);
		cjoGenerate::generateAll();
	}
}

//LIST Ausgabe
$list = new cjolist("SELECT
						*,
						CONCAT(if (width>0,width,'0'),' &times; ',if (height>0,height,'0'), ' px') AS size,
						if(status!='-1','0','1') AS default_crop
					FROM ".TBL_IMG_CROP,
                    'id',
                    'ASC',
                    100);

$cols['icon'] = new staticColumn('<img src="img/silk_icons/shape_handles.png" alt="" />','');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_SORT);

$cols['id'] = new resultColumn('id', $I18N->msg("label_id"));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', $I18N_8->msg("label_name"));
$cols['name']->setBodyAttributes('width="30%"');

$cols['size'] = new resultColumn('size', $I18N_8->msg("label_size"));

$cols['aspectratio'] = new resultColumn('aspectratio', $I18N_8->msg("label_aspectratio"));
$cols['aspectratio']->addCondition('aspectratio', '1', '<img src="img/silk_icons/accept.png" alt="true" />');
$cols['aspectratio']->addCondition('aspectratio', '0', '&nbsp;');
$cols['aspectratio']->addOption(OPT_SORT);

$cols['default_crop'] = new resultColumn('default_crop', $I18N_8->msg("label_default"));
$cols['default_crop']->addCondition('default_crop', '1', '<img src="img/silk_icons/accept.png" alt="true" />');
$cols['default_crop']->addCondition('default_crop', '0', '&nbsp;');
$cols['default_crop']->addOption(OPT_SORT);

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="2"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

// Condition für Feld STATUS
$inaktiv = '<img src="img/silk_icons/eye_off.png" title="'.$I18N_8->msg("label_crop_num_do_online").'" alt="'.$I18N_8->msg("label_crop_num_offline").'" />';
$aktiv   = '<img src="img/silk_icons/eye.png" title="'.$I18N_8->msg("label_crop_num_do_offline").'" alt="'.$I18N_8->msg("label_crop_num_online").'" />';

$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->setBodyAttributes('style="border-left: none;"');
$cols['status']->setBodyAttributes('class="cjo_status"');
$cols['status']->addCondition('status', '1', $aktiv, array ('function' => 'status', 'mode' => 0, 'oid' => '%id%'));
$cols['status']->addCondition('status', '0', $inaktiv, array ('function' => 'status', 'mode' => 1, 'oid' => '%id%'));
$cols['status']->addCondition('status', '-1', '&nbsp;');

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

//Tabelle anzeigen
$list->show(false);