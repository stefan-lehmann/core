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

 
class cjoPageMediaMedia extends cjoPage {
 
    protected function setEdit() {
       
        
        $perm = cjoProp::getUser()->hasMediaPerm('this') && !cjoProp::getUser()->hasPerm('editContentOnly[]');
        $media = OOMedia::getMediaById($this->oid);

        if (!OOMedia::isValid($media)) {
            cjoUrl::redirectBE(array('media_category'=>cjoMedia::getCategoryId(),'function'=>NULL));
        }
        
        if ($media->getCategoryId() != cjo_get('media_category', 'cjo-mediacategory-id')) {
            cjoUrl::redirectBE(array('media_category'=>$media->getCategoryId()));
        }
        
        $details = '<span class="col_details">
                    <span title="'.cjoI18N::translate('label_filename').'"><img src="img/mini_icons/page_white.png" alt="" /> '.$media->getFileName().'</span>
                    <br/>
                    <span title="'.cjoI18N::translate('label_filetype').'"><img src="img/mini_icons/flag_blue.png" alt="" /> '.$media->getType().'</span>
                    <br/>
                    <span title="'.cjoI18N::translate('label_filesize').'"><img src="img/mini_icons/drive.png" alt="" /> '.$media->_getFormattedSize().'</span>
                    <br/>';
        
        if ($media->getWidth() && $media->getHeight())
            $details .= '<span title="'.cjoI18N::translate('label_size').'"><img src="img/mini_icons/shape_handles.png" alt="" /> '.$media->getWidth().' &times; '.$media->getHeight().' px</span><br/>';
        
        $details .= '<span title="'.cjoI18N::translate('label_createdate').'"><img src="img/mini_icons/time.png" alt="" /> '.$media->getCreateDate(cjoI18N::translate("datetimeformat")).'</span>
                        <span title="'.cjoI18N::translate('label_createuser').'"><img src="img/mini_icons/user.png" alt="" /> '.$media->getCreateUser().'</span>
                        <br/>
                        <span title="'.cjoI18N::translate('label_updatedate').'"><img src="img/mini_icons/time.png" alt="" /> '.$media->getUpdateDate(cjoI18N::translate("datetimeformat")).'</span>
                        <span title="'.cjoI18N::translate('label_updateuser').'"><img src="img/mini_icons/user.png" alt="" /> '.$media->getUpdateUser().'</span>
                    </span>';
        

        //$form->applyRedirectOnCancel(array('subpage'=>'media', 'media_category'=>cjoMedia::getCategoryId(), 'opener_input_field' => $opener_input_field));

        
        $hidden['submit_type'] = new hiddenField('submit_type');
        $hidden['submit_type']->setValue(1);
        
        $icon = '<a href="'.$media->getFullPath().'" class="preview" title="'.cjoI18N::translate('label_preview').'">'.OOMedia::toIcon($media->getFileName()).'</a>';
        $this->fields['icon'] = new readOnlyField('icon', '&nbsp;');
        $this->fields['icon']->setValue($icon);
        $this->fields['icon']->needFullColumn(true);
        $this->fields['icon']->activateSave(false);
        
        $this->fields['title'] = new textField('title', cjoI18N::translate('label_title'), array('class' => 'large_item'));
        $this->fields['title']->needFullColumn(true);

        cjoSelectMediaCat::$sel_media->setSelected(cjoMedia::getCategoryId());
        cjoSelectMediaCat::$sel_media->setName("media_category");
        cjoSelectMediaCat::$sel_media->setStyle("width: 354px");

        
        $this->fields['category_cont'] = new readOnlyField('category_cont',cjoI18N::translate('label_file_category'));
        $this->fields['category_cont']->addAttribute('style', 'float: left');
        $this->fields['category_cont']->setValue(cjoSelectMediaCat::$sel_media->get(false));
        $this->fields['category_cont']->needFullColumn(true);
        
        foreach(cjoProp::getClangs() as $clang_id=>$name){
        
            $flag = '<img src="img/flags/'.cjoProp::getClangIso($clang_id).'.png" alt="'.cjoProp::getClangName($clang_id).'" />';
        
            $temp = $media->getDescription($clang_id, false);
            if ($temp == cjoI18N::translate('label_no_media_description')) $temp = '';
        
            $this->fields['description_'.$name] = new textAreaField('description['.$clang_id.']', $flag.' '.cjoI18N::translate("label_description"));
            $this->fields['description_'.$name]->addAttribute('style', 'height: 45px;');
            $this->fields['description_'.$name]->needFullColumn(true);
            $this->fields['description_'.$name]->setValue($temp);
            $this->fields['description_'.$name]->activateSave(false);
        
            if(!cjoProp::getUser()->hasClangPerm($clang_id)){
                $this->fields['description_'.$name]->addAttribute('readonly', 'readonly');
                $this->fields['description_'.$name]->addAttribute('class', 'disabled');
                $this->fields['description_'.$name]->addAttribute('title', cjoI18N::translate("msg_no_permissions"));
            }
        }
        
        $this->fields['copyright'] = new textField('copyright', cjoI18N::translate('label_copyright'));
        $this->fields['copyright']->needFullColumn(true);
        
        $this->fields['crop_1'] = new hiddenField('crop_1',array());
        $this->fields['crop_2'] = new hiddenField('crop_2',array());
        $this->fields['crop_3'] = new hiddenField('crop_3',array());
        $this->fields['crop_4'] = new hiddenField('crop_4',array());
        $this->fields['crop_5'] = new hiddenField('crop_5',array());
        
        $upload_container = '<div class="flash hide_me" style="clear: both;width:340px;margin:.5em 0 0 0" id="fsUploadProgress"></div>';
        $upload_button = '<span id="swf_placeholder"></span><input id="upload_button" type="button" value="'.cjoI18N::translate('button_browse').'" class="hide_me" />';
        $upload_msg = '<span class="warning" style="width:334px;">'.cjoI18N::translate('msg_install_or_upgrade_flash', '10').'</span>';
        
        $this->fields['update_file'] = new textField('upload', cjoI18N::translate('label_update_file'), array ('style'=> 'width:250px;float:left;margin-right:4px'), 'update_file');
        $this->fields['update_file']->setNote($upload_msg.$upload_button.$upload_container,'id="swf_upload_buttons" class="update"','div');
        $this->fields['update_file']->addAttribute('readonly', 'readonly');
        $this->fields['update_file']->addAttribute('class', 'hide_me');
        $this->fields['update_file']->setValue('');
        $this->fields['update_file']->activateSave(false);
        
        $this->fields['update_file2'] =  new selectField('upload2', '&nbsp;');
        $this->fields['update_file2']->addAttribute('size', '1');
        $this->fields['update_file2']->addAttribute('style', 'hide_me');
        $this->fields['update_file2']->needFullColumn(true);
        $this->fields['update_file2']->activateSave(false);
        $this->fields['update_file2']->addOption(cjoI18N::translate('label_select_uploaded_file'), '');
        $this->fields['update_file2']->addOption('', '');
        
        $c_extension = $media->_getExtension();
        $hide_update_file2 = true;
        foreach(cjoMedia::getUploads() as $filename) {
            if (OOMedia::getExtension($filename) != $c_extension) continue;
            $this->fields['update_file2']->addOption($filename, $filename);
            $hide_update_file2 = false;
        }
        if ($hide_update_file2) {
            unset($this->fields['update_file2']);
            $this->fields['update_file']->needFullColumn(true);
        }
        
        $this->fields['details'] = new readOnlyField('details');
        $this->fields['details']->needFullColumn(true);
        $this->fields['details']->setContainer('div');
        $this->fields['details']->addAttribute('style', 'padding-left:200px');
        $this->fields['details']->setValue($details);
        
        $this->fields['updatedate_hidden'] = new hiddenField('updatedate');
        $this->fields['updatedate_hidden']->setValue(time());
        
        $this->fields['updateuser_hidden'] = new hiddenField('updateuser');
        $this->fields['updateuser_hidden']->setValue(cjoProp::getUser()->getValue("name"));
        
        if (!$perm) {
        
            unset($this->fields['update_file']);
            unset($this->fields['category_cont']);
        
            foreach($this->fields as $key=>$field){
                $field->activateSave(false);
                if ($key == 'details' || $key == 'icon') continue;
                $field->addAttribute('readonly', 'readonly');
            }
        
            $this->fields['button'] = new buttonField();
            $this->fields['button']->needFullColumn(true);
        }

        $this->section = new cjoFormSection(TBL_FILES, cjoI18N::translate("title_details"), array ('file_id' => $this->oid), array('100%', '100%'));

        if (OOMedia::isImage($media->getFileName()))
             include_once cjoPath::addon('image_processor', 'pages/media_crop.inc.php');
        
        ?><script type="text/javascript" src="js/swfupload/swfupload.js"></script>
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
                    
                    if (typeof swfu.submitButton != 'undefined') return true;
                     
                    if ($('#update_file').val() != ''){
        
                        var this_name = $(this).attr('name');
                        $('input[name="submit_type"]').attr('name',this_name);
                        swfu.submitButton = $(this);
                        
                        swfu.startUpload();
                        $('#fsUploadProgress').slideDown('fast');
                        return false;
                    }
                    return true;
                });
        
                $('#media_category').selectpath(
                    {path_len: 'short',
                     types   : {root    : 'root',
                                folder  : 'categories',
                                file    : 'category',
                                locked  : 'locked'}
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
       <?php
    }
 
 
    protected function getDefault() {
        
        $perm = cjoProp::getUser()->hasMediaPerm('this') && !cjoProp::getUser()->hasPerm('editContentOnly[]');
        
        $sql = "SELECT
                    *,
                    filename AS checkbox,
                    filename AS thumb,
                    filename AS link,
                    IF(title!='',title, '".cjoI18N::translate('label_no_title')."') AS title
                FROM
                    ".TBL_FILES."
                WHERE
                    category_id='".cjoMedia::getCategoryId()."'";
        
        $this->list = new cjolist($sql, 'title, filename', 'ASC', 'filename', 50);
        $this->list->addGlobalParams(array ('media_category' => cjoMedia::getCategoryId()));
        $this->list->setName('MEDIA_LIST');
        $this->list->setAttributes('id="media_list"');
        
        $add_button = '';
        if ($perm) {
            $add_button = cjoUrl::createBELink('<img src="img/silk_icons/add.png" title="'.cjoI18N::translate("button_add").'" alt="'.cjoI18N::translate("button_add").'" />',
                                                      array('subpage' => 'addmedia', 'media_category'=> cjoMedia::getCategoryId()),
                                                      $this->list->getGlobalParams(),
                                                      'title="'.cjoI18N::translate("button_add").'"');
        }
        
        $this->cols['checkbox'] = new checkboxColumn('checkbox', $add_button, $perm);

        
        $this->cols['thumb'] = new resultColumn('thumb', cjoI18N::translate('label_thumb'), 'call_user_func', array('OOMedia::toThumbnail',array('%s')));
        $this->cols['thumb']->setHeadAttributes('width="80"');
        $this->cols['thumb']->setBodyAttributes('class="preview" width="80" height="70" style="text-align:center!important;"');
        $this->cols['thumb']->setParams(array ('filename' => '%thumb%'));
        $this->cols['thumb']->delOption(OPT_ALL);


        $this->cols['title'] = new concatColumn('title', cjoI18N::translate('label_title'));
        $this->cols['title']->setDetailHtml('<span class="col_details" style="height: auto"><span class="infos">%s %s %s<br/> %s %s %s</span></span>');
        $this->cols['title']->addDetail('filename', 'sprintf', '<span title="'.cjoI18N::translate('label_filename').'"><img src="img/mini_icons/page_white.png" alt="" />%s</span>');
        $this->cols['title']->addDetail('filesize', 'filesize', '<span title="'.cjoI18N::translate('label_filesize').'"><img src="img/mini_icons/drive.png" alt="" />%s</span>');
        $this->cols['title']->addDetail('copyright', 'sprintf', '<span title="'.cjoI18N::translate('label_copyright').'"><img src="img/mini_icons/copyright.png" alt="" />%s</span>');
        $this->cols['title']->addDetail('createdate','strftime', '<span title="'.cjoI18N::translate('label_createdate').'" style="clear:left;"><img src="img/mini_icons/time.png" alt="" />'.cjoI18N::translate("datetimeformat").'</span>');
        $this->cols['title']->addDetail('createuser', 'sprintf', '<span title="'.cjoI18N::translate('label_createuser').'"><img src="img/mini_icons/user.png" alt="" />%s</span>');
        $this->cols['title']->addDetail('description','call_user_func', array('cjoMedia::splitDescription',array('%s','flags')));
        
        $this->cols['title']->setBodyAttributes('class="large_item'.(!$perm ? ' locked' : '').'"');
        $this->cols['title']->setParams(array ('media_category' => cjoMedia::getCategoryId(), 'function' => 'edit', 'oid' => '%file_id%'));
        $this->cols['title']->setOptions(OPT_SEARCH);
        
        if ($perm) {
            
            $this->cols['delete'] = new deleteColumn(array('function'=>'cjoMedia::deleteByName', 'filename' => '%filename%'),true,
                                                     '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("label_delete_media").'" 
                                                           alt="'.cjoI18N::translate("label_delete_media").'" />');
        
            if ($this->list->numRows() != 0) {
        
                cjoSelectMediaCat::$sel_media->setName("target_location");
                cjoSelectMediaCat::$sel_media->setStyle("width:250px;clear:none;");
        
                $buttons = new popupButtonField('', '', '', '');
                $buttons->addButton( cjoI18N::translate('button_move'), false, 'img/silk_icons/page_white_go.png', 'id="move_media" class="cjo_float_l" style="padding:2px; display: block;"');
                $buttons->addButton( cjoI18N::translate('button_delete'), false, 'img/silk_icons/bin.png', 'id="delete_media" class="cjo_float_l" style="padding:2px; margin-left: 10px; display: block;"');
        
                $buttons = new popupButtonField('', '', '', '');
                $buttons->addButton(cjoI18N::translate('label_run_process'), false, 'img/silk_icons/tick.png', 'id="ajax_update_button"');
        
                $update_sel = new cjoSelect();
                $update_sel->setName('update_selection');
                $update_sel->setSize(1);
                $update_sel->setStyle('class="cjo_float_l update_selection" disabled="disabled"');
                $update_sel->addOption(cjoI18N::translate('label_update_selection'), 0);
                $update_sel->setSelected(0);
                $update_sel->addOption(cjoI18N::translate('button_move'), 1);
                $update_sel->addOption(cjoI18N::translate('button_delete'), 2);
        
                $toolbar_ext = '<tr class="toolbar_ext">'."\r\n".
                             '  <td class="icon">'.
                             '      <input type="checkbox" class="hidden_container check_all" title="'.cjoI18N::translate('label_select_deselect_all').'" />'.
                             '  </td>'.
                             '  <td colspan="'.(count($this->cols)-1).'">'.
                             '      <div class="hidden_container">'.$update_sel->get().
                             '      <span class="cjo_float_l cjo_media_path hide_me">'.cjoSelectMediaCat::$sel_media->get(false).'</span>'.
                             '      <span class="cjo_float_l hide_me">'.$buttons->getButtons().'</span>'.
                             '      </div>'.
                             '  </td>'.
                             '</tr>'."\r\n";
        
                $this->list->setVar(LIST_VAR_AFTER_DATA, $toolbar_ext);
            }
        
        }
        
        $this->list->addColumns($this->cols);
        $this->list->addUpLinkRow(array('media_category'=>cjoPageMediaCategories::getParentMediaCatId(), 'function'=> '', 'msg' => ''), cjoMedia::getCategoryId());  
        
        if (!cjo_request('search_key','boolean')) {
            $this->list->setVar(LIST_VAR_NO_DATA, cjoI18N::translate('msg_media_in_this_category'));
        }
        
        $this->list->show();
        
        ?><script type="text/javascript">
          /* <![CDATA[ */
            
                $(function(){
            
                    $('.preview a').click(function(){
                        var href = $(this).attr('href');
                        var filename = href.replace(/^.*filename=/gi,'');
                        var url = '<?php echo cjoUrl::media(); ?>'+filename;
                        cjo.openPopUp('Preview', url, 0, 0);
                        return false;
                    });
            
                    $('.update_selection').change(function(){
                        var $this = $(this);
                        var selected = $this.val();
                        var next_all = $this.nextAll('span');
            
                        if (selected < 1){
                            next_all.addClass('hide_me');
                        }
                        if (selected == 1){
                            next_all.eq(0).removeClass('hide_me');
                            next_all.eq(1).removeClass('hide_me');
                        }
                        if (selected == 2){
                            next_all.eq(0).addClass('hide_me');
                            next_all.eq(1).removeClass('hide_me');
                        }
                    });
            
                    $('#target_location').selectpath(
                        {path_len: 'short',
                         types   : {root          : 'root',
                                    folder        : 'categories',
                                    file          : 'category',
                                    folder_locked : 'categories locked',
                                    file_locked   : 'locked'}
                    });
            
                    $('#ajax_update_button').click(function(){
                        update_media(0);
                        return false;
                    });
            
                    function update_media(selected) {
            
                        var $this = $(this);
                        var tb = $('#media_list tbody');
                        var cb = tb.find('.checkbox:checked');
                        var total = cb.length - 1;
                        var target = $('input[name="target_location"]').val() *1;
                        var category_id = <?php echo cjoMedia::getCategoryId(); ?>;
            
                        if (!selected) selected = $('.update_selection :selected').val() *1;
            
                        var messages    = [];
                            messages[1] = '<?php echo cjoI18N::translate('msg_confirm_rmove_media') ?>';
                            messages[2] = '<?php echo cjoI18N::translate('msg_confirm_rdelete_media') ?>';
            
                        if (cb.length < 1) return false;
            
                        var confirm_action = function() {
            
                            tb.block({ message: null });
            
                            cb.each(function(i){
                                var $this = $(this);
                                var filename = $this.val();
                                var tr = $this.parent().parent();
                                var round = i;
            
                                $this.hide().removeAttr('checked').before(cjo.conf.ajax_loader);
            
                                switch(selected) {
                                    case 1: params = {'function': 'cjoMedia::moveMedia',
                                                      'filename': filename,
                                                      'target' : target,
                                                      'category_id' : category_id
                                                     }; break;
            
                                    case 2: params = {'function': 'cjoMedia::deleteByName',
                                                      'filename': filename
                                                     }; break;
                                }
            
                                $.get('ajax.php', params,
                                        function(message){
            
                                            tr.find('.ajax_loader').remove();
                                            tr.find('.checkbox').show();
                                            tr.removeClass('selected');
            
                                            if (!message.match(/class="success"/)) {
                                                tr.fadeOut('slow', function() {
                                                    tr.remove();
                                                });
                                                if (cb.length == (i+1)) cjo.setStatusMessage(message);
                                            }
                                            else {
                                                cjo.setStatusMessage(message);
                                            }
                                            tb.unblock();
                                });
                            });
                        };
            
                        var jdialog = cjo.appendJDialog(messages[selected]);
            
                        $(jdialog).dialog({
                            buttons: {
                                '<?php echo cjoI18N::translate('label_ok'); ?>': function() {
                                    $(this).dialog('close');
                                    confirm_action();
                                },
                                '<?php echo cjoI18N::translate('label_cancel'); ?>': function() {
                                    $(this).dialog('close');
                                }
                            }
                        });
                    }
                });
            
            /* ]]> */
            </script><?php
    }

    public static function onFormIsValid($params) {
        
        $oid                = cjo_get('oid','int');
        $uploaded_file      = cjo_post('upload','string');
        $uploaded_file2     = cjo_post('upload2','string'); 
        $descriptions       = cjo_post('description', 'array');
        $media_category     = cjo_post('media_category', 'cjo-mediacategory-id');  
        $media              = OOMedia::getMediaById($oid);
        
        foreach($descriptions as $key => $description){
            $descriptions[$key] = stripslashes($description);
        }
    
        $update = new cjoSql();
        $update->setTable(TBL_FILES);
        $update->setWhere("file_id='".$oid."'");
        $update->setValue("description", serialize($descriptions));
        $update->setValue("category_id", $media_category);
        $update->Update();

        $used_articles = $media->isInUse();

        if (is_array($used_articles)) {
            foreach($used_articles as $used_article) {
                cjoGenerate::deleteGeneratedArticle($used_article['article_id']);
            }
        }    
        
        if ($uploaded_file != '' && file_exists(cjoPath::uploads($uploaded_file))) {
            cjoMedia::saveMedia($media->getId(), $uploaded_file, $media_category);
        }
        else if ($uploaded_file2 != '' && file_exists(cjoPath::uploads($uploaded_file2))) {
            cjoMedia::saveMedia($media->getId(), $uploaded_file2, $media_category);
        }
        else {
            cjoExtension::registerExtensionPoint('MEDIA_UPDATED', array('id'=> $oid));
        }
        
        if (cjo_post('cjoform_save_button', 'bool')) {
            cjoUrl::redirectBE(array('media_category'=>$media_category,'oid' => '', 'msg'=>'msg_media_updated'));
        }
    }
}
 