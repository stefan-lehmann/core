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

if (cjo_get('function', 'bool') && $addonname) {

    $msg = false;

    switch($function) {
        case 'install': 	if (cjoAddon::installAddon($addonname))
                            //[translate: msg_addon_installed]
                            $msg = 'msg_addon_installed';
                            break;
        case 'activate': 	if (cjoAddon::activateAddon($addonname))
                            //[translate: msg_addon_activated]
                            $msg = 'msg_addon_activated';
                            break;
        case 'deactivate': 	if (cjoAddon::deactivateAddon($addonname))
                            //[translate: msg_addon_deactivated]
                            $msg = 'msg_addon_deactivated';
                            break;
        case 'uninstall': 	if (cjoAddon::uninstallAddon($addonname))
                            //[translate: msg_addon_uninstalled]
                            $msg = 'msg_addon_uninstalled';
                            break;
        case 'delete': 		if (cjoAddon::deleteAddon($addonname))
                            //[translate: msg_addon_deleted]
                            $msg = 'msg_addon_deleted';
                            break;
        case 'menu': 		if (cjoAddon::enableMenuAddon($addonname, $menu))
                            //[translate: msg_addon_menu_changed]
                            $msg = 'msg_addon_menu_changed';
                            break;
    }
    if ($msg && !cjoMessage::hasErrors()) {
        cjoUrl::redirectBE(array('msg'=>$msg, 'addonname'=>'', 'menu' => '', 'function'=>''));
    }
}
$menu_options = array(0 => cjoI18N::translate("addon_no_menu"),
					  1 => cjoI18N::translate("addon_main_menu"),
					  'edit' => cjoI18N::translate("title_edit"),
					  'media' => cjoI18N::translate("title_media"),
					  'tools' => cjoI18N::translate("title_tools"),
					  'users' => cjoI18N::translate("title_users"),
					  'specials' => cjoI18N::translate("title_specials"),
					  'addons' => cjoI18N::translate("title_addons"));

foreach(cjoAddon::getProperty('status') as $addon=>$status) {
	if (cjoAddon::isActivated($addon) && cjoAddon::getProperty('menu', $addon) == 1) {
		$menu_options[$addon] = cjoAddon::getProperty('name', $addon);
	}
}

$menu_sel = new cjoSelect();
$menu_sel->setStyle('class="inp75" ');
$menu_sel->setSize(1);
$menu_sel->setSelectExtra(' onchange="location = this.options[this.selectedIndex].value"');

foreach (cjoAddon::getProperty('status') as $addon=>$value) {

    $data_temp = array();
    $data_temp['addonname'] = $addon;

    foreach (cjoProp::get('ADDON') as $key=>$val) {

        $data_temp[$key] = $val[$addon];

        if ($key == 'install') {
            $data_temp['uninstall'] = cjoAddon::isInstalled($addon) ? 1 : 0;
        }

        if ($key == 'status' && $data_temp['status'] != 1) {
            $data_temp[$key] = cjoAddon::isInstalled($addon) ? 0 : -1;
        }

        if ($key == 'menu') {

            $menu_sel_temp = clone($menu_sel);

            foreach($menu_options as $value=>$option) {
            	if ($value && $value == $addon) continue;
            	$menu_sel_temp->setId('menu_sel_'.$addon);
                $menu_sel_temp->addOption($option,'index.php?page=addons&amp;addonname='.$addon.'&amp;function=menu&amp;menu='.$value);
            }

            if ((!cjoAddon::isInstalled($addon) && !cjoAddon::isActivated($addon)) ||
            	(!cjoAddon::getProperty('view', $addon, true))) {
            	    
                $menu_sel_temp->setSelectExtra(' disabled="disabled" style="color: #999;"');
                $menu_sel_temp->setSelected('index.php?page=addons&amp;addonname='.$addon.'&amp;function=menu&amp;menu=0');
            }
            else{
                $menu_sel_temp->setSelected('index.php?page=addons&amp;addonname='.$addon.'&amp;function=menu&amp;menu='.str_replace('"','',$data_temp['menu']));
            }
            $data_temp[$key] = $menu_sel_temp->get();

            $data_temp['index'] = file_exists(cjoPath::addon($addon, 'pages/index.inc.php'));
        }

        if($key == 'name' && $data_temp['name'] == '') {
            $data_temp[$key] = $data_temp['addonname'];
        }
    }

	$data_temp['delete']  = 1;
	$data_temp['support'] = cjoAddon::getProperty('support', $addon, false)
	                      ? '<a href="#" onclick="window.open(\''.cjoAddon::getProperty('support', $addon, false).'\'); return false;" >'.
	                        '<img src="img/silk_icons/help.png" alt="[?]"/></a>' 
	                      : '&nbsp;';
	                      
	$data_temp['version'] = cjoAddon::getProperty('version', $addon);

	if (in_array($addon, cjoProp::get('SYSTEM_ADDONS')) && cjoAddon::isActivated($addon)) {
		$data_temp['system'] = 1;
	}

    $data[] = $data_temp;
}

//LIST Ausgabe
$list = new cjoList();
$list->curr_rows = $data;

$cols['icon'] = new staticColumn('<img src="img/silk_icons/plugin.png" alt="" />', '');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', cjoI18N::translate("label_name"));
$cols['name']->setHeadAttributes('colspan="2"');
$cols['name']->delOption(OPT_SORT);
$cols['name']->delOption(OPT_SEARCH);

$cols['support'] = new resultColumn('support', NULL);
$cols['support']->setHeadAttributes('class="icon"');
$cols['support']->setBodyAttributes('class="icon"');

$cols['version'] = new resultColumn('version', cjoI18N::translate("label_version"));
$cols['version']->setHeadAttributes('class="icon"');
$cols['version']->setBodyAttributes('class="icon"');
$cols['version']->delOption(OPT_SORT);
$cols['version']->delOption(OPT_SEARCH);

$cols['menu'] = new resultColumn('menu', cjoI18N::translate("label_addon_view"));
$cols['menu']->setBodyAttributes('width="240"');
$cols['menu']->addCondition('index', false, ' ');
$cols['menu']->delOption(OPT_SORT);
$cols['menu']->delOption(OPT_SEARCH);

$img_install = '<img src="img/silk_icons/plugin_disabled_add.png" alt="'.cjoI18N::translate("addon_install").'" title="'.cjoI18N::translate("addon_install").'"/>';
$img_reinstall = '<img src="img/silk_icons/plugin_add.png" alt="'.cjoI18N::translate("addon_reinstall").'" title="'.cjoI18N::translate("addon_reinstall").'"/>';
$cols['install'] = new staticColumn('install', cjoI18N::translate("label_functions"));
$cols['install']->addCondition('system', '1', '&nbsp;');
$cols['install']->addCondition('install', '0', $img_install, array ('function' => 'install', 'addonname' => '%addonname%'));
$cols['install']->addCondition('install', '1', $img_reinstall, array ('function' => 'install', 'addonname' => '%addonname%'));
$cols['install']->setHeadAttributes('colspan="4"');
$cols['install']->setBodyAttributes('width="16"');
$cols['install']->delOption(OPT_SORT);
$cols['install']->delOption(OPT_SEARCH);

$img_uninstall = '<img src="img/silk_icons/plugin_delete.png" alt="'.cjoI18N::translate("addon_uninstall").'" title="'.cjoI18N::translate("addon_uninstall").'"/>';
$cols['uninstall'] = new staticColumn('uninstall', NULL);
$cols['uninstall']->addCondition('system', '1', '&nbsp;');
$cols['uninstall']->addCondition('uninstall', '0', '&nbsp;');
$cols['uninstall']->addCondition('uninstall', '1', $img_uninstall, array ('function' => 'uninstall', 'addonname' => '%addonname%'), 'title="'.cjoI18N::translate("addon_uninstall").'?'.cjoI18N::translate("addon_uninstall_confirm").'"');
$cols['uninstall']->setBodyAttributes('width="16"');
$cols['uninstall']->delOption(OPT_SORT);
$cols['uninstall']->delOption(OPT_SEARCH);

$img_activate = '<img src="img/silk_icons/plugin_disabled.png" alt="'.cjoI18N::translate("addon_deactivate").'" title="'.cjoI18N::translate("addon_activate").'"/>';
$img_deactivate = '<img src="img/silk_icons/plugin.png" alt="'.cjoI18N::translate("addon_activate").'" title="'.cjoI18N::translate("addon_deactivate").'"/>';
$cols['status'] = new staticColumn('status', NULL);
$cols['status']->addCondition('system', '1', '&nbsp;');
$cols['status']->addCondition('status', '-1', '&nbsp;');
$cols['status']->addCondition('status', '0', $img_activate, array ('function' => 'activate', 'addonname' => '%addonname%'));
$cols['status']->addCondition('status', '1', $img_deactivate, array ('function' => 'deactivate', 'addonname' => '%addonname%'));
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->delOption(OPT_SORT);
$cols['status']->delOption(OPT_SEARCH);

$img_delete = '<img src="img/silk_icons/bin.png" title="'.cjoI18N::translate("button_delete").'" alt="'.cjoI18N::translate("button_delete").'" />';
$cols['delete'] = new staticColumn('delete', NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->addCondition('system', '1', '&nbsp;');
$cols['delete']->addCondition('delete', '1', $img_delete, array ('function' => 'delete', 'addonname' => '%addonname%'), 'title="'.cjoI18N::translate("button_delete").'?'.cjoI18N::translate("addon_uninstall_confirm").'"');

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);
//Tabelle anzeigen
$list->show(false);

?>
<script type="text/javascript">
/* <![CDATA[ */

 $('img[src*=plugin]').parents('a').click(function() {
	 cjo.jconfirm($(this), 'cjo.changeLocation', [$(this).attr('href')]);
 	return false;
 });

/* ]]> */
</script>