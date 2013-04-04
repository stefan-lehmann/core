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


class cjoPageAddonsManage extends cjoPage {

    protected static function readAddonData() {
        
        $data = array();
        
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
        $menu_sel->setStyle('class="inp75 cjo_ajax" data-callback="cjo.updatePage()"');
        $menu_sel->setSize(1);
        
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
                        $menu_sel_temp->addOption($option,'ajax.php?function=cjoAddon::enableMenuAddon&amp;addonname='.$addon.'&amp;menu='.$value);
                    }
        
                    if ((!cjoAddon::isInstalled($addon) && !cjoAddon::isActivated($addon)) ||
                        (!cjoAddon::getProperty('view', $addon, true))) {
                            
                        $menu_sel_temp->setSelectExtra(' disabled="disabled" style="color: #999;"');
                        $menu_sel_temp->setSelected('ajax.php?function=cjoAddon::enableMenuAddon&amp;addonname='.$addon.'&amp;menu=0');
                    }
                    else{
                        $menu_sel_temp->setSelected('ajax.php?function=cjoAddon::enableMenuAddon&amp;addonname='.$addon.'&amp;menu='.str_replace('"','',$data_temp['menu']));
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
        
            if (cjoAddon::isSystemAddon($addon) && cjoAddon::isActivated($addon)) {
                $data_temp['system'] = 1;
            }
        
            $data[] = $data_temp;
        }
        return $data;
    }


    protected function getDefault() {     
        
        $this->list = new cjoList();
        $this->list->setListData(self::readAddonData());
        
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/status.png" alt="" />', '');
        
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_ALL);
        
        $this->cols['name'] = new resultColumn('name', cjoI18N::translate("label_name"));
        $this->cols['name']->setHeadAttributes('colspan="2"');
        $this->cols['name']->delOption(OPT_ALL);
        
        $this->cols['support'] = new resultColumn('support', NULL);
        $this->cols['support']->setHeadAttributes('class="icon"');
        $this->cols['support']->setBodyAttributes('class="icon"');
        
        $this->cols['version'] = new resultColumn('version', cjoI18N::translate("label_version"));
        $this->cols['version']->setHeadAttributes('class="icon"');
        $this->cols['version']->setBodyAttributes('class="icon"');
        $this->cols['version']->delOption(OPT_ALL);

        $this->cols['menu'] = new resultColumn('menu', cjoI18N::translate("label_addon_view"));
        $this->cols['menu']->setBodyAttributes('width="200"');
        $this->cols['menu']->addCondition('index', false, ' ');
        $this->cols['menu']->delOption(OPT_ALL);

        $this->cols['install'] = new StatusColumn('install', NULL, true, cjoI18N::translate('label_functions'));
        $this->cols['install']->addCondition('system', '1');
        $this->cols['install']->addCondition('install', '0', cjoI18N::translate("addon_install"), array('function' => 'cjoAddon::installAddon', 'addonname' => '%addonname%'));
        $this->cols['install']->addCondition('install', '1', cjoI18N::translate("addon_reinstall"), array('function' => 'cjoAddon::installAddon', 'addonname' => '%addonname%'));
        $this->cols['install']->setConditionAttributes('class="cjo_confirm" data-callback="cjo.updatePage()"');
        $this->cols['install']->setHeadAttributes('colspan="4"');
        $this->cols['install']->setBodyAttributes('width="16"');
        $this->cols['install']->delOption(OPT_ALL);
        
        $this->cols['uninstall'] = new StatusColumn('uninstall', NULL);
        $this->cols['uninstall']->addCondition('system', '1');
        $this->cols['uninstall']->addCondition('uninstall', '0');
        $this->cols['uninstall']->addCondition('uninstall', '1', cjoI18N::translate("addon_uninstall"), array('function' => 'cjoAddon::uninstallAddon', 'addonname' => '%addonname%'));
        $this->cols['uninstall']->setConditionAttributes('class="cjo_confirm" data-callback="cjo.updatePage()"'); 
        $this->cols['uninstall']->setBodyAttributes('width="16"');    
        $this->cols['uninstall']->delOption(OPT_ALL);
        
        $this->cols['status'] = new StatusColumn('status', NULL);
        $this->cols['status']->addCondition('system', '1');
        $this->cols['status']->addCondition('status', '-1');
        $this->cols['status']->addCondition('status', '0', cjoI18N::translate("addon_activate"), array('function' => 'cjoAddon::activateAddon', 'addonname' => '%addonname%'));
        $this->cols['status']->addCondition('status', '1', cjoI18N::translate("addon_deactivate"), array('function' => 'cjoAddon::deactivateAddon', 'addonname' => '%addonname%'));
        $this->cols['status']->setConditionAttributes('class="cjo_confirm" data-callback="cjo.updatePage()"'); 
        $this->cols['status']->setBodyAttributes('width="16"');        
        $this->cols['status']->delOption(OPT_ALL);

        $this->cols['delete'] = new deleteColumn(array('function'=>'cjoAddon::deleteAddon', 'addon'=>'%addonname%'));

        $this->list->addColumns($this->cols);
        $this->list->show();
    }

}