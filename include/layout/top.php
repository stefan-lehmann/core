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
$cjo_mainmenu    = array('out' => '');
$cjo_header      = '';
$cjo_popup_class = '';
$login_status    = '';

if (empty($cur_page['popup'])) {

    if ($CJO['USER']) {

        /*     User-Info und Logout zusammenstellen     */
        $login_status = '<ul class="buttonmenu">
                            <li class="first">
                                <a href="index.php?logout=1">
                                    <span class="left"><img src="img/silk_icons/user.png" alt="'.$CJO['USER']->getValue('name').'" /></span>
                                    <span>
                                        '.$CJO['USER']->getValue('name').'
                                    </span>
                                    <span class="rot">
                                        '.$I18N->msg('label_logout').'
                                    </span>
                                    <span class="right"></span>
                                </a>
                            </li>
                          </ul>'."\r\n";


        /*     Menü zusammenstellen     */
        $cjo_mainmenu['items']['edit'] = cjoAssistance::createBELink($I18N->msg('title_edit'), array('clang' => $clang), array('page'=>'edit'));

        if ($CJO['USER']->isAdmin() || $CJO['USER']->isValueOf("rights",'media[')) {
            $cjo_mainmenu['items']['media'] = cjoAssistance::createBELink($I18N->msg('title_media'), array('clang' => $clang), array('page'=>'media'));
        }
        if ($CJO['USER']->hasPerm('tools[')) {
            $cjo_mainmenu['items']['tools'] = cjoAssistance::createBELink($I18N->msg('title_tools'), array('clang' => $clang), array('page'=>'tools'));
        }
        if ($CJO['USER']->hasPerm('users[]')) {
            $cjo_mainmenu['items']['users'] = cjoAssistance::createBELink($I18N->msg('title_users'), array('clang' => $clang), array('page'=>'users'));
        }
        elseif ($CJO['USER']->hasPerm('users[password]')) {
            $cjo_mainmenu['items']['users'] = cjoAssistance::createBELink($I18N->msg('title_my_account'), array('clang' => $clang), array('page'=>'users'));
        }
        if ($CJO['USER']->hasPerm('specials[')) {
            $cjo_mainmenu['items']['specials'] = cjoAssistance::createBELink($I18N->msg('title_specials'), array('clang' => $clang), array('page'=>'specials'));
        }
        if ($CJO['USER']->hasPerm('addons[]')) {
            $cjo_mainmenu['items']['addons'] = cjoAssistance::createBELink($I18N->msg('title_addons'), array('clang' => $clang), array('page'=>'addons'), ' id="addon"');
        }

        foreach ($CJO['ADDON']['status'] as $key => $status)
        {
            $name  = (isset($CJO['ADDON']['name'][$key]))  ? $CJO['ADDON']['name'][$key]  : false;
            $popup = (isset($CJO['ADDON']['popup'][$key])) ? $CJO['ADDON']['popup'][$key] : false;


            if ($CJO['ADDON']['status'][$key] && $name && $CJO['USER']->hasAddonPerm($key,true)) {

                $parent_page = $CJO['ADDON']['menu'][$key];

                if ($parent_page === 0 || $parent_page == 'edit') {
                    continue;
                }
                if ($parent_page != 1 ) {

                    $match = array_search($parent_page,$CJO['ADDON']['page']);

                    if ($match !== false && $CJO['USER']->hasAddonPerm($match,true)) {
                        continue;
                    }
                    else if ($CJO['USER']->hasPerm($parent_page.'[')) {
                        continue;
                    }
                }
                else if (!$CJO['USER']->hasAddonPerm($key,true)) {
                    continue;
                }

                if ($popup) {
                    $item = '<a href="#" onclick="cjo.openPopUp(\''.$name.'\',\'index.php?page='.$key.'&clang='.$clang.'\'); return false;">'.$name.'</a>'."\r\n";
                } elseif (!$popup) {
                    $item =  '<a href="index.php?page='.$key.'&clang='.$clang.'">'.$name.'</a>'."\r\n";
                } else {
                    $item =  '<a href="#" onclick="'.$popup.'"; reurn false;">'.$name.'</a>'."\r\n";
                }
                $cjo_mainmenu['items'][$key] = $item;
            }
        }

        foreach ($cjo_mainmenu['items'] as $key => $item) {

            $current = ($key == $cur_page['page']) ? ' class="current"' : '';
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

$main_style = $CJO['CLANG'] > 2 ? ' style="min-height:'.(100+count($CJO['CLANG'])*24).'px"' : '';

?>
<!DOCTYPE html>
<html>
<head>

<title>CJO | <?php echo $CJO['SERVERNAME'] ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $I18N->msg("htmlcharset"); ?>" />
<meta http-equiv="Content-Language" content="<?php echo $I18N->msg("htmllang"); ?>" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<link rel="shortcut icon" type="image/x-icon" href="<?php echo $CJO['FAVICON'] ?>" />
<link rel="stylesheet" type="text/css" href="css/contejo.css" />
<link href="js/swfupload/swfupload.css" rel="stylesheet" type="text/css" />
<link href="js/Jcrop/css/jquery.Jcrop.css" rel="stylesheet" type="text/css" />
<?php require_once($CJO['INCLUDE_PATH']."/../js/js.inc.php") ?>
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