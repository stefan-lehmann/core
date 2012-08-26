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
 * Suchleiste
 */
class searchBar extends cjoListToolbar {

    public $column;
    public $key;
    public $mode;

    public function searchBar() {

        if (cjo_request('search_cancel', 'bool')) {
            $_REQUEST['search_column'] = null;
            $_REQUEST['search_key'] = null;
            $_REQUEST['search_mode'] = null;  
        }
            
        $this->column  = cjo_request('search_column', 'bool') ? cjo_request('search_column','string') : '';
        $this->key     = cjo_request('search_key', 'bool')    ? cjo_request('search_key','string') : '';
        $this->mode    = cjo_request('search_mode', 'bool')   ? 'exact' : '';
    }

    public function show() {

        global $I18N;

        $search_column = $this->column;
        $search_key = $this->key;
        $search_mode_checked = $this->mode == 'exact' ? ' checked="checked"' : '';

        $this->addGlobalParams(array ('search_key' => $search_key, 'search_column' => $search_column));

        $s = '';
        $s .= '<label for="search_key">'.$I18N->msg('label_search').'</label>'."\n";
        $s .= '          <input type="text" value="'.$search_key.'" id="search_key" title="'.$I18N->msg('label_search_key').'" style="width: 100px" name="search_key" />'."\n";
        $s .= '          <label for="search_column">'.$I18N->msg('label_in').'</label>'."\n";
        $s .= '          <select id="search_column" name="search_column"  style="width: 100px" title="'.$I18N->msg('label_search_column').'">'."\n";

        // Suchspalten anzeigen
        for ($i = 0; $i < $this->cjolist->numColumns(); $i ++) {

            $column = & $this->cjolist->columns[$i];

            if ($column->hasOption(OPT_SEARCH)) {

                $selected = '';
                if ($search_column != '' &&
                    $search_column == $column->name ||
                    $search_column == '' &&
                    $this->cjolist->def_search_col == $column->name) {
                    $selected = ' selected="selected"';
                }

                $label = ($column->label === NULL) ? $I18N->msg('label_'.$column->name) : $column->label;

                $s .= sprintf('            <option value="%s"%s>%s</option>'."\n", $column->name, $selected, $label);
            }
        }

        $s .= '          </select>'."\n";
        $s .= '          <input type="hidden" value="0" name="next" />'."\n";                
        $s .= '          <input type="checkbox" value="exact" title="'.$I18N->msg('label_exact_search_mode').'" name="search_mode"'.$search_mode_checked.' />'."\n";
        $s .= '          <input type="submit" value="'.$I18N->msg('label_search').'" title="'.$I18N->msg('label_start_search').'" name="search_button" />'."\n";

        if ($search_key != '') {
            $s .= '       <input type="submit" value="'.$I18N->msg('label_remove_search').'" name="search_cancel" />'."\n";
        }
        return $s;
    }

    public function prepareQuery(& $listsql) {

        $search_column = $this->column;
        $search_key = $this->key;
        $search_mode = $this->mode;

        if ($search_column != '' && $search_key != '') {
            if ($search_mode == 'exact') {
                $listsql->addWhere($search_column.' = "'.$search_key.'"');
            }
            else {
                $listsql->addWhere($search_column.' LIKE "%'.$search_key.'%"');
            }
        }
    }
}