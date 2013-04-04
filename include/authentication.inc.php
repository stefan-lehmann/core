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

if (!cjoProp::isBackend() || cjoProp::isSetup()) return;

$LOGIN['username'] = '';
$LOGIN['password'] = '';

if (!cjo_session('UID', 'bool')) {
	cjo_set_session('UID', cjo_post('UID', 'int', 0, true));
	if (cjo_post('LOGIN','bool')) {
    	$LOGIN['username'] = (string) $_POST['LOGIN']['username'];
    	$LOGIN['password'] = (string) $_POST['LOGIN']['password'];
	}
}
if (!cjo_session('ST', 'bool')) {
    cjo_set_session('ST', cjo_post('ST', 'int', 0, true));
}

$cjo_login = new cjoLogin(true);
$cjo_login->setUserTable(TBL_USER);
$cjo_login->setLogin($LOGIN['username'], $LOGIN['password']);
$cjo_login->setUserID('user_id');

if (cjo_get('logout', 'bool')) $cjo_login->setLogout(true);

$cjo_login->setUserQuery("SELECT *
                             FROM 	".TBL_USER."
                             WHERE  user_id='%USR_UID%'");

$cjo_login->setLoginQuery("SELECT *
                              FROM 	".TBL_USER."
                              WHERE login = '%USR_LOGIN%'
                              AND 	psw = '%USR_PSW%'
                              AND   lasttrydate <'".(time()-$CJO['RELOGINDELAY'])."'
                              AND   (login_tries <= '".$CJO['MAXLOGINS']."' OR lasttrydate <'".(time()- (12 * 60 * 60))."')");

if (!$cjo_login->checkLogin()) {

    if (!preg_match('/\/index\.php$/',$_SERVER['PHP_SELF'])) {
        header('Location: index.php');
        exit();
    }

	// login failed
	cjoProp::set('USER', false);
    cjoProp::setPage('login');
    cjoProp::set('PAGE_HEADER', false);

    $sql = new cjoSql();
    $sql->setQuery("SELECT lasttrydate, login_tries FROM ".TBL_USER." WHERE login ='".$LOGIN['username']."'");

    // fehlversuch speichern | login_tries++
    if ($sql->getRows() == 1) {

        $cjo_login_tries = ($sql->getValue('lasttrydate') < (time()- (2 * 60 * 60))) ? "1" : $sql->getValue('login_tries') + 1;

        $update = new cjoSql();
        $update->setTable(TBL_USER);
        $update->setWhere("login = '".$LOGIN['username']."'");
        $update->setValue("login_tries", $cjo_login_tries);
        $update->setValue("lasttrydate", time());
        $update->Update();
    }
}
else {
 
    // gelungenen versuch speichern | login_tries = 0
    if ($LOGIN['username'] != '') {

        $update = new cjoSql();
        $update->setTable(TBL_USER);
        $update->setWhere("login='".$LOGIN['username']."'");
        $update->setValue("login_tries","0");
        $update->setValue("lasttrydate",time());
        $update->Update();
        header ('Location:index.php?'.$_SERVER['QUERY_STRING']);
        exit;
    }
    
    cjoProp::set('USER', $cjo_login->USER);
}




