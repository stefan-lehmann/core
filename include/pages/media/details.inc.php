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

$media = OOMedia::getMediaById($oid);

if (!OOMedia::isValid($media)) {
	cjoAssistance::redirectBE(array('subpage'=>'media', 'media_category'=>$media_category));
}

$details =
'<span class="col_details">
	<span title="'.$I18N->msg('label_filename').'"><img src="img/mini_icons/page_white.png" alt="" /> '.$media->getFileName().'</span>
	<br/>
	<span title="'.$I18N->msg('label_filetype').'"><img src="img/mini_icons/flag_blue.png" alt="" /> '.$media->getType().'</span>
	<br/>
	<span title="'.$I18N->msg('label_filesize').'"><img src="img/mini_icons/drive.png" alt="" /> '.$media->_getFormattedSize().'</span>
	<br/>';

if ($media->getWidth() && $media->getHeight())
$details .= '<span title="'.$I18N->msg('label_size').'"><img src="img/mini_icons/shape_handles.png" alt="" /> '.$media->getWidth().' &times; '.$media->getHeight().' px</span><br/>';

$details .=
   '<span title="'.$I18N->msg('label_createdate').'"><img src="img/mini_icons/time.png" alt="" /> '.$media->getCreateDate($I18N->msg("datetimeformat")).'</span>
	<span title="'.$I18N->msg('label_createuser').'"><img src="img/mini_icons/user.png" alt="" /> '.$media->getCreateUser().'</span>
	<br/>
	<span title="'.$I18N->msg('label_updatedate').'"><img src="img/mini_icons/time.png" alt="" /> '.$media->getUpdateDate($I18N->msg("datetimeformat")).'</span>
	<span title="'.$I18N->msg('label_updateuser').'"><img src="img/mini_icons/user.png" alt="" /> '.$media->getUpdateUser().'</span>
</span>';


//Form
$form = new cjoForm();
$form->setEditMode(true);
$form->applyRedirectOnCancel(array('subpage'=>'media', 'media_category'=>$media_category, 'opener_input_field' => $opener_input_field));
//$form->debug = true;

$hidden['media_category'] = new hiddenField('media_category');
$hidden['media_category']->setValue($media_category);

$hidden['oid'] = new hiddenField('oid');
$hidden['oid']->setValue($oid);

$hidden['submit_type'] = new hiddenField('submit_type');
$hidden['submit_type']->setValue(1);

$icon = '<a href="'.$media->getFullPath().'" class="preview" title="'.$I18N->msg('label_preview').'">'.OOMedia::toIcon($media->getFileName()).'</a>';
$fields['icon'] = new readOnlyField('icon', '&nbsp;');
$fields['icon']->setValue($icon);
$fields['icon']->needFullColumn(true);
$fields['icon']->activateSave(false);

$fields['title'] = new textField('title', $I18N->msg('label_title'), array('class' => 'large_item'));
$fields['title']->needFullColumn(true);

$CJO['SEL_MEDIA']->setName("category_id");
$CJO['SEL_MEDIA']->setStyle("width: 354px");

$fields['category_id'] = new hiddenField('category_id');
$fields['category_id']->setValue($media_category);

$fields['category_cont'] = new readOnlyField('category_cont',$I18N->msg('label_file_category'));
$fields['category_cont']->addAttribute('style', 'float: left');
$fields['category_cont']->setValue($CJO['SEL_MEDIA']->get(false));
$fields['category_cont']->needFullColumn(true);

foreach($CJO['CLANG'] as $clang_id=>$name){

	$flag = '<img src="img/flags/'.$CJO['CLANG_ISO'][$clang_id].'.png" alt="'.$name.'" />';

    $temp = $media->getDescription($clang_id, false);
    if ($temp == $I18N->msg('label_no_media_description')) $temp = '';

	$fields['description_'.$name] = new textAreaField('description['.$clang_id.']', $flag.' '.$I18N->msg("label_description"));
	$fields['description_'.$name]->addAttribute('style', 'height: 45px;');
	$fields['description_'.$name]->needFullColumn(true);
	$fields['description_'.$name]->setValue($temp);
	$fields['description_'.$name]->activateSave(false);

	if(!$CJO['USER']->hasClangPerm($clang_id)){
		$fields['description_'.$name]->addAttribute('readonly', 'readonly');
		$fields['description_'.$name]->addAttribute('class', 'disabled');
		$fields['description_'.$name]->addAttribute('title', $I18N->msg("msg_no_permissions"));
	}
}

$fields['copyright'] = new textField('copyright', $I18N->msg('label_copyright'));
$fields['copyright']->needFullColumn(true);

$fields['crop_1'] = new hiddenField('crop_1',array());
$fields['crop_2'] = new hiddenField('crop_2',array());
$fields['crop_3'] = new hiddenField('crop_3',array());
$fields['crop_4'] = new hiddenField('crop_4',array());
$fields['crop_5'] = new hiddenField('crop_5',array());

$upload_container = '<div class="flash hide_me" style="clear: both;width:340px;margin:.5em 0 0 200px" id="fsUploadProgress"></div>';
$upload_button = '<span id="swf_placeholder"></span><input id="upload_button" type="button" value="'.$I18N->msg('button_browse').'" class="hide_me" />';
$upload_msg = '<span class="warning" style="margin-left: 200px!important; width:334px;">'.$I18N->msg('msg_install_or_upgrade_flash', '10').'</span>';

$fields['update_file'] = new textField('upload', $I18N->msg('label_update_file'), array ('style'=> 'width:250px;float:left;margin-right:4px'), 'update_file');
$fields['update_file']->setNote($upload_msg.$upload_button.$upload_container,'id="swf_upload_buttons" class="add"','div');
$fields['update_file']->addAttribute('readonly', 'readonly');
$fields['update_file']->addAttribute('class', 'hide_me');
$fields['update_file']->setValue('');
$fields['update_file']->activateSave(false);

$fields['update_file2'] =  new selectField('upload2', '&nbsp;');
$fields['update_file2']->addAttribute('size', '1');
$fields['update_file2']->addAttribute('style', 'hide_me');
$fields['update_file2']->needFullColumn(true);
$fields['update_file2']->activateSave(false);
$fields['update_file2']->addOption($I18N->msg('label_select_uploaded_file'), '');
$fields['update_file2']->addOption('', '');

$c_extension = $media->_getExtension();
$hide_update_file2 = true;
foreach(cjoMedia::getUploads() as $filename) {
    if (OOMedia::getExtension($filename) != $c_extension) continue;
    $fields['update_file2']->addOption($filename, $filename);
    $hide_update_file2 = false;
}
if ($hide_update_file2) {
    unset($fields['update_file2']);
    $fields['update_file']->needFullColumn(true);
}


$fields['details'] = new readOnlyField('details', NULL);
$fields['details']->needFullColumn(true);
$fields['details']->setValue($details);

$fields['updatedate_hidden'] = new hiddenField('updatedate');
$fields['updatedate_hidden']->setValue(time());

$fields['updateuser_hidden'] = new hiddenField('updateuser');
$fields['updateuser_hidden']->setValue($CJO['USER']->getValue("name"));

if (!$media_perm['w']) {

    unset($fields['update_file']);
    unset($fields['category_cont']);

    foreach($fields as $key=>$field){
        $field->activateSave(false);
        if ($key == 'details' || $key == 'icon') continue;
        $field->addAttribute('readonly', 'readonly');
    }

    $fields['button'] = new buttonField();
    $fields['button']->needFullColumn(true);
}
//Add Fields:
$section = new cjoFormSection(TBL_FILES, $I18N->msg("title_details"), array ('file_id' => $oid), array('100%', '100%'));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show($media_perm['w']);

if ($media_perm['w'] && $form->validate()) {
    
	$uploaded_file      = cjo_post('upload','string');
	$uploaded_file2     = cjo_post('upload2','string');	
	$descriptions       = cjo_post('description', 'array');
	$old_media_category = cjo_post('media_category', 'cjo-mediacategory-id');
	$media_category     = cjo_post('category_id', 'cjo-mediacategory-id');	

	foreach($descriptions as $key => $description){
        $descriptions[$key] = stripslashes($description);
    }

    $update = new cjoSql();
    $update->setTable(TBL_FILES);
    $update->setWhere("file_id='".$oid."'");
    $update->setValue("description", serialize($descriptions));
    $update->Update();
	
    $used_articles = $media->isInUse();

    if (is_array($used_articles)) {
        foreach($used_articles as $used_article) {
            cjoGenerate::deleteGeneratedArticle($used_article['article_id']);
        }
    }    
    
    if ($uploaded_file != '' && file_exists($CJO['UPLOADFOLDER'].'/'.$uploaded_file)) {
		cjoMedia::saveMedia($media->getId(), $uploaded_file, $media_category);
	}
	else if ($uploaded_file2 != '' && file_exists($CJO['UPLOADFOLDER'].'/'.$uploaded_file2)) {
	    cjoMedia::saveMedia($media->getId(), $uploaded_file2, $media_category);
	}
	else {
	    cjoExtension::registerExtensionPoint('MEDIA_UPDATED', array('id'=> $oid));
	}

	if (cjo_post('cjoform_save_button', 'bool')) {
        cjoAssistance::redirectBE(array('subpage'=>'media','media_category'=>$media_category,'oid' => ''));
	}
	else {
		cjoAssistance::redirectBE(array('subpage'=>$subpage,'media_category'=>$media_category,'oid'=>$oid));
	}
}

if (OOMedia::isImage($media->getFileName()))
    include_once $CJO['ADDON_PATH'].'/image_processor/pages/media_crop.inc.php';

if (!$media_perm['w']) return false;
?>

<script type="text/javascript" src="js/swfupload/swfupload.js"></script>
<script type="text/javascript" src="js/swfupload/fileprogress.js"></script>
<script type="text/javascript" src="js/swfupload/handlers_updatemedia.js"></script>
<script type="text/javascript">
/* <![CDATA[ */

	var swfu;

	$(function(){
		$('.preview').bind('click',function(){
			var url = $(this).attr('href');
			cjo.openPopUp('Preview', url, 0, 0);
			return false;
		});

		$('#cjoform_save_button, #cjoform_update_button').bind('click',function(){

			if($('#update_file').val() != ''){

				var this_name = $(this).attr('name');
				$('input[name="submit_type"]').attr('name',this_name);

				swfu.startUpload();
				$('#fsUploadProgress').slideDown('fast');
				return false;
			}
			return true;
		});

		$('#category_id').selectpath(
			{path_len: 'short',
			 types   : {root	: 'root',
                      	folder 	: 'categories',
                      	file	: 'category',
                      	locked 	: 'locked'}
            });

		swfu = new SWFUpload({

			flash_url : "js/swfupload/swfupload.swf", /* Relative to this file */
			upload_url: "<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/upload.php",
			post_params: {"UID": "<?php echo cjo_session('UID', 'int'); ?>",
				  		  "ST": "<?php echo cjo_session('ST', 'int'); ?>"},
			file_size_limit : "10 MB",
			file_types : "*.<?php echo $media->_getExtension(); ?>",
			file_types_description : "<?php echo $media->getType(); ?>",
			file_upload_limit : "1",
			file_queue_limit : "1",
			custom_settings : {
				progress_target : "fsUploadProgress",
				upload_successful : false
			},
			debug: false,

			// Event handler settings
			swfupload_loaded_handler : swfUploadLoaded,
			file_dialog_start_handler: fileDialogStart,
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,

			// Button Settings
			button_placeholder_id : "swf_placeholder",
			button_width: $('#upload_button').width()+15,
			button_height: $('#upload_button').height()+15,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,

			// SWFObject settings
			minimum_flash_version : "9.0.28"
		});
     });
/* ]]> */
</script>