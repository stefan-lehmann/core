<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  ajax_oauth
 * @version     2.7.2
 *
 * @author      Stefan Lehmann <sl@contejo.com> inspired by Saran Chamling's (saaraan@gmail.com) Facebook Ajax Connect
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

$mypage = 'ajax_oauth'; // only for this file

$CJO['ADDON']['addon_id'][$mypage]      = '33';
$CJO['ADDON']['page'][$mypage]          = $mypage; // pagename/foldername
$CJO['ADDON']['name'][$mypage]          = 'AJAX OAuth';  // name
$CJO['ADDON']['perm'][$mypage]          = $mypage.'[]'; // permission
$CJO['ADDON']['author'][$mypage]        = 'Stefan Lehmann';
$CJO['ADDON']['version'][$mypage]       = '1.0';
$CJO['ADDON']['compat'][$mypage]        = '2.7.2';
$CJO['ADDON']['support'][$mypage]       = 'http://contejo.com/addons/'.$mypage;
$CJO['ADDON']['settings'][$mypage]['SETTINGS'] = $CJO['ADDON_CONFIG_PATH']."/".$mypage."/settings.inc.php"; // settings file

if ($CJO['ADDON']['status'][$mypage] != 1) return;


include_once $CJO['ADDON']['settings'][$mypage]['SETTINGS'];

if ($CJO['CONTEJO']) return;
    
foreach(cjoAssistance::toArray($CJO['ADDON']['settings'][$mypage]['provides']) as $provider){
    
    include_once $CJO['ADDON_PATH'].'/'.$mypage.'/providers/'.$provider.'/cjo_'.$provider.'.inc.php';
    $class_name = 'cjo'.$provider;
    $OAuth[$provider] = new $class_name;
    $OAuth[$provider]->isValidUser();
    
    if ($OAuth[$provider]->isValidUser() && 
        cjo_get('connect','bool') && 
        cjo_get('provider','string') == $provider) {
        
        $OAuth[$provider]->connect();
    }
        
}
// 
// 
// 
// ########## app ID and app SECRET (Replace with yours) #############
// $appId = 'xxxxxx'; //Facebook App ID
// $appSecret = 'xxxxxxxxxxxx'; // Facebook App Secret
// $return_url = 'http://www.saaraan.com/assets/ajax-facebook-connect/';
// 
// define("APPID",$appId);
// define("APPSECRET",$appSecret);
// define("RETURNURL",$return_url);
// define("DBUSERNAME",$username);
// define("DBPASSWORD",$password);
// define("DBHOSTNAME",$hostname);
// define("DBNAME",$databasename);
// 
 // //Call Facebook API
 // if (!class_exists('FacebookApiException')) {
 // require_once('inc/facebook.php' );
 // }
 // $facebook = new Facebook(array(
 // 'appId' => APPID,
 // 'secret' => APPSECRET,
 // ));
//  
// 
 // $fbuser = $facebook->getUser();
 // if ($fbuser) {
 // try {
 // // Proceed knowing you have a logged in user who's authenticated.
 // $me = $facebook->api('/me'); //user
 // $uid = $facebook->getUser();
 // } catch (FacebookApiException $e) {
 // //echo error_log($e);
 // $fbuser = null;
 // }
 // }
//  
// 
// //Destroy facebook user session when user clicks log out
 // if(isset($_GET['logout']) && $_GET['logout']==1)
 // {
 // $facebook->destroySession();
 // die("<div>If you are not automatically redirected, <a href='".RETURNURL."'>Click Here</a>.</div><script type=\"text/javascript\">window.location='" . RETURNURL . "';</script>");
 // }
// ?>