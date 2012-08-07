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

$sql = new cjoSql();
$qry = "SELECT * FROM ".TBL_USER;
$sql->setQuery($qry);

for ($i=0; $i < $sql->getRows(); $i++) {

    if (!preg_match('/^group_/',$sql->getValue('login'))) {
        if ($oid != $sql->getValue('user_id')) {
            $used_inputs['login'][$sql->getValue('user_id')] = $sql->getValue('login');
            $used_inputs['names'][$sql->getValue('user_id')] = $sql->getValue('name');
        }
        $used_inputs['rights'][$sql->getValue('user_id')] = array_diff(explode('#',$sql->getValue('rights')), array(''));
        $used_inputs['description'][$sql->getValue('user_id')] = array_diff(explode('|',$sql->getValue('description')), array(''));
    }
    else{
        $used_inputs['group'][$sql->getValue('user_id')] = $sql->getValue('login');
        if ($oid != $sql->getValue('user_id')) {
            $used_inputs['names'][$sql->getValue('user_id')] = preg_replace('/^group_/', '', $sql->getValue('login'));
        }
    }
    $sql->next();
}


if ($mode == 'user' && $function == 'delete' && $oid != '') {

    if ($CJO['USER']->getValue("user_id")!=$oid) {
        $sql->flush();
        $results = $sql->getArray("SELECT * FROM ".TBL_USER." WHERE login NOT REGEXP '^group_' AND user_id = '".$oid."' LIMIT 1");
        $sql->flush();
        if ($sql->statusQuery("DELETE FROM ".TBL_USER." WHERE login NOT REGEXP '^group_' AND user_id = '".$oid."' LIMIT 1",
                          $I18N->msg("msg_editor_deleted"))) {
            cjoExtension::registerExtensionPoint('USER_DELETED', $results[0]);
        }           
    }
    else{
        cjoMessage::addError($I18N->msg("msg_editor_notdeleteself"));
    }
    unset($results);
    unset($function);
    unset($mode);
}

if ($mode == 'user' && $function == 'status' && $oid != '') {

    $status = cjo_get('status', 'int');

    $update = new cjoSql();
    $update->setTable(TBL_USER);
    $update->setWhere("user_id='".$oid."' AND login NOT REGEXP '^group_'");
    $update->setValue("status",$status);
    $update->addGlobalUpdateFields();
    if ($update->Update($I18N->msg("msg_user_status_updated"))) {
        cjoExtension::registerExtensionPoint('USER_UPDATED', array('ACTION' => strtoupper($function),
                                                                   'user_id' =>  $oid,
                                                                   'status'  => $status));
    }
    unset($function);
    unset($mode);    
}

if ($mode == 'user' && $function == 'reset_tries' && $oid != '') {

    $update = new cjoSql();
    $update->setTable(TBL_USER);
    $update->setWhere("user_id='".$oid."' AND login NOT REGEXP '^group_'");
    $update->setValue("login_tries",0);
    $update->addGlobalUpdateFields();
    if ($update->Update($I18N->msg("msg_login_tries_reseted"))) {
        cjoExtension::registerExtensionPoint('USER_UPDATED', array('ACTION' => strtoupper($function),
                                                                   'user_id' =>  $oid));
    }
    unset($function);
    unset($mode);
}

if ($mode == 'groups' && $function == 'delete' && $oid != '') {

    $sql->flush();
    $sql->setQuery("SELECT * FROM ".TBL_USER." WHERE login NOT REGEXP '^group_' AND description LIKE '|%".$oid."%|'");

    if ($sql->getRows() == 0) {
        $sql->flush();
        $results = $sql->getArray("SELECT * FROM ".TBL_USER." WHERE login REGEXP '^group_' AND user_id = '$oid' LIMIT 1");
        $sql->flush();
        if ($sql->statusQuery("DELETE FROM ".TBL_USER." WHERE login REGEXP '^group_' AND user_id = '$oid' LIMIT 1",
                          $I18N->msg("msg_group_deleted"))) {
            cjoExtension::registerExtensionPoint('USER_GROUP_DELETED', $results[0]);  
        }                         
    }
    else{
        cjoMessage::addError($I18N->msg("msg_group_has_children"));
    }
    unset($function);
    unset($mode);
}

if (!$mode || $mode == 'groups') {
	include_once($CJO['INCLUDE_PATH'].'/pages/'.$mypage.'/'.$subpage.'_groups.inc.php');
}
if (!$mode || $mode == 'user') {
	include_once($CJO['INCLUDE_PATH'].'/pages/'.$mypage.'/'.$subpage.'_user.inc.php');
}