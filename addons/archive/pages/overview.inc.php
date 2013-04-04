<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  archive
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

$qry = "SELECT *, ".
       "       ".TBL_28_ARCHIVE_ARTICLES.".id AS article_id, ".
	   "	   ".TBL_28_ARCHIVE_ARTICLES.".id AS checkbox, ".
       "		pa.path AS `pa.path`, ".
       "		pa.createdate AS `pa.createdate`, ".
//	   "(SELECT path FROM ".TBL_28_ARCHIVE_PATHS." ".
//	   "WHERE id=".TBL_28_ARCHIVE_ARTICLES.".id) AS tree, ".

	   "(SELECT name FROM ".TBL_TEMPLATES." ".
	   "WHERE id=".TBL_28_ARCHIVE_ARTICLES.".template_id) AS template, ".

	   "(SELECT count(*) FROM ".TBL_28_ARCHIVE_ARTICLES_SLICE." ".
	   "WHERE article_id=".TBL_28_ARCHIVE_ARTICLES.".id AND clang=".TBL_28_ARCHIVE_ARTICLES.".clang) AS slice_count ".

       "FROM ".TBL_28_ARCHIVE_ARTICLES." ".
       "LEFT JOIN ".TBL_28_ARCHIVE_PATHS." pa ".
       "ON ".TBL_28_ARCHIVE_ARTICLES.".id=pa.id AND ".
       "   ".TBL_28_ARCHIVE_ARTICLES.".clang=pa.clang ".
       "WHERE ".TBL_28_ARCHIVE_ARTICLES.".clang='".$CJO['CLANG']."'";

$list = new cjoList($qry, TBL_28_ARCHIVE_ARTICLES.'.id', 'desc', 'updatedate', 50);
$list->setName('ARCHIVE_ARTICLE_LIST');
$list->setAttributes('id="archive_article_list"');

$cols['checkbox'] = new resultColumn('checkbox', '', 'sprintf', '<input type="checkbox" class="checkbox" value="%s" />');
$cols['checkbox']->setHeadAttributes('class="icon"');
$cols['checkbox']->setBodyAttributes('class="icon"');
$cols['checkbox']->delOption(OPT_ALL);

$cols['article_id'] = new resultColumn('article_id', cjoAddon::translate(28,'label_article_id'));
$cols['article_id']->setHeadAttributes('class="icon"');
$cols['article_id']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', cjoAddon::translate(28,'label_name'), 'truncate',array( 'length' => 140, 'etc' => '...', 'break_words' => true));
$cols['name']->setBodyAttributes('width="250" style="font-weight:bold;font-size:120%"');

$cols['template'] = new resultColumn('template', cjoAddon::translate(28,'label_template'), 'truncate',array( 'length' => 140, 'etc' => '...', 'break_words' => false));

$cols['path'] = new resultColumn('pa.path', cjoAddon::translate(28,'label_path'),'call_user_func', array('cjoArchive::formatPath',array('%s','pa.path'))); //'preg_replace', array( '/\|/',' <br/>|&nbsp;&nbsp;&nbsp;&nbsp;&rarr;'));
//$cols['path']->delOption(OPT_SEARCH);

$cols['slice_count'] = new resultColumn('slice_count', cjoAddon::translate(28,'label_slice_count'));
$cols['slice_count']->setHeadAttributes('class="icon"');
$cols['slice_count']->setBodyAttributes('class="icon"');
$cols['slice_count']->delOption(OPT_SEARCH);

$cols['createdate'] = new resultColumn('pa.createdate', cjoAddon::translate(28,'label_createdate'), 'strftime', cjoI18N::translate('datetimeformat'));
$cols['createdate']->setBodyAttributes('style="white-space:nowrap;"');
$cols['createdate']->delOption(OPT_SEARCH);

$img = '<img src="img/silk_icons/bin.png" alt="'.cjoI18N::translate("button_delete").'" title="'.cjoI18N::translate("button_delete").'" />';
$cols['delete'] = new staticColumn($img, cjoI18N::translate("label_functions"));
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->delOption(OPT_SORT);

$list->addColumns($cols);

if ($list->numRows() != 0) {

    $CJO['SEL_ARTICLE']->setName("target_location");
	$CJO['SEL_ARTICLE']->setStyle("width:250px;clear:none;");
    
	$buttons = new popupButtonField('', '', '', '');
	$buttons->addButton(cjoAddon::translate(28,'label_restore_articles'), false, 'img/silk_icons/tick.png', 'id="ajax_update_button"');

    $update_sel = new cjoSelect();
    $update_sel->setName('update_selection');
    $update_sel->setSize(1);
    $update_sel->setStyle('class="cjo_float_l" disabled="disabled"');
    $update_sel->addOption(cjoI18N::translate('label_update_selection'), 0);
    $update_sel->setSelected(0);
    $update_sel->addOption(cjoAddon::translate(28,'label_restore_articles'), 1);
    $update_sel->addOption(cjoAddon::translate(28,'label_delete_articles'), 2);
	
	$toolbar_ext = '<tr class="toolbar_ext">'."\r\n".
				   '	<td class="icon">'.
				   '    	<input type="checkbox" class="hidden_container check_all" title="'.cjoI18N::translate('label_select_deselect_all').'" />'.
				   '	</td>'.
				   '	<td colspan="'.(count($cols)-1).'">'.
				   '		<div class="hidden_container">'.$update_sel->get().
				   '		<span class="cjo_float_l cjo_article_path hide_me">'.$CJO['SEL_ARTICLE']->_get().'</span>'. 
				   '		<span class="cjo_float_l">'.$buttons->getButtons().'</span>'.
	               '		</div>'.
				   '	</td>'.
				   '</tr>'."\r\n";
	$list->setVar(LIST_VAR_AFTER_DATA, $toolbar_ext);
}


$list->show();
?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('#target_location').selectpath({path_len: 'short'});


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
					$('#archive_article_list tbody .checkbox')
						.attr('checked','checked');
					$('#update_selection')
						.removeAttr('disabled');
				}
				else {
					$('#archive_article_list tbody .checkbox')
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

		$('#update_selection').change(function() {

			var $this = $(this);
			var selected = $this.val();
			var next_all = $this.nextAll('span');

			next_all.addClass('hide_me');

			if (selected == 1) {
				next_all.eq(0).removeClass('hide_me');
				next_all.eq(1).removeClass('hide_me');
			}	
			if (selected == 2) {
				next_all.eq(1).removeClass('hide_me');
			}	
		});

		$('#ajax_update_button').click(function() {

			var $this = $(this);
            var tb = $('#archive_article_list tbody');
			var cb = tb.find('.checkbox:checked');
			var total = cb.length - 1;
			var selected = $('#update_selection :selected').val() *1;
			var target = $('input[name="target_location"]').val() *1;

			var messages 	= [];
			 	messages[1] = '<?php echo cjoAddon::translate(28,'msg_confirm_restore') ?>';
			 	messages[2] = '<?php echo cjoAddon::translate(28,'msg_confirm_rdelete') ?>';			 	

			if (cb.length < 1) return false;

			var confirm_action = function() {

    			tb.block({ message: null });
				var round = 0;
				
    			cb.each(function(i) {
    				var $this = $(this);
    				var id = $this.val();
    				var tr = $('#row_archive_article_list_'+id);


    				$this.hide()
    				  .removeAttr('checked')
    				  .before(cjo.conf.ajax_loader);

        			switch(selected) {
        				case 1: params = {'function': 'cjoArchive::restore',
                						  'id': id,
                						  'target' : target
                						 }; break;

        				case 2: params = {'function': 'cjoArchive::delete',
                						  'id': id
                						 }; break;				 
        			}

        			$.get('ajax.php', params,
        				  	function(message) {
        						if (cjo.setStatusMessage(message)) {
            						
        							round++;
        						  	tr.find('.ajax_loader').remove();
        						  	tr.find('.checkbox').show();
    								tr.removeClass('selected');

        						  	if (!message.match(/class="error"/)) {
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

        $('.cjo_delete').click(function(){

			var el = $(this);
			var id = el.siblings().eq(0).find('input').val();

			var confirm_action = function() {

				cjo.toggleOnOff(el);

    			$.get('ajax.php',{
    				   'function': 'cjoArchive::delete',
					   'id': id
					   },
    				  function(message){

    					if(cjo.setStatusMessage(message)){

    					  	el.find('img.ajax_loader')
    					  	  .remove();

    					  	el.find('img')
    					  	  .toggle();

    						if ($('.statusmessage p.error').length == 0){
    							el.parent('tr').remove();
    						}
    					}
    			});
			};


			var jdialog = cjo.appendJDialog('<?php echo cjoAddon::translate(28,'msg_confirm_delete_article'); ?>');

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

		});
	});

/* ]]> */
</script>