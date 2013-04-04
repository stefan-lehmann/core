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

class cjoPageMediaCategories extends cjoPage {
    
    
    protected function setAdd() {
        
        $this->oid = false;
        $this->setEdit();
        $this->section = new cjoFormSection(TBL_FILE_CATEGORIES, cjoI18N::translate("title_add_media_category"), array ('id' => $this->oid));
    
    }
    protected function setEdit() {
        
        $this->fields['re_id'] = new hiddenField('re_id');
        $this->fields['re_id']->setValue(cjoMedia::getCategoryId());
    
        $this->fields['path'] = new hiddenField('path');
        $this->fields['path']->setValue(self::getMediaCatPath());
    
        $this->fields['name'] = new textField('name', cjoI18N::translate('label_name'), array('class' => 'large_item'));
        $this->fields['name']->addValidator('notEmpty', cjoI18N::translate('msg_name_notEmpty'));
        $this->fields['name']->needFullColumn(true);
    
        $this->fields['comment'] = new textField('comment', cjoI18N::translate('label_category_comment'));
        $this->fields['comment']->needFullColumn(true);
        
        $this->addUpdateFields();
        
        $this->section = new cjoFormSection(TBL_FILE_CATEGORIES, cjoI18N::translate("title_edit_settings"), array ('id' => $this->oid));
    }
 
    protected function getDefault() {
        
            $sql = "SELECT DISTINCT
                    c.*,
                    c.id AS checkbox,
                    c.id AS link1,
                    c.id AS link2,
                    COUNT(f.category_id) AS file_num,
                    IF((SELECT id FROM ".TBL_FILE_CATEGORIES." WHERE re_id=c.id LIMIT 1)>0, 1,0) AS children
                FROM
                    ".TBL_FILE_CATEGORIES." c
                LEFT JOIN
                    ".TBL_FILES." f
                ON
                    f.category_id=c.id
                WHERE
                    c.re_id='".cjoMedia::getCategoryId()."'
                GROUP BY c.id" ;
            
            $this->list = new cjoList($sql, 'name', 'ASC', 'name', 100);   
        
            $add_button = cjoUrl::createBELink('<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
                                               array('media_category'=> cjoMedia::getCategoryId(), 'function' => 'add', 'oid' => ''),
                                               $this->list->getGlobalParams(),
                                               'title="'.cjoI18N::translate("button_add").'"');
                
            $this->cols['checkbox'] = new checkboxColumn('checkbox', $add_button);
           
            $this->cols['thumb'] = new staticColumn('thumb', cjoI18N::translate('label_name'), 'sprintf', '<span>%s</span>');
            $this->cols['thumb']->addCondition('children',0, '<span style="position: relative; display: block;"><img src="img/radium_icons/folder.png" alt="" /></span>');
            $this->cols['thumb']->addCondition('children',1, '<span style="position: relative; display: block;"><img src="img/radium_icons/folders.png" alt="" /></span>');
            
            $this->cols['thumb']->setHeadAttributes('colspan="2"');
            $this->cols['thumb']->setBodyAttributes('class="icon"');
            $this->cols['thumb']->delOption(OPT_ALL);
        
            $this->cols['name'] = new concatColumn('name', NULL, 'truncate', array('length' => 60, 'etc' => '...', 'break_words' => false));
            $this->cols['name']->setDetailHtml('<ul class="col_details"><li class="infos">%s%s</span>'.
                                               ' <li class="quicklinks">'.
                                               '     <ul>'.
                                               '     <li class="first">%s</li>'.
                                               '     <li>%s</li>'.
                                               '     </ul>'.
                                               ' </li>'.
                                               '</ul>');
            $this->cols['name']->addDetail('id', 'sprintf', '<span title="'.cjoI18N::translate('label_id').'">ID %s</span>');
            $this->cols['name']->addDetail('file_num', 'sprintf', '<span title="'.cjoI18N::translate('label_file_num').'"><img src="img/mini_icons/page_white_acrobat.png" alt="" />%s '.cjoI18N::translate('label_media').'</span>');
            $this->cols['name']->addDetail('link1', 'sprintf', str_replace('%5Bs%5D','%s', cjoUrl::createBELink(cjoI18N::translate('title_media'), array('subpage' => 'media', 'media_category'=> '[s]'), array(), 'title="'.cjoI18N::translate("label_display_category_media").'" class="first"')));
            $this->cols['name']->addDetail('link2', 'sprintf', str_replace('%5Bs%5D','%s', cjoUrl::createBELink(cjoI18N::translate('title_addmedia'), array('subpage' => 'addmedia', 'media_category'=> '[s]'), array(), 'title="'.cjoI18N::translate("title_addmedia").'"')));
            
            
            
            $this->cols['name']->setBodyAttributes('class="large_item"');
            $this->cols['name']->setParams(array ('media_category'=> '%id%', 'function' => '', 'mode' => ''));
            $this->cols['name']->delOption(OPT_ALL);
        
            $this->cols['comment'] = new resultColumn('comment', cjoI18N::translate('label_category_comment'));
            $this->cols['comment']->setBodyAttributes('style="width: 45%"');

            $this->cols['edit'] = new editColumn(array ('function' => 'edit', 'media_category' => cjoMedia::getCategoryId(), 'oid' => '%id%'));
            $this->cols['delete'] = new deleteColumn(array('function'=>'cjoMedia::deleteMediaCategory', 'id'=>'%id%'));
        
            $this->list->addColumns($this->cols);
            $this->list->addUpLinkRow(array('media_category'=>self::getParentMediaCatId(), 'function'=> '', 'msg' => ''), cjoMedia::getCategoryId());  
            
            if ($this->list->numRows() != 0) {
        
                cjoSelectMediaCat::$sel_media->setName("target_location");
                cjoSelectMediaCat::$sel_media->setStyle("width:250px;clear:none;");
            
                $buttons = new popupButtonField('', '', '', '');
                $buttons->addButton(cjoI18N::translate('label_run_process'), false, 'img/silk_icons/tick.png', 'id="ajax_update_button"');
            
                $update_sel = new cjoSelect();
                $update_sel->setName('update_selection');
                $update_sel->setSize(1);
                $update_sel->setStyle('class="cjo_float_l update_selection" disabled="disabled"');
                $update_sel->addOption(cjoI18N::translate('label_update_selection'), 0);      
                $update_sel->setSelected(0);
            
                $update_sel->addOption(cjoI18N::translate('label_rmove_to'), 1);
                $update_sel->addOption(cjoI18N::translate('label_delete'), 2);          
            
                $toolbar_ext = '<tr class="toolbar_ext">'."\r\n".
                               '    <td class="icon">'.
                               '        <input type="checkbox" class="hidden_container check_all" title="'.cjoI18N::translate('label_select_deselect_all').'" />'.
                               '    </td>'.
                               '    <td colspan="'.(count($this->cols)-1).'">'.
                               '        <div class="hidden_container">'.$update_sel->get().
                               '        <span class="cjo_float_l hide_me">'.cjoSelectMediaCat::$sel_media->_get().'</span>'.
                               '        <span class="cjo_float_l hide_me">'.$buttons->getButtons().'</span>'.
                               '        </div>'.
                               '    </td>'.
                               '</tr>'."\r\n";
            
                $this->list->setVar(LIST_VAR_AFTER_DATA, $toolbar_ext);
            }  
            
            $this->list->show(false);
            
            ?><script type="text/javascript">
            /* <![CDATA[ */
            
                $(function(){
            
                    $('#target_location').selectpath({path_len: 'short', types: {root: 'root', folder : 'categories', file: 'folder'}});
            
                    $('#update_selection').change(function(){
            
                        var $this = $(this);
                        var selected = $this.val();
                        var next_all = $this.nextAll('span');
            
                        if (selected < 1){
                            next_all.addClass('hide_me');
                        } 
                        if (selected > 0 && selected < 2){
                            next_all.eq(0).removeClass('hide_me');
                            next_all.eq(1).removeClass('hide_me');
                        }
                        if (selected == 2){
                            next_all.eq(0).addClass('hide_me');
                            next_all.eq(1).removeClass('hide_me');
                        }
                    });
            
                    $('#ajax_update_button').click(function(){
            
                        var $this = $(this);
                        var tb = $('#category_list tbody');
                        var cb = tb.find('.checkbox:checked');
                        var total = cb.length - 1;
                        var selected = $('#update_selection :selected').val() *1;
                        var target = $('input[name="target_location"]').val() *1;
            
                        var messages    = [];
                            messages[1] = '<?php echo cjoI18N::translate('msg_confirm_move_media_cat') ?>';
                            messages[2] = '<?php echo cjoI18N::translate('msg_confirm_delete_media_cat') ?>';
            
                        if (cb.length < 1) return false;
            
                        var confirm_action = function() {
            
                            tb.block({ message: null });
            
                            cb.each(function(i){
                                var $this = $(this);
                                var id = $this.val();
                                var tr = $('#row_media_category_list_'+id);
                                var round = i;
            
                                $this.hide()
                                  .removeAttr('checked')
                                  .before(cjo.conf.ajax_loader);
            
                                switch(selected) {
                                    case 1: params = {'function': 'cjoMedia::moveMediaCategory',
                                                      'id': id,
                                                      'target' : target
                                                     }; break;
            
                                    case 2: params = {'function': 'cjoMedia::deleteMediaCategory',
                                                      'id': id
                                                     }; break;                                       
                                }
            
                                $.get('ajax.php', params,
                                        function(message){
                                            if (cjo.setStatusMessage(message)) {
            
                                                tr.find('.ajax_loader').remove();
                                                tr.find('.checkbox').show();
                                                tr.removeClass('selected');
            
                                                if (selected > 0 && !message.match(/class="error"/)) {
                                                    tr.fadeOut('slow', function() {
                                                        tr.remove();
                                                    });
                                                }
                                                tb.unblock();
                                        }
                                });
                            });
                        };
            
                        var message = messages[selected];
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
                                }
                            }
                        });
                        return false;
                    });
            
                    $(".cjo_delete").bind('click', function(){
            
                        var el = $(this);
                        var category_id = el.siblings().eq(0).find('input').val();
                        var cl = el.attr('class');
                        var tr = el.parent('tr');
                        var confirm_action = function() {
                            tr.children().block({ message: null });
                            cjo.toggleOnOff(el);
            
                            $.get('ajax.php',{
                                   'function': 'cjoMedia::deleteMediaCategory',
                                   'id': category_id },
                                  function(message){
            
                                    if(cjo.setStatusMessage(message)){
            
                                        el.find('img.ajax_loader')
                                          .remove();
            
                                        el.find('img')
                                          .toggle();
            
                                        if ($('.statusmessage p.error').length == 0){
            
                                            tr.fadeOut('slow', function(){
                                                tr.remove();
            
                                            });
                                            $('div[id^=cs_options][id$='+category_id+']').remove();
                                        }
                                        tr.children().unblock();
                                    }
                            });
                        };
            
                        var message = el.find('img').attr('title');
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
                    });
            
                });
            
            /* ]]> */
            </script><?php

    }
    
    public static function onFormSaveorUpdate($params) {
        
        if (self::isAddMode()) {
            $oid = $params['form']->last_insert_id;
        } else {
            $oid = cjo_get('oid','int');
        }
        self::setSaveExtention(array ("id" => $oid));
    }
    
    public static function onListDelete($id) {
        
        if ($id == '1') {
            cjoMessage::addError(cjoI18N::translate("msg_cant_delete_default_template"));
        }
        elseif ($id != '') {
    
            $sql = new cjoSql();
            $qry = "SELECT DISTINCT
                    a.id AS id,
                        a.clang AS clang,
                        a.name AS name,
                        t.name AS template_name
                   FROM ".TBL_ARTICLES." a
                   LEFT JOIN ".TBL_TEMPLATES." t
                   ON a.template_id = t.id
                   WHERE a.template_id='".$id."'";
            $results = $sql->getArray($qry);

            $temp = array();
            foreach ($results as $result) {
                    $temp[] = cjoUrl::createBELink(
                                            '<b>'.$result['name'].'</b> (ID='.$result['id'].')',
                                                array('page' => 'edit',
                                                      'subpage' => 'settings',
                                                      'function' => '',
                                                      'oid' => '',
                                                      'article_id' => $result['id'],
                                                      'clang' => $result['clang']));
            }
    
            if (!empty($temp))
                cjoMessage::addError(cjoI18N::translate("msg_cant_delete_template_in_use",
                                                $results[0]['template_name']).'<br />'.implode(' | ',$temp));
    
            if (!cjoMessage::hasErrors()) {
                $sql->flush();  
                $results = $sql->getArray("SELECT * FROM ".TBL_TEMPLATES." WHERE id='".$id."'");
                $sql->flush();
                if ($sql->statusQuery("DELETE FROM ".TBL_TEMPLATES." WHERE id='".$id."'",
                                  cjoI18N::translate("msg_template_deleted"))) {
                    cjoAssistance::updatePrio(TBL_TEMPLATES);
                    cjoExtension::registerExtensionPoint('TEMPLATE_DELETED', $results[0]);
                }
            }
        }
    }    
    
    public static function getParentMediaCatId(){
        $mediacat  = OOMediaCategory::getCategoryById(cjoMedia::getCategoryId());
        return (OOMediaCategory::isValid($mediacat)) ? $mediacat->_parent_id : NULL;
    }
    
    protected static function getMediaCatPath() {
        $mediacat  = OOMediaCategory::getCategoryById(cjoMedia::getCategoryId());
        return (OOMediaCategory::isValid($mediacat)) ? $mediacat->_path.cjoMedia::getCategoryId().'|' : '|';
    }
        
}
