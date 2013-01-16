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

cjoExtension::registerExtension('CJO_LIST_MEDIA_LIST_CELLS', 'cjoFormateCells');

$sql = "SELECT
			*,
			filename AS checkbox,
			filename AS thumb,
			filename AS link,
			IF(title!='',title, '".$I18N->msg('label_no_title')."') AS title
		FROM
			".TBL_FILES."
		WHERE
			category_id='".$media_category."'";

$list = new cjolist($sql, 'title, filename', 'ASC', 'filename', 100);
$list->addGlobalParams(array ('media_category' => $media_category));
$list->setName('MEDIA_LIST');
$list->setAttributes('id="media_list"');
//$list->debug = true;

$add_button = '';
if ($media_perm['w']) {
	$add_button = cjoAssistance::createBELink('<img src="img/silk_icons/add.png" title="'.$I18N->msg("button_add").'" alt="'.$I18N->msg("button_add").'" />',
                                              array('subpage' => 'addmedia', 'media_category'=> $media_category),
                                              $list->getGlobalParams(),
                                              'title="'.$I18N->msg("button_add").'"');
}

$cols['checkbox'] = new resultColumn('checkbox', $add_button, 'sprintf',
									 '<input type="checkbox" class="checkbox"'.
                                     (!$media_perm['w'] ? ' disabled="disabled"' : '').
                                     ' value="%s" />');
$cols['checkbox']->setHeadAttributes('class="icon"');
$cols['checkbox']->setBodyAttributes('class="icon"');
$cols['checkbox']->delOption(OPT_ALL);

$cols['thumb'] = new resultColumn('thumb', $I18N->msg('label_thumb'), 'call_user_func', array('OOMedia::toThumbnail',array('%s')));
$cols['thumb']->setHeadAttributes('width="80"');
$cols['thumb']->setBodyAttributes('class="preview" width="80" height="70" style="text-align:center!important;"');
$cols['thumb']->setParams(array ('filename' => '%thumb%'));
$cols['thumb']->delOption(OPT_ALL);

$cols['filename'] = new resultColumn('filename', null, 'sprintf', '<span title="'.$I18N->msg('label_filename').'"><img src="img/mini_icons/page_white.png" alt="" />%s</span>');
$cols['filename']->setOptions(OPT_ALL);

$cols['filetype'] = new resultColumn('filetype', null);
$cols['filetype']->setOptions(OPT_ALL);

$cols['filesize'] = new resultColumn('filesize', null, 'sprintf', '<span title="'.$I18N->msg('label_filesize').'"><img src="img/mini_icons/drive.png" alt="" />%s</span>');
$cols['filesize']->setOptions(OPT_SORT);

$cols['createdate'] = new resultColumn('createdate', null, 'strftime', '<span title="'.$I18N->msg('label_createdate').'" style="clear:left;"><img src="img/mini_icons/time.png" alt="" />'.$I18N->msg("datetimeformat").'</span>');
$cols['createdate']->setOptions(OPT_SORT);

$cols['createuser'] = new resultColumn('createuser', null, 'sprintf', '<span title="'.$I18N->msg('label_createuser').'"><img src="img/mini_icons/user.png" alt="" />%s</span>');
$cols['createuser']->setOptions(OPT_ALL);

$cols['updatedate'] = new resultColumn('updatedate', null);
$cols['updatedate']->setOptions(OPT_SORT);

$cols['updateuser'] = new resultColumn('updateuser', null);
$cols['updateuser']->setOptions(OPT_ALL);

$cols['copyright'] = new resultColumn('copyright', null, 'sprintf', '<span title="'.$I18N->msg('label_copyright').'"><img src="img/mini_icons/copyright.png" alt="" />%s</span>');
$cols['copyright']->setOptions(OPT_ALL);

$cols['description'] = new resultColumn('description', null, 'call_user_func', array('cjoMedia::splitDescription',array('%s','flags')));
$cols['description']->delOption(OPT_SORT);

$cols['title'] = new resultColumn('title', $I18N->msg('label_title'), 'truncate',array( 'length' => 60, 'etc' => '...', 'break_words' => false));
$cols['title']->setBodyAttributes('class="large_item'.(!$media_perm['w'] ? ' locked' : '').'"');
$cols['title']->setParams(array ('page' => 'media', 'subpage' => 'details','media_category' => $media_category, 'oid' => '%file_id%'));
$cols['title']->setOptions(OPT_SEARCH);

$cols['delete'] = new staticColumn('', $I18N->msg('label_functions'));
$cols['delete']->setBodyAttributes('width="60"');

if ($media_perm['w']) {
    $cond['delete'] = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("label_delete_media").'" alt="'.$I18N->msg("label_delete_media").'" />';
    $cols['delete'] = new staticColumn($cond['delete'], $I18N->msg('label_functions'));
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');

    if ($list->hasRows()) {

    	$CJO['SEL_MEDIA']->setName("target_location");
    	$CJO['SEL_MEDIA']->setStyle("width:250px;clear:none;");

    	$buttons = new popupButtonField('', '', '', '');
    	$buttons->addButton( $I18N->msg('button_move'), false, 'img/silk_icons/page_white_go.png', 'id="move_media" class="cjo_float_l" style="padding:2px; display: block;"');
    	$buttons->addButton( $I18N->msg('button_delete'), false, 'img/silk_icons/bin.png', 'id="delete_media" class="cjo_float_l" style="padding:2px; margin-left: 10px; display: block;"');

    	$buttons = new popupButtonField('', '', '', '');
    	$buttons->addButton($I18N->msg('label_run_process'), false, 'img/silk_icons/tick.png', 'id="ajax_update_button"');

        $update_sel = new cjoSelect();
        $update_sel->setName('update_selection');
        $update_sel->setSize(1);
        $update_sel->setStyle('class="cjo_float_l" disabled="disabled"');
        $update_sel->addOption($I18N->msg('label_update_selection'), 0);
        $update_sel->setSelected(0);
        $update_sel->addOption($I18N->msg('button_move'), 1);
        $update_sel->addOption($I18N->msg('button_delete'), 2);

    	$toolbar_ext = '<tr class="toolbar_ext">'."\r\n".
    				 '	<td class="icon">'.
    				 '  	<input type="checkbox" class="hidden_container check_all" title="'.$I18N->msg('label_select_deselect_all').'" />'.
    				 '	</td>'.
    				 '	<td colspan="'.(count($cols)-2).'">'.
    				 '		<div class="hidden_container">'.$update_sel->get().
    				 '		<span class="cjo_float_l cjo_media_path hide_me">'.$CJO['SEL_MEDIA']->get(false).'</span>'.
    				 '		<span class="cjo_float_l hide_me">'.$buttons->getButtons().'</span>'.
    	             '		</div>'.
    				 '	</td>'.
    				 '</tr>'."\r\n";

    	$list->setVar(LIST_VAR_AFTER_DATA, $toolbar_ext);
    }

}

$list->addColumns($cols);

if (!cjo_request('search_key','boolean')) {
	$list->setVar(LIST_VAR_NO_DATA, $I18N->msg('msg_media_in_this_category'));
}

$list->show();




/**
 * Provides cell formating via extension point api
 * @param array $cell
 * @return array
 * @ignore
 */
function cjoFormateCells($cell){

	global $CJO, $I18N, $list, $oid, $media_category;

	$curr_body = $cell['cells'][$cell['name']]['body'];
	$curr_cell = $cell['cells'][$cell['name']]['cell'];

	switch($cell['name']){

		case 'filesize':
			$size = OOMedia::getFormattedSize($cell['unformated']);
			$curr_cell = cjoFormatter::format($size, $cell['format_type'], $cell['format']);
			break;

		case 'title':

			$inc_cells = array('filename', 'filesize', 'copyright' , 'createdate', 'createuser', 'description');

			$html  = '';
			foreach($inc_cells as $key => $inc_cell){

				$html .= ($key==3) ? '<br />' : ' ';
				$html .= $cell['cells'][$inc_cell]['cell'];
				unset($cell['cells'][$inc_cell]);
			}
			$curr_cell .= '<span class="col_details" style="height: auto">'.
						  '	<span class="infos">'.$html.'</span>'.
			              '</span>';

			unset($cell['cells']['filetype']);
			unset($cell['cells']['updatedate']);
			unset($cell['cells']['updateuser']);
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

    	$('.preview a').click(function(){
    		var href = $(this).attr('href');
    		var filename = href.replace(/^.*filename=/gi,'');
    		var url = '<?php echo $CJO['MEDIAFOLDER']; ?>/'+filename;
    		cjo.openPopUp('Preview', url, 0, 0);
    		return false;
    	});

    	$('#update_selection').change(function(){
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
			 types   : {root		  : 'root',
                      	folder 		  : 'categories',
                      	file		  : 'category',
                      	folder_locked : 'categories locked',
              			file_locked   : 'locked'}
        });

		$('.checkbox:not(:disabled)').click(function(){
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
					$('#media_list tbody .checkbox')
						.attr('checked','checked');
					$('#update_selection')
						.removeAttr('disabled');
				}
				else {
					$('#media_list tbody .checkbox')
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

        $('#ajax_update_button').click(function(){
			update_media(0);
			return false;
		});

		$('.cjo_delete img').click(function(){

			var $this = $(this);

			$('#media_list tbody .checkbox').removeAttr('checked');
			$this.parent().parent().find('.checkbox').attr('checked','checked').hide();
			update_media(2);
			
			return false;
		});

		function update_media(selected) {

			var $this = $(this);
            var tb = $('#media_list tbody');
			var cb = tb.find('.checkbox:checked');
			var total = cb.length - 1;
			var target = $('input[name="target_location"]').val() *1;
			var category_id = <?php echo $media_category; ?>;

			if (!selected) selected = $('#update_selection :selected').val() *1;

			var messages 	= [];
			 	messages[1] = '<?php echo $I18N->msg('msg_confirm_rmove_media') ?>';
			 	messages[2] = '<?php echo $I18N->msg('msg_confirm_rdelete_media') ?>';

			if (cb.length < 1) return false;

			var confirm_action = function() {

    			tb.block({ message: null });

    			cb.each(function(i){
    				var $this = $(this);
    				var filename = $this.val();
    				var tr = $this.parent().parent();
    				var round = i;

    				$this.hide()
    				  .removeAttr('checked')
    				  .before(cjo.conf.ajax_loader);

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
    				'<?php echo $I18N->msg('label_ok'); ?>': function() {
    					$(this).dialog('close');
    					confirm_action();
    				},
    				'<?php echo $I18N->msg('label_cancel'); ?>': function() {
    					$(this).dialog('close');
    				}
    			}
    		});
    	}
	});

/* ]]> */
</script>