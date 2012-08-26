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

/**
 * Do not delete translate values for i18n collection!
 * [translate: label_no_password]
 * [translate: label_allow_to_all]
 */

if ($function == "delete") {

    if ($oid != '') {

        $sql = new cjoSql();
        $qry = "SELECT
                    name, id, clang,
                    SUBSTRING_INDEX(SUBSTRING_INDEX(RTRIM(path), '|', -2), '|', 1) AS article_id
               FROM
                    ".TBL_ARTICLES."
               WHERE
                    type_id = '".$oid."'";

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
        }

        if (!empty($temp))
            cjoMessage::addError($I18N->msg("msg__still_used").'<br/>'.implode(' | ',$temp));

        if (!cjoMessage::hasErrors()) {
            $sql->flush();
            $result = $sql->getArray("SELECT * FROM ".TBL_ARTICLES_TYPE." WHERE type_id='".$oid."'");
            $sql->flush();            
            if ($sql->statusQuery("DELETE FROM ".TBL_ARTICLES_TYPE." WHERE type_id = '".$oid."' LIMIT 1",
                              $I18N->msg("msg_article_type_deleted")) &&
                $sql->statusQuery("UPDATE ".TBL_ARTICLES." SET type_id = '1' WHERE type_id = '".$oid."'",
                              $I18N->msg("msg_article_type_deleted"))) {
                cjoExtension::registerExtensionPoint('ARTICLE_TYPE_DELETED', $result[0]); 
            }
        }
        unset($function);
}

if ($function == "add" || $function == "edit" ) {

    //Form
    $form = new cjoForm();
    $form->setEditMode($oid);

    //Fields
    $fields['name'] = new textField('name', $I18N->msg("label_type_name"));
    $fields['name']->addValidator('notEmpty', $I18N->msg("msg_type_name_notEmpty"), false, false);

    $fields['description'] = new textAreaField('description', $I18N->msg("label_type_description"), array('rows' => 2));
    $fields['description']->addValidator('notEmpty', $I18N->msg("msg_type_description_notEmpty"), false, false);

    $hidden['groups_hidden'] = new hiddenField('groups');
	$hidden['groups_hidden']->setValue('0');

    if ($CJO['ADDON']['status']['community']) {

		$sel_group = cjoCommunityGroups::getSelectGroups($oid);
		$sel_group->setSelected(cjo_post('groups', 'array'));

		if (cjo_post('cjo_form_name','string') == $form->getName()) {
			$group_ids = cjo_post('groups', 'array');
		}
		else {
			$sql = new cjoSql();
	        $qry = "SELECT groups FROM ".TBL_ARTICLES_TYPE." WHERE type_id = '".$oid."'";
	        $sql->setQuery($qry);
	        $group_ids = cjoAssistance::toArray($sql->getValue('groups'));
		}
		foreach ($group_ids as $val) {
			$sel_group->setSelected($val);
		}

		$fields['groups'] = new readOnlyField('groups[]', $I18N->msg('label_groups'));
		$fields['groups']->setValue($sel_group->get());
		$fields['groups']->addValidator('notEmpty', $I18N_10->msg('err_notEmpty_groups'));
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
     * Do not delete translate values for i18n collection!
     * [translate: label_add_type]
     * [translate: label_edit_type]
     */
    $section = new cjoFormSection(TBL_ARTICLES_TYPE, $I18N->msg('label_'.$function."_type"), array ('type_id' => $oid));

    $section->addFields($fields);
    $form->addSection($section);
    $form->addFields($hidden);
    $form->show();

    if ($form->validate()) {
		$oid = ($function == "add") ? $form->last_insert_id : $oid;

		$update = new cjoSql();
        $update->setTable(TBL_ARTICLES_TYPE);
        $update->setWhere("type_id='".$oid."'");
        $update->setValue("groups",implode('|',cjo_post('groups', 'array')));
        $update->Update();

        if (cjo_post('cjoform_save_button', 'boolean')) {
    		if ($function == 'edit') {
    		    cjoMessage::addSuccess($I18N->msg("msg_article_type_updated"));
    		}
    		else {
    		    cjoMessage::addSuccess($I18N->msg("msg_article_type_saved"));
    		}
    	}
    	if ($function == "add") {
			cjoAssistance::updatePrio(TBL_ARTICLES_TYPE, $oid, time(), 'type_id');
            cjoExtension::registerExtensionPoint('ARTICLES_TYPE_ADDED', array('id' => $oid));
		}
		else {
            cjoExtension::registerExtensionPoint('ARTICLES_TYPE_UPDATED', array('id' => $oid));
		}
		if (cjo_post('cjoform_save_button', 'boolean')) {
		    unset($function);
		}
    }
}
if (!$function) {

    //LIST Ausgabe
    $list = new cjoList("SELECT * FROM ".TBL_ARTICLES_TYPE,
    	                "prior",
    					'ASC',
    	                '',
    	                100);
    $list->setName('TYPES_LIST');
    $list->setAttributes('id="types_list"');

    $cols['icon'] = new staticColumn('<img src="img/silk_icons/lock.png" alt="" />',
    	                             cjoAssistance::createBELink(
    	                             			  '<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
    	                                           array('function' => 'add', 'oid' => ''),
                                                   $list->getGlobalParams(),
    	                                          'title="'.$I18N->msg("button_add").'"')
    	                            );

    $cols['icon']->setHeadAttributes('class="icon"');
    $cols['icon']->setBodyAttributes('class="icon"');
    $cols['icon']->delOption(OPT_SORT);

    $cols['type_id'] = new resultColumn('type_id', $I18N->msg("label_id"));
    $cols['type_id']->setHeadAttributes('class="icon"');
    $cols['type_id']->setBodyAttributes('class="icon"');

    $cols['name'] = new resultColumn('name', $I18N->msg("label_type_name").' ');

    $cols['description'] = new resultColumn('description', $I18N->msg("label_type_description").' ');
    $cols['description']->delOption(OPT_SORT);

    $cols['prio'] = new resultColumn('prior', $I18N->msg('label_prio'),'sprintf','<strong>%s</strong>');
    $cols['prio']->setHeadAttributes('class="icon"');
    $cols['prio']->setBodyAttributes('class="icon dragHandle tablednd"');
    $cols['prio']->setBodyAttributes('title="'.$I18N->msg("label_change_prio").'"');

    $replace_groups = array();
    $sql = new cjoSql();
    $sql->setQuery("SELECT * FROM ".TBL_COMMUNITY_GROUPS);
    for ($i=0; $i<$sql->getRows(); $i++) {
    	$id = $sql->getValue('id');
    	$name = $sql->getValue('name');
    	$replace_groups[$id] = $name;
    	$sql->next();
    }

    $cols['groups'] = new resultColumn('groups', $I18N->msg('label_groups'), 'replace_array', array($replace_groups,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));
    $cols['groups']->setBodyAttributes('width="300"');
    $cols['groups']->delOption(OPT_ALL);

    // Bearbeiten link
    $img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
    $cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
    $cols['edit']->setHeadAttributes('colspan="2"');
    $cols['edit']->setBodyAttributes('width="16"');
    $cols['edit']->setParams(array ('function' => 'edit', 'oid' => '%type_id%'));

    $img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
    $cols['delete'] = new staticColumn($img, NULL);
    $cols['delete']->setBodyAttributes('width="60"');
    $cols['delete']->setBodyAttributes('class="cjo_delete"');
    $cols['delete']->setParams(array ('function' => 'delete', 'oid' => '%type_id%'));

    //Spalten zur Anzeige hinzufügen
    $list->addColumns($cols);

    //Tabelle anzeigen
    $list->show(false);
}
?>
<script type="text/javascript">
/* <![CDATA[ */

	$(function() {

		var update_id, curr_row_id, old_prio, new_prio;

        $("table#types_list").tableDnD({
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
						   'table': '<?php echo TBL_ARTICLES_TYPE; ?>',
						   'type_id': update_id,
						   'new_prio' : new_prio,
						   'col' : 'type_id' },
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