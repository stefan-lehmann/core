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
return;
if (cjoProp::isBackend() || !cjoProp::getArticleId()) return;

if (cjoLogin::isBackendLogin()) {
        cjoProp::set('ONLINE_FROM_TO_ENABLED',false);
        cjoGenerate::deleteGeneratedArticles(array('alist','aspath','article')); 
        cjoGenerate::generateTemplates();       
}

$LOGIN['username'] = (isset($_POST['LOGIN']['username'])) ? (string) $_POST['LOGIN']['username'] : '';
$LOGIN['password'] = (isset($_POST['LOGIN']['password'])) ? (string) $_POST['LOGIN']['password'] : '';

$CJO['USER'] = array('LOGIN' => false, 'BACKEND' => false);

if (cjoAddon::isActivated('community') && (
    cjo_session('UID', 'int') ||
    $LOGIN['username'] != "" ||
    $LOGIN['password'] != "")) {

    $CJO['LOGIN'] = new cjoLogin();
    $CJO['LOGIN']->setUserTable(TBL_COMMUNITY_USER);
    $CJO['LOGIN']->setLogin($LOGIN['username'], $LOGIN['password']);
    $CJO['LOGIN']->setUserID("id");

    if ($CJO['LOGOUT'] || cjo_get('logout', 'bool')) $CJO['LOGIN']->setLogout(true);

    $CJO['LOGIN']->setUserQuery("SELECT DISTINCT
                                     id, username, status, activation,
                                     (SELECT GROUP_CONCAT(group_id SEPARATOR '|' )
                                     FROM ".TBL_COMMUNITY_UG."
                                     WHERE user_id=id
                                     GROUP BY user_id) AS groups
                                 FROM     ".TBL_COMMUNITY_USER."
                                 WHERE     id='%USR_UID%'");

    $CJO['LOGIN']->setLoginQuery("SELECT DISTINCT
                                     id, username, status, activation,
                                     (SELECT GROUP_CONCAT(group_id SEPARATOR '|' )
                                     FROM ".TBL_COMMUNITY_UG."
                                     WHERE user_id=id
                                     GROUP BY user_id) AS groups
                                 FROM     ".TBL_COMMUNITY_USER."
                                 WHERE    username='%USR_LOGIN%'
                                 AND    password='%USR_PSW%'
                                 AND    lasttrydate < '".(time()-$CJO['RELOGINDELAY'])."'
                                 AND    (login_tries <= '".$CJO['MAXLOGINS']."' OR lasttrydate <'".(time()- (12 * 60 * 60))."')
                               ");

    if (!$CJO['LOGIN']->checkLogin()) {

        $CJO['USER']['LOGIN']    = false;
        $CJO['USER']['NAME']     = false;
        $CJO['USER']['ID']       = false;
        $CJO['USER']['PASSWORD'] = false;
        $CJO['USER']['GROUPS']   = false;
        $CJO['USER']['MESSAGES'] = $CJO['LOGIN']->getMessages();

        $sql = new cjoSql();
        $sql->setQuery("SELECT lasttrydate, login_tries FROM ".TBL_COMMUNITY_USER." WHERE username ='".$LOGIN['username']."'");

        // fehlversuch speichern | login_tries++
        if ($sql->getRows() == 1) {

            $login_tries = ($sql->getValue('lasttrydate') < (time()- (2 * 60 * 60))) ? "1" : $sql->getValue('login_tries') + 1;

            $update = new cjoSql();
            $update->setTable(TBL_COMMUNITY_USER);
            $update->setWhere("username = '".$LOGIN['username']."'");
            $update->setValue("login_tries", $login_tries);
            $update->setValue("lasttrydate", time());
            $update->Update();
        }
    }
    else {

        $CJO['USER']['LOGIN']    = true;
        $CJO['USER']['ID']       = $CJO['LOGIN']->getValue("id");
        $CJO['USER']['NAME']     = $LOGIN['username'];
        $CJO['USER']['PASSWORD'] = $LOGIN['password'];
        $CJO['USER']['GROUPS']   = explode('|',$CJO['LOGIN']->getValue("groups"));
        $CJO['USER']['MESSAGES'] = '';

        $update = new cjoSql();
        $update->setTable(TBL_COMMUNITY_USER);
        $update->setWhere("username ='".$LOGIN['username']."'");
        $update->setValue("login_tries",'0');
        $update->setValue("lasttrydate",time());
        $update->Update();
    }
}
else {
    // nicht eingeloggt und kein login
    $CJO['USER']['LOGIN']    = false;
    $CJO['USER']['NAME']     = false;
    $CJO['USER']['ID']       = false;
    $CJO['USER']['PASSWORD'] = false;
    $CJO['USER']['GROUPS']   = false;
}

cjoLogin::getAtypes();
