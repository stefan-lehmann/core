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

//Form
$form = new cjoForm();
$form->setEditMode($oid);

$hidden['mode'] = new hiddenField('mode');
$hidden['mode']->setValue($mode);

$fields['input'] = new codeField('input', cjoI18N::translate("label_input"));

$fields['output'] = new codeField('output', cjoI18N::translate("label_output"));

$fields['updatedate_hidden'] = new hiddenField('updatedate');
$fields['updatedate_hidden']->setValue(time());

$fields['updateuser_hidden'] = new hiddenField('updateuser');
$fields['updateuser_hidden']->setValue(cjoProp::getUser()->getValue("name"));


//Add Fields:
$section = new cjoFormSection(TBL_MODULES, '', array ('id' => $oid));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show();

if ($form->validate()) {

	$liveEdit = new liveEdit();
	$inputFilename = $liveEdit->livePath.$liveEdit->ModulePath.$oid.$liveEdit->ModuleInputExtension;
    $outputFilename = $liveEdit->livePath.$liveEdit->ModulePath.$oid.$liveEdit->ModuleOutputExtension;
	@unlink($inputFilename);
	@unlink($outputFilename);
	$liveEdit->writeModuleFiles();

	$sql = new cjoSql();
	$qry = "SELECT DISTINCT " .
		  "	a.id AS article_id" .
		  "FROM ".TBL_ARTICLES." a " .
		  "LEFT JOIN ".TBL_ARTICLES_SLICE." s " .
		  "ON a.id=s.article_id" .
		  "WHERE s.modultyp_id='$oid'";
	$sql->setQuery($qry);

	for ($i=0; $i<$sql->getRows(); $i++) {
		cjoGenerate::deleteGeneratedArticle($sql->getValue("article_id"));
		$sql->next();
	}

    cjoExtension::registerExtensionPoint('MODULE_UPDATED', 
                                             array('ACTION' => 'LOGIC_UPDATED',
                                                   'moduletyp_id' => $oid));  

	if (cjo_post('cjoform_save_button', 'boolean')) {
		cjoUrl::redirectBE(array('mode'=>'', 'oid'=> '', 'msg'=>'msg_data_saved'));
	}
	if (cjo_post('cjoform_update_button', 'boolean')) {
		cjoUrl::redirectBE(array('mode'=>$mode, 'oid'=>$oid, 'msg'=>'msg_data_saved'));
	}
}