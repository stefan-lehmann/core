<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
 * @version     2.6.0
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

cjoCommunityBounce::bounce();

$add_qry = (empty($group_id))
		  ? "ug.group_id = '0'"
		  : "ug.group_id = '".$group_id."'";

$qry = "SELECT 
			us.username,
			us.gender,
			us.firstname,
			us.name,
			us.email,
			us.lasttrydate,
			us.newsletter,	
			us.status,		
			us.id AS user_id,
			us.id AS checkbox,
			IF(us.password<>'',1,0) AS login
		FROM
			".TBL_COMMUNITY_USER." us
		LEFT JOIN
			".TBL_COMMUNITY_UG." ug
		ON
			ug.user_id=us.id
		WHERE
			".$add_qry." AND
			us.clang = '".$clang."'";

$list = new cjolist($qry, 'username', 'ASC', 'name', 50);
$list->addGlobalParams(array ('group_id' => $group_id));
$list->setName('USER_LIST');
$list->setAttributes('id="user_list"');
//$list->debug = true;

$add_button = cjoAssistance::createBELink(
						    '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
							array('re_id'=> $group_id, 'function' => 'add', 'clang' => $clang),
							$list->getGlobalParams(),
							'title="'.$I18N->msg("button_add").'"');

$cols['checkbox'] = new resultColumn('checkbox', $add_button, 'sprintf', '<input type="checkbox" class="checkbox" value="%s" />');
$cols['checkbox']->setHeadAttributes('class="icon"');
$cols['checkbox']->setBodyAttributes('class="icon"');
$cols['checkbox']->delOption(OPT_ALL);

$cols['id'] = new resultColumn('user_id', $I18N->msg("label_id"));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');
$cols['id']->addCondition('activation', '0', '<span class="disabled">%s</span>');

$cols['username'] = new resultColumn('username', $I18N_10->msg("label_username"), 'truncate', array( 'length' => 15, 'etc' => '...', 'break_words' => true));
$cols['username']->addCondition('status', '-1', '<strike>%s</strike>');
$cols['username']->addCondition('activation', '0', '<span class="disabled">%s</span>');

$cols['gender'] = new resultColumn('gender', $I18N_10->msg("label_gender"));
$cols['gender']->delOption(OPT_SEARCH);
$cols['gender']->addCondition('status', '-1', '<strike>%s</strike>');
$cols['gender']->addCondition('activation', '0', '<span class="disabled">%s</span>');

$cols['firstname'] = new resultColumn('firstname', $I18N_10->msg("label_firstname"), 'truncate', array( 'length' => 15, 'etc' => '...', 'break_words' => true));
$cols['firstname']->setOptions(OPT_ALL);
$cols['firstname']->addCondition('status', '-1', '<strike>%s</strike>');
$cols['firstname']->addCondition('activation', '0', '<span class="disabled">%s</span>');

$cols['name'] = new resultColumn('name', $I18N_10->msg("label_name"), 'truncate', array( 'length' => 15, 'etc' => '...', 'break_words' => true));
$cols['name']->setOptions(OPT_ALL);
$cols['name']->addCondition('status', '-1', '<strike>%s</strike>');
$cols['name']->addCondition('activation', '0', '<span class="disabled">%s</span>');

$cols['email'] = new resultColumn('email', $I18N_10->msg("label_email_to"), 'email', array('params' => "?subject=".$CJO['SERVERNAME']." -- Infomail"));
$cols['email']->addOption(OPT_SEARCH);
$cols['email']->addCondition('status', '-1', '<strike>%s</strike>');
$cols['email']->addCondition('activation', '0', '<span class="disabled">%s</span>');

$cols['lasttrydate'] = new resultColumn('lasttrydate', $I18N->msg("label_last_login"), 'strftime', $I18N->msg("dateformat_sort"));
$cols['lasttrydate']->addCondition('login', '0', ' ');
$cols['lasttrydate']->setBodyAttributes('style="white-space:nowrap;"');
$cols['lasttrydate']->addCondition('activation', '0', '<span class="disabled">%s</span>');

$cols['newsletter'] = new resultColumn('newsletter', $I18N_10->msg("label_newsletter_list"));
$cols['newsletter']->addCondition('activation', '0', ' ');
$cols['newsletter']->addCondition('newsletter', '1', '<img src="img/silk_icons/newspaper.png" alt="'.$I18N_10->msg("label_newsletter_active").'" />');
$cols['newsletter']->addCondition('newsletter', '0', ' ');
$cols['newsletter']->setHeadAttributes('class="icon"');
$cols['newsletter']->setBodyAttributes('class="icon"');

//
$cols['login'] = new resultColumn('login', $I18N_10->msg("label_login"));
$cols['login']->addCondition('login', '1', '<img src="img/silk_icons/key2.png" alt="'.$I18N_10->msg("label_login_active").'" />');
$cols['login']->addCondition('login', '0', ' ');
$cols['login']->setHeadAttributes('class="icon"');
$cols['login']->setBodyAttributes('class="icon"');

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="3"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%user_id%'));

// Status link
$added = '<img src="img/silk_icons/user_add.png" title="'.$I18N_10->msg("label_not_activated").'"  alt="" />';
$aktiv = '<img class="cjo_status" src="img/silk_icons/user.png" title="'.$I18N->msg("label_status_do_false").'" alt="'.$I18N->msg("label_status_true").'" />';
$inaktiv = '<img class="cjo_status" src="img/silk_icons/user_off.png" title="'.$I18N->msg("label_status_do_false").'" alt="'.$I18N->msg("label_status_false").'" />';
$disabled = '<img src="img/silk_icons/lock.png" title="" alt="" />';

$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->addCondition('activation', '0', $added);
$cols['status']->addCondition('status', '1', $aktiv);
$cols['status']->addCondition('status', '0', $inaktiv);
$cols['status']->addCondition('status', '-1', $disabled);

// Lösch link
$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');

$list->addColumns($cols);

if ($list->numRows() != 0) {

	$sel_group = cjoCommunityGroups::getSelectGroups($group_id);
	$sel_group->setName("new_group_id");
	$sel_group->setSize(1);
	$sel_group->setMultiple(false);
	$sel_group->setStyle("width:320px;");
	$sel_group->setDisabled(0);
	$sel_group->setSelected($group_id);
	$sel_group->setSelectedPath(cjoCommunityGroups::getPath($group_id));

	$buttons = new popupButtonField('', '', '', '');
	$buttons->addButton($I18N->msg('label_run_process'), false, 'img/silk_icons/tick.png', 'id="ajax_update_button"');

    $update_sel = new cjoSelect();
    $update_sel->setName('update_selection');
    $update_sel->setSize(1);
    $update_sel->setStyle('class="cjo_float_l" disabled="disabled"');
    $update_sel->addOption($I18N->msg('label_update_selection'), 0);
    $update_sel->addOption($I18N_10->msg('label_copy_users'), 1);
    $update_sel->addOption($I18N_10->msg('label_move_users'), 2);
    $update_sel->addOption($I18N_10->msg('label_delete_users'), 3);
    $update_sel->setSelected(0);

	$toolbar_ext = '<tr class="toolbar_ext">'."\r\n".
				   '	<td class="icon">'.
				   '    	<input type="checkbox" class="hidden_container check_all" title="'.$I18N->msg('label_select_deselect_all').'" />'.
				   '	</td>'.
				   '	<td colspan="'.(count($cols)-1).'">'.
				   '		<div class="hidden_container">'.$update_sel->get().
				   '		<span class="cjo_float_l hide_me">'.$sel_group->get().'</span>'.
				   '		<span class="cjo_float_l hide_me">'.$buttons->getButtons().'</span>'.
	               '		</div>'.
				   '	</td>'.
				   '</tr>'."\r\n";

	$list->setVar(LIST_VAR_AFTER_DATA, $toolbar_ext);
}

if ($group_id == 0){

	$sql = new cjoSql;
	$qry = "SELECT COUNT(*) as count FROM ".TBL_COMMUNITY_USER." WHERE clang='".$clang."'";
	$sql->setQuery($qry);
	$temp = $sql->getValue('count');
	$list->setVar(LIST_VAR_NO_DATA, $I18N_10->msg('msg_no_data_in_root', (int) $temp));
}

$list->show();


$form = new cjoForm();
$form->setEditMode(false);
$form->debug = false;


if ($CJO['ADDON']['settings'][$mypage]['BOUNCE']) {

    $fields['mail_account'] = new selectField('BOUNCE_MAIL_ACCOUNT', $I18N_10->msg('label_php_mailer_account'));
    $fields['mail_account']->addSqlOptions("SELECT CONCAT(from_name,' &lt;',from_email,'&gt;') AS name, id FROM ".TBL_20_MAIL_SETTINGS);
    $fields['mail_account']->setMultiple(false);
    $fields['mail_account']->addAttribute('size', '1', true);
    $fields['mail_account']->addColAttribute('style', 'height:46px', true);
    $fields['mail_account']->addValidator('notEmpty', $I18N_10->msg("msg_php_mailer_account"));
  
    $button = '<input type="submit" id="cjoform_bounce_button" class="cjo_form_button green"
                   value="'.$I18N_10->msg("button_start_bounce").'" 
                   title="'.$I18N_10->msg("button_start_bounce").'"
                   name="cjoform_bounce_button" value="1" />
          </button>';

    
    
    $fields['mail_account']->setNote($button);
    
    $section = new cjoFormSection($CJO['ADDON']['settings'][$mypage], '', array ());
    
    $section->addFields($fields);
    $form->addSection($section);
    
    $form->show(false);
    
    if ($form->validate() && cjo_post('cjoform_bounce_button','bool')) {
        $CJO['ADDON']['settings'][$mypage]['BOUNCE_MAIL_ACCOUNT'] = cjo_post('BOUNCE_MAIL_ACCOUNT','int', 0);
        cjoGenerate::updateSettingsFile($CJO['ADDON']['settings'][$mypage]['SETTINGS']);
        cjoCommunityBounce::bounce(true);
    }
}
?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function(){

		$('#new_group_id').selectpath({path_len: 'short',
									   types  : {root	 : 'root',
            		  							 folder  : 'categories',
               		  							 file	 : 'folder'
               		  				  }});

		$(".cjo_status").bind('click', function(){

			var el = $(this);
			var user_id = el.parent('td').siblings('td').eq(0).find(':checkbox').val();
			var mode = (el.attr('src').match(/_off\.png$/)) ? '1' : '0';

			cjo.toggleOnOff(el.parent());

			$.get('ajax.php',{
				   'function': 'cjoCommunityUser::changeUserStatus',
				   'user_id': user_id,
				   'mode' : mode},
				  function(message){

					if(cjo.setStatusMessage(message)){

					  	el.siblings('img.ajax_loader')
					  	  .remove();

					  	el.toggle();
					}
			});
		});

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
					$('#user_list tbody .checkbox')
						.attr('checked','checked');
					$('#update_selection')
						.removeAttr('disabled');
				}
				else {
					$('#user_list tbody .checkbox')
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
			if (selected > 0 && selected < 3){
				next_all.eq(0).removeClass('hide_me');
				next_all.eq(1).removeClass('hide_me');
			}
			if (selected == 3){
				next_all.eq(0).addClass('hide_me');
				next_all.eq(1).removeClass('hide_me');
			}
		});

		$('#ajax_update_button').click(function(){

			var $this = $(this);
            var tb = $('#user_list tbody');
			var cb = tb.find('.checkbox:checked');
			var total = cb.length - 1;
			var selected = $('#update_selection :selected').val() *1;
			var target = $('input[name="new_group_id"]').val() *1;

			var messages 	= [];
			 	messages[1] = '<?php echo $I18N_10->msg('msg_confirm_copy') ?>';
			 	messages[2] = '<?php echo $I18N_10->msg('msg_confirm_move') ?>';
			 	messages[3] = '<?php echo $I18N_10->msg('msg_confirm_delete') ?>';

			if (cb.length < 1) return false;

			var confirm_action = function() {

    			tb.block({ message: null });

    			cb.each(function(i){
    				var $this = $(this);
    				var id = $this.val();
    				var tr = $('#row_user_list_'+id);
    				var round = i;

    				$this.hide()
    				  .removeAttr('checked')
    				  .before(cjo.conf.ajax_loader);

        			switch(selected) {
        				case 1: params = {'function': 'cjoCommunityUser::copyUser',
                						  'id': id,
                						  'target' : target
                						 }; break;

        				case 2: params = {'function': 'cjoCommunityUser::moveUser',
                						  'id': id,
                						  'target' : target,
                						  'group_id' : '<?php echo $group_id; ?>'
                						 }; break;

        				case 3: params = {'function': 'cjoCommunityUser::deleteUser',
                						  'id': id,
                						  'group_id' : '<?php echo $group_id; ?>'
                						 }; break;
        			}

        			$.get('ajax.php', params,
        				  	function(message){
        						if (cjo.setStatusMessage(message)) {

        						  	tr.find('.ajax_loader').remove();
        						  	tr.find('.checkbox').show();
    								tr.removeClass('selected');

        						  	if (selected >= 2 && !message.match(/class="error"/)) {
        						  		tr.fadeOut('slow', function() {
        						  			tr.remove();
        						  		});
        						  	}
        						  	tb.unblock();

//        						  	if (selected != 1) {
//        						  		location.reload();
//        						   }
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

        $('.cjo_delete').click(function(){

			var el = $(this);
			var id = el.siblings().eq(0).find('input').val();

			var confirm_action = function() {

				cjo.toggleOnOff(el);

    			$.get('ajax.php',{
    				   'function': 'cjoCommunityUser::deleteUser',
					   'id': id,
					   'group_id' : '<?php echo $group_id; ?>'
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


			var jdialog = cjo.appendJDialog('<?php echo $I18N_10->msg('msg_confirm_delete_user'); ?>');

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

		});

        $('form[name^="<?php echo $mypage.'_'.$subpage; ?>"]').submit(function(){
            $('#cjo_page').block({ message: null })
            $('button').attr('disabled','disabled');        
        });
        
	});

	function cm_automateScript(url) {

		if (!url.match(/\bfinished\b/)) {
			$('#cjo_page').block({ message: null }); 
	    	$('button').attr('disabled','disabled');
		    setTimeout(function(){ location.href = url },2000);
		}
		else {
			$('#cjo_page').unblock(); 
	    	$('button').removeAttr('disabled');
		}
	}

/* ]]> */
</script>