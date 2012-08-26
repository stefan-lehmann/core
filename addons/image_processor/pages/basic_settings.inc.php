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

if (cjo_post('empty_cache_button', 'boolean')) {

	require_once $CJO['ADDON_PATH'].'/'.$mypage.'/classes/class.garbagecollector.inc.php';
	$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');
	$garbagecollector = new garbagecollector($ip_cachedir, 0);

	if ($garbagecollector->tidy() > 0){
		cjoMessage::addSuccess($I18N_8->msg("msg_cache_deleted")); // "Cache geleert.";
	}
	else{
		cjoMessage::addError($I18N_8->msg("msg_cache_not_deleted")); // "Nichts gelöscht, war evtl. leer oder Schreibrechte stimmen nicht.";
	}
	$_POST = array();
}
if (cjo_post('reset_to_defaults_button', 'boolean')) {

	$from = $CJO['ADDON_PATH'].'/'.$mypage.'/settings.inc.bak';
	$to = $CJO['ADDON']['settings'][$mypage]['SETTINGS'];

	if (!@copy($from,$to)){
		cjoMessage::addError($I18N->msg('msg_addon_bak_does_not_exist',
		                     cjoAssistance::absPath($from),
		                     cjoAssistance::absPath($to)));
	}
	else {
		@chmod($to, $CJO['FILEPERM']);
		//[translate: msg_reset_to_defaults]
		cjoAssistance::redirectBE(array('msg'=>'msg_reset_to_defaults'));
	}
	$_POST = array();
}

$dataset['cachesize'] 						 = $CJO['ADDON']['settings'][$mypage]['cachesize'];
$dataset['cachedir'] 						 = str_replace($CJO['CACHEFOLDER'].'/','',$CJO['ADDON']['settings'][$mypage]['cachedir']);
$dataset['error_img'] 						 = $CJO['ADDON']['settings'][$mypage]['error_img'];
$dataset['default_resize'] 					 = $CJO['ADDON']['settings'][$mypage]['default']['resize'];
$dataset['default_aspectratio'] 			 = $CJO['ADDON']['settings'][$mypage]['default']['aspectratio'];
$dataset['default_jpg-quality'] 			 = $CJO['ADDON']['settings'][$mypage]['default']['jpg-quality'];
$dataset['allowoverride_jpg-quality'] 		 = $CJO['ADDON']['settings'][$mypage]['allowoverride']['jpg-quality'];
$dataset['shadow_crop_nums'] 		 		 = $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_crop_nums'];
$dataset['shadow_angle'] 		 	 		 = $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_angle'];
$dataset['shadow_size'] 		 			 = $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_size'];
$dataset['shadow_distance'] 		 		 = $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_distance'];
$dataset['shadow_color'] 		 			 = $CJO['ADDON']['settings'][$mypage]['shadow']['shadow_color'];
$dataset['samples'] 		 			 	 = $CJO['ADDON']['settings'][$mypage]['shadow']['samples'];
$dataset['border_width'] 		 		 	 = $CJO['ADDON']['settings'][$mypage]['shadow']['border_width'];
$dataset['border_color'] 		 			 = $CJO['ADDON']['settings'][$mypage]['shadow']['border_color'];
$dataset['background_color'] 		 		 = $CJO['ADDON']['settings'][$mypage]['shadow']['background_color'];
$dataset['brand_default_brand_on_off'] 		 = $CJO['ADDON']['settings'][$mypage]['brand']['default']['brand_on_off'];
$dataset['brand_default_brandimg'] 			 = $CJO['ADDON']['settings'][$mypage]['brand']['default']['brandimg'];
$dataset['brand_allowoverride_brand_on_off'] = $CJO['ADDON']['settings'][$mypage]['brand']['allowoverride']['brand_on_off'];
$dataset['brand_allowoverride_brandimg'] 	 = $CJO['ADDON']['settings'][$mypage]['brand']['allowoverride']['brandimg'];
$dataset['brand_size'] 						 = $CJO['ADDON']['settings'][$mypage]['brand']['size'];
$dataset['brand_x_margin'] 					 = $CJO['ADDON']['settings'][$mypage]['brand']['x_margin'];
$dataset['brand_y_margin'] 					 = $CJO['ADDON']['settings'][$mypage]['brand']['y_margin'];
$dataset['brand_orientation'] 				 = $CJO['ADDON']['settings'][$mypage]['brand']['orientation'];
$dataset['brand_limit'] 					 = $CJO['ADDON']['settings'][$mypage]['brand']['limit'];
$dataset['brand_resize'] 					 = $CJO['ADDON']['settings'][$mypage]['brand']['resize'];
$dataset['brand_opacity'] 					 = $CJO['ADDON']['settings'][$mypage]['brand']['opacity'];
$dataset['res_orig_on_off'] 				 = $CJO['ADDON']['settings'][$mypage]['res_orig']['on_off'];
$dataset['res_orig_size'] 					 = $CJO['ADDON']['settings'][$mypage]['res_orig']['size'];
$dataset['res_orig_jpg-quality'] 			 = $CJO['ADDON']['settings'][$mypage]['res_orig']['jpg-quality'];

$error_img = $dataset['error_img'] ;
$brand_img = $dataset['brand_default_brandimg'] ;

//Form
$form = new cjoForm();
$form->setEditMode(false);
//$form->debug = true;

// Cache ----------------------------------------------------------------------------------------------------------------------------

$fields['error_img'] = new cjoMediaButtonField('error_img', $I18N_8->msg('label_error_img'), array('preview' => array('enabled' => true,'height'=> 100)));
$fields['error_img']->needFullColumn(true);

$fields['cachesize'] = new textField('cachesize', $I18N_8->msg("label_cachesize"));
$fields['cachesize']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_cachesize'));
$fields['cachesize']->addValidator('isRange', $I18N_8->msg('msg_wrong_cachesize'), array('low' => '1', 'high' => 1000));
$fields['cachesize']->addAttribute('style', 'width: 50px');
$fields['cachesize']->setNote('MB');

$fields['cachedir'] = new textField('cachedir', $I18N_8->msg('label_cachedir'));
$fields['cachedir']->addValidator('notEmpty', $I18N_8->msg("msg_not_empty_cachedir"));

// Resize ----------------------------------------------------------------------------------------------------------------------------

$fields['headline2'] = new readOnlyField('headline2', '', array('class' => 'formheadline slide'));
$fields['headline2']->setValue($I18N_8->msg("label_resize_settings"));

$fields['default_resize'] = new checkboxField('default_resize', '&nbsp;', array('style' => 'width: auto;'));
$fields['default_resize']->setUncheckedValue();
$fields['default_resize']->addBox($I18N_8->msg('label_default_resize'), '1');

$fields['default_aspectratio'] = new checkboxField('default_aspectratio', '&nbsp;',  array('style' => 'width: auto;'));
$fields['default_aspectratio']->setUncheckedValue();
$fields['default_aspectratio']->addBox($I18N_8->msg('label_default_aspectratio'), '1');

$fields['default_jpg_quality'] = new textField('default_jpg-quality', $I18N_8->msg("label_jpg_quality"));
$fields['default_jpg_quality']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_default_jpg_quality'));
$fields['default_jpg_quality']->addValidator('isRange', $I18N_8->msg('msg_wrong_default_jpg_quality'), array('low' => '1', 'high' => 100));
$fields['default_jpg_quality']->addAttribute('style', 'width: 40px');
$fields['default_jpg_quality']->setNote('%');

$fields['override_jpg_quality'] = new checkboxField('allowoverride_jpg-quality', '&nbsp;',  array('style' => 'width: auto;'));
$fields['override_jpg_quality']->setUncheckedValue();
$fields['override_jpg_quality']->addBox($I18N_8->msg('label_override_jpg_quality'), '1');

// Shadow  ----------------------------------------------------------------------------------------------------------------------------

$fields['headline2a'] = new readOnlyField('headline2a', '', array('class' => 'formheadline slide'));
$fields['headline2a']->setValue($I18N_8->msg("label_shadow_settings"));

$fields['shadow_crop_nums'] = new selectField('shadow_crop_nums', $I18N_8->msg("label_shadow_crop_nums"));
$fields['shadow_crop_nums']->setMultiple();
$fields['shadow_crop_nums']->setValueSeparator('|');
$fields['shadow_crop_nums']->addSqlOptions("SELECT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_IMG_CROP." WHERE status!='0' ORDER BY id");
$fields['shadow_crop_nums']->addAttribute('size', '5');

$fields['shadow_angle'] = new textField('shadow_angle', $I18N_8->msg('label_shadow_angle'));
$fields['shadow_angle']->addValidator('isRange', $I18N_8->msg('msg_wrong_shadow_angle'), array('low' => '0', 'high' => '360'));
$fields['shadow_angle']->addAttribute('style', 'width: 40px');
$fields['shadow_angle']->setNote('°');

$fields['shadow_distance'] = new textField('shadow_distance', $I18N_8->msg('label_shadow_distance'));
$fields['shadow_distance']->addValidator('isRange', $I18N_8->msg('msg_wrong_shadow_distance'), array('low' => '0', 'high' => '100'));
$fields['shadow_distance']->addAttribute('style', 'width: 40px');
$fields['shadow_distance']->setNote('Pixel');

$fields['shadow_size'] = new textField('shadow_size', $I18N_8->msg('label_shadow_size'));
$fields['shadow_size']->addValidator('isRange', $I18N_8->msg('msg_wrong_shadow_size'), array('low' => '0', 'high' => '100'));
$fields['shadow_size']->addAttribute('style', 'width: 40px');
$fields['shadow_size']->setNote('Pixel');

$fields['samples'] = new selectField('samples', $I18N_8->msg("label_shadow_samples"));
$fields['samples']->addOption($I18N_8->msg('label_best'),30);
$fields['samples']->addOption($I18N_8->msg('label_better'), 20);
$fields['samples']->addOption($I18N_8->msg('label_good'), 15);
$fields['samples']->addOption($I18N_8->msg('label_bad'), 10);
$fields['samples']->addOption($I18N_8->msg('label_worst'), 5);
$fields['samples']->setDefault(15);
$fields['samples']->addAttribute('size', '1');
$fields['samples']->addAttribute('style', 'width: 118px');

$fields['shadow_color'] = new colorpickerField('shadow_color', $I18N_8->msg('label_shadow_color'));
$fields['shadow_color']->addValidator('isColor', $I18N_8->msg('msg_wrong_shadow_color'), true);
$fields['shadow_color']->addAttribute('style', 'width: 110px');

$fields['border_width'] = new textField('border_width', $I18N_8->msg('label_border_width'));
$fields['border_width']->addValidator('isRange', $I18N_8->msg('msg_wrong_border_width'), array('low' => '0', 'high' => '100'));
$fields['border_width']->addAttribute('style', 'width: 40px');
$fields['border_width']->setNote('Pixel');

$fields['border_color'] = new colorpickerField('border_color', $I18N_8->msg('label_border_color'));
$fields['border_color']->addValidator('isColor', $I18N_8->msg('msg_wrong_border_color'), true);
$fields['border_color']->addAttribute('style', 'width: 110px');

$fields['background_color'] = new colorpickerField('background_color', $I18N_8->msg('label_background_color'));
$fields['background_color']->addValidator('isColor', $I18N_8->msg('msg_wrong_background_color'), true);
$fields['background_color']->addAttribute('style', 'width: 110px');

// Brand ----------------------------------------------------------------------------------------------------------------------------

$fields['headline3'] = new readOnlyField('headline3', '', array('class' => 'formheadline slide'));
$fields['headline3']->setValue($I18N_8->msg("label_brand_settings"));

//$fields['brand_default_brand_on_off_hidden'] = new hiddenField('brand_default_brand_on_off');
//$fields['brand_default_brand_on_off_hidden']->setValue('0');

$fields['brand_default_brand_on_off'] = new checkboxField('brand_default_brand_on_off', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_default_brand_on_off']->setUncheckedValue();
$fields['brand_default_brand_on_off']->addBox($I18N_8->msg('label_brand_default_brand_on_off'), '1');

$fields['brand_allowoverride_brand_on_off'] = new checkboxField('brand_allowoverride_brand_on_off', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_allowoverride_brand_on_off']->setUncheckedValue();
$fields['brand_allowoverride_brand_on_off']->addBox($I18N_8->msg('label_brand_allowoverride_brand_on_off'), '1');

$fields['brand_default_brandimg'] = new cjoMediaButtonField('brand_default_brandimg', $I18N_8->msg('label_brandimg'));
if (file_exists($CJO['MEDIAFOLDER'].'/'.$brand_img))
    $fields['brand_default_brandimg']->setNote(OOMedia::toThumbnail($brand_img, $CJO['MEDIAFOLDER'].'/'.$brand_img, $params = array ('width' => 80, 'height' => 50)));

$fields['brand_allowoverride_brandimg'] = new checkboxField('brand_allowoverride_brandimg', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_allowoverride_brandimg']->setUncheckedValue();
$fields['brand_allowoverride_brandimg']->addBox($I18N_8->msg('label_brand_allowoverride_brandimg'), '1');

$fields['brand_size'] = new textField('brand_size', $I18N_8->msg('label_brand_size'));
$fields['brand_size']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_brand_size'));
$fields['brand_size']->addValidator('isRange', $I18N_8->msg('msg_wrong_brand_size'), array('low' => '0', 'high' => 100));
$fields['brand_size']->addAttribute('style', 'width: 40px');
$fields['brand_size']->setNote($I18N_8->msg('note_brandimg_size'));

$fields['brand_x_margin'] = new textField('brand_x_margin', $I18N_8->msg('label_brand_x_margin'));
$fields['brand_x_margin']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_brand_x_margin'));
$fields['brand_x_margin']->addValidator('isRange', $I18N_8->msg('msg_wrong_brand_x_margin'), array('low' => '0', 'high' => 100000));
$fields['brand_x_margin']->addAttribute('style', 'width: 40px');
$fields['brand_x_margin']->setNote('Pixel');

$fields['brand_y_margin'] = new textField('brand_y_margin', $I18N_8->msg('label_brand_y_margin'));
$fields['brand_y_margin']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_brand_y_margin'));
$fields['brand_y_margin']->addValidator('isRange', $I18N_8->msg('msg_wrong_brand_y_margin'), array('low' => '-1', 'high' => 100000));
$fields['brand_y_margin']->addAttribute('style', 'width: 40px');
$fields['brand_y_margin']->setNote($I18N_8->msg('note_brand_y_margin'));

$fields['brand_orientation'] = new selectField('brand_orientation', $I18N_8->msg('label_brand_orientation'));
$fields['brand_orientation']->addAttribute('size', '1');
$fields['brand_orientation']->addOption('','');
$fields['brand_orientation']->addOption($I18N_8->msg('label_top_left'),'lt');
$fields['brand_orientation']->addOption($I18N_8->msg('label_top_right'),'rt');
$fields['brand_orientation']->addOption($I18N_8->msg('label_bottom_right'),'rb');
$fields['brand_orientation']->addOption($I18N_8->msg('label_bottom_left'),'lb');
$fields['brand_orientation']->addValidator('notEmpty', $I18N_8->msg('msg_empty_brand_orientation'));

$fields['brand_limit'] = new textField('brand_limit', $I18N_8->msg('label_brand_limit'));
$fields['brand_limit']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_brand_limit'));
$fields['brand_limit']->addValidator('isRange', $I18N_8->msg('msg_wrong_brand_limit'), array('low' => '1', 'high' => 100000));
$fields['brand_limit']->addAttribute('style', 'width: 40px');
$fields['brand_limit']->setNote('Pixel');

$fields['brand_resize'] = new checkboxField('brand_resize', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_resize']->setUncheckedValue();
$fields['brand_resize']->addBox($I18N_8->msg('label_brand_resize'), '1');

$fields['brand_opacity'] = new textField('brand_opacity', $I18N_8->msg('label_brand_opacity'));
$fields['brand_opacity']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_brand_opacity'));
$fields['brand_opacity']->addValidator('isRange', $I18N_8->msg('msg_wrong_brand_opacity'), array('low' => '0', 'high' => 100));
$fields['brand_opacity']->addAttribute('style', 'width: 40px');
$fields['brand_opacity']->setNote('%');

// Resize Originals ----------------------------------------------------------------------------------------------------------------------------

$fields['headline4'] = new readOnlyField('headline4', '', array('class' => 'formheadline slide'));
$fields['headline4']->setValue($I18N_8->msg("label_resize_originals_settings"));

$fields['res_orig_on_off'] = new checkboxField('res_orig_on_off', '',  array('style' => 'width: auto; margin-left: 200px'));
$fields['res_orig_on_off']->setUncheckedValue();
$fields['res_orig_on_off']->addBox($I18N_8->msg('label_res_orig_on_off'), '1');
$fields['res_orig_on_off']->setNote($I18N_8->msg('note_resize_originals'), 'class="warning" style="position: relative; display:block; margin: 5px 0 0 200px!important"');

$fields['res_orig_size'] = new textField('res_orig_size', $I18N_8->msg('label_res_orig_size'));
$fields['res_orig_size']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_res_orig_size'));
$fields['res_orig_size']->addValidator('isRange', $I18N_8->msg('msg_wrong_res_orig_size'), array('low' => '100', 'high' => '5000'));
$fields['res_orig_size']->addAttribute('style', 'width: 40px');
$fields['res_orig_size']->setNote('Pixel');

$fields['res_orig_jpg_quality'] = new textField('res_orig_jpg-quality', $I18N_8->msg('label_jpg_quality'));
$fields['res_orig_jpg_quality']->addValidator('notEmpty', $I18N_8->msg('msg_wrong_res_orig_res_orig_jpg'));
$fields['res_orig_jpg_quality']->addValidator('isRange', $I18N_8->msg('msg_wrong_res_orig_res_orig_jpg'), array('low' => '50', 'high' => '100'));
$fields['res_orig_jpg_quality']->addAttribute('style', 'width: 40px');
$fields['res_orig_jpg_quality']->setNote('%');

$fields['headline5'] = new readOnlyField('headline5', '', array('class' => 'formheadline slide'));
$fields['headline5']->setValue($I18N_8->msg("label_help"));

$fields['help1'] = new readOnlyField('help1', '', array('style' => 'margin: 0 10px;'));
$fields['help1']->setContainer('div');
$fields['help1']->setValue($I18N_8->msg("text_help1").'<br/><br/>'.$I18N_8->msg("text_help2"));

$fields['help2'] = new readOnlyField('help2', '', array('style' => 'margin: 0 10px;'));
$fields['help2']->setContainer('div');
$fields['help2']->setValue($I18N_8->msg("text_help3").'<br/><br/>'.$I18N_8->msg("text_resize").'<br/><br/>'.$I18N_8->msg("text_aspectratio").'<br/><br/>'.$I18N_8->msg("text_jpg_quality").'<br/><br/>'.$I18N_8->msg("text_brand_on_off").'<br/><br/>'.$I18N_8->msg("text_brandimg"));

$fields['buttons'] = new buttonField(array('style'=>'float:right; padding-right:10px;'));
$fields['buttons']->addButton('cjoform_update_button',$I18N->msg("button_update"), true, 'img/silk_icons/tick.png');
$fields['buttons']->setButtonAttributes('cjoform_update_button', 'style="position: absolute; left:200px;"');
$fields['buttons']->addButton('empty_cache_button',$I18N_8->msg("button_empty_cache"), true, 'img/silk_icons/bin.png');
$fields['buttons']->setButtonAttributes('empty_cache_button', 'class="cjo_button_delete"');
$fields['buttons']->addButton('reset_to_defaults_button',$I18N_8->msg("reset_to_defaults_button"), true, 'img/silk_icons/arrow_refresh.png');
$fields['buttons']->setButtonAttributes('reset_to_defaults_button', 'class="cjo_button_delete"');


//Add Fields
$section = new cjoFormSection($dataset, '', array());

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {

	$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');

	if (!cjoAssistance::isWritable($ip_cachedir)){
	    $error = cjoMessage::removeLastError();
		cjoMessage::addError($I18N->msg("msg_data_not_saved"));
		cjoMessage::addError($error);
		$form->valid_master = false;
	}
	else {
        $dataset = $_POST;
		$new_content =
			"// --- DYN"."\r\n\r\n".
			"// CACHE"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['cachesize'] = '".$dataset['cachesize']."'; //MB"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['cachedir'] = '/".preg_replace('/(^\/{1,})(.*)/','\2',$dataset['cachedir'])."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['error_img'] = '".$dataset['error_img']."'; //better not to change"."\r\n\r\n".
			"// RESIZE"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['default']['resize'] = '".$dataset['default_resize'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['default']['aspectratio'] = '".$dataset['default_aspectratio'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['default']['jpg-quality'] = '".$dataset['default_jpg-quality']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['allowoverride']['jpg-quality'] = '".$dataset['allowoverride_jpg-quality']."';"."\r\n\r\n".
			"//SHADOW"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['shadow_crop_nums'] = '".@implode('|',$dataset['shadow_crop_nums'])."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['shadow_angle'] = '".$dataset['shadow_angle'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['shadow_size'] = '".$dataset['shadow_size'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['shadow_distance'] = '".$dataset['shadow_distance'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['shadow_color'] = '".$dataset['shadow_color'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['background_color'] = '".$dataset['background_color']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['samples'] = '".$dataset['samples']."';"."\r\n\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['border_width'] = '".$dataset['border_width']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['shadow']['border_color'] = '".$dataset['border_color']."';"."\r\n\r\n".
			"// BRAND"."\r\n".
			"//Defaultwerte"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['default']['brand_on_off'] = '".$dataset['brand_default_brand_on_off'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['default']['brandimg'] = '".$dataset['brand_default_brandimg']."';"."\r\n\r\n".
			"//Settings"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['allowoverride']['brand_on_off'] = '".$dataset['brand_allowoverride_brand_on_off']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['allowoverride']['brandimg'] = '".$dataset['brand_allowoverride_brandimg']."';"."\r\n\r\n".
			"//Brandposition"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['size'] = '".$dataset['brand_size']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['x_margin'] = '".$dataset['brand_x_margin']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['y_margin'] = '".$dataset['brand_y_margin']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['orientation'] = '".$dataset['brand_orientation']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['limit'] = '".$dataset['brand_limit']."'; //Brand muss min x Pixel breit oder hoch sein"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['resize'] = '".$dataset['brand_resize']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['brand']['opacity'] = '".$dataset['brand_opacity']."';"."\r\n\r\n".
			"//Resize Originals"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['res_orig']['on_off'] = '".$dataset['res_orig_on_off'] ."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['res_orig']['size'] = '".$dataset['res_orig_size']."';"."\r\n".
			"\$CJO['ADDON']['settings'][\$mypage]['res_orig']['jpg-quality'] = '".$dataset['res_orig_jpg-quality']."';"."\r\n\r\n".
			"// --- /DYN";

		if (cjoGenerate::replaceFileContents($CJO['ADDON']['settings'][$mypage]['SETTINGS'], $new_content)){
    		$ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');
    		foreach (cjoAssistance::toArray(glob($ip_cachedir."*,s.{png,jpg,gif}",GLOB_BRACE)) as $filename) {
    			@ unlink($filename);
    		}
    		// generate all articles,cats,templates,caches
    	    cjoGenerate::generateAll();
		}
		else{
			$form->valid_master = false;
			cjoMessage::addError($I18N->msg("msg_data_not_saved"));
			cjoMessage::addError($I18N->msg("msg_file_no_chmod",
			                     cjoAssistance::absPath($CJO['ADDON']['settings'][$mypage]['SETTINGS'])));
		}
	}
}
$form->show(false);

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('#empty_cache_button, #reset_to_defaults_button').click(function(){
			cjo_jconfirm($(this), 'cjo.submitForm', [$(this)]);
			return false;
		});
	});

/* ]]> */
</script>