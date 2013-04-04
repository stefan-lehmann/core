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

// LÖSCHEN
if ($function == 'delete') {
	$sql = new cjoSql();
    $qry = "DELETE FROM ".TBL_CHANNELPACKAGES." WHERE id='".$oid."'";
    $sql->statusQuery($qry,cjoAddon::translate(23,"msg_package_deleted"));
    unset($function);
}

// HINZUFÜGEN
if ($function == "add" || $function == "edit" ) {

    //Form
    $form = new cjoForm();
    $form->setEditMode(true);
    //$form->debug = true;

    //Fields
    $fields['name'] = new textField('name', cjoAddon::translate(23,'label_package_name'), $readonly);
    $fields['name']->addValidator('notEmpty', cjoAddon::translate(23,'msg_package_name_notEmpty'), false, false);    
    
    $fields['symbol'] = new textField('symbol', cjoAddon::translate(23,'label_package_symbol'), $readonly);
    $fields['symbol']->addValidator('notEmpty', cjoAddon::translate(23,'msg_package_symbol_notEmpty'), false, false);       
   
	$fields['selectable'] = new checkboxField('selectable', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['selectable']->addBox(cjoAddon::translate(23,"label_selectable"), '1');    
	
    $fields['description'] = new cjoWYMeditorField('description', cjoAddon::translate(23,'label_description'));
    $fields['description']->setWidth('650');
    $fields['description']->setHeight('200');    
    
    $fields['media'] = new cjoMediaButtonField('media', cjoAddon::translate(23,'label_connected_media'), array('preview' => array('disabled' => false))); 
        
    //Add Fields:
    $section = new cjoFormSection(TBL_CHANNELPACKAGES, cjoAddon::translate(23,$function."_packages"), array ('id' => $oid));
    
    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {

    	if ($function == "add") {
    		$oid = $form->last_insert_id;
			cjoAssistance::updatePrio(TBL_CHANNELPACKAGES,$oid,time());
		}
        
    	$update = new cjoSql();
    	$update->setTable(TBL_CHANNELPACKAGES);

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
$sql = "SELECT *, id AS icon FROM ".TBL_CHANNELPACKAGES;
$list = new cjolist($sql, 'prior', 'ASC', 'name', 50);
$list->setName('CHANNELPACKAGES');
$list->setAttributes('id="packages_list"');

$add_button = cjoUrl::createBELink(
						    '<img src="img/silk_icons/add.png" alt="'.cjoI18N::translate("button_add").'" />',
							array('function' => 'add'),
							$list->getGlobalParams(),
							'title="'.cjoI18N::translate("button_add").'"');

$cols['id'] = new resultColumn('id', $add_button);
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');
$cols['id']->delOption(OPT_ALL);



$cols['icon'] = new resultColumn('media', cjoAddon::translate(23,'label_package_icon'), 'call_user_func', array('OOMedia::toThumbnail',array('%s')));
$cols['icon']->delOption(OPT_ALL);
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', cjoAddon::translate(23,'label_package_name'));

$cols['symbol'] = new resultColumn('symbol', cjoAddon::translate(23,'label_package_symbol'));
$cols['symbol']->setBodyAttributes('width="25%"');

$cols['prio'] = new resultColumn('prior', cjoI18N::translate('label_prio'));
$cols['prio']->setHeadAttributes('class="icon"');
$cols['prio']->setBodyAttributes('class="icon dragHandle tablednd"');
$cols['prio']->setBodyAttributes('title="'.cjoI18N::translate("label_change_prio").'"');
$cols['prio']->addCondition('prior', array('!=', ''), '<strong>%s</strong>');

$cols['selectable'] = new resultColumn('selectable', cjoAddon::translate(23,'label_selectable'));
$cols['selectable']->addCondition('selectable', '1', '<img src="img/silk_icons/accept.png" alt="true" title="'.cjoAddon::translate(23,"label_selectable").'" />');
$cols['selectable']->addCondition('selectable', '0', '&nbsp;');
$cols['selectable']->setHeadAttributes('class="icon"');
$cols['selectable']->setBodyAttributes('class="icon"');
$cols['selectable']->setOptions(OPT_SORT);

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.cjoI18N::translate("button_edit").'" alt="'.cjoI18N::translate("button_edit").'" />';
$cols['edit'] = new staticColumn($img, cjoI18N::translate("label_functions"));
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'clang' => $clang, 'oid' => '%id%'));
$cols['edit']->setHeadAttributes('colspan="2"');

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

        $("table#packages_list").tableDnD({
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
						   'table': '<?php echo TBL_CHANNELPACKAGES; ?>',
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