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

$mypage    = 'login';
$title = (strlen(cjoProp::getServerName()) > 19) ? substr(cjoProp::getServerName(),0,16).'...' : cjoProp::getServerName();

$local_params = array('page'     => cjo_request('page', 'string'),
                      'subpage'  => cjo_request('subpage', 'string'),
					  'function' => '',
				      'mode'     => '',
				      'msg'      => '',
				      'err_msg'  => '',
				      'upd'      => '');

if (cjo_get('logout', 'bool'))  $local_params['page'] = 'edit';

$subpages = array(array('login',
					'title' => $title.' '.cjoI18N::translate('title_login'),
					'important' => true));

cjoSubPages::setTabs($mypage, $subpages, $mypage);

?>
<!DOCTYPE html>
<html>
<head>
<title>CJO </title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo cjoI18N::translate("htmlcharset"); ?>" />
<meta http-equiv="Content-Language" content="<?php echo cjoI18N::translate("htmllang"); ?>" />
<meta http-equiv="Pragma" content="no-cache" />
<link rel="shortcut icon" type="image/x-icon" href="<?php echo cjoProp::get('FAVICON') ?>" />
<link rel="stylesheet" type="text/css" href="css/contejo.css" />
<link rel="stylesheet" type="text/css" href="css/login.css" />
<?php require_once cjoPath::backend('js/js.inc.php'); ?>
<script type="text/javascript">
	/* <![CDATA[ */
		var contejo = true;
		var clang = '<?php echo cjoProp::getClang(); ?>';
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
            	action="<?php echo cjoUrl::createBEUrl($local_params); ?>"
            	method="post" name="loginformular" autocomplete="off">
                <p><label for="cjo_ulogin"><?php echo cjoI18N::translate('label_user_login'); ?>:</label>
                   <input type="text" size="15" id="cjo_ulogin"
                    	value="<?php echo cjoProp::getUser() ?>" name="LOGIN[username]"
                    	title="<?php echo cjoI18N::translate('label_user_login'); ?>" /></p>
                <p><label for="cjo_upsw"><?php echo cjoI18N::translate('label_user_psw'); ?>:</label>
                   <input type="password" size="15" id="cjo_upsw" name="LOGIN[password]" value=""
                    	title="<?php echo cjoI18N::translate('label_user_psw'); ?>" /></p>
                <p><input type="submit" value="<?php echo cjoI18N::translate('label_login'); ?>"
                		class="submit" /></p>
            </form>
            <a href="http://contejo.com" class="cjo_home_link">contejo.com</a>
        </div>
    </div>
</div>
</body>
</html>