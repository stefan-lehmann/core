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

cjoExtension::registerExtension('CJO_LIST_MEDIA_CATEGORY_LIST_CELLS', 'cjoFormateCells');

$mediacat  = OOMediaCategory::getCategoryById($media_category);
$parent_id = (OOMediaCategory::isValid($mediacat)) ? $mediacat->_parent_id : NULL;
$path      = (OOMediaCategory::isValid($mediacat)) ? $mediacat->_path.$media_category.'|' : '|';

if (cjo_post('cjoform_cancel_button','boolean')) {
	cjoAssistance::redirectBE(array('media_category'=>$media_category));
}

if ($function == 'add' || $function == 'edit') {

	//Form
	$form = new cjoForm();
	$form->setEditMode(false);

	//$form->debug = true;

	//Hidden Fields
	$hidden['media_category'] = new hiddenField('media_category');
	$hidden['media_category']->setValue($media_category);

	//Fields
	$fields['re_id'] = new hiddenField('re_id');
	$fields['re_id']->setValue($media_category);

	$fields['path'] = new hiddenField('path');
	$fields['path']->setValue($path);

	$fields['name'] = new textField('name', $I18N->msg('label_name'), array('class' => 'large_item'));
	$fields['name']->addValidator('notEmpty', $I18N->msg('msg_name_notEmpty'));
	$fields['name']->needFullColumn(true);

	$fields['comment'] = new textField('comment', $I18N->msg('label_category_comment'));
	$fields['comment']->needFullColumn(true);

	if ($function == 'add') {

		$oid = '';

		$fields['createdate_hidden'] = new hiddenField('createdate');
		$fields['createdate_hidden']->setValue(time());

		$fields['createuser_hidden'] = new hiddenField('createuser');
		$fields['createuser_hidden']->setValue($CJO['USER']->getValue("name"));

		$title = $I18N->msg("title_add_media_category");
	}
	else {

		$fields['updatedate_hidden'] = new hiddenField('updatedate');
		$fields['updatedate_hidden']->setValue(time());

		$fields['updateuser_hidden'] = new hiddenField('updateuser');
		$fields['updateuser_hidden']->setValue($CJO['USER']->getValue("name"));

		$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline slide'));
		$fields['headline1']->setValue($I18N->msg("label_info"));
		$fields['headline1']->needFullColumn(true);

		$fields['updatedate'] = new readOnlyField('updatedate', $I18N->msg('label_updatedate'), array(), 'label_updatedate');
		$fields['updatedate']->setFormat('strftime',$I18N->msg('dateformat_sort'));
		$fields['updatedate']->needFullColumn(true);

		$fields['updateuser'] = new readOnlyField('updateuser', $I18N->msg('label_updateuser'), array(), 'label_updateuser');
		$fields['updateuser']->needFullColumn(true);

		$fields['createdate'] = new readOnlyField('createdate', $I18N->msg('label_createdate'), array(), 'label_createdate');
		$fields['createdate']->setFormat('strftime',$I18N->msg('dateformat_sort'));
		$fields['createdate']->needFullColumn(true);

		$fields['createuser'] = new readOnlyField('createuser', $I18N->msg('label_createuser'), array(), 'label_createuser');
		$fields['createuser']->needFullColumn(true);

		$title = $I18N->msg("title_edit_settings");
	}

	//Add Fields:
	$section = new cjoFormSection(TBL_FILE_CATEGORIES, $title, array ('id' => $oid));

	$section->addFields($fields);
	$form->addSection($section);
	$form->addFields($hidden);
    $form->show();
    
	if ($form->validate()) {
	    if ($function == 'add') {
	       cjoExtension::registerExtensionPoint('MEDIA_CATEGORY_ADDED', array('id'=> $form->last_insert_id));
	    }
	    else {
	       cjoExtension::registerExtensionPoint('MEDIA_CATEGORY_UPDATED', array('id'=> $oid));
	    }
       cjoAssistance::redirectBE(array('media_category'=>$media_category,'function' => '','msg'=>'msg_data_saved'));
	}	
}

    $sql = "SELECT DISTINCT
    			c.*,
    			c.id AS checkbox,
    			COUNT(f.category_id) AS file_num,
    			IF((SELECT id FROM ".TBL_FILE_CATEGORIES." WHERE re_id=c.id LIMIT 1)>0, 1,0) AS children
            FROM
            	".TBL_FILE_CATEGORIES." c
            LEFT JOIN
              	".TBL_FILES." f
            ON
                f.category_id=c.id
            WHERE
                c.re_id='".$media_category."'
            GROUP BY c.id" ;

    $list = new cjolist($sql, 'name', 'ASC', 'name', 100);
    $list->setName('MEDIA_CATEGORY_LIST');
    $list->setAttributes('id="category_list"');

    $add_button = cjoAssistance::createBELink('<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
                               array('media_category'=> $media_category, 'function' => 'add', 'oid' => ''),
                               $list->getGlobalParams(),
                               'title="'.$I18N->msg("button_add").'"');
        
    $cols['checkbox'] = new resultColumn('checkbox', $add_button, 'sprintf', '<input type="checkbox" class="checkbox" value="%s" />');
    $cols['checkbox']->setHeadAttributes('class="icon"');
    $cols['checkbox']->setBodyAttributes('class="icon"');
    $cols['checkbox']->delOption(OPT_ALL);    

    $cols['thumb'] = new resultColumn('thumb', $I18N->msg('label_name'), 'sprintf', '<span>%s</span>');
    $cols['thumb']->setHeadAttributes('colspan="2"');
    $cols['thumb']->setBodyAttributes('class="icon"');
    $cols['thumb']->delOption(OPT_ALL);

    $cols['id'] = new resultColumn('id', null, 'sprintf', '<span title="'.$I18N->msg('label_id').'">ID %s</span>');
    $cols['file_num'] = new resultColumn('file_num', null, 'sprintf', '<span title="'.$I18N->msg('label_file_num').'"><img src="img/mini_icons/page_white_acrobat.png" alt="" />%s '.$I18N->msg('label_media').'</span>');

    $cols['name'] = new resultColumn('name', NULL, 'truncate', array('length' => 60, 'etc' => '...', 'break_words' => false));
    $cols['name']->setBodyAttributes('class="large_item"');
    $cols['name']->setParams(array ('media_category'=> '%id%', 'function' => '', 'mode' => ''));
    $cols['name']->delOption(OPT_ALL);

    $cols['comment'] = new resultColumn('comment', $I18N->msg('label_category_comment'));
    $cols['comment']->setBodyAttributes('style="width: 45%"');
    // Bearbeiten link
    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("label_edit_category").'" alt="'.$I18N->msg("label_edit").'" />';
    $cols['edit'] = new staticColumn($img, $I18N->msg('label_functions'));
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setHeadAttributes('colspan="2"');
    $cols['edit']->setParams(array ('function' => 'edit', 'media_category' => $media_category, 'oid' => '%id%'));

    $cond['delete'] = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("label_delete_category").'" alt="'.$I18N->msg("label_delete_category").'" />';
    $cols['delete'] = new staticColumn($cond['delete'], null);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');

    $rowAttributes = ' onclick="location.href=\''.cjoAssistance::createBEUrl(array('media_category'=>$parent_id, 'function'=> '', 'msg'=>'msg_data_saved')).'\';" ' .
                     ' class="cat_uplink" title="'.$I18N->msg("label_level_up").'"';

    $up_link  = '            <tr'.$rowAttributes.' valign="middle" class="nodrop">'."\r\n".
                '              <td class="icon" height="20">&nbsp;</td>'."\r\n".
                '              <td colspan="'.(count($cols)-1).'" height="20">'."\r\n".
                '              	<img src="img/silk_icons/level_up.png" alt="up" />'."\r\n".
                '              </td>'."\r\n".
                '            </tr>'."\r\n";

    if ($media_category) {
        if ($list->numRows() != 0 ) $list->setVar(LIST_VAR_BEFORE_DATA, $up_link);
        else $list->setVar(LIST_VAR_NO_DATA, $up_link);
    }

    $list->addColumns($cols);
    
    if ($list->numRows() != 0) {

    	$CJO['SEL_MEDIA']->setName("target_location");
    	$CJO['SEL_MEDIA']->setStyle("width:250px;clear:none;");
    
    	$buttons = new popupButtonField('', '', '', '');
    	$buttons->addButton($I18N->msg('label_run_process'), false, 'img/silk_icons/tick.png', 'id="ajax_update_button"');
    
        $update_sel = new cjoSelect();
        $update_sel->setName('update_selection');
        $update_sel->setSize(1);
        $update_sel->setStyle('class="cjo_float_l" disabled="disabled"');
        $update_sel->addOption($I18N->msg('label_update_selection'), 0);      
        $update_sel->setSelected(0);
    
        $update_sel->addOption($I18N->msg('label_rmove_to'), 1);
        $update_sel->addOption($I18N->msg('label_delete'), 2);          
    
    	$toolbar_ext = '<tr class="toolbar_ext">'."\r\n".
    				   '	<td class="icon">'.
    				   '    	<input type="checkbox" class="hidden_container check_all" title="'.$I18N->msg('label_select_deselect_all').'" />'.
    				   '	</td>'.
    				   '	<td colspan="'.(count($cols)-2).'">'.
    				   '		<div class="hidden_container">'.$update_sel->get().
    				   '		<span class="cjo_float_l hide_me">'.$CJO['SEL_MEDIA']->_get().'</span>'.
    				   '		<span class="cjo_float_l hide_me">'.$buttons->getButtons().'</span>'.
    	               '		</div>'.
    				   '	</td>'.
    				   '</tr>'."\r\n";
    
        $list->setVar(LIST_VAR_AFTER_DATA, $toolbar_ext);
    }  
    
    $list->show(false);

/**
 * Provides cell formating via extension point api
 * @param array $cell
 * @return array
 * @ignore
 */
function cjoFormateCells($cell){

    global $CJO, $I18N, $list, $oid;

    $curr_body = $cell['cells'][$cell['name']]['body'];
    $curr_cell = $cell['cells'][$cell['name']]['cell'];

    $media_category = $cell['cells']['checkbox']['unformated'];

    switch($cell['name']){

    	case 'thumb':
    			$icon = ($cell['row']['children']['unformated'] > 0) ? 'folders' : 'folder';
    			$icon = ($cell['row']['file_num']['unformated'] > 0) ? $icon.'2' : $icon;
        		$icon = '<img src="img/radium_icons/'.$icon.'.png" alt="" />';
        		$curr_cell = '<span style="position: relative; display: block;">'.$icon.'</span>';
        		break;

		case 'name':
       			$id =  $cell['cells']['id']['unformated'];
				if (!$CJO['USER']->hasMediaPerm($id)){
					$curr_cell = preg_replace('/(<a.*?href\="?\S+"[^>]*?>)(.+?)(<\/a>)/i',
											  '<span class="locked">$2</span>',
											  $curr_cell);
				}

				$inc_cells = array('id', 'file_num');

				$html  = '';
				foreach($inc_cells as $inc_cell){
					$html .= $cell['cells'][$inc_cell]['cell'];
					unset($cell['cells'][$inc_cell]);
				}

				$curr_cell .= '<ul class="col_details">'.
							  '	<li class="infos">'.$html.'</span>'.
							  '	<li class="quicklinks">'.
				              '		<ul>'.
							  '		<li class="first">'.cjoAssistance::createBELink($I18N->msg('title_media'), array('subpage' => 'media', 'media_category'=>$media_category), array(), 'title="'.$I18N->msg("label_display_category_media").'" class="first"').'</li>'.
							  '		<li>'.cjoAssistance::createBELink($I18N->msg('title_addmedia'), array('subpage' => 'addmedia', 'media_category'=>$media_category), array(), 'title="'.$I18N->msg("title_addmedia").'"').'</li>'.
							  '		</ul>'.
							  ' </li>'.
							  '</ul>';
				break;
	}

    $cell['cells'][$cell['name']]['body'] = $curr_body;
    $cell['cells'][$cell['name']]['cell'] = $curr_cell;

    return $cell['cells'];
}
?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('#target_location').selectpath({path_len: 'short', types: {root: 'root', folder : 'categories', file: 'folder'}});

		
		$('.checkbox').click(function(){
            if ($('.checkbox:checked').length > 0 ||
				$(this).is(':checked')) {
				$('#update_selection').removeAttr('disabled');
				$('.toolbar_ext .hidden_container')
					.fadeIn('slow');
			} else {
				$('#update_selection')
					.attr('disabled', 'disabled')
					.find('option')
					.removeAttr('selected');
				$('#update_selection')
					.nextAll('span')
					.addClass('hide_me');
				$('.toolbar_ext .hidden_container')
					.fadeOut('slow');
			}
		});

    	$('.check_all').click(function(){
    			if($(this).is(':checked')){
    				$('#category_list tbody .checkbox')
    					.attr('checked','checked');
    				$('#update_selection')
    					.removeAttr('disabled');
    			}
    			else {
    				$('#category_list tbody .checkbox')
    					.removeAttr('checked');
    
    				$('#update_selection')
    					.attr('disabled', 'disabled')
    					.find('option')
    					.removeAttr('selected');
    
    				$('#update_selection')
    					.nextAll('span')
    					.addClass('hide_me');
    			}
    	});

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

			var messages 	= [];
			 	messages[1] = '<?php echo $I18N->msg('msg_confirm_move_media_cat') ?>';
			 	messages[2] = '<?php echo $I18N->msg('msg_confirm_delete_media_cat') ?>';

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
    				'<?php echo $I18N->msg('label_ok'); ?>': function() {
    					$(this).dialog('close');
    					confirm_action();
    				},
    				'<?php echo $I18N->msg('label_cancel'); ?>': function() {
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
		});

    });

/* ]]> */
</script>
