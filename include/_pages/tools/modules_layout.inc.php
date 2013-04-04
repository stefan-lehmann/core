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

if (!cjo_post('cjoform_cancel_button','bool')) {
    $template = cjo_request('template', 'cjo-template-id');
    $ctype = cjo_request('ctype', 'cjo-ctype-id');
    $type = cjo_request('type', 'string');
    $action = cjo_request('action', 'string');
}

if ($action != '') {

    $path['path'] 	  = $CJO['ADDON']['settings']['developer']['edit_path'].'/'.$CJO['TMPL_FILE_TYPE'];
    $path['type'] 	  = $path['path'].'/'.$type;

    if ($template > 0) {
        $path['type_template'] = $path['type'].'/'.$template.'.template';
    }
    elseif ($template == 0 && $ctype > 0) {
        $path['type_ctype'] = $path['type'].'/'.$ctype.'.ctype';
    }
    if ($template > 0 && $ctype > 0) {
        $path['type_template_ctype'] = $path['type_template'].'/'.$ctype.'.ctype';
    }

    if ($action == 'add'){

        foreach($path as $val){
            if (!file_exists($val)){ mkdir($val, $CJO['FILEPERM']); }
        }

        $new_content = @file_get_contents(cjoModulTemplate::getTemplatePath($oid,0,0,$type)).' ';
                                            $new_file 	 = array_pop($path).'/'.
                                            $oid.'.'.
                                            cjo_specialchars($curr_modultyp['name']).'.'.
                                            $type.'.'.
                                            $CJO['TMPL_FILE_TYPE'];

        /**
         * Do not delete translate values for cjoI18N collection!
         * [translate: label_input]
         * [translate: label_output]
         */

        if (file_put_contents($new_file,$new_content)) {
            unset($CJO['ADDON']['settings']['developer']['tmpl']['html']);
            cjoExtension::registerExtensionPoint('MODULE_UPDATED', 
                                                 array('ACTION' => 'LAYOUT_ADDED',
                                                       'moduletyp_id' => $oid,
                                                       'template_id' => $template,
                                                       'ctype' => $ctype,
                                                       'type' => $type));  
            cjoMessage::addSuccess($I18N->msg("msg_modul_layout_added", $template, $ctype, $I18N->msg("label_".$type)));
        }
        else {
            cjoMessage::addError($I18N->msg("msg_modul_layout_not_added", $template, $ctype, $I18N->msg("label_".$type)));
            cjoAssistance::isWritable($new_file);
        }
    }
    if ($action == 'delete'){

        $default = cjoModulTemplate::getTemplatePath($oid,0,0,$type);
        $curr = cjoModulTemplate::getTemplatePath($oid,$template,$ctype,$type);

        if ($default != $curr){
            if (unlink($curr)) {

                $path = array_reverse($path);
                foreach($path as $val){ @rmdir($val); }
                unset($CJO['ADDON']['settings']['developer']['tmpl']['html']);
                cjoMessage::addSuccess($I18N->msg("msg_modul_layout_deleted", $template, $ctype, $I18N->msg("label_".$type)));
                
                cjoExtension::registerExtensionPoint('MODULE_UPDATED', 
                                                     array('ACTION' => 'LAYOUT_DELETED',
                                                           'moduletyp_id' => $oid,
                                                           'template_id' => $template,
                                                           'ctype' => $ctype,
                                                           'type' => $type));  
            }
            else {
                cjoMessage::addError($I18N->msg("msg_modul_layout_not_deleted", $template, $ctype, $I18N->msg("label_".$type)));
            }
        }
        else {
            cjoMessage::addError($I18N->msg("msg_modul_layout_no_deleted_default"));
        }
        unset($template);
        unset($ctype);
        unset($type);
        unset($action);
    }
}


$templates= array();
$templates[0] = '';

$data = array();
$used = array();

$sql = new cjoSql();
$qry = "SELECT CONCAT(name,' (ID=',id,')') AS name, id
		FROM ".TBL_TEMPLATES."
		WHERE active = 1
		ORDER BY prior";
$sql->setQuery($qry);

for ($i=0; $i<$sql->getRows(); $i++){
    $templates[$sql->getValue('id')] = $sql->getValue('name');
    $sql->next();
}  

foreach ($templates as $c_tmpl_id => $c_tmpl_name){

    $data_temp = array();

    $data_temp['template_id'] = $c_tmpl_id;
    $data_temp['templates'] = ($c_tmpl_id==0) ? $I18N->msg("label_default_template") : $c_tmpl_name;

    foreach($CJO['CTYPE'] as $c_ctype_id=>$c_ctype_name){

        $input_path  = cjoModulTemplate::getTemplatePath($oid,$c_tmpl_id,$c_ctype_id,'input');
        $output_path = cjoModulTemplate::getTemplatePath($oid,$c_tmpl_id,$c_ctype_id,'output');
        
        $has_curr_template = $curr_modultyp['templates'] === 0 || strpos($curr_modultyp['templates'], '|0|') !== false || strpos($curr_modultyp['templates'], '|'.$c_tmpl_id.'|') !== false;
        $has_curr_ctype    = strpos($curr_modultyp['ctypes'], '|'.$c_ctype_id.'|') !== false;   

        $has_curr_template = $curr_modultyp['templates'] === 0 || strpos($curr_modultyp['templates'], '|0|') !== false || strpos($curr_modultyp['templates'], '|'.$c_tmpl_id.'|') !== false;
        $has_curr_ctype    = strpos($curr_modultyp['ctypes'], '|'.$c_ctype_id.'|') !== false;   

		$buttons['input'] = new buttonField();
		$buttons['input']->addButton('input_add_button_'.$c_tmpl_id.$c_ctype_id, $I18N->msg("label_input").' '.$I18N->msg('button_add'), true, 'img/silk_icons/add.png');
		$buttons['input']->setButtonAttributes('input_add_button_'.$c_tmpl_id.$c_ctype_id, 'class="small"');
		$buttons['input']->setButtonAttributes('input_add_button_'.$c_tmpl_id.$c_ctype_id,
											   'onclick="cjo.jconfirm($(this), \'cjo.changeLocation\', [\''.
        cjoAssistance::createBEUrl(array('mode'=>$mode, 'oid'=>$oid, 'template'=>$c_tmpl_id, 'ctype'=>$c_ctype_id, 'type'=>'input', 'action'=>'add'), array(), '&amp;').
											   '\'])"');

        $buttons['input']->addButton('input_edit_button_'.$c_tmpl_id.$c_ctype_id, $I18N->msg("label_input").' '.$I18N->msg('button_edit'), true, 'img/silk_icons/page_white_edit.png');
        $buttons['input']->setButtonAttributes('input_edit_button_'.$c_tmpl_id.$c_ctype_id, 'class="small"');
        $buttons['input']->setButtonAttributes('input_edit_button_'.$c_tmpl_id.$c_ctype_id,
											   'onclick="cjo.changeLocation(\''.
        cjoAssistance::createBEUrl(array('mode'=>$mode, 'oid'=>$oid, 'template'=>$c_tmpl_id, 'ctype'=>$c_ctype_id, 'type'=>'input', 'action'=>'edit'), array(), '&amp;').
											   '\')"');

        $buttons['input']->addButton('input_delete_button_'.$c_tmpl_id.$c_ctype_id, $I18N->msg("label_input").' '.$I18N->msg('button_delete'), true, 'img/silk_icons/bin.png');
        $buttons['input']->setButtonAttributes('input_delete_button_'.$c_tmpl_id.$c_ctype_id, 'class="small"');
        $buttons['input']->setButtonAttributes('input_delete_button_'.$c_tmpl_id.$c_ctype_id,
											   'onclick="cjo.jconfirm($(this), \'cjo.changeLocation\', [\''.
        cjoAssistance::createBEUrl(array('mode'=>$mode, 'oid'=>$oid, 'template'=>$c_tmpl_id, 'ctype'=>$c_ctype_id, 'type'=>'input', 'action'=>'delete'), array(), '&amp;').
											   '\'])"');

        $buttons['output'] = new buttonField();
        $buttons['output']->addButton('output_add_button_'.$c_tmpl_id.$c_ctype_id, $I18N->msg("label_output").' '.$I18N->msg('button_add'), true, 'img/silk_icons/add.png');
        $buttons['output']->setButtonAttributes('output_add_button_'.$c_tmpl_id.$c_ctype_id, 'class="small"');
        $buttons['output']->setButtonAttributes('output_add_button_'.$c_tmpl_id.$c_ctype_id,
											   'onclick="cjo.jconfirm($(this), \'cjo.changeLocation\', [\''.
        cjoAssistance::createBEUrl(array('mode'=>$mode, 'oid'=>$oid, 'template'=>$c_tmpl_id, 'ctype'=>$c_ctype_id, 'type'=>'output', 'action'=>'add'), array(), '&amp;').
											   '\'])"');

        $buttons['output']->addButton('output_edit_button_'.$c_tmpl_id.$c_ctype_id, $I18N->msg("label_output").' '.$I18N->msg('button_edit'), true, 'img/silk_icons/page_white_edit.png');
        $buttons['output']->setButtonAttributes('output_edit_button_'.$c_tmpl_id.$c_ctype_id, 'class="small"');
        $buttons['output']->setButtonAttributes('output_edit_button_'.$c_tmpl_id.$c_ctype_id,
											    'onclick="cjo.changeLocation(\''.
        cjoAssistance::createBEUrl(array('mode'=>$mode, 'oid'=>$oid, 'template'=>$c_tmpl_id, 'ctype'=>$c_ctype_id, 'type'=>'output', 'action'=>'edit'), array(), '&amp;').
											    '\')"');

        $buttons['output']->addButton('output_delete_button_'.$c_tmpl_id.$c_ctype_id, $I18N->msg("label_output").' '.$I18N->msg('button_delete'), true, 'img/silk_icons/bin.png');
        $buttons['output']->setButtonAttributes('output_delete_button_'.$c_tmpl_id.$c_ctype_id, 'class="small"');
        $buttons['output']->setButtonAttributes('output_delete_button_'.$c_tmpl_id.$c_ctype_id,
											   'onclick="cjo.jconfirm($(this), \'cjo.changeLocation\', [\''.
        cjoAssistance::createBEUrl(array('mode'=>$mode, 'oid'=>$oid, 'template'=>$c_tmpl_id, 'ctype'=>$c_ctype_id, 'type'=>'output', 'action'=>'delete'), array(), '&amp;').
											   '\'])"');

        if(!isset($used[$input_path]) && !empty($input_path)){
            $used[$input_path] = array('template_id'=>$c_tmpl_id, 'ctype'=>$c_ctype_id);
            $buttons['input']->setButtonStatus('input_add_button_'.$c_tmpl_id.$c_ctype_id, false);
            $buttons['input']->setButtonStatus('input_edit_button_'.$c_tmpl_id.$c_ctype_id, true);
            $buttons['input']->setButtonStatus('input_delete_button_'.$c_tmpl_id.$c_ctype_id, ($c_tmpl_id > 0 || $c_ctype_id > 0));
        }
        else {
            $buttons['input']->setButtonStatus('input_add_button_'.$c_tmpl_id.$c_ctype_id, true);
            $buttons['input']->setButtonStatus('input_edit_button_'.$c_tmpl_id.$c_ctype_id, false);
            $buttons['input']->setButtonStatus('input_delete_button_'.$c_tmpl_id.$c_ctype_id, false);
        }

        if (!isset($used[$output_path]) && !empty($output_path)){
            $used[$output_path] = array('template_id'=>$c_tmpl_id, 'ctype'=>$c_ctype_id);
            $buttons['output']->setButtonStatus('output_add_button_'.$c_tmpl_id.$c_ctype_id, false);
            $buttons['output']->setButtonStatus('output_edit_button_'.$c_tmpl_id.$c_ctype_id, true);
            $buttons['output']->setButtonStatus('output_delete_button_'.$c_tmpl_id.$c_ctype_id, ($c_tmpl_id > 0 || $c_ctype_id > 0));
        }
        else {
            $buttons['output']->setButtonStatus('output_add_button_'.$c_tmpl_id.$c_ctype_id, true);
            $buttons['output']->setButtonStatus('output_edit_button_'.$c_tmpl_id.$c_ctype_id, false);
            $buttons['output']->setButtonStatus('output_delete_button_'.$c_tmpl_id.$c_ctype_id, false);
        }

        if ($c_tmpl_id == $template && $ctype == $c_ctype_id && $type == 'input')
          $buttons['input']->setButtonAttributes('input_edit_button_'.$c_tmpl_id.$c_ctype_id, 'disabled="disabled"');

        if ($c_tmpl_id == $template && $ctype == $c_ctype_id && $type == 'output')
            $buttons['output']->setButtonAttributes('output_edit_button_'.$c_tmpl_id.$c_ctype_id, 'disabled="disabled"');


         if (($c_tmpl_id > 0 || $c_ctype_id > 0) && !($has_curr_template && $has_curr_ctype)) {               
			foreach($buttons['input']->getButtons() as $name=>$params){
			    if(strpos($name,'button_0') === false)
				$buttons['input']->setButtonAttributes($name, 'disabled="disabled"');
			}
			foreach($buttons['output']->getButtons() as $name=>$params){
			    if(strpos($name,'button_0') === false)
				$buttons['output']->setButtonAttributes($name, 'disabled="disabled"');
			}
		}

        $data_temp['ctypes'.$c_ctype_id] = $buttons['input']->_get().' | '.$buttons['output']->_get();
    }
    $data[] = $data_temp;
}

?>
<span style="clear: both;"></span>
<?php

unset($_GET['oid']);

//LIST Ausgabe
$list = new cjolist();
$list->curr_rows = $data;

$cols['icon'] = new staticColumn('<img src="img/silk_icons/layout.png" alt="" />', '');
$cols['icon']->setHeadAttributes('class="icon"');
$cols['icon']->setBodyAttributes('class="icon"');
$cols['icon']->delOption(OPT_ALL);

$style_width = 96/(count($CJO['CTYPE'])+1);

$cols['templates'] = new resultColumn('templates', $I18N->msg("title_templates").' / '.$I18N->msg("title_ctypes"));
$cols['templates']->setBodyAttributes('style="width:'.$style_width.'%"');
$cols['templates']->delOption(OPT_ALL);

foreach($CJO['CTYPE'] as $c_ctype_id=>$c_ctype_name){
    $name = ($c_ctype_id==0) ? $c_ctype_name.' ('.$I18N->msg("label_default").')' : $c_ctype_name;
    $cols['ctypes'.$c_ctype_id] = new resultColumn('ctypes'.$c_ctype_id, $name);
    $cols['ctypes'.$c_ctype_id]->setHeadAttributes('style="width:'.$style_width.'%; text-align: center;"');
    $cols['ctypes'.$c_ctype_id]->setBodyAttributes('style="width:'.$style_width.'%; text-align: center; border-left: 1px solid #ddd;"');
    $cols['ctypes'.$c_ctype_id]->delOption(OPT_ALL);
}

//Spalten zur Anzeige hinzufügen
$list->addColumns($cols);
$list->show(false);

if (!$type) return false;

$dataset = array();
$dataset['path'] = cjoModulTemplate::getTemplatePath($oid,$template,$ctype,$type);
$dataset['html'] = is_readable($dataset['path']) ? file_get_contents($dataset['path']) : '';

//Form
$form = new cjoForm();
$form->setEditMode(true);
//$form->debug = true;

$hidden['mode'] = new hiddenField('mode');
$hidden['mode']->setValue($mode);

$hidden['oid'] = new hiddenField('oid');
$hidden['oid']->setValue($oid);

$hidden['template'] = new hiddenField('template');
$hidden['template']->setValue($template);

$hidden['ctype'] = new hiddenField('ctype');
$hidden['ctype']->setValue($ctype);

$hidden['type'] = new hiddenField('type');
$hidden['type']->setValue($type);

$hidden['action'] = new hiddenField('action');
$hidden['action']->setValue('edit');

//Fields
$fields['path'] = new readOnlyField('path', $I18N->msg("label_path"), array('class' => 'large_item'));
$fields['path']->activateSave(false);

$fields['html'] = new codeField('html', $I18N->msg("label_".$type));
$fields['html']->addAttribute('class', 'inp75');
$fields['html']->addAttribute('rows', '30');
$fields['html']->activateSave(false);
$fields['html']->setNote('<a href="http://contejo.com/contejo-variablen.104.0.html" target="_blank" title="'.$I18N->msg("label_help").'"><img src="./img/silk_icons/help.png" alt="?" /></a>');

$fields['path_hidden'] = new hiddenField('path', array(), 'hidden_path');
$fields['path_hidden']->activateSave(false);

//Add Fields:
$section = new cjoFormSection(TBL_MODULES, '', array ('id' => $oid));
$section->dataset = $dataset;

$section->addFields($fields);
$form->addSection($section);
$form->addFields($hidden);
$form->show();

if ($form->validate()) {

	if (cjoGenerate::putFileContents(cjo_post('path','string'), stripslashes(cjo_post('html','string')))){
	    
        cjoExtension::registerExtensionPoint('MODULE_UPDATED', 
                                             array('ACTION' => 'LAYOUT_UPDATED',
                                                   'moduletyp_id' => $oid,
                                                   'template_id' => $template,
                                                   'ctype' => $ctype,
                                                   'type' => $type));  

		if (cjo_post('cjoform_save_button', 'boolean')) {
			cjoAssistance::redirectBE(array('mode'=>$mode, 'oid'=>$oid, 'action'=>'', 'msg'=>'msg_data_saved'));
		}
		else {
			cjoAssistance::redirectBE(array('mode'=>$mode, 'oid'=>$oid, 'template'=>$template, 'ctype'=>$ctype, 'type'=>$type, 'action'=>'', 'msg'=>'msg_data_saved'));
		}
	}
	else {
		cjoMessage::addError($I18N->msg("msg_data_not_saved"));
		cjoMessage::addError($I18N->msg("msg_file_no_chmod",
		                     cjoAssistance::absPath($config_file)));
	}
}