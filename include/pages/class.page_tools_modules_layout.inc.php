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



class cjoPageToolsModulesLayout extends cjoPageToolsModules {

    private $path = array();

    protected function setAdd() {
        
        if (($this->params['type'] != 'input' && 
            $this->params['type'] != 'output') || 
            $this->params['delete'] == '1') return false;
        
        $this->setEdit();
        
        $dataset = array();
        $dataset['path'] = $this->getCurrentPath(self::getCurrModultyp('name'));
        $dataset['html'] = @file_get_contents(cjoModulTemplate::getTemplatePath($this->oid,0,0,$this->params['type']));

        $this->setMode(true);
        $this->setMessage('msg_modul_layout_added');
        $this->AddSection($dataset, '', array ('id' => ''));
    } 

    protected function setEdit() {
        
        if (($this->params['type'] != 'input' && 
            $this->params['type'] != 'output') || 
            $this->params['delete'] == '1')  return false;

        $this->getDefault();
        
        $dataset = array();
        $dataset['path'] = self::getRelativePath(cjoModulTemplate::getTemplatePath($this->oid,$this->params['template'],$this->params['ctype'],$this->params['type']));
        $dataset['html'] = is_readable($dataset['path']) ? file_get_contents($dataset['path']) : '';

        $this->fields['path'] = new readOnlyField('path', cjoI18N::translate("label_path"), array('class' => 'large_item'));
        $this->fields['path']->activateSave(false);
        
        $this->fields['html'] = new codeField('html', cjoI18N::translate("label_".$this->params['type']));
        $this->fields['html']->activateSave(false);
        $this->fields['html']->setNote('<a href="http://contejo.com/contejo-variablen.104.0.html" target="_blank" '.
                                       'title="'.cjoI18N::translate("label_help").'">'.
                                       '<img src="./img/silk_icons/help.png" alt="?" /></a>',
                                       ' style="float:right!important;position:static;width:auto;margin:5px 10px;"');
        
        $this->fields['path_hidden'] = new hiddenField('path', array(), 'hidden_path');
        $this->fields['path_hidden']->activateSave(false);

        $this->setMessage('msg_data_saved');
        $this->AddSection($dataset, '', array ('id' => $this->oid));
        $this->form->applyRedirectOnUpdate(array('function' => 'edit', 'type' => $this->params['type']));

    }  

    protected function getDefault() {

        if ($this->params['delete'] == '1') $this->deleteLayout();

        $templates= array('');
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
        
        foreach ($templates as $template_id => $template_name){
        
            $temp = array();
            $buttons = array();
        
            $temp['template_id'] = $template_id;
            $temp['templates'] = ($template_id==0) ? cjoI18N::translate("label_default_template") : $template_name;
        
            foreach(cjoProp::get('CTYPE') as $ctype_id=>$ctype_name){
                
                $url_params = array_merge($this->params, array('mode' => $this->params['mode'], 'oid'=>$this->oid, 'template'=>$template_id, 'ctype'=>$ctype_id, 'delete'=>NULL, 'msg'=>NULL));
        
                $input_path  = cjoModulTemplate::getTemplatePath($this->oid,$template_id,$ctype_id,'input',false);
                $output_path = cjoModulTemplate::getTemplatePath($this->oid,$template_id,$ctype_id,'output',false);

                $has_curr_template = self::getCurrModultyp('templates') === 0 || strpos(self::getCurrModultyp('templates'), '|0|') !== false || strpos(self::getCurrModultyp('templates'), '|'.$template_id.'|') !== false;
                $has_curr_ctype    = strpos(self::getCurrModultyp('ctypes'), '|'.$ctype_id.'|') !== false;   
        
                $has_curr_template = self::getCurrModultyp('templates') === 0 || strpos(self::getCurrModultyp('templates'), '|0|') !== false || strpos(self::getCurrModultyp('templates'), '|'.$template_id.'|') !== false;
                $has_curr_ctype    = strpos(self::getCurrModultyp('ctypes'), '|'.$ctype_id.'|') !== false;   
                
                $add_icon = 'img/silk_icons/add.png';
                
                if ($this->params['template'] == $template_id && 
                    $this->params['ctype'] == $ctype_id &&
                    $this->params['type'] == 'input' && 
                    $this->function == 'add')  $add_icon = 'img/silk_icons/page_white_edit.png';
        
                $buttons['input'] = new buttonField();
                $buttons['input']->addButton('input_add_button_'.$template_id.$ctype_id, cjoI18N::translate("label_input").' '.cjoI18N::translate('button_add'), true, $add_icon);
                $buttons['input']->setButtonAttributes('input_add_button_'.$template_id.$ctype_id, 'class="small"');
                $buttons['input']->setButtonAttributes('input_add_button_'.$template_id.$ctype_id,
                                                       'onclick="cjo.changeLocation(\''.
                                                        cjoUrl::createBEUrl(array('type'=>'input', 'function'=>'add'), $url_params, '&amp;').
                                                       '\')"');
        
                $buttons['input']->addButton('input_edit_button_'.$template_id.$ctype_id, cjoI18N::translate("label_input").' '.cjoI18N::translate('button_edit'), true, 'img/silk_icons/page_white_edit.png');
                $buttons['input']->setButtonAttributes('input_edit_button_'.$template_id.$ctype_id, 'class="small"');
                $buttons['input']->setButtonAttributes('input_edit_button_'.$template_id.$ctype_id,
                                                       'onclick="cjo.changeLocation(\''.
                                                        cjoUrl::createBEUrl(array('type'=>'input', 'function'=>'edit'), $url_params, '&amp;').
                                                       '\')"');
        
                $buttons['input']->addButton('input_delete_button_'.$template_id.$ctype_id, cjoI18N::translate("label_input").' '.cjoI18N::translate('button_delete'), true, 'img/silk_icons/bin.png');
                $buttons['input']->setButtonAttributes('input_delete_button_'.$template_id.$ctype_id, 'class="small"');
                $buttons['input']->setButtonAttributes('input_delete_button_'.$template_id.$ctype_id,
                                                       'onclick="cjo.jconfirm($(this), \'cjo.changeLocation\', [\''.
                                                        cjoUrl::createBEUrl(array('type'=>'input', 'function'=>'edit', 'delete'=>'1'), $url_params, '&amp;').
                                                       '\'])"');
                                                       
                $add_icon = 'img/silk_icons/add.png';
                
                if ($this->params['template'] == $template_id && 
                    $this->params['ctype'] == $ctype_id &&
                    $this->params['type'] == 'output' && 
                    $this->function == 'add')  $add_icon = 'img/silk_icons/page_white_edit.png';

                $buttons['output'] = new buttonField();
                $buttons['output']->addButton('output_add_button_'.$template_id.$ctype_id, cjoI18N::translate("label_output").' '.cjoI18N::translate('button_add'), true, $add_icon);
                $buttons['output']->setButtonAttributes('output_add_button_'.$template_id.$ctype_id, 'class="small"');
                $buttons['output']->setButtonAttributes('output_add_button_'.$template_id.$ctype_id,
                                                       'onclick="cjo.changeLocation(\''.
                                                        cjoUrl::createBEUrl(array('type'=>'output', 'function'=>'add'), $url_params, '&amp;').
                                                       '\')"');
        
                $buttons['output']->addButton('output_edit_button_'.$template_id.$ctype_id, cjoI18N::translate("label_output").' '.cjoI18N::translate('button_edit'), true, 'img/silk_icons/page_white_edit.png');
                $buttons['output']->setButtonAttributes('output_edit_button_'.$template_id.$ctype_id, 'class="small"');
                $buttons['output']->setButtonAttributes('output_edit_button_'.$template_id.$ctype_id,
                                                        'onclick="cjo.changeLocation(\''.
                                                        cjoUrl::createBEUrl(array('type'=>'output', 'function'=>'edit'), $url_params, '&amp;').
                                                        '\')"');
        
                $buttons['output']->addButton('output_delete_button_'.$template_id.$ctype_id, cjoI18N::translate("label_output").' '.cjoI18N::translate('button_delete'), true, 'img/silk_icons/bin.png');
                $buttons['output']->setButtonAttributes('output_delete_button_'.$template_id.$ctype_id, 'class="small"');
                $buttons['output']->setButtonAttributes('output_delete_button_'.$template_id.$ctype_id,
                                                       'onclick="cjo.jconfirm($(this), \'cjo.changeLocation\', [\''.
                                                        cjoUrl::createBEUrl(array('type'=>'output', 'function'=>'edit', 'delete'=>'1'), $url_params, '&amp;').
                                                       '\'])"');
        
                if (!isset($used[$input_path]) && !empty($input_path)) {
                    $used[$input_path] = array('template_id'=>$template_id, 'ctype'=>$ctype_id);
                    $buttons['input']->setButtonStatus('input_add_button_'.$template_id.$ctype_id, false);
                    $buttons['input']->setButtonStatus('input_edit_button_'.$template_id.$ctype_id, true);
                    $buttons['input']->setButtonStatus('input_delete_button_'.$template_id.$ctype_id, ($template_id > 0 || $ctype_id > 0));
                }
                else {
                    $buttons['input']->setButtonStatus('input_add_button_'.$template_id.$ctype_id, true);
                    $buttons['input']->setButtonStatus('input_edit_button_'.$template_id.$ctype_id, false);
                    $buttons['input']->setButtonStatus('input_delete_button_'.$template_id.$ctype_id, false);
                }
        
                if (!isset($used[$output_path]) && !empty($output_path)) {
                    $used[$output_path] = array('template_id'=>$template_id, 'ctype'=>$ctype_id);
                    $buttons['output']->setButtonStatus('output_add_button_'.$template_id.$ctype_id, false);
                    $buttons['output']->setButtonStatus('output_edit_button_'.$template_id.$ctype_id, true);
                    $buttons['output']->setButtonStatus('output_delete_button_'.$template_id.$ctype_id, ($template_id > 0 || $ctype_id > 0));
                }
                else {
                    $buttons['output']->setButtonStatus('output_add_button_'.$template_id.$ctype_id, true);
                    $buttons['output']->setButtonStatus('output_edit_button_'.$template_id.$ctype_id, false);
                    $buttons['output']->setButtonStatus('output_delete_button_'.$template_id.$ctype_id, false);
                }
        
                if ($template_id == $template && $ctype == $ctype_id && $type == 'input')
                  $buttons['input']->setButtonAttributes('input_edit_button_'.$template_id.$ctype_id, 'disabled="disabled"');
        
                if ($template_id == $template && $ctype == $ctype_id && $type == 'output')
                    $buttons['output']->setButtonAttributes('output_edit_button_'.$template_id.$ctype_id, 'disabled="disabled"');
        
        
                 if (($template_id > 0 || $ctype_id > 0) && !($has_curr_template && $has_curr_ctype)) {               
                    foreach($buttons['input']->getButtons() as $name=>$params){
                        if(strpos($name,'button_0') === false)
                        $buttons['input']->setButtonAttributes($name, 'disabled="disabled"');
                    }
                    foreach($buttons['output']->getButtons() as $name=>$params){
                        if(strpos($name,'button_0') === false)
                        $buttons['output']->setButtonAttributes($name, 'disabled="disabled"');
                    }
                }
        
                $temp['ctypes'.$ctype_id] = $buttons['input']->_get().' | '.$buttons['output']->_get();
            }
            $data[] = $temp;
        }

        $this->list = new cjoList();
        $this->list->curr_rows = $data;
        
        $this->cols['icon'] = new staticColumn('<img src="img/silk_icons/layout.png" alt="" />', '');
        $this->cols['icon']->setHeadAttributes('class="icon"');
        $this->cols['icon']->setBodyAttributes('class="icon"');
        $this->cols['icon']->delOption(OPT_ALL);
        
        $style_width = 96/(cjoProp::countCtypes()+1);
        
        $this->cols['templates'] = new resultColumn('templates', cjoI18N::translate("title_templates").' / '.cjoI18N::translate("title_ctypes"));
        $this->cols['templates']->setBodyAttributes('style="width:'.$style_width.'%"');
        $this->cols['templates']->delOption(OPT_ALL);
        
        foreach(cjoProp::get('CTYPE') as $ctype_id=>$ctype_name){
            $name = ($ctype_id==0) ? $ctype_name.' ('.cjoI18N::translate("label_default").')' : $ctype_name;
            $this->cols['ctypes'.$ctype_id] = new resultColumn('ctypes'.$ctype_id, $name);
            $this->cols['ctypes'.$ctype_id]->setHeadAttributes('style', 'width:'.$style_width.'%; text-align: center;');
            $this->cols['ctypes'.$ctype_id]->setBodyAttributes('style', 'width:'.$style_width.'%; text-align: center; border-left: 1px solid #ddd;');
            $this->cols['ctypes'.$ctype_id]->delOption(OPT_ALL);
        }
        
        $this->list->addColumns($this->cols);
        $this->list->show(false);
    }
    
    public static function onFormSaveorUpdate($params) {
               
        $posted = array();        
        $posted['path'] = cjo_post('path','string'); 
        $posted['html'] = stripslashes(cjo_post('html','string'));         
        $action = 'LAYOUT_UPDATED';
             
        if (self::isAddMode()) {
            $path = cjoAssistance::toArray($posted['path'],'/');
            array_pop($path);
            $temp = '';
            foreach($path as $val){
                $temp .= $val.'/';
                if (!file_exists($temp)){ mkdir($temp, cjoProp::getDirPerm()); }
            }
            $action = 'LAYOUT_ADDED';
        }  
        
        if (cjoGenerate::putFileContents($posted['path'], $posted['html'])){
            self::setSaveExtention(array('ACTION' => $action,
                                         'moduletyp_id' => cjo_get('oid', 'int'),
                                         'template_id' => cjo_get('template_id', 'int'),
                                         'ctype' => cjo_get('ctype', 'int'),
                                         'type' => cjo_get('type', 'string')),
                                         'MODULE_UPDATED');
        }
    }

    public function deleteLayout() {

        $default = cjoModulTemplate::getTemplatePath($this->oid,0,0,$this->params['type']);
        $current = cjoModulTemplate::getTemplatePath($this->oid,$this->params['template'],$this->params['ctype'],$this->params['type']);

        if ($default != $current){
            if (unlink($current)) {

                $path = array_reverse($this->getCurrentPath());
                foreach($path as $val){ @rmdir($val); }

                cjoMessage::addSuccess(cjoI18N::translate("msg_modul_layout_deleted", $this->params['template'], $this->params['ctype'], cjoI18N::translate("label_".$this->params['type'])));
                
                cjoExtension::registerExtensionPoint('MODULE_UPDATED', 
                                                     array('ACTION' => 'LAYOUT_DELETED',
                                                           'moduletyp_id' => $this->oid,
                                                           'template_id' => $this->params['template'],
                                                           'ctype' => $this->params['ctype'],
                                                           'type' => $this->params['type']));  
                unset($this->params['template']);
                unset($this->params['ctype']);
                unset($this->params['type']);           
            }
            else {
                cjoMessage::addError(cjoI18N::translate("msg_modul_layout_not_deleted", $this->params['template'], $this->params['ctype'], cjoI18N::translate("label_".$this->params['type'])));
            }
        }
        else {
            cjoMessage::addError(cjoI18N::translate("msg_modul_layout_no_deleted_default"));
        }
    } 
    
    private function getCurrentPath($name=NULL) {
        
        $this->path             = array();
        $this->path['path']     = self::getRelativePath();
        $this->path['type']     = $this->path['path'].'/'.$this->params['type'];

        if ($this->params['template'] > 0) {
            $this->path['type_template'] = $this->path['type'].'/'.$this->params['template'].'.template';
        }
        elseif ($this->params['template'] == 0 && $this->params['ctype'] > 0) {
            $this->path['type_ctype'] = $this->path['type'].'/'.$this->params['ctype'].'.ctype';
        }
        if ($this->params['template'] > 0 && $this->params['ctype'] > 0) {
            $this->path['type_template_ctype'] = $this->path['type_template'].'/'.$this->params['ctype'].'.ctype';
        }
        return $name === NULL  ? $this->path : array_pop($this->path).'/'.$this->oid.'.'.cjo_specialchars($name).'.'.$this->params['type'].'.'.liveEdit::getTmplExtension();
    }

    private static function getRelativePath($path=NULL) {
        if ($path === NULL) $path = liveEdit::getEditPath(liveEdit::getTmplExtension());
        return str_replace(cjoPath::absolute(cjoPath::base()),'..',$path);
    }
}