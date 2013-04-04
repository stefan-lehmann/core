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

$icons['php'] = 'tick';
$icons['gd'] = 'tick';
$icons['copy'] = 'tick';
$icons['perm'] = 'tick';
$icons['addons'] = 'tick';

if (version_compare($CJO['PHP_VERSION'], phpversion(), ">")){
	cjoMessage::addError(cjoI18N::translate("msg_setup_step3_err_php", $CJO['PHP_VERSION']));
	$icons['php'] = 'exclamation';
}

$gd_info = gd_info();

if ($gd_info["GD Version"] === false){
	cjoMessage::addError(cjoI18N::translate("msg_setup_step3_err_gd", $gd_info["GD Version"]));
	$icons['gd'] = 'exclamation';
}

cjoFile::copyDir($CJO['INSTALL_PATH'].'/structure', $CJO['HTDOCS_PATH']);
    
if (cjoMessage::hasErrors()){
    $icons['copy'] = 'exclamation';
}

$CJO['FRONTPAGE_PATH'] = $CJO['HTDOCS_PATH']."page";

$writeable = array ($CJO['ADDON_PATH'],
					$CJO['FRONTPAGE_PATH'],
					$CJO['FOLDER_GENERATED_ARTICLES'],
					$CJO['FOLDER_GENERATED_TEMPLATES'],
					$CJO['MEDIAFOLDER'],
					$CJO['CACHEFOLDER'],
					$CJO['UPLOADFOLDER'],
					$CJO['TEMPFOLDER'],
					$CJO['FRONTPAGE_PATH']."/tmpl",
					$CJO['FRONTPAGE_PATH']."/tmpl/html",
					$CJO['FRONTPAGE_PATH']."/tmpl/modules",
					$CJO['FRONTPAGE_PATH']."/tmpl/templates"
					);

$errors = '';
foreach ($writeable as $item) {

    if ($item != '' && !cjoFile::isWritable($item)) {
        cjoMessage::removeLastError();
        $errors .= "<br/>".$item;
        continue;
    }
}

if ($errors != '') {
    cjoMessage::addError(cjoI18N::translate("msg_setup_step3_err_perm",$errors));
    $icons['perm'] = 'exclamation';
}

//Form
$form = new cjoForm();
$form->setEditMode(false);
$form->debug = false;

//Hidden Fields
$hidden['prev_subpage'] = new hiddenField('prev_subpage');
$hidden['prev_subpage']->setValue('step2');

$hidden['lang'] = new hiddenField('lang');
$hidden['lang']->setValue($lang);

$fields['headline1'] = new headlineField(cjoI18N::translate("label_php_version"));

$fields['info'] = new readOnlyField('info', '', array('style'=>'margin-left: 200px;'));
$fields['info']->setContainer('div');
$fields['info']->setValue(cjoI18N::translate("msg_setup_step3_info",
    					  			 '<img src="img/silk_icons/'.$icons['php'].'.png" alt="OK" />',
									 phpversion(),
									 '<img src="img/silk_icons/'.$icons['gd'].'.png" alt="OK" />',
                                     '<img src="img/silk_icons/'.$icons['copy'].'.png" alt="OK" />',
                                     '<img src="img/silk_icons/'.$icons['perm'].'.png" alt="OK" />',
    					  			 '<img src="img/silk_icons/'.$icons['addons'].'.png" alt="OK" />'));

$fields['test_again'] = new readOnlyField('test_again', '', array('style'=>'margin-left: 200px;'));
$fields['test_again']->setContainer('div');
$fields['test_again']->setValue('<input type="button" value="'.cjoI18N::translate("label_test_again").'" onclick="window.location.reload()" />');


$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_back_button',cjoI18N::translate("button_back"), true, 'img/silk_icons/control_play_backwards.png');
$fields['button']->addButton('cjoform_next_button',cjoI18N::translate("button_next_step4"), true, 'img/silk_icons/control_play.png');
$fields['button']->setButtonAttributes('cjoform_next_button', ' style="color: green"');

if (cjoMessage::hasErrors()){
	$fields['button']->setButtonAttributes('cjoform_next_button', ' disabled="disabled"');
}
//Add Fields:
$section = new cjoFormSection('', cjoI18N::translate("label_setup_".$subpage."_title"), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
    cjoUrl::redirectBE(array('subpage' => 'step4', 'lang' => $lang));
}
$form->show(false);