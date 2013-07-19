<?php
/**
 * This file is part of CONTEJO ADDON - CHANNEL LIST
 *
 * PHP Version: 5.3.1+
 *
 * @package 	Addon_channel_list
 * @subpackage 	pages
 * @version   	SVN: $Id: channels.inc.php 1084 2010-11-24 12:37:42Z s_lehmann $
 *
 * @author 		Stefan Lehmann <sl@contejo.com>
 * @copyright	Copyright (c) 2008-2011 CONTEJO. All rights reserved.
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

if ($function == 'status') {
	$sql = new cjoSql();
    $qry = "SELECT status FROM ".TBL_TV_CHANNELS." WHERE id='".$oid."'";
    $sql->setQuery($qry);

    $status = $sql->getValue('status') ? 0 : 1;
    
    $sql->flush();     
    $update = & $sql;
    $update->setTable(TBL_TV_CHANNELS);
    $update->setWhere("id='".$oid."'");
    $update->setValue("status", $status);  
    $update->addGlobalUpdateFields();
    $update->Update($I18N_23->msg("msg_channel_updated"));
    unset($function);
}

// LÖSCHEN
if ($function == 'delete') {
	$sql = new cjoSql();
    $qry = "DELETE FROM ".TBL_TV_CHANNELS." WHERE id='".$oid."'";
    $sql->statusQuery($qry,$I18N_23->msg("msg_channel_deleted"));
    unset($function);
}

// HINZUFÜGEN
if ($function == "add" || $function == "edit" ) {

    //Form
    $form = new cjoForm();
    $form->setEditMode(true);
    //$form->debug = true;

    //Fields
    $fields['name'] = new textField('name', $I18N_23->msg('label_channel_name'), $readonly);
    $fields['name']->addValidator('notEmpty', $I18N_23->msg('msg_channel_name_notEmpty'), false, false);    
    $fields['name']->setNote('<div class="channel_preview_small"></div>'); 
    $fields['name']->needFullColumn(true);
    
    $fields['short_name'] = new textField('short_name', $I18N_23->msg('label_channel_short_name'), $readonly);
    $fields['short_name']->addValidator('notEmpty', $I18N_23->msg('msg_channel_short_name_notEmpty'), false, false); 
    $fields['short_name']->needFullColumn(true);
    
    $fields['online_from'] = new datepickerField('online_from', $I18N->msg('label_from_to'), '', array('online_from','online_to'));
    $fields['online_from']->addSettings("defaultDate: 'd', buttonImage: 'img/silk_icons/calendar_begin.png'");
    $fields['online_from']->setDefault(time());
    
    $fields['online_to'] = new datepickerField('online_to', '&nbsp;', '', 'online_to');
    $fields['online_to']->addSettings("defaultDate: new Date(2020, 1 - 1, 1), buttonImage: 'img/silk_icons/calendar_end.png'");
    $fields['online_to']->setDefault(mktime(0, 0, 0, 1, 1, 2020)); 
    $fields['online_to']->needFullColumn(true);
    
	$fields['pay'] = new checkboxField('pay', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['pay']->addBox($I18N_23->msg("label_pay_tv"), '1');    
    $fields['pay']->needFullColumn(true);
	
	$fields['hd'] = new checkboxField('hd', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['hd']->addBox($I18N_23->msg("label_hd"), '1');    	
    $fields['hd']->needFullColumn(true);
	
    $fields['description'] = new cjoWYMeditorField('description', $I18N_23->msg('label_description'));
    $fields['description']->setWidth('650');
    $fields['description']->setHeight('200');
    $fields['description']->needFullColumn(true);
    
    $fields['video1'] = new cjoMediaButtonField('video1', $I18N_23->msg('label_connected_video').' 1', array('preview' => array('disabled' => false)));
    
    $fields['video1_online_from'] = new datepickerField('video1_online_from', '', '', array('video1_online_from','video1_online_to'));
    $fields['video1_online_from']->addSettings("defaultDate: 'd', buttonImage: 'img/silk_icons/calendar_begin.png'");
    $fields['video1_online_from']->setDefault(time());
    
    $fields['video1_online_to'] = new datepickerField('video1_online_to', '', '', 'video1_online_to');
    $fields['video1_online_to']->addSettings("defaultDate: new Date(2020, 1 - 1, 1), buttonImage: 'img/silk_icons/calendar_end.png'");
    $fields['video1_online_to']->setDefault(mktime(0, 0, 0, 1, 1, 2020));
    
    $fields['video2'] = new cjoMediaButtonField('video2', $I18N_23->msg('label_connected_video').' 2', array('preview' => array('disabled' => false)));
    
    $fields['video2_online_from'] = new datepickerField('video2_online_from', '', '', array('video2_online_from','video2_online_to'));
    $fields['video2_online_from']->addSettings("defaultDate: 'd', buttonImage: 'img/silk_icons/calendar_begin.png'");
    $fields['video2_online_from']->setDefault(time());
    
    $fields['video2_online_to'] = new datepickerField('video2_online_to', '', '', 'video2_online_to');
    $fields['video2_online_to']->addSettings("defaultDate: new Date(2020, 1 - 1, 1), buttonImage: 'img/silk_icons/calendar_end.png'");
    $fields['video2_online_to']->setDefault(mktime(0, 0, 0, 1, 1, 2020)); 
    
    
    $fields['medialist'] = new cjoMediaListField('medialist', $I18N_23->msg('label_connected_medialist'), array('style'=>'width:334px;'));  
    $fields['medialist']->needFullColumn(true);
           
    $fields['packages'] = new selectField('packages', $I18N_23->msg('label_packages'));
    $fields['packages']->addSqlOptions("SELECT name, id FROM ".TBL_CHANNELPACKAGES." WHERE selectable=1 ORDER BY prior");
    $fields['packages']->addAttribute('size', 10);
    $fields['packages']->setMultiple(true);
    $fields['packages']->needFullColumn(true);
    
    //Add Fields:
    $section = new cjoFormSection(TBL_TV_CHANNELS, $I18N_23->msg($function."_channels"), array ('id' => $oid),array('576px','135px','183px'));
    
    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {

    	if ($function == "add") {
			$oid = $form->last_insert_id;
			cjoAssistance::updatePrio(TBL_TV_CHANNELS,$oid,time());
		}
        
    	$update = new cjoSql();
    	$update->setTable(TBL_TV_CHANNELS);

    	if ($function == "add") {
    		$update->setWhere("id='".$oid."'");
    		$update->addGlobalCreateFields();
    	}
    	else {
    		$update->setWhere("id='".$oid."'");
    		$update->addGlobalUpdateFields();
    	}
    	$update->Update();
    	
        if (cjo_post('cjoform_update_button', 'bool') && !cjoMessage::hasErrors()) {
    		cjoAssistance::redirectBE(array('oid'=>$oid, 'function'=>'edit','msg' => 'msg_data_saved'));
    	}
		if (cjo_post('cjoform_save_button','bool')) {
			unset($function);
		}
    }
} 

if ($function == "") {

//LIST Ausgabe
$sql = "SELECT *, CONCAT(video1,video2) AS video, id AS icon FROM ".TBL_TV_CHANNELS;
$list = new cjolist($sql, 'prior', 'ASC', 'name', 50);
$list->setName('CHANNEL_LIST');
$list->setAttributes('id="channel_list"');

$add_button = cjoAssistance::createBELink(
						    '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
							array('function' => 'add'),
							$list->getGlobalParams(),
							'title="'.$I18N->msg("button_add").'"');

$cols['id'] = new resultColumn('id', $add_button);
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');
$cols['id']->delOption(OPT_ALL);

$cols['icon'] = new resultColumn('icon', $I18N_23->msg('label_channel_icon'), 'call_user_func', array('cjoChannelList::formatIcon',array('%s','id')));
$cols['icon']->setBodyAttributes('class="channel_preview_small"');
$cols['icon']->delOption(OPT_ALL);

$cols['name'] = new resultColumn('name', $I18N_23->msg('label_channel_name'));
$cols['name']->setBodyAttributes('width="20%"');

$cols['short_name'] = new resultColumn('short_name', $I18N_23->msg('label_channel_short_name'));
$cols['short_name']->setBodyAttributes('width="10%"');

$cols['packages'] = new resultColumn('packages', $I18N_23->msg('label_packages'), 'call_user_func', array('cjoChannelList::formatPackages',array('%s','packages')));
$cols['packages']->setBodyAttributes('width="15%"');
$cols['packages']->setOptions(OPT_SEARCH);

$cols['prio'] = new resultColumn('prior', $I18N->msg('label_prio'));
$cols['prio']->setHeadAttributes('class="icon"');
$cols['prio']->setBodyAttributes('class="icon dragHandle tablednd"');
$cols['prio']->setBodyAttributes('title="'.$I18N->msg("label_change_prio").'"');
$cols['prio']->addCondition('prior', array('!=', ''), '<strong>%s</strong>');


$cols['video'] = new resultColumn('video', $I18N_23->msg('label_connected_video'));
$cols['video']->addCondition('video', array('!=', ''), '<img src="img/silk_icons/accept.png" alt="true" title="'.$I18N_23->msg("label_connected_video").'" />');
$cols['video']->setBodyAttributes('class="icon"');
$cols['video']->setOptions(OPT_SORT);

$cols['medialist'] = new resultColumn('medialist', $I18N_23->msg('label_connected_medialist'));
$cols['medialist']->addCondition('medialist', array('!=', ''), '<img src="img/silk_icons/accept.png" alt="true" title="'.$I18N_23->msg("label_connected_medialist").'" />');
$cols['medialist']->setBodyAttributes('class="icon"');
$cols['medialist']->setOptions(OPT_SORT);

$cols['pay'] = new resultColumn('pay', $I18N_23->msg('label_pay_tv'));
$cols['pay']->addCondition('pay', '1', '<img src="img/silk_icons/accept.png" alt="true" title="'.$I18N_23->msg("label_pay_tv").'" />');
$cols['pay']->addCondition('pay', '0', '&nbsp;');
$cols['pay']->setBodyAttributes('class="icon"');
$cols['pay']->setOptions(OPT_SORT);

$cols['hd'] = new resultColumn('hd', $I18N_23->msg('label_hd'));
$cols['hd']->addCondition('hd', '1', '<img src="img/silk_icons/accept.png" alt="true" title="'.$I18N_23->msg("label_hd").'" />');
$cols['hd']->addCondition('hd', '0', '&nbsp;');
$cols['hd']->setBodyAttributes('class="icon"');
$cols['hd']->setOptions(OPT_SORT);

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'clang' => $clang, 'oid' => '%id%'));
$cols['edit']->setHeadAttributes('colspan="3"');

// Status link
$aktiv = '<img class="cjo_status" src="img/silk_icons/eye.png" title="'.$I18N->msg("label_status_do_false").'" alt="'.$I18N->msg("label_status_true").'" />';
$inaktiv = '<img class="cjo_status" src="img/silk_icons/eye_off.png" title="'.$I18N->msg("label_status_do_false").'" alt="'.$I18N->msg("label_status_false").'" />';

$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->addCondition('status', '1', $aktiv, array ('function' => 'status', 'oid' => '%id%'));
$cols['status']->addCondition('status', '0', $inaktiv, array ('function' => 'status', 'oid' => '%id%'));

$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));


//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);
$list->show();

}
    
?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function() {

		var update_id, curr_row_id, old_prio, new_prio;

        $("table#channel_list").tableDnD({
            onDragClass: "dragging",
            onDrop: function(table, row) {

				var cells   = $(table).find('td.tablednd');
				var allrows	= $(row).parent('tbody').children();
				var change  = true;

                cells.each(function(i) {

                	if ($(this).parent('tr').is('#'+curr_row_id)) {

                		new_prio = i+1;
                		if (old_prio == new_prio) {
                			change = false;
						}
						return true;
					}
				});

				if (!change) return false;

				var confirm_action = function() {

	                allrows.block({ message: null });

	                cells.each(function(i) {
	                	$(this).children().hide().text((i+1));
					});

					cells.removeClass('dragHandle')
						 .removeClass('tablednd')
						 .append(cjo.conf.ajax_loader);

	                if (old_prio < new_prio) new_prio++;

					$.get('ajax.php',{
						   'function': 'cjoAssistance::updatePrio',
						   'table': '<?php echo TBL_TV_CHANNELS; ?>',
						   'id': update_id,
						   'new_prio' : new_prio },
						  	function(message) {

							  	if (cjo.setStatusMessage(message)) {

									cells
										.find('img.ajax_loader')
										.remove();
									cells
										.children()
										.toggle();
									 cells
									 	.addClass('dragHandle')
										.addClass('tablednd');
									 allrows
									 	.unblock();
							   }
					});
                };

        		var message = $(row).find('td.tablednd').attr('title');
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
            },
            onDragStart: function(table, row) {

            	old_prio = $(row).text();

            	curr_row_id = $(row).parent('tr').attr('id');

            	var re = new RegExp('[0-9]+$');
  				var ma = re.exec(curr_row_id);
			    for (i = 0; i < ma.length; i++) {
			      update_id = ma[i];
			    }
			},
            dragHandle: "dragHandle"
        });
    });

/* ]]> */
</script>
<style type="text/css">
<!--
.channel_preview_small {
    overflow:hidden;
    width:106px;
    height:80px;
    padding: 4px;
    background-repeat: no-repeat;
}
.a22-cjolist-data .channel_preview_small {
    background-color: #fff!important;
    background-image: url('<?php echo $CJO['MEDIAFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['tv_sprite_small']; ?>');
    
}
.a22-cjolist-data .channel_preview_small.sprite2 {
    background-image: url('<?php echo $CJO['MEDIAFOLDER'].'/'.str_replace('sprite_', 'sprite2_', $CJO['ADDON']['settings'][$mypage]['tv_sprite_small']); ?>');
}
-->
</style>