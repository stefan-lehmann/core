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

if ($function == 'delete') {

	if ($oid == '1') {
	    cjoMessage::addError($I18N->msg("msg_cant_delete_default_template"));
	}
	elseif ($oid != '') {

		$sql = new cjoSql();
		$qry = "SELECT DISTINCT
			   	a.id AS id,
			    	a.clang AS clang,
			   	    a.name AS name,
			   	    t.name AS template_name
			   FROM ".TBL_ARTICLES." a
			   LEFT JOIN ".TBL_TEMPLATES." t
			   ON a.template_id = t.id
			   WHERE a.template_id='".$oid."'";
		$results = $sql->getArray($qry);

		$temp = array();
		foreach ($results as $result) {
				$temp[] = cjoAssistance::createBELink(
			                            '<b>'.$result['name'].'</b> (ID='.$result['id'].')',
                                            array('page' => 'edit',
                                            	  'subpage' => 'settings',
                                            	  'function' => '',
                                            	  'oid' => '',
                                            	  'article_id' => $result['id'],
                                            	  'clang' => $result['clang']));
		}

        if (!empty($temp))
    		cjoMessage::addError($I18N->msg("msg_cant_delete_template_in_use",
    		                                $results[0]['template_name']).'<br />'.implode(' | ',$temp));

		if (!cjoMessage::hasErrors()) {
            $sql->flush();	
		    $results = $sql->getArray("SELECT * FROM ".TBL_TEMPLATES." WHERE id='".$oid."'");
		    $sql->flush();
		    if ($sql->statusQuery("DELETE FROM ".TBL_TEMPLATES." WHERE id='".$oid."'",
			                  $I18N->msg("msg_template_deleted"))) {
    			cjoAssistance::updatePrio(TBL_TEMPLATES);
                cjoExtension::registerExtensionPoint('TEMPLATE_DELETED', $results[0]);
			}
		}
	}
	unset($function);
}

if ($function == "add" || $function == "edit" ) {

	//Form
	$form = new cjoForm();
	$form->setEditMode($oid);
    //$form->debug = true;

	//Fields
	$fields['name'] = new textField('name', $I18N->msg("label_name"));
	$fields['name']->addValidator('notEmpty', $I18N->msg("msg_name_notEmpty"), false, false);

	$labels = array('application_gallery',
					'application_view_gallery_star',
					'application_view_list2',
					'application_view_tile',
					'page_white',
					'page_white_code_red',
					'page_white_text');
	sort($labels);

	$fields['label'] = new selectField('label', $I18N->msg("label_icon"));
	$fields['label']->addOption('--','');
	foreach($labels as $label) {
			$fields['label']->addOption($label,$label);
	}
	$fields['label']->addAttribute('size', '1');

	$fields['active_hidden'] = new hiddenField('active');
	$fields['active_hidden']->setValue('0');
	$fields['active'] = new checkboxField('active', '&nbsp;',  array('style' => 'width: auto;'));
	$fields['active']->addBox($I18N->msg("label_active"), '1');

	$fields['content'] = new codeField('content', $I18N->msg("label_input"));
	$fields['content']->addAttribute('rows', '30');

    if (count($CJO['CTYPE']) > 0) {
    	$fields['ctypes'] = new selectField('ctypes', $I18N->msg("label_ctype_connection"));
    	$fields['ctypes']->setMultiple();
    	$fields['ctypes']->setValueSeparator('|');
    	$fields['ctypes']->activateSave(false);

    	foreach($CJO['CTYPE'] as $key=>$val) {
    		$fields['ctypes']->addOption($val,$key);
    	}
    	$fields['ctypes']->addAttribute('size', count($CJO['CTYPE'])+1);
    } else {
    	$fields['ctypes'] = new hiddenField('ctypes');
    	$fields['ctypes']->setValue('0');
    }


	if ($function == 'add') {

		$oid = '';

		$fields['createdate_hidden'] = new hiddenField('createdate');
		$fields['createdate_hidden']->setValue(time());

		$fields['createuser_hidden'] = new hiddenField('createuser');
		$fields['createuser_hidden']->setValue($CJO['USER']->getValue("name"));
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
	}

    /**
     * Do not delete translate values for cjoI18N collection!
     * [translate: label_add_template]
     * [translate: label_edit_template]
     */
	$section = new cjoFormSection(TBL_TEMPLATES, $I18N->msg("label_".$function."_template"), array ('id' => $oid));

	$section->addFields($fields);
	$form->addSection($section);
	$form->show();

	if ($form->validate()) {
		if ($function == "add") {
			$oid = $form->last_insert_id;
			cjoAssistance::updatePrio(TBL_TEMPLATES,$oid,time());
		}

        $ctypes = cjo_post('ctypes','array', array());

    	$update = new cjoSql();
    	$update->setTable(TBL_TEMPLATES);
    	$update->setValue("ctypes",'|'.@implode('|',$ctypes).'|');

    	if ($function == "add") {
    		$update->setWhere("id='".$oid."'");
    		$update->addGlobalCreateFields();
    	}
    	else {
    		$update->setWhere("id='".$oid."'");
    		$update->addGlobalUpdateFields();

    	}
    	$update->Update();

		cjoGenerate::generateTemplates($oid);
		
		if (!cjoMessage::hasErrors()) {
		    
		    if ($function == "add") {
                cjoExtension::registerExtensionPoint('TEMPLATE_ADDED', array ("id" => $oid));
            } else {
                cjoExtension::registerExtensionPoint('TEMPLATE_UPDATED', array ("id" => $oid));
            }
		    
		    if (cjo_post('cjoform_save_button', 'boolean')) {
    			if ($function == 'edit') {
    			    cjoMessage::addSuccess($I18N->msg("msg_template_updated", cjo_post('name', 'string')));
    			}
    			else {
    			    cjoMessage::addSuccess($I18N->msg("msg_template_added", cjo_post('name', 'string')));
    			}
    			unset($function);
		    }
		}
	}
}

if ($function != '') return;

//LIST Ausgabe
$list = new cjolist("SELECT * FROM ".TBL_TEMPLATES, 'prior', 'ASC', '', 100);
//$list->debug = true;
$list->setName('TEMPLATES_LIST');
$list->setAttributes('id="templates_list"');

$cols['icon'] = new resultColumn('label',
								 cjoAssistance::createBELink(
								 			  '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("label_add_template").'" />',
											  array('function' => 'add', 'oid' => ''),
											  $list->getGlobalParams(),
											  'title="'.$I18N->msg("label_add_template").'"'),
								'sprintf',
								'<img src="img/silk_icons/%s.png" alt="true" />');

$cols['icon']->addCondition('label', '', '<img src="img/silk_icons/layout.png" alt="true" />');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_SORT);

$cols['id'] = new resultColumn('id', $I18N->msg("label_id"));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', $I18N->msg("label_name").' ');

$cols['prio'] = new resultColumn('prior', $I18N->msg('label_prio'));
$cols['prio']->setHeadAttributes('class="icon"');
$cols['prio']->setBodyAttributes('class="icon dragHandle tablednd"');
$cols['prio']->setBodyAttributes('title="'.$I18N->msg("label_change_prio").'"');
$cols['prio']->addCondition('prior', array('!=', ''), '<strong>%s</strong>');

$cols['active'] = new resultColumn('active', $I18N->msg("label_active"));
$cols['active']->addCondition('active', 1, '<img src="img/silk_icons/accept.png" alt="true" />');
$cols['active']->addCondition('active', 0, '&nbsp;');
$cols['active']->setBodyAttributes('width="95" style="text-align: center"');
$cols['active']->addOption(OPT_SORT);

$cols['ctypes'] = new resultColumn('ctypes', $I18N->msg("label_ctype_connection"), 'replace_array', array($CJO['CTYPE'],'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
$cols['ctypes']->setBodyAttributes('width="300"');

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="2"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%id%'));

$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%id%'));

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

//Tabelle anzeigen
$list->show(false);

?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function() {

		var update_id, curr_row_id, old_prio, new_prio;

        $("table#templates_list").tableDnD({
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
						   'table': '<?php echo TBL_TEMPLATES; ?>',
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