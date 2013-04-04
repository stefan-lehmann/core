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
$cjo_mainmenu    = array('out' => '');
$cjo_header      = '';
$cjo_popup_class = '';
$login_status    = '';

if (!cjoProp::get('POPUP_PAGE')) {

    if (cjoProp::getUser()) {

        /*     User-Info und Logout zusammenstellen     */
        $login_status = '<ul class="buttonmenu">
                            <li class="first">
                                <a href="index.php?logout=1">
                                    <span class="left"><img src="img/silk_icons/user.png" alt="'.cjoProp::getUser()->getValue('name').'" /></span>
                                    <span>
                                        '.cjoProp::getUser()->getValue('name').'
                                    </span>
                                    <span class="rot">
                                        '.cjoI18N::translate('label_logout').'
                                    </span>
                                    <span class="right"></span>
                                </a>
                            </li>
                          </ul>'."\r\n";


        /*     Menü zusammenstellen     */
        $cjo_mainmenu['items']['edit'] = cjoUrl::createBELink(cjoI18N::translate('title_edit'), array('clang' => $clang), array('page'=>'edit'));

        if (cjoProp::getUser()->isAdmin() || cjoProp::getUser()->isValueOf("rights",'media[')) {
            $cjo_mainmenu['items']['media'] = cjoUrl::createBELink(cjoI18N::translate('title_media'), array('clang' => $clang), array('page'=>'media'));
        }
        if (cjoProp::getUser()->hasPerm('tools[')) {
            $cjo_mainmenu['items']['tools'] = cjoUrl::createBELink(cjoI18N::translate('title_tools'), array('clang' => $clang), array('page'=>'tools'));
        }
        if (cjoProp::getUser()->hasPerm('users[]')) {
            $cjo_mainmenu['items']['users'] = cjoUrl::createBELink(cjoI18N::translate('title_users'), array('clang' => $clang), array('page'=>'users'));
        }
        elseif (cjoProp::getUser()->hasPerm('users[password]')) {
            $cjo_mainmenu['items']['users'] = cjoUrl::createBELink(cjoI18N::translate('title_my_account'), array('clang' => $clang), array('page'=>'users'));
        }
        if (cjoProp::getUser()->hasPerm('specials[')) {
            $cjo_mainmenu['items']['specials'] = cjoUrl::createBELink(cjoI18N::translate('title_specials'), array('clang' => $clang), array('page'=>'specials'));
        }
        if (cjoProp::getUser()->hasPerm('addons[]')) {
            $cjo_mainmenu['items']['addons'] = cjoUrl::createBELink(cjoI18N::translate('title_addons'), array('clang' => $clang), array('page'=>'addons'), ' id="addon"');
        }

        foreach (cjoAddon::getProperty('status') as $addon => $status) {
            
            
            $name  = cjoAddon::getProperty('name',$addon, false);
            $popup = cjoAddon::getProperty('popup',$addon, false);

            if (cjoAddon::isActivated($addon) && $name && cjoProp::getUser()->hasAddonPerm($addon,true)) {

                $parent_page = cjoAddon::getProperty('menu',$addon);

                if ($parent_page === 0 || $parent_page == 'edit') {
                    continue;
                }
                if ($parent_page != 1 ) {

                    $match = array_search($parent_page, cjoAddon::getProperty('page'));

                    if ($match !== false && cjoProp::getUser()->hasAddonPerm($match,true)) {
                        continue;
                    }
                    else if (cjoProp::getUser()->hasPerm($parent_page.'[')) {
                        continue;
                    }
                }
                else if (!cjoProp::getUser()->hasAddonPerm($addon,true)) {
                    continue;
                }

                if ($popup) {
                    $item = '<a href="#" onclick="cjo.openPopUp(\''.$name.'\',\'index.php?page='.$addon.'&clang='.$clang.'\'); return false;">'.$name.'</a>'."\r\n";
                } elseif (!$popup) {
                    $item =  '<a href="index.php?page='.$addon.'&clang='.$clang.'">'.$name.'</a>'."\r\n";
                } else {
                    $item =  '<a href="#" onclick="'.$popup.'"; reurn false;">'.$name.'</a>'."\r\n";
                }
                $cjo_mainmenu['items'][$addon] = $item;
            }
        }

        foreach ($cjo_mainmenu['items'] as $key => $item) {

            $current = ($key == cjoProp::getPage()) ? ' class="current"' : '';
            $pattern = '/<(a.*href\="?\S+"[^>]*)>(.+)(<\/a>)/';
            $replace = '<$1'.$current.'><span class="left"></span>'."\r\n".
                       '<span>$2</span>'."\r\n".
                       '<span class="right"></span>$3'."\r\n";
            $first   = (empty($cjo_mainmenu['out'])) ? ' class="first"' : '';

            $cjo_mainmenu['items'][$key] = preg_replace($pattern, '$2', $item);

            $cjo_mainmenu['out'] .= '<li'.$first.'>'.preg_replace($pattern, $replace, $item).'</li>'."\n\r";
        }

        $cjo_mainmenu['out'] = '<div id="cjo_mainmenu"><ul class="buttonmenu">'.$cjo_mainmenu['out'].'</ul></div>';
    }

    if (cjo_post('cjoform_cancel_button','boolean')) {
        unset($_REQUEST['function']);
        unset($_REQUEST['mode']);
        unset($_REQUEST['oid']);
    }

    $cjo_header =   '<div id="cjo_header">'."\r\n".
                    '   <div id="cjo_login_status">'.$login_status.'</div>'."\r\n".
                    '   '. $cjo_mainmenu['out']."\r\n".
                    '</div>'."\r\n";
}
else{
    $cjo_header = '';
    $cjo_popup_class = ' class="cjo_popup"';
}


?>
<!DOCTYPE html>
<html>
<head>

<title>CJO | <?php echo cjoProp::get('SERVERNAME') ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo cjoI18N::translate("htmlcharset"); ?>" />
<meta http-equiv="Content-Language" content="<?php echo cjoI18N::translate("htmllang"); ?>" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<link rel="shortcut icon" type="image/x-icon" href="<?php echo cjoProp::get('FAVICON') ?>" />
<link rel="stylesheet" type="text/css" href="css/contejo.css" />
<link href="js/swfupload/swfupload.css" rel="stylesheet" type="text/css" />
<link href="js/Jcrop/css/jquery.Jcrop.css" rel="stylesheet" type="text/css" />
<?php require_once cjoPath::backend('js/js.inc.php'); ?>
<!--[if gt IE 6]><link rel="stylesheet" href="css/ie.css" type="text/css" media="screen" /><![endif]-->
</head>
<body>
<a name="oben" class="cjo_hidden_anchor"></a>
<div id="cjo_page_margin">
    <div id="cjo_page"<?php echo $cjo_popup_class; ?>>
        <?php echo $cjo_header; ?>
        <div id="cjo_tabs" class="clearfix"></div>
        <div id="cjo_main"<?php echo $main_style; ?>>
        <div id="cjo_sub_tabs" class="hide_me"></div>
        <div id="cjo_lang_tabs"></div>