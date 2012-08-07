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

$group_id = ($group_id != '') ? $group_id : 0;

if ($function == "delete") {
    cjoCommunityGroups::deleteGroups($oid, $group_id);
}

$users_number = cjoCommunityGroups::countUsers();

$qry = "SELECT
			*, id AS users, id as article_types
		FROM ".TBL_COMMUNITY_GROUPS."
		WHERE re_id='".$group_id."'" ;

$list = new cjolist($qry, 'name', 'ASC', 'name', 100);
$list->setName('GROUP_LIST');
$list->setAttributes('id="group_list"');
//$list->debug = true;

$add_button = cjoAssistance::createBELink(
							'<img src="img/silk_icons/add.png" alt="'.$I18N->msg("button_add").'" />',
							array('group_id'=> $group_id, 'function' => 'add', 'oid' => ''),
							$list->getGlobalParams(),
                            'title="'.$I18N->msg("button_add").'"');

$cols['id'] = new resultColumn('id', $add_button, 'sprintf', '<span>%s</span>');
$cols['id']->setHeadAttributes('class="icon"');
$cols['id']->setBodyAttributes('class="icon cjo_id"');
$cols['id']->delOption(OPT_ALL);

$cols['name'] = new resultColumn('name', $I18N_10->msg('label_group_name'));
$cols['name']->setBodyAttributes('class="large_item"');
$cols['name']->setParams(array ('group_id' => '%id%'));
$cols['name']->delOption(OPT_ALL);

$cols['article_types'] = new resultColumn('article_types', $I18N_10->msg('label_article_types'), 'call_user_func',
								  		  array('cjoCommunityGroups::getArticleTypesOfGroup',array('%s','names')));
$cols['article_types']->delOption(OPT_ALL);

$cols['users'] = new resultColumn('users', $I18N_10->msg('label_user'), 'replace', array($users_number,'--'));
$cols['users']->setBodyAttributes('width="150"');
$cols['users']->delOption(OPT_ALL);

// Bearbeiten link
$img = '<img src="img/silk_icons/page_white_edit.png" title="'.$I18N->msg("button_edit").'" alt="'.$I18N->msg("button_edit").'" />';
$cols['edit'] = new staticColumn($img, $I18N->msg("label_functions"));
$cols['edit']->setHeadAttributes('colspan="2"');
$cols['edit']->setBodyAttributes('width="16"');
$cols['edit']->setParams(array ('function' => 'edit', 'group_id'=> $group_id , 'oid' => '%id%'));

$img = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn($img, NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'group_id'=> $group_id , 'oid' => '%id%'));

$sql = new cjoSql();
$sql->setQuery("SELECT re_id FROM ".TBL_COMMUNITY_GROUPS." WHERE id='".$group_id."'");
$re_id = $sql->getValue('re_id');

$rowAttributes = ' onclick="location.href=\'index.php?page='.$mypage.'&amp;subpage='.$subpage.'&amp;clang='.$clang.'&amp;group_id='.$re_id.'\';" ' .
	             ' class="cat_uplink" title="'.$I18N->msg("label_level_up").'"';

$up_link  = '            <tr'.$rowAttributes.' valign="middle" class="nodrop">'."\r\n".
            '              <td class="icon" height="20"> ID </td>'."\r\n".
            '              <td colspan="'.(count($cols)-1).'" height="20">'."\r\n".
            '              	<img src="img/silk_icons/level_up.png" alt="up" />'."\r\n".
            '              </td>'."\r\n".
            '            </tr>'."\r\n";

$sql->flush();
$sql->setQuery("SELECT id FROM ".TBL_COMMUNITY_GROUPS." WHERE re_id='".$group_id."'");
$children = $sql->getRows();

if (!empty($children) && $group_id) $list->setVar(LIST_VAR_BEFORE_DATA, $up_link);
if (empty($children)) $list->setVar(LIST_VAR_NO_DATA, $up_link);

$list->addColumns($cols);
$list->show(false);
