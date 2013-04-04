<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  comments
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

if ($function == 'to_spam' ||
	$function == 'to_ham' ||
	$function == 'delete') {

	require_once $CJO['ADDON']['settings'][$mypage]['b8'];

	$sql = new cjoSql();
	$b8 = new b8(array('mysqlRes' => $sql->identifier, 'tableName' => TBL_COMMENTS_B8));

	switch($function){
		case 'to_spam' :
			$b8->unlearn(cjo_get('token','string'), 'ham');
			$b8->learn(cjo_get('token','string'), 'spam');
			cjoMessage::addSuccess(cjoAddon::translate(7,"msg_token_made_spam", cjo_get('token','string')));
			break;
		case 'to_ham' :
			$b8->unlearn(cjo_get('token','string'), 'spam');
			$b8->learn(cjo_get('token','string'), 'ham');
			cjoMessage::addSuccess(cjoAddon::translate(7,"msg_token_made_ham", cjo_get('token','string')));
			break;
		default:
			$b8->unlearn(cjo_get('token','string'), 'spam');
			$b8->unlearn(cjo_get('token','string'), 'ham');
			cjoMessage::addSuccess(cjoAddon::translate(7,"msg_token_unlearned", cjo_get('token','string')));
	}
    unset($function);
}


//LIST Ausgabe
$sql = "SELECT
			*,
			IF(SUBSTRING_INDEX(count,' ',1) > 0,1,0) AS ham,
			IF(SUBSTRING_INDEX(SUBSTRING_INDEX(count,' ',-2),' ',1) > 0,1,0) AS spam
		FROM
			".TBL_COMMENTS_B8."
		WHERE
			token NOT LIKE 'bayes%'";

$list = new cjoList($sql, '', '', '', 60);
//$list-> debug = 1;

$cols['icon'] = new staticColumn('','');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');

$cols['token'] = new resultColumn('token', cjoAddon::translate(7,'label_token'));

$ham = '<img src="img/silk_icons/accept.png" title="'.cjoAddon::translate(7,"label_to_spam").'" alt="'.cjoI18N::translate("label_ham").'" />';
$spam = '<img src="img/silk_icons/exclamation.png" title="'.cjoAddon::translate(7,"label_to_ham").'" alt="'.cjoI18N::translate("label_spam").'" />';
$cols['count'] = new resultColumn('count', cjoAddon::translate(7,'label_status'));
$cols['count']->addCondition('ham', '1', $ham, array ('function' => 'to_spam', 'token' => '%token%'));
$cols['count']->addCondition('spam', '1', $spam, array ('function' => 'to_ham', 'token' => '%token%'));
$cols['count']->setBodyAttributes('width="40"');
$cols['count']->setOptions(OPT_SORT);


$img = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
$cols['delete'] = new staticColumn($img, cjoI18N::translate("label_functions"));
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->setParams(array ('function' => 'delete', 'token' => '%token%'));

$list->addColumns($cols);
$list->show();