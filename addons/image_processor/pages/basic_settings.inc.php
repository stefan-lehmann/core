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

// if (cjo_post('empty_cache_button', 'boolean')) {
// 
	// $ip_cachedir = str_replace('//','/',$CJO['CACHEFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['cachedir'].'/');
	// $garbagecollector = new garbagecollector($ip_cachedir, 0);
// 
	// if ($garbagecollector->tidy() > 0){
		// cjoMessage::addSuccess(cjoAddon::translate(8,"msg_cache_deleted")); // "Cache geleert.";
	// }
	// else{
		// cjoMessage::addError(cjoAddon::translate(8,"msg_cache_not_deleted")); // "Nichts gelöscht, war evtl. leer oder Schreibrechte stimmen nicht.";
	// }
	// $_POST = array();
// }
// if (cjo_post('reset_to_defaults_button', 'boolean')) {
// 
	// $from = $CJO['ADDON_PATH'].'/'.$mypage.'/settings.inc.bak';
	// $to = $CJO['ADDON']['settings'][$mypage]['SETTINGS'];
// 
	// if (!@copy($from,$to)){
		// cjoMessage::addError(cjoI18N::translate('msg_addon_bak_does_not_exist',
		                     // cjoFile::absPath($from),
		                     // cjoFile::absPath($to)));
	// }
	// else {
		// @chmod($to, cjoProp::getFilePerm());
		// //[translate: msg_reset_to_defaults]
		// cjoUrl::redirectBE(array('msg'=>'msg_reset_to_defaults'));
	// }
	// $_POST = array();
// }
/*
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
*/
$form = new cjoForm();
$form->setEditMode(false);

$fields['error_img'] = new cjoMediaButtonField('error_img', cjoAddon::translate(8,'label_error_img'), array('preview' => array('enabled' => true,'height'=> 100)));
$fields['error_img']->needFullColumn(true);

$fields['cachesize'] = new textField('cachesize', cjoAddon::translate(8,"label_cachesize"));
$fields['cachesize']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_cachesize'));
$fields['cachesize']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_cachesize'), array('low' => '1', 'high' => 1000));
$fields['cachesize']->addAttribute('style', 'width: 50px');
$fields['cachesize']->setNote('MB');

$fields['cachedir'] = new textField('cachedir', cjoAddon::translate(8,'label_cachedir'));
$fields['cachedir']->addValidator('notEmpty', cjoAddon::translate(8,"msg_not_empty_cachedir"));

// Resize ----------------------------------------------------------------------------------------------------------------------------

$fields['headline2'] = new headlineField(cjoAddon::translate(8,"label_resize_settings"), true);

$fields['default_resize'] = new checkboxField('default[resize]', '&nbsp;', array('style' => 'width: auto;'));
$fields['default_resize']->setUncheckedValue();
$fields['default_resize']->addBox(cjoAddon::translate(8,'label_default_resize'), '1');

$fields['default_aspectratio'] = new checkboxField('default[aspectratio]', '&nbsp;',  array('style' => 'width: auto;'));
$fields['default_aspectratio']->setUncheckedValue();
$fields['default_aspectratio']->addBox(cjoAddon::translate(8,'label_default_aspectratio'), '1');

$fields['default_jpg_quality'] = new textField('default[jpg-quality]', cjoAddon::translate(8,"label_jpg_quality"));
$fields['default_jpg_quality']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_default_jpg_quality'));
$fields['default_jpg_quality']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_default_jpg_quality'), array('low' => '1', 'high' => 100));
$fields['default_jpg_quality']->addAttribute('style', 'width: 40px');
$fields['default_jpg_quality']->setNote('%');

$fields['override_jpg_quality'] = new checkboxField('allowoverride[jpg-quality]', '&nbsp;',  array('style' => 'width: auto;'));
$fields['override_jpg_quality']->setUncheckedValue();
$fields['override_jpg_quality']->addBox(cjoAddon::translate(8,'label_override_jpg_quality'), '1');

// Shadow  ----------------------------------------------------------------------------------------------------------------------------

$fields['headline2a'] = new headlineField(cjoAddon::translate(8,"label_shadow_settings"), true);

$fields['shadow_crop_nums'] = new selectField('shadow[shadow_crop_nums]', cjoAddon::translate(8,"label_shadow_crop_nums"));
$fields['shadow_crop_nums']->setMultiple();
$fields['shadow_crop_nums']->setValueSeparator('|');
$fields['shadow_crop_nums']->addSqlOptions("SELECT CONCAT(name,' (ID=',id,')') AS name, id FROM ".TBL_IMG_CROP." WHERE status!='0' ORDER BY id");
$fields['shadow_crop_nums']->addAttribute('size', '5');

$fields['shadow_angle'] = new textField('shadow[shadow_angle]', cjoAddon::translate(8,'label_shadow_angle'));
$fields['shadow_angle']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_shadow_angle'), array('low' => '0', 'high' => '360'));
$fields['shadow_angle']->addAttribute('style', 'width: 40px');
$fields['shadow_angle']->setNote('°');

$fields['shadow_distance'] = new textField('shadow[shadow_distance]', cjoAddon::translate(8,'label_shadow_distance'));
$fields['shadow_distance']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_shadow_distance'), array('low' => '0', 'high' => '100'));
$fields['shadow_distance']->addAttribute('style', 'width: 40px');
$fields['shadow_distance']->setNote('Pixel');

$fields['shadow_size'] = new textField('shadow[shadow_size]', cjoAddon::translate(8,'label_shadow_size'));
$fields['shadow_size']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_shadow_size'), array('low' => '0', 'high' => '100'));
$fields['shadow_size']->addAttribute('style', 'width: 40px');
$fields['shadow_size']->setNote('Pixel');

$fields['samples'] = new selectField('shadow[samples]', cjoAddon::translate(8,"label_shadow_samples"));
$fields['samples']->addOption(cjoAddon::translate(8,'label_best'),30);
$fields['samples']->addOption(cjoAddon::translate(8,'label_better'), 20);
$fields['samples']->addOption(cjoAddon::translate(8,'label_good'), 15);
$fields['samples']->addOption(cjoAddon::translate(8,'label_bad'), 10);
$fields['samples']->addOption(cjoAddon::translate(8,'label_worst'), 5);
$fields['samples']->setDefault(15);
$fields['samples']->addAttribute('size', '1');
$fields['samples']->addAttribute('style', 'width: 118px');

$fields['shadow_color'] = new colorpickerField('shadow[shadow_color]', cjoAddon::translate(8,'label_shadow_color'));
$fields['shadow_color']->addValidator('isColor', cjoAddon::translate(8,'msg_wrong_shadow_color'), true);
$fields['shadow_color']->addAttribute('style', 'width: 110px');

$fields['border_width'] = new textField('shadow[border_width]', cjoAddon::translate(8,'label_border_width'));
$fields['border_width']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_border_width'), array('low' => '0', 'high' => '100'));
$fields['border_width']->addAttribute('style', 'width: 40px');
$fields['border_width']->setNote('Pixel');

$fields['border_color'] = new colorpickerField('shadow[border_color]', cjoAddon::translate(8,'label_border_color'));
$fields['border_color']->addValidator('isColor', cjoAddon::translate(8,'msg_wrong_border_color'), true);
$fields['border_color']->addAttribute('style', 'width: 110px');

$fields['background_color'] = new colorpickerField('shadow[background_color]', cjoAddon::translate(8,'label_background_color'));
$fields['background_color']->addValidator('isColor', cjoAddon::translate(8,'msg_wrong_background_color'), true);
$fields['background_color']->addAttribute('style', 'width: 110px');

// Brand ----------------------------------------------------------------------------------------------------------------------------

$fields['headline3'] = new headlineField(cjoAddon::translate(8,"label_brand_settings"), true);

$fields['brand_default_brand_on_off'] = new checkboxField('brand[default][brand_on_off]', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_default_brand_on_off']->setUncheckedValue();
$fields['brand_default_brand_on_off']->addBox(cjoAddon::translate(8,'label_brand_default_brand_on_off'), '1');

$fields['brand_allowoverride_brand_on_off'] = new checkboxField('brand[allowoverride][brand_on_off]', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_allowoverride_brand_on_off']->setUncheckedValue();
$fields['brand_allowoverride_brand_on_off']->addBox(cjoAddon::translate(8,'label_brand_allowoverride_brand_on_off'), '1');

$fields['brand_default_brandimg'] = new cjoMediaButtonField('brand[default][brandimg]', cjoAddon::translate(8,'label_brandimg'), array('preview' => array('enabled' => 'auto','height'=> 50)));

$fields['brand_allowoverride_brandimg'] = new checkboxField('brand[allowoverride][brandimg]', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_allowoverride_brandimg']->setUncheckedValue();
$fields['brand_allowoverride_brandimg']->addBox(cjoAddon::translate(8,'label_brand_allowoverride_brandimg'), '1');

$fields['brand_size'] = new textField('brand[size]', cjoAddon::translate(8,'label_brand_size'));
$fields['brand_size']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_brand_size'));
$fields['brand_size']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_brand_size'), array('low' => '0', 'high' => 100));
$fields['brand_size']->addAttribute('style', 'width: 40px');
$fields['brand_size']->setNote(cjoAddon::translate(8,'note_brandimg_size'));

$fields['brand_x_margin'] = new textField('brand[x_margin]', cjoAddon::translate(8,'label_brand_x_margin'));
$fields['brand_x_margin']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_brand_x_margin'));
$fields['brand_x_margin']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_brand_x_margin'), array('low' => '0', 'high' => 100000));
$fields['brand_x_margin']->addAttribute('style', 'width: 40px');
$fields['brand_x_margin']->setNote('Pixel');

$fields['brand_y_margin'] = new textField('brand[y_margin]', cjoAddon::translate(8,'label_brand_y_margin'));
$fields['brand_y_margin']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_brand_y_margin'));
$fields['brand_y_margin']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_brand_y_margin'), array('low' => '-1', 'high' => 100000));
$fields['brand_y_margin']->addAttribute('style', 'width: 40px');
$fields['brand_y_margin']->setNote(cjoAddon::translate(8,'note_brand_y_margin'));

$fields['brand_orientation'] = new selectField('brand[orientation]', cjoAddon::translate(8,'label_brand_orientation'));
$fields['brand_orientation']->addAttribute('size', '1');
$fields['brand_orientation']->addOption('','');
$fields['brand_orientation']->addOption(cjoAddon::translate(8,'label_top_left'),'lt');
$fields['brand_orientation']->addOption(cjoAddon::translate(8,'label_top_right'),'rt');
$fields['brand_orientation']->addOption(cjoAddon::translate(8,'label_bottom_right'),'rb');
$fields['brand_orientation']->addOption(cjoAddon::translate(8,'label_bottom_left'),'lb');
$fields['brand_orientation']->addValidator('notEmpty', cjoAddon::translate(8,'msg_empty_brand_orientation'));

$fields['brand_limit'] = new textField('brand[limit]', cjoAddon::translate(8,'label_brand_limit'));
$fields['brand_limit']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_brand_limit'));
$fields['brand_limit']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_brand_limit'), array('low' => '1', 'high' => 100000));
$fields['brand_limit']->addAttribute('style', 'width: 40px');
$fields['brand_limit']->setNote('Pixel');

$fields['brand_resize'] = new checkboxField('brand[resize]', '&nbsp;',  array('style' => 'width: auto;'));
$fields['brand_resize']->setUncheckedValue();
$fields['brand_resize']->addBox(cjoAddon::translate(8,'label_brand_resize'), '1');

$fields['brand_opacity'] = new textField('brand[opacity]', cjoAddon::translate(8,'label_brand_opacity'));
$fields['brand_opacity']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_brand_opacity'));
$fields['brand_opacity']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_brand_opacity'), array('low' => '0', 'high' => 100));
$fields['brand_opacity']->addAttribute('style', 'width: 40px');
$fields['brand_opacity']->setNote('%');

// Resize Originals ----------------------------------------------------------------------------------------------------------------------------

$fields['headline4'] = new headlineField(cjoAddon::translate(8,"label_resize_originals_settings"), true);

$fields['res_orig_on_off'] = new checkboxField('res_orig[on_off]', '',  array('style' => 'width: auto; margin-left: 200px'));
$fields['res_orig_on_off']->setUncheckedValue();
$fields['res_orig_on_off']->addBox(cjoAddon::translate(8,'label_res_orig_on_off'), '1');
$fields['res_orig_on_off']->setNote(cjoAddon::translate(8,'note_resize_originals'), 'class="warning" style="position: relative; display:block; margin: 5px 0 0 200px!important"');

$fields['res_orig_size'] = new textField('res_orig[size]', cjoAddon::translate(8,'label_res_orig_size'));
$fields['res_orig_size']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_res_orig_size'));
$fields['res_orig_size']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_res_orig_size'), array('low' => '100', 'high' => '5000'));
$fields['res_orig_size']->addAttribute('style', 'width: 40px');
$fields['res_orig_size']->setNote('Pixel');

$fields['res_orig_jpg_quality'] = new textField('res_orig[jpg-quality]', cjoAddon::translate(8,'label_jpg_quality'));
$fields['res_orig_jpg_quality']->addValidator('notEmpty', cjoAddon::translate(8,'msg_wrong_res_orig_res_orig_jpg'));
$fields['res_orig_jpg_quality']->addValidator('isRange', cjoAddon::translate(8,'msg_wrong_res_orig_res_orig_jpg'), array('low' => '50', 'high' => '100'));
$fields['res_orig_jpg_quality']->addAttribute('style', 'width: 40px');
$fields['res_orig_jpg_quality']->setNote('%');

$fields['headline5'] = new headlineField(cjoAddon::translate(8,"label_help"), true);

$fields['help1'] = new readOnlyField('help1', '', array('style' => 'margin: 0 10px;'));
$fields['help1']->setContainer('div');
$fields['help1']->setValue(cjoAddon::translate(8,"text_help1").'<br/><br/>'.cjoAddon::translate(8,"text_help2"));

$fields['help2'] = new readOnlyField('help2', '', array('style' => 'margin: 0 10px;'));
$fields['help2']->setContainer('div');
$fields['help2']->setValue(cjoAddon::translate(8,"text_help3").'<br/><br/>'.cjoAddon::translate(8,"text_resize").'<br/><br/>'.cjoAddon::translate(8,"text_aspectratio").'<br/><br/>'.cjoAddon::translate(8,"text_jpg_quality").'<br/><br/>'.cjoAddon::translate(8,"text_brand_on_off").'<br/><br/>'.cjoAddon::translate(8,"text_brandimg"));

$fields['buttons'] = new buttonField(array('style'=>'float:right; padding-right:10px;'));
$fields['buttons']->addButton('cjoform_update_button',cjoI18N::translate("button_update"), true, 'img/silk_icons/tick.png');
$fields['buttons']->setButtonAttributes('cjoform_update_button', 'style="position: absolute; left:200px;"');
$fields['buttons']->addButton('empty_cache_button',cjoAddon::translate(8,"button_empty_cache"), true, 'img/silk_icons/bin.png');
$fields['buttons']->setButtonAttributes('empty_cache_button', 'class="cjo_button_delete"');
$fields['buttons']->addButton('reset_to_defaults_button',cjoAddon::translate(8,"reset_to_defaults_button"), true, 'img/silk_icons/arrow_refresh.png');
$fields['buttons']->setButtonAttributes('reset_to_defaults_button', 'class="cjo_button_delete"');

$section = new cjoFormSection($addon);
$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
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