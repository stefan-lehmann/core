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

if ($mode) {

	$sql = new cjoSql();
	$qry = "SELECT * FROM ".TBL_MODULES." ORDER BY prior";
	$modultyps = $sql->getArray($qry);

	foreach($modultyps as $modultyp) {
		if ($modultyp['id'] == $oid) {
			$curr_modultyp = $modultyp;
			break;
		}
	}

	$modes 	= array();

	$modes[] = array('settings',
					 'title' => $I18N->msg("label_modul_settings"),
					 'query_str' => 'page='.$mypage.'&subpage='.$subpage.'&mode=settings&function=edit&oid='.$oid,
					 'important' => true);

	if ($function == 'add' && $mode == 'settings') {

        /**
         * Do not delete translate values for cjoI18N collection!
         * [translate: label_add_module]
         * [translate: label_edit_module]
         */
		$modes[0]['title'] = $I18N->msg("label_".$function."_module");
	}
	else {
		$modes[] = array('logic',
						 'title' => $I18N->msg("label_modul_logic"),
						 'query_str' => 'page='.$mypage.'&subpage='.$subpage.'&mode=logic&function=edit&oid='.$oid,
						 'important' => true);

		$modes[] = array('layout',
						 'title' => $I18N->msg("label_modul_layouts"),
						 'query_str' => 'page='.$mypage.'&subpage='.$subpage.'&mode=layout&function=edit&oid='.$oid,
						 'important' => true);

		$modes[] = array('actions',
					 	 'title' => $I18N->msg("label_modul_actions"),
						 'query_str' => 'page='.$mypage.'&subpage='.$subpage.'&mode=actions&function=edit&oid='.$oid,
						 'important' => true);

		echo '<h2 class="layout_name">
				<span>'.$I18N->msg("label_modul").':</span> '.
				$curr_modultyp['name'].'
				<span>(ID='.$curr_modultyp['id'].')</span>
			  </h2>';
	}
	cjoSubpages::setTabs($mode, $modes, $mypage);

	include_once($CJO['INCLUDE_PATH'].'/pages/'.$mypage.'/'.$subpage.'_'.$mode.'.inc.php');

	return;
}

if ($function == 'delete') {

	if ($oid != '') {

		$sql = new cjoSql();
		$qry = "SELECT DISTINCT
					 	s.article_id AS id,
					 	(SELECT name FROM ".TBL_ARTICLES." WHERE id=s.article_id AND clang=s.clang) AS name,
					  	s.clang AS clang,
						m.name AS modul_name
				FROM ".TBL_ARTICLES_SLICE." s
				LEFT JOIN ".TBL_MODULES." m
				ON s.modultyp_id = m.id
				WHERE s.modultyp_id='".$oid."'";
		$results = $sql->getArray($qry);

		$temp = array();
		foreach ($results as $result) {
			$temp[] = cjoAssistance::createBELink(
			                            '<b>'.$result['name'].'</b> (ID='.$result['id'].')',
										 array('page' => 'edit',
										 	   'subpage' => 'content',
										       'function' => '',
										       'oid' => '',
										 	   'article_id' => $result['id'],
										 	   'clang' => $result['clang'],
										 	   'mode' => 'edit'));
		}

        if (!empty($temp))
			cjoMessage::addError($I18N->msg("msg_module_cannot_be_deleted",
			                     $results[0]['modul_name']).'<br />'.implode(' | ',$temp));

		if (!cjoMessage::hasErrors()) {

			$sql->flush();
			$sql->statusQuery("DELETE FROM ".TBL_MODULES." WHERE id='".$oid."'",
			                  $I18N->msg("msg_module_deleted"));

			$sql->statusQuery("DELETE FROM ".TBL_MODULES_ACTIONS." WHERE module_id='".$oid."'",
			                  $I18N->msg("msg_all_actions_deleted_from_modul"));

			$path['path'] 	  			 = $CJO['ADDON']['settings']['developer']['edit_path'].'/'.
										   $CJO['TMPL_FILE_TYPE'];
		    $path['type'] 	  			 = $path['path'].'/'.$type;
			$path['type_template'] 		 = $path['type'].'/'.$template.'.template';
		    $path['type_ctype'] 		 = $path['type'].'/'.$ctype.'.ctype';
		    $path['type_template_ctype'] = $path['type_template'].'/'.$ctype.'.ctype';

			$path = array_reverse($path);
			foreach($path as $key=>$val) {
				@unlink(cjoModulTemplate::getTemplatePath($oid,$template,$ctype,'input'));
				@unlink(cjoModulTemplate::getTemplatePath($oid,$template,$ctype,'output'));
				@rmdir($val);
				unset($CJO['ADDON']['settings']['developer']['tmpl']['html']);
			}
			cjoMessage::addSuccess($I18N->msg("msg_modul_all_layout_deleted"));
			cjoAssistance::updatePrio(TBL_MODULES);
            cjoExtension::registerExtensionPoint('MODULE_DELETED', $results);
		}
	}
	unset($function);
}

	//LIST Ausgabe
$list = new cjolist("SELECT *, id AS actions FROM ".TBL_MODULES, "prior", 'ASC', '', 100);
//$list->debug = true;
$list->setName('MODULES_LIST');
$list->setAttributes('id="modules_list"');

$cols['icon'] = new staticColumn('<img src="img/silk_icons/application_form.png" alt="" />',
								 cjoAssistance::createBELink(
								 		'<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
								         array('mode'=>'settings', 'function' => 'add', 'oid' => ''),
								         $list->getGlobalParams(),
										'title="'.$I18N->msg("button_add").'"'));

$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_SORT);

$cols['id'] = new resultColumn('id', $I18N->msg("label_id"));
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', $I18N->msg("label_name"));

$cols['prio'] = new resultColumn('prior', $I18N->msg('label_prio'));
$cols['prio']->setHeadAttributes('class="icon"');
$cols['prio']->setBodyAttributes('class="icon dragHandle tablednd"');
$cols['prio']->setBodyAttributes('title="'.$I18N->msg("label_change_prio").'"');
$cols['prio']->addCondition('prior', array('!=', ''), '<strong>%s</strong>');

$replace_templates = array();
$replace_templates[0] = $I18N->msg("label_rights_all").' '.$I18N->msg("title_templates");
$sql = new cjoSql();
$qry = "SELECT id, name FROM ".TBL_TEMPLATES." ORDER BY prior";
$sql->setQuery($qry);
for ($i=0;$i<$sql->getRows();$i++) {
	$replace_templates[$sql->getValue('id')] = $sql->getValue('name');
	$sql->next();
}
$cols['templates'] = new resultColumn('templates', $I18N->msg("label_template_connection"), 'replace_array', array($replace_templates,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));

$cols['ctypes'] = new resultColumn('ctypes', $I18N->msg("label_ctype_connection"), 'replace_array', array($CJO['CTYPE'],'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));

$replace_actions = array();
$sql = new cjoSql();
$qry = "SELECT *, (SELECT name FROM ".TBL_ACTIONS." WHERE id=ma.action_id LIMIT 1) AS name FROM ".TBL_MODULES_ACTIONS." ma";
$sql->setQuery($qry);
for ($i=0;$i<$sql->getRows();$i++) {
    $replace_actions[$sql->getValue('module_id')] .= $replace_actions[$sql->getValue('module_id')] ? ', '.$sql->getValue('name') : $sql->getValue('name');
    $sql->next();
}
$cols['actions'] = new resultColumn('actions', $I18N->msg("label_actions_connection"), 'replace_array', array($replace_actions,'%s', 'delimiter_in' => '|','delimiter_out' => ', ' ));

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="2"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('mode'=>'settings', 'function' => 'edit', 'oid' => '%id%'));

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

        $("table#modules_list").tableDnD({
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
						   'table': '<?php echo TBL_MODULES; ?>',
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