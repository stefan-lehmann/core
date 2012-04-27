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

$mypage    = 'login';
$title = (strlen($CJO['SERVERNAME']) > 19) ? substr($CJO['SERVERNAME'],0,16).'...' : $CJO['SERVERNAME'];

$local_params = array('page'     => cjo_request('page', 'string'),
                      'subpage'  => cjo_request('subpage', 'string'),
					  'function' => '',
				      'mode'     => '',
				      'msg'      => '',
				      'err_msg'  => '',
				      'upd'      => '');

if (cjo_get('logout', 'bool'))  $local_params['page'] = 'edit';

$subpages = array(array('login',
					'title' => $title.' '.$I18N->msg('title_login'),
					'important' => true));

cjoSubPages::setTabs($mypage, $subpages, $mypage);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
	xml:lang="<?php echo $I18N->msg("htmllang"); ?>"
	lang="<?php echo $I18N->msg("htmllang"); ?>">
<head>
<title>CJO </title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $I18N->msg("htmlcharset"); ?>" />
<meta http-equiv="Content-Language" content="<?php echo $I18N->msg("htmllang"); ?>" />
<meta http-equiv="Pragma" content="no-cache" />
<link rel="shortcut icon" type="image/x-icon" href="<?php echo $CJO['FAVICON'] ?>" />
<link rel="stylesheet" type="text/css" href="css/contejo.css" />
<link rel="stylesheet" type="text/css" href="css/login.css" />
<?php require_once($CJO['INCLUDE_PATH']."/../js/js.inc.php") ?>
<script type="text/javascript">
	/* <![CDATA[ */
		var contejo = true;
		var clang = '<?php echo $CJO['CUR_CLANG']; ?>';
        window.onload = function(){
            document.getElementById("cjo_ulogin").focus();
        }
	/* ]]> */
	</script>
</head>
<body>
<div id="cjo_padding_top"></div>
<div id="cjo_page_margin">
    <div id="cjo_page">
        <div id="cjo_login_top"></div>
        <div id="cjo_login" class="clearfix">
            <div id="cjo_tabs"></div>
            <form
            	action="<?php echo cjoAssistance::createBEUrl($local_params); ?>"
            	method="post" name="loginformular" autocomplete="off">
                <p><label for="cjo_ulogin"><?php echo $I18N->msg('label_user_login'); ?>:</label>
                   <input type="text" size="15" id="cjo_ulogin"
                    	value="<?php echo $LOGIN['username']; ?>" name="LOGIN[username]"
                    	title="<?php echo $I18N->msg('label_user_login'); ?>" /></p>
                <p><label for="cjo_upsw"><?php echo $I18N->msg('label_user_psw'); ?>:</label>
                   <input type="password" size="15" id="cjo_upsw" name="LOGIN[password]" value=""
                    	title="<?php echo $I18N->msg('label_user_psw'); ?>" /></p>
                <p><input type="submit" value="<?php echo $I18N->msg('label_login'); ?>"
                		class="submit" /></p>
            </form>
            <a href="http://contejo.com" class="cjo_home_link">contejo.com</a>
        </div>
    </div>
</div>
</body>
</html>