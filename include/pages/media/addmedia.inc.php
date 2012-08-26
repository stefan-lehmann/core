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

$uploads 	= '';

if (cjoAssistance::isWritable($CJO['UPLOADFOLDER'])){

	$uploads = '';

	foreach(cjoMedia::getUploads() as $filename){
		$fullpath = $CJO['UPLOADFOLDER'].'/'.$filename;
		$uploads .= cjoMedia::getMediaContainer($filename, $fullpath, true);
	}

	if ($uploads == '') {
		$uploads = '<span class="warning" style="width:334px;">'.$I18N->msg('msg_first_upload_some_files').'</span>';
	}
}
else { $uf_ready = false; }

//Form
$form = new cjoForm();
$form->setEditMode(true);
//$form->debug = true;

$hidden['media_category'] = new hiddenField('media_category');
$hidden['media_category']->setValue($media_category);

$buttons = new buttonField();
$buttons->addButton('cjoform_upload_button',$I18N->msg('button_upload'), true, 'img/silk_icons/page_white_get.png');
$buttons->addButton('cjoform_cancel_button',$I18N->msg('button_cancel_upload'), true, 'img/silk_icons/cancel.png'); //onclick="swfu.selectFiles()
$buttons->setButtonAttributes('cjoform_cancel_button', 'disabled="disabled"');

//Fields
$fields['swfupload'] = new readOnlyField('container', $I18N->msg('label_file_upload'), array('class'=>'flash'), 'fsUploadProgress');
$fields['swfupload']->setContainer('div');
$fields['swfupload']->setNote('<div id="swf_status"></div>
							  <span id="swf_placeholder"></span>'.
							  $buttons->_get().'
							  <span class="warning hide_me"  style="width:334px;">'.
							  $I18N->msg('msg_install_or_upgrade_flash', '10').'
							  </span>',
							  'id="swf_upload_buttons" style="margin-left: 200px;"','div');

$fields['container'] = new readOnlyField('container', $I18N->msg('label_uploaded_media'), array('class'=>'floatbox'), 'cjo_uploads');
$fields['container']->needFullColumn(true);
$fields['container']->setContainer('div');
$fields['container']->setValue($uploads);
$fields['container']->setNote('<input type="button" value="'.$I18N->msg('label_select_deselect_all').'" id="select_al_uploads" />');

$hidden['syncfiles'] = new hiddenField('files', array(), 'syncfiles');

$fields['title'] = new textField('title', $I18N->msg('label_title'), array('class' => 'large_item'));
$fields['title']->activateSave(false);

$CJO['SEL_MEDIA']->setName("file_category");
$CJO['SEL_MEDIA']->setStyle("width:354px;");

$fields['file_category'] = new readOnlyField('filecategory',$I18N->msg('label_file_category'));
$fields['file_category']->addAttribute('style', 'float: left');
$fields['file_category']->setValue($CJO['SEL_MEDIA']->get(false));

foreach($CJO['CLANG'] as $clang_id=>$name){

	$flag = '<img src="img/flags/'.$CJO['CLANG_ISO'][$clang_id].'.png" alt="'.$name.'" />';

	$fields['description_'.$name] = new textAreaField('description['.$clang_id.']', $flag.' '.$I18N->msg("label_description"));
	$fields['description_'.$name]->addAttribute('rows', '3');
	$fields['description_'.$name]->needFullColumn(true);
	$fields['description_'.$name]->activateSave(false);

	if(!$CJO['USER']->hasClangPerm($clang_id)){
		$fields['description_'.$name]->addAttribute('readonly', 'readonly');
		$fields['description_'.$name]->addAttribute('class', 'disabled');
		$fields['description_'.$name]->addAttribute('title', $I18N->msg("msg_no_permissions"));
	}
}

$fields['copyright'] = new textField('copyright', $I18N->msg('label_copyright'));
$fields['copyright']->activateSave(false);

$fields['buttons'] = new buttonField();
$fields['buttons']->addButton('cjoform_sync_button',$I18N->msg('button_sync'), true, 'img/silk_icons/add.png');
$fields['buttons']->setButtonAttributes('cjoform_sync_button', 'id="cjoform_sync_button"');

//Add Fields:
$section = new cjoFormSection('', $I18N->msg("label_add_media"), array ('id' => ''));

if ($uf_ready === false){
	unset($fields['swfupload']);
	unset($fields['container']);
}

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

$form->show(false);

?>

<script type="text/javascript" src="js/swfupload/swfupload.js"></script>
<script type="text/javascript" src="js/swfupload/swfupload.swfobject.js"></script>
<script type="text/javascript" src="js/swfupload/swfupload.queue.js"></script>
<script type="text/javascript" src="js/swfupload/fileprogress.js"></script>
<script type="text/javascript" src="js/swfupload/handlers_addmedia.js"></script>

<script type="text/javascript">
/* <![CDATA[ */

	var swfu;

	function cjo_bind_upload_actions(el){

		el.bind('click', function(){
			select_uploads($(this));
		});

		$('#select_al_uploads').click(function(){

            if (el.length == el.filter('.selected').length) {
                el.each(function(){
                    select_uploads($(this));
                });
            }   
            else {
                el.not('.selected').each(function(){
                    select_uploads($(this));
                });
            }
		});

		el.find('input[name="preview_upload"]').bind('click',function(){
			var inp = $(this);
			cjo.openPopUp('Preview', '<?php echo $CJO['UPLOADFOLDER']; ?>/'+inp.val(), 0, 0);
			return false;
		});

		el.find('input[name="delete_upload"]').bind('click',function(){
			var $this = $(this);
			var confirm_action = function() {

				$this.attr('src', './img/contejo/ajax/ajax-loader1.gif');

				$this.parent().block({ message: null });

				$.get('ajax.php', {
						'function': 'unlink',
						'file': '<?php echo $CJO['UPLOADFOLDER']; ?>/'+$this.val()},
						function(data){
        					if(data == 1){
        						$this.parent().parent('.cjo_image_container')
        							.fadeOut('slow', function(){
        								$(this).remove();
        						});
        				    }
				});
			};

		    var message = $this.attr('title');
            if (!message.match(/\?/))  message += '?';
            var jdialog = cjo.appendJDialog(message);

			$(jdialog).dialog({
    			buttons: {
    				'<?php echo $I18N->msg('label_ok'); ?>': function() {
    					$(this).dialog('close');
    					confirm_action();
    				},
    				'<?php echo $I18N->msg('label_cancel'); ?>': function() {
    					$(this).dialog('close');
    					location.reload();
    				}
    			}
    		});

			return false;
		});

		var select_uploads = function(elm){
			
			elm.toggleClass('selected');
			elm.find('input[src$="cross.png"]').toggle(400, function() {

    			var thisform = $('form[name=<?php echo $mypage.'_'.$subpage.'_form'; ?>]');
    			if (thisform.find('.cjo_image_container.selected input[name="delete_upload"]').length == 0){
    				$('#cjoform_sync_button').attr('disabled','disabled').block({ message: null });
    			}
    			else {
    				$('#cjoform_sync_button').removeAttr('disabled').unblock();
    			}
			});
		};
	}

	$(function(){

		$('#file_category').selectpath(
			{path_len: 'short',
			 types   : {root	: 'root',
                      	folder 	: 'categories',
                      	file	: 'category',
                      	locked 	: 'locked'}
            });

		cjo_bind_upload_actions($('#cjo_uploads .cjo_image_container'));


		$('#cjoform_sync_button').click(function(){

			var thisform, selected_files, filenames, file_category, fileinfos;

			thisform = $('form[name=<?php echo $mypage.'_'.$subpage.'_form'; ?>]');
			selected_files = thisform.find('.cjo_image_container.selected input[name="delete_upload"]');

			filenames = '';
			$(this)
				.block({ message: null })
				.attr('disabled','disabled');

			selected_files.each(function(){

				var inp = $(this);
				inp.attr('src', './img/contejo/ajax/ajax-loader1.gif')
				   .show('fast');

				filenames += (filenames != '') ? '|' : '';
				filenames += inp.val();

			});

			file_category = thisform.find('input[name=file_category]').val();
			fileinfos = thisform.serialize();

			$.post("ajax.php", {
				   'function': "cjoMedia::saveMedia",
				   'file_id': 'add',
				   'filenames': filenames,
				   'media_category': file_category,
				   'fileinfos': fileinfos
				   },
				  	function(message){

						if (cjo.setStatusMessage(message)){

						  	 selected_files.each(function(){
								var inp = $(this);
						  		var filename = inp.val();

								if ($('.statusmessage p.error:contains("'+filename+'")').length == 0){
									inp.parent()
									   .parent('.cjo_image_container')
							   		   .fadeOut('slow', function(){
							   		   		$(this).remove();
							   		   	});
						  		}

							});
						}
		   			});
		   	return false;
		});

		$('#cjoform_sync_button')
			.block({ message: null })
			.attr('disabled','disabled');

		swfu = new SWFUpload({

			flash_url : "js/swfupload/swfupload.swf", /* Relative to this file */
			upload_url: "<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/upload.php",
			post_params: {"UID": "<?php echo cjo_session('UID', 'int'); ?>",
				  		  "ST": "<?php echo cjo_session('ST', 'int'); ?>"},
			file_size_limit : "10 MB",
			file_types : "*.*",
			file_types_description : "Files unlike: .php, .cgi, .pl, .asp",
			file_upload_limit : 100,
			file_queue_limit : 0,
			custom_settings : {
				progressTarget : "fsUploadProgress",
				cancelButtonId : "cjoform_cancel_button"
			},
			debug: false,

			// Button Settings
			button_placeholder_id : "swf_placeholder",
			button_width: $('#cjoform_upload_button').width()+15,
			button_height: $('#cjoform_upload_button').height()+15,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,

			// The event handler functions are defined in handlers.js
			swfupload_loaded_handler : swfUploadLoaded,
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			queue_complete_handler : queueComplete,	// Queue plugin event

			// SWFObject settings
			minimum_flash_version : "9.0.28",
			swfupload_pre_load_handler : swfUploadPreLoad,
			swfupload_load_failed_handler : swfUploadLoadFailed
		});
     });
/* ]]> */
</script>
