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
    $update->Update(cjoAddon::translate(23,"msg_channel_updated"));
    unset($function);
}

// LÖSCHEN
if ($function == 'delete') {
	$sql = new cjoSql();
    $qry = "DELETE FROM ".TBL_TV_CHANNELS." WHERE id='".$oid."'";
    $sql->statusQuery($qry,cjoAddon::translate(23,"msg_channel_deleted"));
    unset($function);
}

// HINZUFÜGEN
if ($function == "add" || $function == "edit" ) {

    //Form
    $form = new cjoForm();
    $form->setEditMode(true);
    //$form->debug = true;

    //Fields
    $fields['name'] = new textField('name', cjoAddon::translate(23,'label_channel_name'), $readonly);
    $fields['name']->addValidator('notEmpty', cjoAddon::translate(23,'msg_channel_name_notEmpty'), false, false);    
    $fields['name']->setNote('<div class="channel_preview_small"></div>'); 
    
    $fields['short_name'] = new textField('short_name', cjoAddon::translate(23,'label_channel_short_name'), $readonly);
    $fields['short_name']->addValidator('notEmpty', cjoAddon::translate(23,'msg_channel_short_name_notEmpty'), false, false); 
    
	$fields['pay'] = new checkboxField('pay', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['pay']->addBox(cjoAddon::translate(23,"label_pay_tv"), '1');    
	
	$fields['hd'] = new checkboxField('hd', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['hd']->addBox(cjoAddon::translate(23,"label_hd"), '1');    	
	
    $fields['description'] = new cjoWYMeditorField('description', cjoAddon::translate(23,'label_description'));
    $fields['description']->setWidth('650');
    $fields['description']->setHeight('200');
    
    $fields['video'] = new cjoMediaButtonField('video', cjoAddon::translate(23,'label_connected_video'), array('preview' => array('disabled' => false)));
    $fields['video']->setNote(cjoAddon::translate(23,'note_connected_video')); 
    
    //$fields['media'] = new cjoMediaButtonField('media', cjoAddon::translate(23,'label_connected_media'), array('preview' => array('disabled' => false)));    
           
    $fields['packages'] = new selectField('packages', cjoAddon::translate(23,'label_packages'));
    $fields['packages']->addSqlOptions("SELECT name, id FROM ".TBL_CHANNELPACKAGES." WHERE selectable=1 ORDER BY prior");
    $fields['packages']->addAttribute('size', 10);
    $fields['packages']->setMultiple(true);
    
    //Add Fields:
    $section = new cjoFormSection(TBL_TV_CHANNELS, cjoAddon::translate(23,$function."_channels"), array ('id' => $oid));
    
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
    		cjoUrl::redirectBE(array('oid'=>$oid, 'function'=>'edit','msg' => 'msg_data_saved'));
    	}
		if (cjo_post('cjoform_save_button','bool')) {
			unset($function);
		}
    }
} 

if ($function == "") {

//LIST Ausgabe
$sql = "SELECT *, id AS icon FROM ".TBL_TV_CHANNELS;
$list = new cjolist($sql, 'prior', 'ASC', 'name', 50);
$list->setName('CHANNEL_LIST');
$list->setAttributes('id="channel_list"');

$add_button = cjoUrl::createBELink(
						    '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
							array('function' => 'add'),
							$list->getGlobalParams(),
							'title="'.cjoI18N::translate("button_add").'"');

$cols['id'] = new resultColumn('id', $add_button);
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');
$cols['id']->delOption(OPT_ALL);

$cols['icon'] = new resultColumn('icon', cjoAddon::translate(23,'label_channel_icon'), 'call_user_func', array('cjoChannelList::formatIcon',array('%s','id')));
$cols['icon']->setBodyAttributes('class="channel_preview_small"');
$cols['icon']->delOption(OPT_ALL);

$cols['name'] = new resultColumn('name', cjoAddon::translate(23,'label_channel_name'));
$cols['name']->setBodyAttributes('width="20%"');

$cols['short_name'] = new resultColumn('short_name', cjoAddon::translate(23,'label_channel_short_name'));
$cols['short_name']->setBodyAttributes('width="10%"');

$cols['packages'] = new resultColumn('packages', cjoAddon::translate(23,'label_packages'), 'call_user_func', array('cjoChannelList::formatPackages',array('%s','packages')));
$cols['packages']->setBodyAttributes('width="20%"');
$cols['packages']->setOptions(OPT_SEARCH);

$cols['prio'] = new resultColumn('prior', cjoI18N::translate('label_prio'));
$cols['prio']->setHeadAttributes('class="icon"');
$cols['prio']->setBodyAttributes('class="icon dragHandle tablednd"');
$cols['prio']->setBodyAttributes('title="'.cjoI18N::translate("label_change_prio").'"');
$cols['prio']->addCondition('prior', array('!=', ''), '<strong>%s</strong>');


$cols['video'] = new resultColumn('video', cjoAddon::translate(23,'label_connected_video'));
$cols['video']->addCondition('video', array('!=', ''), '<img src="img/silk_icons/accept.png" alt="true" title="'.cjoAddon::translate(23,"label_connected_video").'" />');
$cols['video']->setOptions(OPT_SORT);

$cols['pay'] = new resultColumn('pay', cjoAddon::translate(23,'label_pay_tv'));
$cols['pay']->addCondition('pay', '1', '<img src="img/silk_icons/accept.png" alt="true" title="'.cjoAddon::translate(23,"label_pay_tv").'" />');
$cols['pay']->addCondition('pay', '0', '&nbsp;');
$cols['pay']->setOptions(OPT_SORT);

$cols['hd'] = new resultColumn('hd', cjoAddon::translate(23,'label_hd'));
$cols['hd']->addCondition('hd', '1', '<img src="img/silk_icons/accept.png" alt="true" title="'.cjoAddon::translate(23,"label_hd").'" />');
$cols['hd']->addCondition('hd', '0', '&nbsp;');
$cols['hd']->setOptions(OPT_SORT);

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
$cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'clang' => $clang, 'oid' => '%id%'));
$cols['edit']->setHeadAttributes('colspan="3"');

// Status link
$aktiv = '<img class="cjo_status" src="img/silk_icons/eye.png" title="'.cjoI18N::translate("label_status_do_false").'" alt="'.cjoI18N::translate("label_status_true").'" />';
$inaktiv = '<img class="cjo_status" src="img/silk_icons/eye_off.png" title="'.cjoI18N::translate("label_status_do_false").'" alt="'.cjoI18N::translate("label_status_false").'" />';

$cols['status'] = new staticColumn('status', NULL);
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->addCondition('status', '1', $aktiv, array ('function' => 'status', 'oid' => '%id%'));
$cols['status']->addCondition('status', '0', $inaktiv, array ('function' => 'status', 'oid' => '%id%'));

$img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
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

}
.a22-cjoform-section .channel_preview_small  {
    padding:2px; 
    border: 1px solid #ccc; 
    float: right;
    margin-right: 28px;
    background-color: #fff;
    background-image: url('<?php echo $CJO['MEDIAFOLDER'].'/'.$CJO['ADDON']['settings'][$mypage]['tv_sprite_small']; ?>');
    background-position: <?php  echo cjoChannelList::getPosition($oid) ?>;
}

-->
</style>