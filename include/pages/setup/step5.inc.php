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

if (empty($CJO['LOCALHOST'])) $CJO['LOCALHOST'] = 'contejo.localhost';

$dataset['TABLE_PREFIX'] = $CJO['TABLE_PREFIX'];

$dataset['DB_1_NAME'] 	= $CJO['DB']['1']['NAME'];
$dataset['DB_1_HOST'] 	= $CJO['DB']['1']['HOST'];
$dataset['DB_1_LOGIN'] 	= $CJO['DB']['1']['LOGIN'];
$dataset['DB_1_PSW'] 	= $CJO['DB']['1']['PSW'];

$dataset['DB_LOCAL_NAME'] 	= $CJO['DB']['LOCAL']['NAME'];
$dataset['DB_LOCAL_HOST'] 	= $CJO['DB']['LOCAL']['HOST'];
$dataset['DB_LOCAL_LOGIN'] 	= $CJO['DB']['LOCAL']['LOGIN'];
$dataset['DB_LOCAL_PSW'] 	= $CJO['DB']['LOCAL']['PSW'];

//Form
$form = new cjoForm();
$form->setEditMode(false);
$form->debug = false;

//Hidden Fields
$hidden['prev_subpage'] = new hiddenField('prev_subpage');
$hidden['prev_subpage']->setValue('step4');

$hidden['lang'] = new hiddenField('lang');
$hidden['lang']->setValue($lang);

$fields['headline1'] = new readOnlyField('headline1', '', array('class' => 'formheadline'));
$fields['headline1']->setValue($I18N->msg("label_table_prefix"));

$fields['table_prefix'] = new textField('TABLE_PREFIX', $I18N->msg("label_table_prefix"));
$fields['table_prefix']->addValidator('notEmpty', $I18N->msg("msg_table_prefix_notEmpty"));

$fields['headline2'] = new readOnlyField('headline2', '', array('class' => 'formheadline'));
$fields['headline2']->setValue($I18N->msg("label_mysql_db"));

$fields['name'] = new textField('DB_1_NAME', $I18N->msg("label_db_name"));
$fields['host'] = new textField('DB_1_HOST', $I18N->msg("label_db_host"));
$fields['login'] = new textField('DB_1_LOGIN', $I18N->msg("label_db_login"));
$fields['psw'] = new textField('DB_1_PSW', $I18N->msg("label_db_psw"));


$fields['headline3'] = new readOnlyField('headline3', '', array('class' => 'formheadline slide'));
$fields['headline3']->setValue($I18N->msg("label_mysql_db_local", $CJO['LOCALHOST']));

$fields['name_local'] = new textField('DB_LOCAL_NAME', $I18N->msg("label_db_name"));
$fields['host_local'] = new textField('DB_LOCAL_HOST', $I18N->msg("label_db_host"));
$fields['login_local'] = new textField('DB_LOCAL_LOGIN', $I18N->msg("label_db_login"));
$fields['psw_local'] = new textField('DB_LOCAL_PSW', $I18N->msg("label_db_psw"));


$fields['button'] = new buttonField();
$fields['button']->addButton('cjoform_back_button',$I18N->msg("button_back"), true, 'img/silk_icons/control_play_backwards.png');
$fields['button']->addButton('cjoform_next_button',$I18N->msg("button_next_step6"), true, 'img/silk_icons/control_play.png');
$fields['button']->setButtonAttributes('cjoform_next_button', ' style="color: green"');

//Add Fields:
$section = new cjoFormSection($dataset, $I18N->msg("label_setup_".$subpage."_title"));

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);

if ($form->validate()) {
    $databases    = array();
    $databases[1] = array(
    				 'host' => cjo_post('DB_1_HOST', 'string'),
                     'name' => cjo_post('DB_1_NAME', 'string'),
                     'login' => cjo_post('DB_1_LOGIN', 'string'),
                     'psw'  => cjo_post('DB_1_PSW', 'string')
                   );
    $databases['local'] = array(
    				 'host' => cjo_post('DB_LOCAL_HOST', 'string'),
                     'name' => cjo_post('DB_LOCAL_NAME', 'string'),
                     'login' => cjo_post('DB_LOCAL_LOGIN', 'string'),
                     'psw'  => cjo_post('DB_LOCAL_PSW', 'string')
                   );                   

    $state = cjoSql::checkDbConnection($databases[1]['host'],
                                       $databases[1]['name'],
                                       $databases[1]['login'],
                                       $databases[1]['psw'],
                                       true);
    if (!$state) { 
                 
        if (cjoSql::checkDbConnection($databases['local']['host'],
                                      $databases['local']['name'],
                                      $databases['local']['login'],
                                      $databases['local']['psw'],
                                      true)) {
           $state = true;
           cjoMessage::removeLastError();                               
        }
    }
	
	if ($state) {
    
        $data = file_get_contents($CJO['FILE_CONFIG_DB']);
    
    	if ($data != '') {
    		$data = preg_replace('/^(\$CJO\[\'TABLE_PREFIX\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.cjo_get('TABLE_PREFIX','string','cjo_').'"$3', $data);
    
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'1\'\]\[\'HOST\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases[1]['host'].'"$3', $data);
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'1\'\]\[\'LOGIN\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases[1]['login'].'"$3', $data);
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'1\'\]\[\'PSW\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases[1]['psw'].'"$3', $data);
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'1\'\]\[\'NAME\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases[1]['name'].'"$3', $data);
    
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'LOCAL\'\]\[\'HOST\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases['local']['host'].'"$3', $data);
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'LOCAL\'\]\[\'LOGIN\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases['local']['login'].'"$3', $data);
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'LOCAL\'\]\[\'PSW\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases['local']['psw'].'"$3', $data);
    		$data = preg_replace('/^(\$CJO\[\'DB\'\]\[\'LOCAL\'\]\[\'NAME\'\]\s*=\s*)(".*")(.*?)$/imx', '$1"'.$databases['local']['name'].'"$3', $data);

    		if (!cjoGenerate::putFileContents($CJO['FILE_CONFIG_DB'], $data)){
    			cjoMessage::addError($I18N->msg("msg_config_db_no_perm"));
    		}
    	}
    	else {
    	    cjoMessage::addError($I18N->msg("msg_config_db_does_not_exist"));
    	}
    
    	if (!cjoMessage::hasErrors()){
    	    cjoAssistance::redirectBE(array('subpage' => 'step6', 'lang' => $lang));
    	}
	}
}
$form->show(false);