<?php


/**
 * Addon Framework Classes
 * @author staab[at]public-4u[dot]de Markus Staab
 * @author <a href="http://www.public-4u.de">www.public-4u.de</a>
 * @package contejo3
 * @version $Id: cjo_ValidateEngine.inc.class.php 1220 2011-03-28 08:42:16Z s_lehmann $
 */

/**
 * ValidierungsEngine der cjoForm Klasse
 * @see SmartyValidate
 */
class cjoValidateEngine extends SmartyValidate {

	// Statische Call auf Member Call mappen
	function register_criteria($name, $func_name, $form = SMARTY_VALIDATE_DEFAULT_FORM) {
		return parent::register_criteria($name, $func_name, $form);
	}

	// Statischer Call auf Member Call mappen
	function register_object($obj_name,&$object) {
		return parent::register_object($obj_name,$object);
	}
	
	
    public static function remember($form_name) {
        
        global $SMARTY_VALIDATE;
	
        if ($SMARTY_VALIDATE[$form_name]) $SMARTY_VALIDATE[$form_name]['cleanup'] = false;
        
        //cjo_Debug($SMARTY_VALIDATE[$form_name],$form_name,'orange');
	}
    	
    public static function prepare() {
        
        global $SMARTY_VALIDATE;
        
        if (!is_array($SMARTY_VALIDATE)) 
            $SMARTY_VALIDATE = cjo_session('SMARTY_VALIDATE', 'array', array());
        
        foreach($SMARTY_VALIDATE as $form_name=>$form){
            $SMARTY_VALIDATE[$form_name]['cleanup'] = true;
        }
	}
	
	public static function cleanup() {
	    
	    global $SMARTY_VALIDATE;

	    if (!is_array($SMARTY_VALIDATE)) return false;
	           // cjo_Debug($SMARTY_VALIDATE,'','yellow');
	    foreach($SMARTY_VALIDATE as $form_name=>$form){
	        // cjo_Debug($SMARTY_VALIDATE[$form_name], $form_name,'lightgreen');
	        if ($form['cleanup']) unset($SMARTY_VALIDATE[$form_name]);
	    }
	    cjo_set_session('SMARTY_VALIDATE', $SMARTY_VALIDATE);
         //cjo_Debug($_SESSION);
	}
	
	
}