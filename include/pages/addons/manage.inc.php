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
        case 'install': 	OOAddon :: installAddon($ADDONS,$addonname);
                            //[translate: msg_addon_installed]
                            $msg = 'msg_addon_installed';
                            break;
        case 'activate': 	OOAddon :: activateAddon($ADDONS,$addonname);
                            //[translate: msg_addon_activated]
                            $msg = 'msg_addon_activated';
                            break;
        case 'deactivate': 	OOAddon :: deactivateAddon($ADDONS,$addonname);
                            //[translate: msg_addon_deactivated]
                            $msg = 'msg_addon_deactivated';
                            break;
        case 'uninstall': 	OOAddon :: uninstallAddon($ADDONS,$addonname);
                            //[translate: msg_addon_uninstalled]
                            $msg = 'msg_addon_uninstalled';
                            break;
        case 'delete': 		OOAddon :: deleteAddon($ADDONS,$addonname);
                            //[translate: msg_addon_deleted]
                            $msg = 'msg_addon_deleted';
                            break;
        case 'menu': 		OOAddon :: enableMenuAddon($ADDONS, $addonname, $menu);
                            //[translate: msg_addon_menu_changed]
                            $msg = 'msg_addon_menu_changed';
                            break;
    }
    if ($msg && !cjoMessage::hasErrors()) {
        cjoAssistance::redirectBE(array('msg'=>$msg, 'addonname'=>'', 'menu' => '', 'function'=>''));
    }
}

$menu_options = array(0 => $I18N->msg("addon_no_menu"),
					  1 => $I18N->msg("addon_main_menu"),
					  'edit' => $I18N->msg("title_edit"),
					  'media' => $I18N->msg("title_media"),
					  'tools' => $I18N->msg("title_tools"),
					  'users' => $I18N->msg("title_users"),
					  'specials' => $I18N->msg("title_specials"),
					  'addons' => $I18N->msg("title_addons"));

foreach($CJO['ADDON']['menu'] as $name=>$status) {
	if ($status == 1) {
		$menu_options[$name] = $CJO['ADDON']['name'][$name];
	}
}

$menu_sel = new cjoSelect();
$menu_sel->setStyle('class="inp75" ');
$menu_sel->setSize(1);
$menu_sel->setSelectExtra(' onchange="location = this.options[this.selectedIndex].value"');

foreach ($ADDONS as $addonname) {

    $data_temp = array();
    $data_temp['addonname'] = $addonname;

    foreach ($CJO['ADDON'] as $key=>$val) {

        $data_temp[$key] = $val[$addonname];

        if ($key == 'install') {
            $data_temp['uninstall'] = ($CJO['ADDON']['install'][$addonname] == 1) ? 1 : 0;
        }

        if ($key == 'status' && $data_temp['status'] != 1) {
            $data_temp[$key] = ($CJO['ADDON']['install'][$addonname] == 1) ? 0 : -1;
        }

        if ($key == 'menu') {

            $menu_sel_temp = clone($menu_sel);

            foreach($menu_options as $value=>$option) {
            	if($value && $value == $addonname) continue;
            	$menu_sel_temp->setId('menu_sel_'.$addonname);
                $menu_sel_temp->addOption($option,'index.php?page=addons&amp;addonname='.$addonname.'&amp;function=menu&amp;menu='.$value);
            }

            if (($CJO['ADDON']['install'][$addonname] != 1 && $CJO['ADDON']['status'][$addonname] != 1) ||
            	(isset($CJO['ADDON']['view'][$addonname]) && !$CJO['ADDON']['view'][$addonname]))
            	{
                $menu_sel_temp->setSelectExtra(' disabled="disabled" style="color: #999;"');
                $menu_sel_temp->setSelected('index.php?page=addons&amp;addonname='.$addonname.'&amp;function=menu&amp;menu=0');
            }
            else{
                $menu_sel_temp->setSelected('index.php?page=addons&amp;addonname='.$addonname.'&amp;function=menu&amp;menu='.str_replace('"','',$data_temp['menu']));
            }
            $data_temp[$key] = $menu_sel_temp->get();

            $data_temp['index'] = file_exists($CJO['ADDON_PATH']."/".$addonname."/pages/index.inc.php");
        }

        if($key == 'name' && $data_temp['name'] == '') {
            $data_temp[$key] = $data_temp['addonname'];
        }
    }

	$data_temp['delete']  = 1;
	$data_temp['support'] = $CJO['ADDON']['support'][$addonname] 
	                      ? '<a href="#" onclick="window.open(\''.$CJO['ADDON']['support'][$addonname] .'\'); return false;" >'.
	                        '<img src="img/silk_icons/help.png" alt="[?]"/></a>' 
	                      : '&nbsp;';
	                      
	$data_temp['version'] = $CJO['ADDON']['version'][$addonname];

	if (in_array($addonname, $CJO['SYSTEM_ADDONS']) && $CJO['ADDON']['status'][$addonname]) {
		$data_temp['system'] = 1;
	}

    $data[] = $data_temp;
}

//LIST Ausgabe
$list = new cjolist();
$list->curr_rows = $data;

$cols['icon'] = new staticColumn('<img src="img/silk_icons/plugin.png" alt="" />', '');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');

$cols['name'] = new resultColumn('name', $I18N->msg("label_name"));
$cols['name']->setHeadAttributes('colspan="2"');
$cols['name']->delOption(OPT_SORT);
$cols['name']->delOption(OPT_SEARCH);

$cols['support'] = new resultColumn('support', NULL);
$cols['support']->setHeadAttributes('class="icon"');
$cols['support']->setBodyAttributes('class="icon"');

$cols['version'] = new resultColumn('version', $I18N->msg("label_version"));
$cols['version']->setHeadAttributes('class="icon"');
$cols['version']->setBodyAttributes('class="icon"');
$cols['version']->delOption(OPT_SORT);
$cols['version']->delOption(OPT_SEARCH);

$cols['menu'] = new resultColumn('menu', $I18N->msg("label_addon_view"));
$cols['menu']->setBodyAttributes('width="240"');
$cols['menu']->addCondition('index', false, ' ');
$cols['menu']->delOption(OPT_SORT);
$cols['menu']->delOption(OPT_SEARCH);

$img_install = '<img src="img/silk_icons/plugin_disabled_add.png" alt="'.$I18N->msg("addon_install").'" title="'.$I18N->msg("addon_install").'"/>';
$img_reinstall = '<img src="img/silk_icons/plugin_add.png" alt="'.$I18N->msg("addon_reinstall").'" title="'.$I18N->msg("addon_reinstall").'"/>';
$cols['install'] = new staticColumn('install', $I18N->msg("label_functions"));
$cols['install']->addCondition('system', '1', '&nbsp;');
$cols['install']->addCondition('install', '0', $img_install, array ('function' => 'install', 'addonname' => '%addonname%'));
$cols['install']->addCondition('install', '1', $img_reinstall, array ('function' => 'install', 'addonname' => '%addonname%'));
$cols['install']->setHeadAttributes('colspan="4"');
$cols['install']->setBodyAttributes('width="16"');
$cols['install']->delOption(OPT_SORT);
$cols['install']->delOption(OPT_SEARCH);

$img_uninstall = '<img src="img/silk_icons/plugin_delete.png" alt="'.$I18N->msg("addon_uninstall").'" title="'.$I18N->msg("addon_uninstall").'"/>';
$cols['uninstall'] = new staticColumn('uninstall', NULL);
$cols['uninstall']->addCondition('system', '1', '&nbsp;');
$cols['uninstall']->addCondition('uninstall', '0', '&nbsp;');
$cols['uninstall']->addCondition('uninstall', '1', $img_uninstall, array ('function' => 'uninstall', 'addonname' => '%addonname%'), 'title="'.$I18N->msg("addon_uninstall").'?'.$I18N->msg("addon_uninstall_confirm").'"');
$cols['uninstall']->setBodyAttributes('width="16"');
$cols['uninstall']->delOption(OPT_SORT);
$cols['uninstall']->delOption(OPT_SEARCH);

$img_activate = '<img src="img/silk_icons/plugin_disabled.png" alt="'.$I18N->msg("addon_deactivate").'" title="'.$I18N->msg("addon_activate").'"/>';
$img_deactivate = '<img src="img/silk_icons/plugin.png" alt="'.$I18N->msg("addon_activate").'" title="'.$I18N->msg("addon_deactivate").'"/>';
$cols['status'] = new staticColumn('status', NULL);
$cols['status']->addCondition('system', '1', '&nbsp;');
$cols['status']->addCondition('status', '-1', '&nbsp;');
$cols['status']->addCondition('status', '0', $img_activate, array ('function' => 'activate', 'addonname' => '%addonname%'));
$cols['status']->addCondition('status', '1', $img_deactivate, array ('function' => 'deactivate', 'addonname' => '%addonname%'));
$cols['status']->setBodyAttributes('width="16"');
$cols['status']->delOption(OPT_SORT);
$cols['status']->delOption(OPT_SEARCH);

$img_delete = '<img src="img/silk_icons/bin.png" title="'.$I18N->msg("button_delete").'" alt="'.$I18N->msg("button_delete").'" />';
$cols['delete'] = new staticColumn('delete', NULL);
$cols['delete']->setBodyAttributes('width="60"');
$cols['delete']->setBodyAttributes('class="cjo_delete"');
$cols['delete']->addCondition('system', '1', '&nbsp;');
$cols['delete']->addCondition('delete', '1', $img_delete, array ('function' => 'delete', 'addonname' => '%addonname%'), 'title="'.$I18N->msg("button_delete").'?'.$I18N->msg("addon_uninstall_confirm").'"');

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