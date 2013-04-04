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

class cjoPageMediaAddMedia extends cjoPage {
        
    protected $function        = 'edit';
    protected $default_buttons = false;
    
    protected function setEdit() {


        $uploads 	= '';
        
        if (cjoFile::isWritable(cjoPath::uploads())){
        
        	$uploads = '';
        
        	foreach(cjoMedia::getUploads() as $filename){
        		$uploads .= cjoMedia::getMediaContainer($filename, cjoPath::uploads($filename), true);
        	}
        	if ($uploads == '') {
        		$uploads = '<span class="warning" style="width:334px;">'.cjoI18N::translate('msg_first_upload_some_files').'</span>';
        	}
        }
        else { $uf_ready = false; }
        
        $buttons = new buttonField();
        $buttons->addButton('cjoform_upload_button',cjoI18N::translate('button_upload'), true, 'img/silk_icons/page_white_get.png');
        $buttons->addButton('cjoform_cancel_button',cjoI18N::translate('button_cancel_upload'), true, 'img/silk_icons/cancel.png'); //onclick="swfu.selectFiles()
        $buttons->setButtonAttributes('cjoform_cancel_button', 'disabled="disabled"');

        $this->fields['swfupload'] = new readOnlyField('container', cjoI18N::translate('label_file_upload'), array('class'=>'flash'), 'fsUploadProgress');
        $this->fields['swfupload']->setContainer('div');
        $this->fields['swfupload']->setNote('<div id="swf_status"></div>
        							  <span id="swf_placeholder"></span>'.
        							  $buttons->_get().'
        							  <span class="warning hide_me"  style="width:334px;">'.
        							  cjoI18N::translate('msg_install_or_upgrade_flash', '11').'
        							  </span>',
        							  'id="swf_upload_buttons" style="margin-left:200px"','div');
        
        $this->fields['container'] = new readOnlyField('container', cjoI18N::translate('label_uploaded_media'), array('class'=>'floatbox'), 'cjo_uploads');
        $this->fields['container']->needFullColumn(true);
        $this->fields['container']->setContainer('div');
        $this->fields['container']->setValue($uploads);
        $this->fields['container']->setNote('<input type="button" value="'.cjoI18N::translate('label_select_deselect_all').'" id="select_al_uploads" />');
        
        $hidden['syncfiles'] = new hiddenField('files', array(), 'syncfiles');
        
        $this->fields['title'] = new textField('title', cjoI18N::translate('label_title'), array('class' => 'large_item'));
        $this->fields['title']->activateSave(false);
        
        cjoSelectMediaCat::$sel_media->setName("file_category");
        cjoSelectMediaCat::$sel_media->setStyle("width:354px;");
        
        $this->fields['file_category'] = new readOnlyField('filecategory',cjoI18N::translate('label_file_category'));
        $this->fields['file_category']->addAttribute('style', 'float: left');
        $this->fields['file_category']->setValue(cjoSelectMediaCat::$sel_media->get(false));
        
        foreach(cjoProp::getClangs() as $clang_id=>$name){
        
        	$flag = '<img src="img/flags/'.cjoProp::getClangIso($clang_id).'.png" alt="'.$name.'" />';
        
        	$this->fields['description_'.$name] = new textAreaField('description['.$clang_id.']', $flag.' '.cjoI18N::translate("label_description"));
        	$this->fields['description_'.$name]->addAttribute('rows', '3');
        	$this->fields['description_'.$name]->needFullColumn(true);
        	$this->fields['description_'.$name]->activateSave(false);
        
        	if(!cjoProp::getUser()->hasClangPerm($clang_id)){
        		$this->fields['description_'.$name]->addAttribute('readonly', 'readonly');
        		$this->fields['description_'.$name]->addAttribute('class', 'disabled');
        		$this->fields['description_'.$name]->addAttribute('title', cjoI18N::translate("msg_no_permissions"));
        	}
        }
        
        $this->fields['copyright'] = new textField('copyright', cjoI18N::translate('label_copyright'));
        $this->fields['copyright']->activateSave(false);
        
        $this->fields['buttons'] = new buttonField();
        $this->fields['buttons']->addButton('cjoform_sync_button',cjoI18N::translate('button_sync'), true, 'img/silk_icons/add.png');
        $this->fields['buttons']->setButtonAttributes('cjoform_sync_button', 'id="cjoform_sync_button"');

        $this->section = new cjoFormSection('', cjoI18N::translate("label_add_media"), array ('id' => ''));
        
        if ($uf_ready === false){
        	unset($this->fields['swfupload']);
        	unset($this->fields['container']);
        }

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
                    cjo.openPopUp('Preview', '<?php echo cjoUrl::uploads(); ?>'+inp.val(), 0, 0);
                    return false;
                });
        
                el.find('input[name="delete_upload"]').bind('click',function(){
                    var $this = $(this);
                    var confirm_action = function() {
        
                        $this.attr('src', './img/contejo/ajax/ajax-loader1.gif');
        
                        $this.parent().block({ message: null });
        
                        $.get('ajax.php', {
                                'function': 'unlink',
                                'file': '<?php echo cjoUrl::uploads(); ?>/'+$this.val()},
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
                            '<?php echo cjoI18N::translate('label_ok'); ?>': function() {
                                $(this).dialog('close');
                                confirm_action();
                            },
                            '<?php echo cjoI18N::translate('label_cancel'); ?>': function() {
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
        
                        var thisform = $('#cjo_form_media_addmedia');
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
                     types   : {root    : 'root',
                                folder  : 'categories',
                                file    : 'category',
                                locked  : 'locked'}
                    });
        
                cjo_bind_upload_actions($('#cjo_uploads .cjo_image_container'));
        
        
                $('#cjoform_sync_button').click(function(){
        
                    var thisform, selected_files, filenames, file_category, fileinfos;
        
                    thisform = $('#cjo_form_media_addmedia');
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
                    button_width: $('#cjoform_upload_button').width(),
                    button_height: $('#cjoform_upload_button').height(),
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
                    queue_complete_handler : queueComplete, // Queue plugin event
        
                    // SWFObject settings
                    minimum_flash_version : "9.0.28",
                    swfupload_pre_load_handler : swfUploadPreLoad,
                    swfupload_load_failed_handler : swfUploadLoadFailed
                });
             });
        /* ]]> */
        </script><?php
        
        
        
    }

}