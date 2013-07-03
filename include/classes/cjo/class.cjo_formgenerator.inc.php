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

/**
 * cjoFormGenerator class
 *
 * The cjoFormGenerator provides a flexible way for creating 
 * forms in the frontend.
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoFormGenerator {

    public $is_valid;  
    public $is_submit;  
    public $has_errors;         
    public $sender_email;
    public $name;   
    public $errors;           
    public $post_action;
    public $settings;
    public $equal_values;
    public $default_values;    
    public $elements_in;
    public $elements_out;
    public $elements_mail;
    public $mail_replace;    
    public $mail_text;           
    public $confirm_mail_text;   
    public $mail_separator;
    public $error_html;
    public $message_html;
    public $after_action_output;
    public $function_name  = 'cjo_performPostAction';
    
    private $slice_value_id = 11;    
    
    public function __construct() {
 
        $this->is_valid            = Null;  
        $this->is_submit           = false;  
        $this->has_errors          = false;         
        $this->sender_email        = false;
        $this->name                = false;   
        $this->errors              = array();           
        $this->post_action         = array();
        $this->settings            = array();
        $this->equal_values        = array();
        $this->default_values      = array();
        $this->elements_in         = array();
        $this->elements_out        = array();
        $this->elements_mail       = array();
        $this->mail_replace        = array();
        $this->after_action_output = array('before', 'after');        
        $this->mail_text           = '';           
        $this->confirm_mail_text   = '';  
        $this->mail_separator      = "\r\n\r\n\r\n---------------------------------------------------\r\n";           
        $this->error_html          = '<p class="error">%s</p>';  
        $this->message_html        = '<div class="statusmessage">%s</div>';
        
        $this->setName('form_'.md5(time()));
    }
    
    public function setName($name) {
        global $CJO;
        
        if ($CJO['CONTEJO']) return false;
        
        $name = trim($name);
        
        if ($name == '') {
            $name = 'LOGIN';
        }
        elseif(!preg_match('/^[a-z0-9_\- ]+$/i', $name)) {
            $name = 'form_'.substr(md5($name), 0, 6);            
        }
        else {
            $name = str_replace(' ','', strtolower($name));  
        }

        $this->name = $name;
    }
    
    public function getName() {
        return $this->name;
    }   
      
    public function setRecipients($recipients) {
        $this->settings['recipients'] = $recipients;
    }
    
    public function setConfirmMail($status) {
        $this->settings['confirm_mail'] = !empty($status);
    }
    
    public function setSubject($subject) {
        $this->settings['subject'] = $subject;
    }
    
    public function setPHPMailerAccount($id) {
        $this->settings['phpmailer_id'] = $id;
    }
    
    public function setPHPMailer($status) {
        $this->settings['phpmailer_enabled'] = (bool) $status;
    }   
    
    public function isPHPMailerEnabled(){
        return $this->settings['phpmailer_enabled'];
    }
     
    public function setReturnMailtext($text) {
        $this->confirm_mail_text = $text;
    }
    
    private function addMailReplaceValue($key, $value) {
        if (!isset($this->mail_replace['%'.strtolower($key).'%']))
            $this->mail_replace['%'.strtolower($key).'%'] = $value;
    }
        
    public function setScriptTemplate($template_id) {
        
        global $CJO, $I18N;
        
        if (!$template_id) return false;

        if (!file_exists($CJO['FOLDER_GENERATED_TEMPLATES'].'/'.$template_id.'.template')) {
            cjoGenerate::generateTemplates($template_id);
        }
        
        if (!file_exists($CJO['FOLDER_GENERATED_TEMPLATES'].'/'.$template_id.'.template')) {
            $this->addError($I18N->msg('msg_err_template_include', $template_id));
        }
        else {
            $this->settings['template_id'] = $template_id;
        }
    } 
    
    
    public function addAttachments($attachments) {
        $this->settings['attachments'] = $attachments;
    }   
    
    public function addError($error) {
        $this->errors[md5($error)] = $error;
        $this->is_valid = false;
    }
    
    public function getErrors($render = true) {
        if (empty($this->errors)) return false;
        $errors = '';

        foreach($this->errors as $error) {
           $errors .= sprintf($this->error_html, $error);
        }
        $message = sprintf($this->message_html, $errors);
        
        if ($render) echo $message; else return $message;
    }    
    
    public function setDefaults($defaults) {
        list($this->equal_values, $this->default_values) = $defaults;
    }
    
    public function getFormElementsFromSlice($slice_id) {
        
        $slice = OOArticleSlice::getArticleSliceById($slice_id);
        if (!OOArticleSlice::isValid($slice)) return false;
        $this->convertFormElements($slice->getValue($this->slice_value_id));
    }
    
    public function processFormElements() {

        global $CJO, $I18N;

        $form_name = & $this->name;
        $elements_out = & $this->elements_out;
        $elements_mail = & $this->elements_mail;        

        $i = 0;
        foreach (cjoAssistance::toArray($this->elements_in) as $elm){

            $name = $elm['name'];
            $send_value = $this->send_data[$name];

            $elements_out['element_name'][$i] 	  = $form_name.'['.$elm['name'].']';            
            $elements_out['element_label'][$i] 	  = $elm['label'];
            $elements_out['element_required'][$i] = (bool) $elm['required'];
            $elements_out['element_class'][$i]    = $elm['css'] == '' ? 'form_elm_norm' : $elm['css'];
            $elements_out['element_id'][$i] 	  = $form_name.'_el_'.$i;
            $elements_out['element_row_id'][$i]   = $form_name.'_'.$elm['name'];

            if ($elements_out['element_required'][$i]){
                $elements_out['element_class'][$i] .= ' required';
            }

            if ($send_value == '' && !$this->is_submit) {

               if (!isset($elm['default']) && isset($this->default_values[$name])) {
                    $elm['default'] = $this->default_values[$name];
               }
               $elements_out['element_value'][$i] = $elm['default'];
            }
            else {
                $elements_out['element_value'][$i] = $send_value;
            }
            
            if (!isset($elm['equal_value']) && 
                isset($this->equal_values[$name])) {
                $elm['equal_value'] = $this->equal_values[$name];
            }
            
            if ($this->is_submit) {
                $temp = self::validateFormElement($elm, $send_value);
                if ($temp !== false) {
                    $elements_out['element_error_msg'][$i] = $temp;
                    $this->has_errors = true;
                }
            }

            if ($elm['name'] == 'absendermail' ||
                $elm['name'] == 'sender_email' ) {
                $this->sender_email = $send_value;
            }
            
            
            $elements_out['element_title'][$i] = preg_replace('/\/[^\/]*$/', '', $elm['label']);

            switch($elm['type']){
                
                case "errors":
                    $elements_out['element_type_errors'][$i]   = true;
                    break;
                    
                case "headline":
                    $elements_out['element_type_headline'][$i] = true;
                    $elements_out['element_value'][$i]         = $elm['default'];
                    
                    $elements_mail['element_label'][$i]        = '';                    
                    $elements_mail['element_value'][$i]        = "\r\n".$elm['default']."\r\n";
                    break;

                case "fieldset":
                    $elements_out['element_type_fieldset'][$i] = true;
                    $elements_out['element_value'][$i]         = $elm['default'];
                    $elements_out['element_class'][$i]         = str_replace('form_elm_norm', '',$elements_out['element_class'][$i]);
                                        
                    
                    $elements_mail['element_label'][$i] 	   = '';
                    $elements_mail['element_value'][$i]        = "------------------------------------------------";
                    break;

				case "separator":
                case "trennelement":
                    $elements_out['element_type_'.$elm['type']][$i] = true;
                    
                    $elements_mail['element_label'][$i] 		= '';
                    $elements_mail['element_value'][$i]         = '';                    
                    break;

				case "notice":
                case "hinweistext":
                case "advice":
                case "hinweis":
                    $elements_out['element_type_'.$elm['type']][$i] = true;
                    $elements_out['element_value'][$i]              = $elm['default'];
                    break;

                case "hidden":
                    $elements_out['element_type_hidden'][$i] = true;
                    break;

                case "text":
                    $elements_out['element_type_text'][$i] = true;

                    $elements_mail['element_name'][$i] 	   = $elm['name'];
                    $elements_mail['element_label'][$i]    = $elm['label'];
                    $elements_mail['element_value'][$i]    = $send_value;
                    
                    break;

                case "password":
                    $elements_out['element_type_password'][$i] = true;

                    $elements_mail['element_name'][$i] 	 	   = $elm['name'];
                    $elements_mail['element_label'][$i] 	   = $elm['label'];
                    $elements_mail['element_value'][$i]   	   = $send_value;
                    break;

                case "textarea":
                    $elements_out['element_type_textarea'][$i] = true;

                    $elements_mail['element_name'][$i] 	 	   = $elm['name'];
                    $elements_mail['element_label'][$i] 	   = $elm['label'];
                    $elements_mail['element_value'][$i]   	   = "\r\n".$send_value;
                    break;

                case "checkbox":

                    $elements_out['element_type_checkbox'][$i] = true;
                    $elements_out['checkbox_label'][$i] 	   = $elm['label'];
                    $elements_out['element_label'][$i] 		   = ' ';
                    $elements_out['element_checked'][$i]       = cjoAssistance::setChecked($elements_out['element_value'][$i], array($elm['value']));
                    $elements_out['element_value'][$i]   	   = $elm['value'];
                    $elements_out['element_class'][$i]         = str_replace('form_elm_norm', '',$elements_out['element_class'][$i]);

                    if (!empty($send_value)) {
                  	    $elements_mail['element_name'][$i]  = $elm['name'];
                        $elements_mail['element_label'][$i] = $elm['label'];
                        $elements_mail['element_value'][$i] = $send_value;
                    }
                    break;
                    
                case "radio":

                    $elements_out['element_type_radio'][$i]    = true;
                    $elements_out['radio_label'][$i] 	       = $elm['label'];
                    $elements_out['element_label'][$i] 		   = ' ';
                    $elements_out['element_checked'][$i]       = cjoAssistance::setChecked($elements_out['element_value'][$i], array($elm['value']));
                    $elements_out['element_value'][$i]   	   = $elm['value'];
                    $elements_out['element_class'][$i]         = str_replace('form_elm_norm', '',$elements_out['element_class'][$i]);
                    
                    if (!empty($send_value)) {
                  	    $elements_mail['element_name'][$i]  = $elm['name'];
                        $elements_mail['element_label'][$i] = $elm['label'];
                        $elements_mail['element_value'][$i] = $send_value;
                    }
                    break;
                    
                case "select":
                    $elements_out['element_type_select'][$i] = true;
                    
                    if ($elements_out['element_required'][$i])
                        $elm['css'] .= ' required'; 
                    
                    $elements_out['element_class'][$i] = $elm['css'];   
                    
                    $sel = new cjoSelect();
                    $sel->setName($elements_out['element_name'][$i]);
                    $sel->setId($elements_out['element_id'][$i]);
                    $sel->setSize(1);
                    $sel->setStyle('class="'.$elm['css'].'"');      
                    $elements_out['element_value'][$i] = $send_value ? $send_value : $elm['default'];
                    self::getFormSelectOptions($sel, $elm['values']);
                    $sel->setSelected($elements_out['element_value'][$i]);
                    $sel->setSelectExtra('title="'.$elements_out['element_title'][$i].'"');                    
                    $elements_out['element_select_out'][$i] = $sel->get();

                    $elements_mail['element_name'][$i] 	 	 = $elm['name'];
                    $elements_mail['element_label'][$i] 	 = $elm['label'];
                    $elements_mail['element_value'][$i]   	 = $send_value;
                    break;

                case "antispam":
                    $elements_out['element_type_antispam'][$i] = true;
                    $elements_out['element_required'][$i]      = true;
                    $elements_out['element_name'][$i] 	   	   = 'antispam';
                    $elements_out['element_value'][$i]		   = $this->is_submit ? cjo_post('antispam', 'int', '', true) : '';
                    $elements_out['element_value'][$i]		   = empty($elements_out['element_value'][$i]) ? '' : $elements_out['element_value'][$i];
                    $elements_out['element_class'][$i] 	      .= ' required';

                    srand ((double)microtime()*1000000);
                    $x = rand(1, 9);
                    $y = rand(1, 9);

                    if (rand(0, 1) == 0){
                        $result = $x + $y;
                        $output = $x.' + '.$y.' =';
                    }
                    else {
                        if ($x > $y) {
                            $result = $x - $y;
                            $output = $x.' - '.$y.' =';
                        } elseif ($x < $y) {
                            $result = $y - $x;
                            $output = $y.' - '.$x.' =';
                        } else {
                            $x++;
                            $result = $x - $y;
                            $output = $x.' - '.$y.' =';
                        }
                    }

                    $elements_out['security_label'][$i] = $elements_out['element_label'][$i];
                    $elements_out['element_label'][$i]
                        = ($this->is_submit && cjo_post('antispam_label', 'bool'))
                        ? cjo_post('antispam_label', 'string','',true)
                        : $output;

                    $elements_out['mathresult'][$i]
                        = ($this->is_submit && cjo_post('antispam_label', 'bool'))
                        ? cjo_post('mathresult', 'string','',true)
                        : $result * self::getAntispamFactor();                   

                    if ($this->is_submit) {
                        $temp = self::runValidator(cjo_post('antispam', 'int'), 
                        						   'antispam', 
                                                   $elm['error_msg'],
                                                   cjo_post('mathresult', 'string'), 
                                                   true);
                    if ($temp) {
                        $elements_out['element_error_msg'][$i] = $temp;
                        $elements_out['element_value'][$i]     = '';
                        $this->has_errors = true;
                    }
                }
                break;

                default: continue 2;
            }
            
            if (!empty($elements_mail['element_name'][$i])) {
                $this->addMailReplaceValue($elements_mail['element_name'][$i],$elements_mail['element_value'][$i]);
            }
            $i++;
        }        

		if ($this->is_submit && 
		    $this->is_valid !== false) {
		        
		    $this->is_valid = !$this->has_errors;
		}
            
        if (is_array($elements_out['element_type_errors'])) {
            foreach (array_keys($elements_out['element_type_errors']) as $i) {
                $elements_out['has_errors'][$i] = $this->has_errors;
            }
        }
            
    }
    
    public function getFormeElmentsOut() {
        return $this->elements_out;
    }
    
    public function getErrorMessages() {
        return array('element_error_msg' => $this->elements_out['element_error_msg']);
    }
    
    public function hasFormeElments() {
        return !empty($this->elements_out);
    }
    
    public function hasAfterActionOutput($type) {
        return !empty($this->after_action_output[$type]);
    }
    
    private static function validateFormElement($elm, $send_value, $anti_spam = false) {

        global $CJO;
        
        $validators = explode("|",preg_replace('/(?<!\\\)(\(|\))|\\\/ims', '', $elm['validate']));
        
        $messages   = preg_replace('/(?<!\\\)(\(|\))|\\\/ims', '', $elm['error_msg']);
	    $messages   = strpos($messages,'|') !== false ? explode("|",$messages) : $messages;
        $equals     = preg_replace('/(?<!\\\)(\(|\))|\\\/ims', '', $elm['equal_value']);
        $equals     = strpos($equals,'|') !== false ?  explode("|",$equals) : $equals;	    
	    $errors     = array();

   
		foreach(cjoAssistance::toArray($validators) as $key => $validator) {
		    
            if ($validator != '' && (
	            $elm['required'] == 1 || $send_value != '')) {
                $message = is_array($messages) ? $messages[$key] : $messages;
                $equal   = is_array($equals) && strpos($validators[$key],'equal') === false ? $equals[$key] : $equals;
                $temp    = self::runValidator($send_value, $validators[$key], $message, $equal);
                if ($temp) {
                    return $temp;
                }
	        }
        }
        return false;    
    }
    
    /**
     * Validates a submitted value.
     * @param mixed $value
     * @param string $type
     * @param string $message
     * @param mixed $equal_value
     * @param boolean $anti_spam
     * @return string|boolean
     * @access public
     */
    public static function runValidator($value, $type='not_empty', $message='', $equal_value, $anti_spam = false) {

        global $CJO;

        $message = (empty($message)) ? '&nbsp;' : $message;             

        switch($type) {
            # is eMail-Adresse
            case "mail":
            case "email":
                if(!preg_match("/^([A-Z0-9._%+-])+@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4}|museum)$/imu",$value)) return $message;
                break;
            # Telefonnummern mindestens 6 Zahlen
            case "phone":
            case "telefon":
            case "telephone":
                if(!preg_match("/^[0-9\/\-\x20]{6,}+$/u",$value)) return $message;
                break;
            # Postleitzahlen
            case "plz":
                if(strlen($value)<5 || !ctype_digit($value)) return $message;
                break;
            # Prüft ob die eingegebenen Zeichen Buchstaben sind
            case "letters":
            case "letters_only":
                if (!preg_match("/^[a-zäöüß\x20]+$/iu",$value)) return $message;
                break;
            # Nur Preis
            case "price":
            case "preis":
                if(!preg_match ("/^\d{1,}(\.|\,)?\d{2}$/",$value)) return $message;
                break;
            # Nur Zahlen
            case "digit":
            case "digit_only":
            case "numeric":
                if (!ctype_digit($value)) return $message;
                break;
            # Nur Buchstaben
            case "alpha":
            case "alpha_only":
                if (!ctype_alpha($value)) return $message;
                break;
            # Nur Zahlen oder Buchstaben
            case "alphanumeric":
                if(!preg_match ("/^[\w0-9\#\~\|\_\.\:\!\-;\+\*\?\&\x20]*$/",$value)) return $message;
                break;
            # String länger als
            case "longer_than":
            case "longer_then":
                if (strlen($value) < $equal_value) return $message;
                break;
            # String kürzer als
            case "shorter_than":
            case "shorter_then":
                if (strlen($value) > $equal_value) return $message;
                break;
            # String hat Länge
            case "is_length":
                if (strlen($value) != $equal_value) return $message;
                break;
            # ist URL
            case "url":
                if (!preg_match("/^(http|https):\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?$/u",$value)) return $message;
                break;
            # größer als
            case "larger_than":
            case "larger_then":
                if ((int) $value < $equal_value) return $message;
                break;
            # kleiner als
            case "smaller_than":
            case "smaler_than":
            case "smaler_then":
                if ((int) $value > $equal_value) return $message;
                break;
            # gleich
            case "equal":
                $equal_values = cjoAssistance::toArray($equal_value);
                if (array_search($value, $equal_values, true) === false) return $message;
                break;
            # gleich mit MD5-Verschlüsselung
            case "equal_md5":
                $equal_values = cjoAssistance::toArray($equal_value);
                if (array_search(md5($value), $equal_values, true) === false) return $message;
                break;
            # ungleich
            case "not_equal":
                $equal_values = cjoAssistance::toArray($equal_value);
                if (array_search($value, $equal_values, true) !== false) return $message;
                break;
            # ist URL
            case "date":
            case "date_dd.mm.yyyy":
                if (!strtotime($value) || !preg_match('/^([0-3]{1}[\d]{1})\.([0-1]{1}[\d]{1})\.([\d]{4})$/',$value)) return $message;
                break;
            # Prüfung Spam-Aufgabe
            case 'antispam':
                $factor   = self::getAntispamFactor();
                $antispam = cjo_post('antispam', 'float', '',true) ;
                if (!$anti_spam || $equal_value != $antispam * $factor) return $message;
                break;
            case 'not_empty_strict':
                if (empty($value)) return $message;
                break;                
            default:
                if (trim($value) == '') return $message;
                break;
        }
        return false;
    }    
    
    private function convertFormElements($fields) {

        $fields   = preg_split('/(?<!\\\)(\})/ims', $fields, -1);
        
		foreach(cjoAssistance::toArray($fields) as $key1 => $value1) {

            $temp = preg_split('/(?<!\\\)(\{)/ims', trim($value1), -1);
			if (empty($temp[0])) continue;

            $this->elements_in[$key1]['type'] = trim($temp[0]);
            $temp = preg_split('/(?<!\\\)(:|;)/ims', $temp[1], -1);

            for ($i = 0; $i < count($temp); $i++) {
                $key2 = trim($temp[$i]);
                $i++;
                $value2 = trim($temp[$i]);
                $this->elements_in[$key1][$key2] = stripcslashes($value2);
            }
        }
    }
    
    public function setElementValues($field, $values, $overwrite = true) {
        foreach($this->elements_in as $key => $element){
            if ($element['name'] != $field) continue;
            if (!$overwrite && isset($element['values'])) continue;
            $this->elements_in[$key]['values'] = $values;
        }
    }
    
    public function getAfterActionOutput($type) {
        echo $this->after_action_output[$type];
    }
    
    private function getSendData() {
        $this->send_data = cjo_request($this->name, 'array', array(), true);   
        $this->is_submit = (bool) $this->send_data['submit'];
    }
    
    private function updateClassVars($post_action) {
        $class_vars = get_class_vars(get_class($this));
        foreach($class_vars as $name=>$value){ 
            if (isset($post_action[$name])) {
                $this->{$name} = $post_action[$name];
            }
        }
    }
    
    private static function getFormSelectOptions(&$sel, $values) {

    	self::splitFormValues($values);
    	
    	foreach($values as $key => $value) {
    		$sel->addOption($value, $key);
    	}
    }
    
    private static function splitFormValues(&$values) {
    	
        if (is_array($values)) return;

        $temp = preg_replace('/^\s*\(|\)\s*$/', '', $values); 
    	$temp_array = explode("|",$temp);
	    $values = array();

        foreach($temp_array as $value) {
            
        	$temp  = explode("=",$value);
        	$key = ($temp[0] != '') ? trim($temp[0]) : trim($value);
	        $value  = (trim($temp[1]) != '') ? trim($temp[1]) : $key;

            $values[$key] = $value;
    	}
    }
    
    private static function getAntispamFactor() {
    	global $CJO;
        return (int) preg_replace('/\D/', '', $CJO['INSTNAME']);
    }    
    
    private function sendRecipientMail() {        
             
        if (!$this->settings['recipients']) return false;
        
     	if ($this->post_action['mail_body']) {
			$this->mail_text .= $this->post_action['mail_body'];
     	}
     	else {
     		foreach($this->elements_mail['element_label'] as $key=>$element) {
     		    if (empty($this->elements_mail['element_value'][$key])) continue;
                $this->mail_text .= ($element != '') ? "*".$element."*: " : "";
                $this->mail_text .= stripslashes($this->elements_mail['element_value'][$key])." \r\n";
            }
     	}

        if (!empty($this->post_action['subject'])) {
			$this->phpmailer->Subject = $this->post_action['subject'];
     	}

        if (!empty($this->post_action['attachment'])) {
			$this->phpmailer->AddAttachment($this->post_action['attachment']['path'],
			                          $this->post_action['attachment']['name'],
			                          $this->post_action['attachment']['encoding'],
			                          $this->post_action['attachment']['type']);
     	}

        $this->phpmailer->ClearReplyTos();
        $this->phpmailer->AddReplyTo($this->sender_email);
        $this->phpmailer->Body = $this->mail_text;

        $this->phpmailer->Send(true); //
    }
    
    private function sendConfirmMail() {
        
        if (!$this->sender_email || !$this->settings['confirm_mail']) return false;

        if ($this->settings['attachments']) {
        	$this->phpmailer->AddMedialistAttachment($this->settings['attachments']);
        }

		$this->phpmailer->ClearAddresses();
        $this->phpmailer->AddAddress($this->sender_email);
        
        $this->addMailReplaceValue('mail_body', $this->mail_text);

        if (is_array($this->mail_replace)) {
            foreach($this->mail_replace as $key=>$val) {
                $this->confirm_mail_text = str_replace($key, $val, $this->confirm_mail_text);
            }
        }
        
        $this->phpmailer->Body = $this->confirm_mail_text;
        $this->phpmailer->Send(true);
    }
    
    public function get(){
        
        global $CJO;
        
        $this->getSendData();      
        
        $this->phpmailer = new cjoPHPMailer();
        $this->phpmailer->IsHTML(false);
        $this->phpmailer->setAccount($this->settings['phpmailer_id']);
        $this->phpmailer->Subject = $this->settings['subject'];
        $this->phpmailer->AddAddresses($this->settings['recipients']);

        $callfunction = $this->function_name.'__'.md5($this->name);
        
        if ($this->settings['template_id']) {
            $template = file_get_contents($CJO['FOLDER_GENERATED_TEMPLATES']."/".$this->settings['template_id'].".template");
            $template = str_replace($this->function_name, $callfunction, $template);

            if (strpos($template, '<?') === false) {
                echo $template; 
            } else {
                eval('?> '.$template);
            }
        }

        $this->processFormElements();

        if ($this->is_valid !== true) return false;
        
        if ($this->settings['template_id'] &&
             function_exists($callfunction)) {
                $this->updateClassVars($callfunction($this));
        }

        $this->getErrors();

        if ($this->is_valid !== true || !$this->isPHPMailerEnabled()) return;

        $this->sendRecipientMail();
        $this->sendConfirmMail();
    }
}