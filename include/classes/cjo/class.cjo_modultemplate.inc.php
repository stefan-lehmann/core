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
 * cjoModulTemplate class
 *
 * The cjoModulTemplate class is used for easy customization of modul inputs and outputs.
 * @package 	contejo
 * @subpackage 	core
 */
class cjoModulTemplate extends cjoHtmlTemplate {
        
    /**
     * Returns the full path to the first matching HTML-template file in the
     * HTML-template directory. The search starts at the highest level of
     * differentiation where template and ctype have to correspond to
     * the given paramaters of the method. If there is no such file in
     * the directory tree, the search continues on the next lower level, where
     * either the template id or the ctype id must be matching. If there is no
     * such file either, the last possibility is a matching file at the default Level.
     * Finally if no matching HTML-template file has been found, a warning is echoed
     * and the metod returns false.
     *
     * EXAMPLE:
     *
     * call 1: getTemplatePath(3, 2, 1, 'input');<br/>
     * call 2: getTemplatePath(3, 1, 2, 'input');<br/>
     * call 3: getTemplatePath(3, 1, 2, 'input');<br/>
     * call 4: getTemplatePath(2, 2, 2, 'input');<br/>
     * call 5: getTemplatePath(2, 2, 1, 'input');<br/>
     *
     * Files in the template directory:<br/>
     * ./html/input/<br/>
     * ./html/input/1.foo.input.html<br/>
     * ./html/input/2.bar.input.html ........................ <-- return of call 5<br/>
     * ./html/input/3.foobar.input.html ..................... <-- return of call 3<br/>
     * ./html/input/2.ctype/3.foobar.input.html ............. <-- return of call 2<br/>
     * ./html/input/2.template/1.ctype/2.bar.input.html ..... <-- return of call 4<br/>
     * ./html/input/1.template/2.ctype/3.foobar.input.html .. <-- return of call 1<br/>
     * ./html/output/<br/>
     * ...
     *
     * @param int $modultyp_id the id of the current modul
     * @param int $template_id the id of the current template
     * @param int $ctype the id of the current ctype
     * @param string $type either 'input' or 'output'
     * @return string
	 * @access public
     */
	public static function getTemplatePath($modultyp_id='', $template_id, $ctype, $type='output') {

	    global $CJO;

	    $path['path'] = $CJO['ADDON']['settings']['developer']['edit_path'].'/'.
	                    $CJO['TMPL_FILE_TYPE'];

	    //$html_templates = $CJO['ADDON']['settings']['developer']['tmpl']['html'];
	    $extension 		= $type.'.'.$CJO['TMPL_FILE_TYPE'];

	   // if (empty($html_templates))
		$html_templates = cjoModulTemplate::readTemplateDir($path['path']);

	    $ctype = (empty($ctype) || $ctype == '-1') ? 0 : $ctype;

	    $path['path'] 	             = $CJO['ADDON']['settings']['developer']['edit_path'].'/'.
	                                   $CJO['TMPL_FILE_TYPE'];
	    $path['type'] 	  			 = $path['path'].'/'.$type;
	    $path['type_template'] 		 = $path['type'].'/'.$template_id.'.template';
	    $path['type_ctype'] 		 = $path['type'].'/'.$ctype.'.ctype';
	    $path['type_template_ctype'] = $path['type_template'].'/'.$ctype.'.ctype';

	    /**
	     * If matching HTML-template (eg. "./input/X.template/Y.ctype/Z.foo.input.html") exists
	     */
	    if (isset($html_templates[$path['type_template_ctype']]) && 
	        is_array($html_templates[$path['type_template_ctype']])) {
		    foreach($html_templates[$path['type_template_ctype']] as $file) {
		    	if (preg_match('/^'.$modultyp_id.'\.\S+?\.?'.$extension.'/',$file))
		    		return $path['type_template_ctype'].'/'.$file;
		    }
	    }

	    /**
	     * If matching HTML-template (eg. "./input/X.template/Z.foo.input.html") exists
	     */
	    if (isset($html_templates[$path['type_template']]) && 
            is_array($html_templates[$path['type_template']])) {
		    foreach($html_templates[$path['type_template']] as $file) {
		    	if (preg_match('/^'.$modultyp_id.'\.\S+?\.?'.$extension.'/',$file))
		    		return $path['type_template'].'/'.$file;
		    }
	    }

	    /**
	     * If matching HTML-template (eg. "./input/Y.ctype/Z.foo.input.html") exists
	     */
	    if (isset($html_templates[$path['type_ctype']]) &&
	        is_array($html_templates[$path['type_ctype']])) {
		    foreach($html_templates[$path['type_ctype']] as $file) {
		    	if (preg_match('/^'.$modultyp_id.'\.\S+?\.?'.$extension.'/',$file))
		    		return $path['type_ctype'].'/'.$file;
		    }
	    }

	    /**
	     * If matching HTML-template (eg. "./input/Z.foo.input.html") exists
	     */
	    if (isset($html_templates[$path['type']]) && 
	        is_array($html_templates[$path['type']])) {
		    foreach($html_templates[$path['type']] as $file) {
		    	if (preg_match('/^'.$modultyp_id.'\.\S+?\.?'.$extension.'/',$file))
		    		return $path['type'].'/'.$file;
		    }
	    }
	}

	/**
	 * Reads the HTML-template files recursively.
	 * @param string $directory the directory
	 * @return array
	 * @access public
	 */
	private static function readTemplateDir($directory = '') {

		global $CJO;

		$files = array();

		if ($directory == '' || !file_exists($directory)) return array();

		$handle = opendir($directory);

		while (false!==($item = readdir($handle))) {

			if ($item == '.' || $item == '..' || $item == '.svn')  continue;

			if (is_dir($directory.'/'.$item)) {
				$temp = cjoModulTemplate::readTemplateDir($directory.'/'.$item);
				if (is_array($temp)) $files = array_merge($files,$temp);
			}
			else {
				$files[$directory][] = $item;
			}
		}
		closedir($handle);

		return $files;
	}

    /**
	 * Adds search and replace values to the $CJO_MODUL_TMPL object.
	 * @param string $section the template section
     * @param array $replace_data search and replace vars (eg. array('seach_value1'=>'replace_value1', 'seach_value2'=>'replace_value2'))
     * @param object|boolean $slice commits a slice object for CONTEJO default replace vars
	 * @return void
	 * @access public
     */
    public static function addVars($section, $replace_data=array(), $slice = false) {
        global $CJO_MODUL_TMPL;
        $CJO_MODUL_TMPL->fillTemplate($section, $replace_data, $slice);
	}

	/**
	 * Adds a list of search and replace values to the $CJO_MODUL_TMPL object.
	 * @param string $section the template section
	 * @param array $replace_data the list of search and replace vars
     * @param object|boolean $slice commits a slice object for CONTEJO default replace vars
	 * @return boolean
	 */
    public static function addVarsArray($section, $replace_data, $slice = false) {
        global $CJO_MODUL_TMPL;
        $CJO_MODUL_TMPL->fillTemplateArray($section, $replace_data, $slice);
	}

    /**
     * Writes the $CJO_MODUL_TMPL object.
     * @return void
	 * @access public
     */
    public static function getModul() {
        global $CJO_MODUL_TMPL;
        $CJO_MODUL_TMPL->get();
    }
}