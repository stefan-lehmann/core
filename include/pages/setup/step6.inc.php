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
 * @version     2.6.0
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
$form->setEditMode(false);
$form->debug = false;

//Hidden Fields
$hidden['prev_subpage'] = new hiddenField('prev_subpage');
$hidden['prev_subpage']->setValue('step5');

$hidden['lang'] = new hiddenField('lang');
$hidden['lang']->setValue($lang);

$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline'));
$fields['headline1']->setValue($I18N->msg("label_setup_mysql_db"));

$fields['import'] = new selectField('import', $I18N->msg("label_import_mode"));
$fields['import']->addValidator('notEmpty', $I18N->msg("msg_import_notEmpty"));
$fields['import']->addAttribute('size', '7', false);

$fields['import']->addOption($I18N->msg("label_contejo_import_db_without_drop"), 1);
$fields['import']->addOption($I18N->msg("label_contejo_import_db_with_drop"), 2);
$fields['import']->addOption($I18N->msg("label_update_from_contejo",'2.3', $CJO['VERSION']), 3);
$fields['import']->addOption($I18N->msg("label_update_from_contejo",'2.0', $CJO['VERSION']), 6);
$fields['import']->addOption($I18N->msg("label_update_from_contejo",'1.1', $CJO['VERSION']), 7);

$fields['import_name'] = new selectField('import_name', $I18N->msg("label_exports"));

$fields['import_name']->addAttribute('disabled', 'disabled');

$im_export_dir = $CJO['ADDON']['settings']['import_export']['folder'];
$exports_found = false;
$show_export_option = false;

if (is_dir($im_export_dir)){
	if ($handle = opendir($im_export_dir)) {
		$export_tars = array ();
		$export_sqls = array ();

		while (($file = readdir($handle)) !== false){
			if ($file == '.' || $file == '..' || $file == '.svn') continue;

			$isSql = (substr($file, strlen($file) - 4) == '.sql');
			$isTar = (substr($file, strlen($file) - 7) == '.tar.gz');

			if ($isSql) {
				$export_sqls[] = substr($file, 0, -4);
				$exports_found = true;
			}
			elseif ($isTar){
				$export_tars[] = substr($file, 0, -7);
				$exports_found = true;
			}
		}
		closedir($handle);
	}

	foreach ($export_sqls as $sql_export){
		// Es ist ein Export Archiv + SQL File vorhanden
		$note = (!in_array($sql_export, $export_tars))
		? '&nbsp;&nbsp; '.$I18N->msg("label_only_sql") : '';

		$fields['import_name']->addOption($sql_export.$note, $sql_export);
		$show_export_option = true;
	}
}
$fields['import_name']->addAttribute('size', count($export_sqls)+1);

if ($show_export_option)
	$fields['import']->addOption($I18N->msg("label_contejo_import_im_export_db"), 5);

$fields['import']->addOption($I18N->msg("label_contejo_import_no_db"), 0);

$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_back_button',$I18N->msg("button_back"), true, 'img/silk_icons/control_play_backwards.png');
$fields['button']->addButton('cjoform_next_button',$I18N->msg("button_next_step7"), true, 'img/silk_icons/control_play.png');
$fields['button']->setButtonAttributes('cjoform_next_button', ' style="color: green"');

//Add Fields:
$section = new cjoFormSection('', $I18N->msg("label_setup_".$subpage."_title"), array ());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {

	$import = cjo_post('import', 'int');
	$import_name = cjo_post('import_name','string');

    switch ($import) {

        case 1: cjoSetupImport($CJO['INCLUDE_PATH'].'/install/cjo_'.$CJO['VERSION'].'_without_drop.sql');
                break;
        case 2: cjoSetupImport($CJO['INCLUDE_PATH'].'/install/cjo_'.$CJO['VERSION'].'_with_drop.sql');
                break;
        case 3: cjoSetupImport($CJO['INCLUDE_PATH'].'/install/cjo_2.3_to_contejo'.$CJO['VERSION'].'.sql');
                break;
        case 7: cjoSetupImport($CJO['INCLUDE_PATH'].'/install/cjo_1.1_to_contejo'.$CJO['VERSION'].'.sql');
        		break;
        case 6: cjoSetupImport($CJO['INCLUDE_PATH'].'/install/cjo_2.0_to_contejo'.$CJO['VERSION'].'.sql');
        		break;
        case 4: if ($import_name) cjoMessage::addError($I18N->msg('msg_no_export_selected'));
        		break;
        case 5:
			$import_sql = $CJO['ADDON']['settings']['import_export']['folder'].'/'.$import_name.'.sql';
			//$import_tar = $CJO['ADDON']['settings']['import_export']['folder'].'/'.$import_name.'.tar.gz';
			$import_tar = null;
			cjoSetupImport($import_sql, $import_tar);
			
	}

	if (!cjoMessage::hasErrors()) {
    	// Benötigte Tabellen
    	$check_tables = array (TBL_ACTIONS => 0,
    						   TBL_ARTICLES => 0,
    						   TBL_ARTICLES_CAT_GROUPS => 0,
    						   TBL_ARTICLES_SLICE => 0,
    						   TBL_ARTICLES_TYPE => 0,
    						   TBL_CLANGS => 0,
    						   TBL_FILES => 0,
    						   TBL_FILE_CATEGORIES => 0,
    						   TBL_MODULES_ACTIONS => 0,
    						   TBL_MODULES => 0,
    						   TBL_TEMPLATES => 0,
    						   TBL_USER => 0);
    
    	// Prüfen, welche Tabellen bereits vorhanden sind
    	$sql = new cjoSql();
    	$db_tables = $sql->getArray("SHOW TABLES");
    
    	foreach (cjoAssistance::toArray($db_tables) as $tablename_array) {
    
    		$tablename = array_shift($tablename_array);
    		if (array_key_exists($tablename, $check_tables)){
    			$check_tables[$tablename] = 1;
    		}
    	}
    
    	foreach ($check_tables as $tablename=>$status) {
    		if ($status != 1){
    			cjoMessage::addError($I18N->msg("msg_table_not_found",$tablename));
    		}
    	}
	}
	if (!cjoMessage::hasErrors()) {
	    cjoAssistance::redirectBE(array('subpage' => 'step7', 'lang' => $lang));
	}
}

$form->show(false);

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){
		var im = $('select[name="import"]');
		var na = $('select[name="import_name"]');

		im.click(function(){
			if (!im.find('option[value=5]').is(':selected')){
				if(!na.is(':disabled')){
					na.find('option').removeAttr('selected');
					na.attr('disabled','disabled');
				}
			}
			else if (na.find('option:first').text() != ''){
				na.find('option:eq(0)').attr('selected','selected');
				na.removeAttr('disabled');
				setTimeout(function () { na.focus() }, 50);
			}

			$('#cjoform_next_button').unbind('click');

			if (im.find('option[value=5]').is(':selected') ||
				im.find('option[value=2]').is(':selected')) {
				$('#cjoform_next_button').click(function(){
					cjo.jconfirm('<?php echo $I18N->msg("msg_confirm_overwrite_db"); ?>', 'cjo.submitForm', [$(this)]);
					return false;
				});
			}
		});
	});

/* ]]> */
</script>