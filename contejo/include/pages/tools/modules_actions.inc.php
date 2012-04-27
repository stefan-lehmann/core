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
 * @version     2.6.0
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

$action_id = cjo_get('action_id', 'int');

if ($function == 'add') {
		$sql = new cjoSql();
		$sql->statusQuery("INSERT INTO ".TBL_MODULES_ACTIONS." SET module_id='".$oid."', action_id='".$action_id."'",
		                  $I18N->msg("msg_action_updated"));
        cjoExtension::registerExtensionPoint('MODULE_UPDATED', array('ACTION' => 'ACTION_ADDED',
                                                                     'moduletyp_id' => $oid,
                                                                     'action_id=' => $action_id));      
}
elseif ($function == 'remove') {
		$sql = new cjoSql();
		$sql->statusQuery("DELETE FROM ".TBL_MODULES_ACTIONS." WHERE module_id='".$oid."' AND action_id='".$action_id."' LIMIT 1",
		                  $I18N->msg("msg_action_deleted_from_modul"));
		                  
        cjoExtension::registerExtensionPoint('MODULE_UPDATED', array('ACTION' => 'ACTION_REMOVED',
                                                                     'moduletyp_id' => $oid,
                                                                     'action_id=' => $action_id));   		                  
}

//LIST Ausgabe
$list = new cjolist("SELECT " .
					"	a.id AS action_id, " .
					"	a.name AS name, " .
					" 	CONCAT_WS('|'," .
					"		IF(a.prepost=1,'POST','PRE')," .
					"		IF(a.sadd=1,'ADD',NULL)," .
					"		IF(a.sedit=1,'EDIT',NULL)," .
					"		IF(a.sdelete=1,'DELETE',NULL)" .
					"	) AS status " .
					"FROM ".TBL_ACTIONS." a " .
					"LEFT JOIN ".TBL_MODULES_ACTIONS." ma " .
					"ON ma.action_id = a.id " .
					"WHERE ma.module_id='".$oid."'",
					"	name",
					"ASC",
					" ma.module_id",
					100);

//$list->debug = true;
$cols['icon'] = new staticColumn('<img src="img/silk_icons/lightning.png" alt="" />', '');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_ALL);

$cols['name'] = new resultColumn('name', $I18N->msg("label_actions"));
$cols['name']->setHeadAttributes('colspan="3"');
$cols['name']->setBodyAttributes('style="width: 60%"');
$cols['name']->setParams(array ('page' => 'tools', 'subpage' => 'actions', 'function' => 'edit', 'oid' => '%action_id%'));
$cols['name']->delOption(OPT_ALL);

$cols['status'] = new resultColumn('status', NULL, 'sprintf', '[%s]');
$cols['status']->delOption(OPT_ALL);

// Lösch link
$cols['delete'] = new staticColumn('<img src="img/silk_icons/cross.png" alt="'.$I18N->msg("button_delete").'" />', NULL);
$cols['delete']->setBodyAttributes('class="icon"');
$cols['delete']->setParams(array ('mode'=>'actions', 'function' => 'edit', 'function' => 'remove', 'oid' => $oid, 'action_id' => '%action_id%', '#' => 'function'));

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);

$add_action_sel = new cjoSelect();
$add_action_sel->setName("action_id");
$add_action_sel->setSize(1);
$add_action_sel->setStyle('width: 100%');

$sql = new cjoSql();
$qry = "SELECT action_id as id FROM ".TBL_MODULES_ACTIONS." WHERE module_id='".$oid."'";
$used_actions = $sql->getArray($qry);

$sql->flush();
$qry = "SELECT DISTINCT " .
			 "	id, name, " .
			 " 	CONCAT_WS('|'," .
			 "		IF(prepost=1,'POST','PRE')," .
			 "		IF(sadd=1,'ADD',NULL)," .
			 "		IF(sedit=1,'EDIT',NULL)," .
			 "		IF(sdelete=1,'DELETE',NULL)" .
			 "	) AS status " .
			 "FROM ".TBL_ACTIONS." " .
			 "ORDER BY name";
$sql->setQuery($qry);

for ($i=0; $i<$sql->getRows(); $i++) {
    if (array_search(array('id'=>$sql->getValue("id")), $used_actions) === false) {
        $add_action_sel->addOption($sql->getValue("name").' ['.$sql->getValue("status").']',$sql->getValue("id"));
    }
    $sql->next();
}

$add_action = '<tr>' .
			  '	<td class="icon">&nbsp;</td>' .
			  '	<td colspan="2">'.$add_action_sel->get().'</td>' .
			  '	<td class="icon"><input type="image" src="img/silk_icons/add.png" title="'.$I18N->msg("button_add").'" value="'.$I18N->msg("button_add").'" alt="+" /></td>' .
			  '</tr>';

$list->setVar(LIST_VAR_BEFORE_DATAHEAD, '<a name="function"></a>');
$list->setVar(LIST_VAR_AFTER_DATA, $add_action);
$list->setVar(LIST_VAR_NO_DATA, $I18N->msg("msg_no_actions_connected_to_module"));

//Tabelle anzeigen
echo '<form action="index.php#function" method="get">' .
	'<input type="hidden" name="page" value="'.$mypage.'" />' .
	'<input type="hidden" name="subpage" value="'.$subpage.'" />' .
	'<input type="hidden" name="oid" value="'.$oid.'" />' .
	'<input type="hidden" name="function" value="'.$function.'" />' .
	'<input type="hidden" name="mode" value="'.$mode.'" />' .
	'<input type="hidden" name="function" value="add" />';

$list->show(false);
echo  '</form>';