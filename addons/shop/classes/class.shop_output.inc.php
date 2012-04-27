<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  shop
 * @version     2.6.0
 *
 * @author      Matthias Schomacker <ms@raumsicht.com>
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

class shopOutput {

    
	public static function getFormElements($form_name, $form_fields, $is_valid = NULL) {

        global $CJO, $FORM_EQUAL_VALUE, $FORM_DEFAULT_VALUE;

        $form_elements_in = array();
        $form_elements    = array();
        $factor           = (int) preg_replace('/\D/', '', $CJO['INSTNAME']);
        $form_values      = cjo_request($form_name, 'array', array(), true);

		if (!is_array($form_fields)) $form_fields = self::convertFormFields($form_fields);

		$form_elements_in = $form_fields;
        $i = 0;
        foreach (cjoAssistance::toArray($form_elements_in) as $form_element){

            $name = $form_element['name'];
            $submitted_value = $form_values[$name];

            $form_elements['element_name'][$i] 	   	  = $form_name.'['.$form_element['name'].']';            
            $form_elements['element_label'][$i] 	  = $form_element['label'];
            $form_elements['element_required'][$i]    = $form_element['required'];
            $form_elements['element_class'][$i]  	  = $form_element['css'] == '' ? 'form_elm_norm' : $form_element['css'];
            $form_elements['element_id'][$i] 		  = $form_name.'_el_'.$i;
            $form_elements['element_row_id'][$i]      = $form_name.'_'.$form_element['name'];

            if ($form_elements['element_required'][$i]){
                $form_elements['element_class'][$i] .= ' required';
            }

            if ($submitted_value == '' && $_POST[$form_name]['submit'] == '') {

               if (!isset($form_element['default']) && isset($FORM_DEFAULT_VALUE[$form_name][$name])) {
                    $form_element['default'] = $FORM_DEFAULT_VALUE[$form_name][$name];
               }
               $form_elements['element_value'][$i] = $form_element['default'];
            }
            else {
                $form_elements['element_value'][$i] = $submitted_value;
            }

            if ($_POST[$form_name]['submit'] != '') {

	            $validates = explode("|",preg_replace('/(?<!\\\)(\(|\))|\\\/ims', '', $form_element['validate']));
	            $error_msgs = explode("|",preg_replace('/(?<!\\\)(\(|\))|\\\/ims', '', $form_element['error_msg']));
	            $error_out_temp = '';

				foreach(cjoAssistance::toArray($validates) as $key=>$validate_temp) {


                    if (!isset($form_element['equal_value']) && isset($FORM_EQUAL_VALUE[$form_name][$name])) {
                        $form_element['equal_value'] = $FORM_EQUAL_VALUE[$form_name][$name];
                    }

	                if ($validate_temp != '' && (
	                    $form_element['required'] == 1 ||
	                    $submitted_value != '')) {

	                    $error_msg_temp = $error_msgs[$key];
	                    $error_out_temp .= ($error_out_temp != '') ? '<br />' : '';
	                    $error_out_temp .= self::validateFormElement($submitted_value,
	                                                                      $validate_temp,
	                                                                      $error_msg_temp,
	                                                                      $form_element['equal_value']);
	                }
	            }
	            $form_elements['element_error_msg'][$i] = $error_out_temp;
	            $is_valid_temp = false;
            }

            if ($form_element['name'] == 'absendermail' ||
                $form_element['name'] == 'sender_email' ) {
                $sender_email = $submitted_value;
            }
            switch($form_element['type']){

                case "headline":
                    $form_elements['element_type_headline'][$i] = true;
                    $form_elements['element_value'][$i] = $form_element['default'];
                    
                    $mail_elements_out['element_label'][$i] = '';                    
                    $mail_elements_out['element_value'][$i] = "\r\n".$form_element['default']."\r\n";
                    break;

                case "fieldset":
                    $form_elements['element_type_fieldset'][$i] = true;
                    $form_elements['element_value'][$i] = $form_element['default'];
                    
                    $mail_elements_out['element_label'][$i] 	= '';
                    $mail_elements_out['element_value'][$i]     = "------------------------------------------------";
                    break;

				case "separator":
                case "trennelement":
                    $form_elements['element_type_'.$form_element['type']][$i] = true;
                    
                    $mail_elements_out['element_label'][$i] 			      = '';
                    $mail_elements_out['element_value'][$i]                   = '';                    
                    break;

				case "notice":
                case "hinweistext":
                case "advice":
                case "hinweis":
                    $form_elements['element_type_'.$form_element['type']][$i] = true;
                    $form_elements['element_value'][$i] = $form_element['default'];
                    break;

                case "hidden":
                    $form_elements['element_type_hidden'][$i] = true;
                    break;

                case "text":
                    $form_elements['element_type_text'][$i] = true;

                    $mail_elements_out['element_name'][$i] 	 	 	= $form_element['name'];
                    $mail_elements_out['element_label'][$i] 	 	= $form_element['label'];
                    $mail_elements_out['element_value'][$i]   		= $submitted_value;
                    break;

                case "password":
                    $form_elements['element_type_password'][$i] = true;

                    $mail_elements_out['element_name'][$i] 	 	 	= $form_element['name'];
                    $mail_elements_out['element_label'][$i] 	 	= $form_element['label'];
                    $mail_elements_out['element_value'][$i]   		= $submitted_value;
                    break;

                case "textarea":
                    $form_elements['element_type_textarea'][$i] = true;

                    $mail_elements_out['element_name'][$i] 	 	 	= $form_element['name'];
                    $mail_elements_out['element_label'][$i] 	 	= $form_element['label'];
                    $mail_elements_out['element_value'][$i]   		= "\r\n".$submitted_value;
                    break;

                case "checkbox":
                    $form_elements['element_type_checkbox'][$i] = true;
                    $form_elements['checkbox_label'][$i] 		= $form_element['label'];
                    $form_elements['element_label'][$i] 		= ' ';
                    $form_elements['element_checked'][$i]       = cjoAssistance::setChecked($form_elements['element_value'][$i], array($form_element['value']));
                    $form_elements['element_value'][$i]   		= $form_element['value'];

                    if (!empty($submitted_value)) {
                  	    $mail_elements_out['element_name'][$i]  = $form_element['name'];
                        $mail_elements_out['element_label'][$i] = $form_element['label'];
                        $mail_elements_out['element_value'][$i] = $submitted_value;
                    }
                    break;

                case "select":
                    $form_elements['element_type_select'][$i] = true;

                    $sel = new cjoSelect();
                    $sel->setName($form_elements['element_name'][$i]);
                    $sel->setId($form_elements['element_id'][$i]);
                    $sel->setSize(1);
                    $sel->setStyle('class="'.$form_element['css'].'"');
                    $form_elements['element_value'][$i] = $submitted_value ? $submitted_value : $form_element['default'];
                    self::getFormSelectOptions($sel, $form_element['values']);
                    $sel->setSelected($form_elements['element_value'][$i]);
                    $form_elements['element_select_out'][$i] = $sel->get();

                    $mail_elements_out['element_name'][$i] 	 	 = $form_element['name'];
                    $mail_elements_out['element_label'][$i] 	 = $form_element['label'];
                    $mail_elements_out['element_value'][$i]   	 = $submitted_value;
                    break;

                case "antispam":
                    $form_elements['element_type_antispam'][$i] = true;
                    $form_elements['element_required'][$i]    	= true;
                    $form_elements['element_name'][$i] 	   	    = 'antispam';
                    $form_elements['element_value'][$i]			= cjo_post('antispam', 'int');
                    $form_elements['element_value'][$i]			= empty($form_elements['element_value'][$i]) ? '' : $form_elements['element_value'][$i];
                    $form_elements['element_class'][$i] 	   .= ' required';

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

                    $form_elements['security_label'][$i] = $form_elements['element_label'][$i];
                    $form_elements['element_label'][$i]
                        = (cjo_post('antispam_label', 'bool'))
                        ? cjo_post('antispam_label', 'string')
                        : $output;

                    $form_elements['mathresult'][$i]
                        = (cjo_post('antispam_label', 'bool'))
                        ? cjo_post('mathresult', 'string')
                        : $result * $factor;

                    $form_elements['element_error_msg'][$i] = self::validateFormElement($submitted_value,
                                                                                		   		   'antispam',
                                                                                                   $form_element['error_msg'],
                                                                                                   $_POST['mathresult'],
                                                                                                   true);
                    break;

                default: continue 2;
            }

            $i++;
        }

        $errors = @implode('',$form_elements['element_error_msg']);
		if (isset($is_valid_temp) && $is_valid !== false) $is_valid = empty($errors);

        return array($form_elements, $mail_elements_out, $is_valid, $sender_email);
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
    public static function validateFormElement($value, $type='not_empty', $message='', $equal_value, $anti_spam = false) {

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
            case "phone":
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
                $factor = (int) preg_replace('/\D/', '', $CJO['INSTNAME']);
                if ($anti_spam && $_POST['mathresult'] != ($_POST['antispam'] * $factor)) return $message;
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

    private static function convertFormFields($form_fields) {

    	$form_fields   = preg_split('/(?<!\\\)(\})/ims', $form_fields, -1);
		foreach(cjoAssistance::toArray($form_fields) as $key1 => $value1) {

            $temp = preg_split('/(?<!\\\)(\{)/ims', trim($value1), -1);
			if (empty($temp[0])) continue;

            $form_elements_in[$key1]['type'] = trim($temp[0]);
            $temp = preg_split('/(?<!\\\)(:|;)/ims', $temp[1], -1);

            for ($i = 0; $i < count($temp); $i++) {
                $key2 = trim($temp[$i]);
                $i++;
                $value2 = trim($temp[$i]);
                $form_elements_in[$key1][$key2] = stripcslashes($value2);
            }
        }
        return $form_elements_in;
    }

    private static function getFormSelectOptions(&$sel, $element_values) {

    	self::splitFormValues($element_values);
    	
    	foreach($element_values as $key => $value) {
    		$sel->addOption($value, $key);
    	}
    }
    
    private static function splitFormValues(&$element_values) {
	
    	
        if (is_array($element_values)) return;

        $values = preg_replace('/^\s*\(|\)\s*$/', '', $element_values); 
    	$values = explode("|",$values);
	    	$element_values = array();

        foreach($values as $value) {
            
        	$temp  = explode("=",$value);
        	$key = ($temp[0] != '') ? trim($temp[0]) : trim($value);
	            $value  = (trim($temp[1]) != '') ? trim($temp[1]) : $key;

            $element_values[$key] = $value;
    	}
    }
}