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

class codeField extends textAreaField {

	public function codeField($name, $label, $attributes = array(), $id = '') {
	    if (!isset($attributes['width'])) $attributes['width'] = '750';
        if (!isset($attributes['height'])) $attributes['height'] = 550;
        
		$this->cjoFormField($name, $label, $attributes, $id);
	}


	public function get() {
	    
        global $I18N;

        cjo_insertJS(false, 'js/codemirror/lib/codemirror.js');
        cjo_insertJS(false, 'js/codemirror/lib/php.js');
        cjo_insertJS(false, 'js/codemirror/lib/javascript.js');
        cjo_insertJS(false, 'js/codemirror/lib/xml.js');
        cjo_insertJS(false, 'js/codemirror/lib/css.js');
        cjo_insertCSS(false, 'js/codemirror/lib/codemirror.css');
        
        $js = '<script type="text/javascript">'."\r\n".
             '/* <![CDATA[ */'."\r\n".
             'var editor = CodeMirror.fromTextArea(document.getElementById("'.$this->getId().'"), {'."\r\n".
             'mode: "application/xml", '."\r\n".
             'theme: "monokai", '."\r\n".
             'lineNumbers: true, '."\r\n".
             'lineWrapping: false, '."\r\n".        
             'indentUnit: 4, '."\r\n". 
             'indentWithTabs: false, '."\r\n".
             'extraKeys: { '."\r\n".
             '  "F11": function(cm) { cm_setFullScreen(cm, !cm_isFullScreen(cm)); }, '."\r\n".
             '  "Esc": function(cm) { if (cm_isFullScreen(cm)) cm_setFullScreen(cm, false); } '."\r\n".
             '}, '."\r\n".
             'onCursorActivity: function() { '."\r\n".
             '  editor.setLineClass(hlLine, null, null); '."\r\n".
             '  hlLine = editor.setLineClass(editor.getCursor().line, null, "activeline"); '."\r\n".
             '}'."\r\n".
             '});'."\r\n".
             'var hlLine = editor.setLineClass(0, "activeline");'."\r\n".
             '  editor.setSize("'.$this->attributes['width'].'", "'.$this->attributes['height'].'"); '."\r\n".
             ' /* ]]> */'."\r\n".
             '</script>'."\r\n";
             
		$this->attributes = array_merge(array ('rows'=>'5','cols'=>'20'), $this->attributes);
    	$value = htmlspecialchars($this->getValue(), ENT_QUOTES, "UTF-8");
        
        $edit_note = '<span class="multiple_note">' . $I18N->msg('code_fullscreen') . '</span>';
        
		return sprintf('<textarea name="%s" id="%s" tabindex="%s"%s>%s</textarea>%s%s%s', $this->getName(), $this->getId(), cjo_a22_nextTabindex(), $this->getAttributes(), $value, $edit_note, $this->getNote(),$js);
	}
        
}
