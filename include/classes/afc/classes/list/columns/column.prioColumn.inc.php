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
 * Klasse für Prior-Spalten innerhalb der Liste.
 */
class prioColumn extends staticColumn {

    public function __construct($column='prior', $label=false) {
        
        $label = $label===false ? cjoI18N::translate('label_prio') : $label;
        
        parent::__construct($column, $label);
        $this->setHeadAttributes('class', 'icon');
        $this->setBodyAttributes('class', 'icon dragHandle tablednd');
        $this->setBodyAttributes('title', cjoI18N::translate("label_change_prio"));
        $this->addCondition($column, array('!=', ''), '<strong>%s</strong>');
    }
    
    public static function writeTabledndJS($form_id, $table) {

        cjo_insertJS(false, 'js/jquery/jquery.tablednd.js');
        
        echo '<script type="text/javascript">'."\r\n".
             '/* <![CDATA[ */'."\r\n".
             '$(function() { tableDnDUpdate("'.$form_id.'", "'.$table.'", "'.cjoI18N::translate('label_ok').'", "'.cjoI18N::translate('label_cancel').'"); });'."\r\n".
             ' /* ]]> */'."\r\n".
             '</script>'."\r\n";
    }
}
