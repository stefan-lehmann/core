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

if (!$CJO['CONTEJO'] || $CJO['SETUP']) return;

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

$CJO['USER'] = array('LOGIN' => false, 'BACKEND' => true);

$CJO['LOGIN'] = new cjoLogin();
$CJO['LOGIN']->setUserTable(TBL_USER);
$CJO['LOGIN']->setLogin($LOGIN['username'], $LOGIN['password']);
$CJO['LOGIN']->setUserID('user_id');

if (cjo_get('logout', 'bool')) $CJO['LOGIN']->setLogout(true);

$CJO['LOGIN']->setUserQuery("SELECT *
                             FROM 	".TBL_USER."
                             WHERE  user_id='%USR_UID%'");

$CJO['LOGIN']->setLoginQuery("SELECT *
                              FROM 	".TBL_USER."
                              WHERE login = '%USR_LOGIN%'
                              AND 	psw = '%USR_PSW%'
                              AND   lasttrydate <'".(time()-$CJO['RELOGINDELAY'])."'
                              AND   (login_tries <= '".$CJO['MAXLOGINS']."' OR lasttrydate <'".(time()- (12 * 60 * 60))."')");

if (!$CJO['LOGIN']->checkLogin()) {

    if (!preg_match('/\/index\.php$/',$_SERVER['PHP_SELF'])) {
        header('Location: index.php');
        exit();
    }

	// login failed
	$CJO['USER']        = false;
	$cur_page['page']   = 'login';
	$cur_page['header'] = false;

    $sql = new cjoSql();
    $sql->setQuery("SELECT lasttrydate, login_tries FROM ".TBL_USER." WHERE login ='".$LOGIN['username']."'");

    // fehlversuch speichern | login_tries++
    if ($sql->getRows() == 1) {

        $login_tries = ($sql->getValue('lasttrydate') < (time()- (2 * 60 * 60))) ? "1" : $sql->getValue('login_tries') + 1;

        $update = new cjoSql();
        $update->setTable(TBL_USER);
        $update->setWhere("login = '".$LOGIN['username']."'");
        $update->setValue("login_tries", $login_tries);
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

    $CJO['USER'] = $CJO['LOGIN']->USER;
}
