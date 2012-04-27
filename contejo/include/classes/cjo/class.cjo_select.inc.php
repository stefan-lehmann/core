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

/**
 * cjoSelect class
 *
 * The cjoSelect class provides an easy way of creating selectboxes and
 * selectboxes that map hierarchical structures.
 *
 * Example:<br/>
 * |-> Item 1<br/>
 * |    |-> Item 1.1<br/>
 * |    |-> Item 1.2<br/>
 * |    |    |-> Item 1.2.1<br/>
 * |-> Item 2<br/>
 * |-> Item 3<br/>
 * |    |-> Item 3.1<br/>
 * |    |-> Item 3.2
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoSelect {

    /**
     * name of the selectbox
     * @var string
     */
    public $select_name;

    /**
     * id of the selectbox
     * @var string
     */
    public $select_id;

    /**
     * generated label of the selectbox
     * @var string
     */
    public $label;

    /**
     * position string the label ('left' or 'right')
     * @var unknown_type
     */
    public $label_pos;

    /**
     * style class of the label
     * @var string
     */
    public $label_css;

    /**
     * option of the selectbox
     * @var array
     */
    public $options;

    /**
     * option title attributes
     * @var array
     */
    public $titles;

    /**
     * selected options of the selectbox
     * @var array
     */
    public $option_selected;

    /**
     * disabled options of the selectbox
     * @var array
     */
    public $option_disabled;

    /**
     * vertical dimension of the selectbox
     * @var int
     */
    public $select_size;

    /**
     * multiple selectbox
     * @var boolean
     */
    public $select_multiple;

    /**
     * style definition
     * @var string
     */
    public $select_style;

    /**
     * style class declaration
     * @var string
     */
    public $select_style_class;

    /**
     * extra attribute of the selectbox
     * @var string
     */
    public $select_extra;

    /**
     * the selected path in the hierarchy
     * @var array
     */
    public $selected_path;

    /**
     * text of the root option
     * if empty root option is hidden
     * @var string
     */
    public $root;

    /**
     * Constructor.
	 * @return void
     */
    public function __construct() {
        $this->setName("standard");
        $this->setSize(5);
        $this->setMultiple(false);
        $this->setLabel("");
        $this->option_selected = array();
        $this->option_disabled = array();
        $this->titles          = array();
        $this->selected_path   = array();
        $this->root            = "";
    }

    /**
     * Sets the selectbox to multiple.
     * @param boolean $multiple if true the selectbox is multiple
     * return void
     */
    public function setMultiple($multiple) {
        $this->select_multiple = ($multiple) ? ' multiple="multiple"' : '';
    }

    /**
     * Set the name of the selectbox.
     * @param string $text
     * return void
     */
    public function setName($name) {
        $this->select_name = $name;
    }

    /**
     * Resets the name of the selectbox to standard.
     * @return void
     */
    public function resetName() {
        $this->select_name = "standard";
    }

    /**
     * Sets an extra attribute in the selectbox. (eg. onclick="foo('bar')")
     * @param string $extra
     * return void
     */
    public function setSelectExtra($extra) {
        $this->select_extra = ' '.trim($extra);
    }

    /**
     * Sets the id attribute of the selectbox.
     * @param string $id
     * return void
     */
    public function setId($id) {
        $this->select_id = $id;
    }

    /**
     * Returns the selectbox id generated from the name of the selectbox or
     * if the select_id already has been set by set_id() the select_id is returned.
     * @return string
     */
    public function getSelectId() {
        return (!$this->select_id) ? str_replace(array('][','[',']'), '_',$this->select_name) : $this->select_id;
    }

    /**
     * Sets the label of the selectbox.
     * @param string $label label text
     * @param string $label_pos position of the label ('left' or 'right')
     * @param string $label_css css class of the leabel
     * return void
     */
    public function setLabel($label, $label_pos='left', $label_css='') {
        $this->label = $label;
        $this->label_pos = $label_pos;
        $this->label_css = $label_css;
    }

    /**
     * Returns the class attribute of the label.
     * @return string
     */
    public function getLabelCss() {
        return ($this->label_css != '') ? ' class="'.$this->label_css.'"' : '';
    }

    /**
     * Returns the generated label of the selectbox.
     * @return string
     */
    public function getLabel() {
        return ($this->label != '')
        ? '<label for="'.$this->getSelectId().'"'.$this->getLabelCss().'>'.$this->label.'</label>'
        : '';
    }
    
    /**
     * Returns true if the selectbox as options.
     * @return bool
     */
    public function hasOptions() {
        return isset($this->options[0]);
    }

    /**
     * Resets the selectbox completely.
     * @return void
     */
    function resetAll() {
        $this->resetName();
        $this->resetSelected();
        $this->resetSelectedPath();
        $this->resetDisabled();
        $this->resetStyle();
    }

    /**
     * Sets the style of the selectbox. Is is possible to
     * use style definitions and/or style classes.
     * @param string $style style definition (eg. "width:150px;") and style class (eg. 'class="inp100"')
     * @return void
     */
    public function setStyle($style) {

        if (strpos($style, "class=") !== false) {
            $this->select_style_class = ' '.trim($style);
        }
        else {
            $this->select_style = ' style="' . $style . '"';
        }
    }

    /**
     * Resets the style of the selectbox.
     * @return void
     */
    public function resetStyle() {
        $this->select_style_class = '';
        $this->select_style = '';
    }

    /**
     * Adds the root option.
     * @param boolean $root if true the root option is added
     * @param string $title title of the root option
     * @return void
     */
    public function showRoot($root, $title='root') {
        $this->root = $root;
        $this->addTitles($title, $re_id=0, 0);
    }

    /**
     * Sets the vertical dimension of the selectbox
     * in order to show more less options per default.
     * @param int $size
     * @return void
     */
    public function setSize($size) {
        $this->select_size = $size;
    }

    /**
     * Adds a single value or a set of values
     * to the selected options. All items of t
     * he selected options will be marked as selected.
     * @param array|string $selected can be 'value1', array('value1','value2') or 'value1|value2'
     * @param string $separator separates string formated values
     * @return void
     */
    public function setSelected($selected, $separator='|') {
        foreach(cjoAssistance::toArray($selected, $separator) as $select) {
            $this->option_selected[] = (string) $select;
        }
    }

    /**
     * Resets the selected options.
     * @return void
     */
    public function resetSelected() {
        $this->option_selected = array();
    }

    /**
     * Adds a value to the selected path.
     * All items of the selected path will
     * be marked as members of the current select path.
     * @param mixed $selected
     * @return void
     */
    public function setSelectedPath($path='') {
        $this->selected_path[] = cjoAssistance::toArray($path);
    }

    /**
     * Resets the selected path.
     * @return void
     */
    public function resetSelectedPath() {
        $this->selected_path = array();
    }

    /**
     * Returns true if the given value is part of the selected path.
     * @param mixed $value
     * @return boolean
     */
    public function isSelectedPath($value) {

        if (empty($this->selected_path)) return false;

        foreach($this->selected_path as $path) {
            if (empty($path) || !is_array($path)) {
                continue;
            }
            else if (in_array($value, $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds a value to the disabled options.
     * All items of the  disabled options. will
     * be marked as disabled.
     * @param mixed $disabled
     * @return void
     */
    public function setDisabled($disabled) {
        $this->option_disabled[] = (string) $disabled;
    }

    /**
     * Resets the disabled options.
     * @return void
     */
    public function resetDisabled() {
        $this->option_disabled = array();
    }

    /**
     * Adds the title of an option.
     * @see add_option()
     * @param mixed $title title text
     * @param int $re_id id of the parent element
     * @param int|boolean $id id of the current element
     * @return void
     */
    public function addTitles($title, $re_id=0, $id=false) {
        if (!$id) {
            $this->titles[$re_id][] = $title;
        }
        else{
            $this->titles[$re_id][$id] = $title;
        }
    }

    /**
     * Returns the title of an option.
     * @param int $id id of the current element
     * @param int $re_id id of the parent element
     * @return string
     */
    public function getTitle($id, $re_id=0) {
        if (isset($this->titles[$re_id]) &&
            isset($this->titles[$re_id][$id]) &&
            $this->titles[$re_id][$id] != '') {
            return ' title="'.$this->titles[$re_id][$id] .'"';
        }
        return '';
    }

    /**
     * Adds a new option to the selectbox.
     * @param string|int $text option text
     * @param string|int|boolean $value option value
     * @param int $id option value
     * @param int $re_id
     * @param string $title
     * @return void
     */
    public function addOption($text, $value, $id = 0, $re_id = 0, $title = '') {
        $re_id = (empty($re_id)) ? '0' : $re_id;
        $this->options[$re_id][] = array ($text, $value, $id);

        if ($title != '') {
            $this->addTitles($title,$re_id,$id);
        }
    }

    /**
     * Adds a set of options to the selectbox.
     * @param array $options array of options (eg. array('text'=>'mytext', 'value'=>'myvalue', 'id'=>4, 're_id'=>2, 'title'=>'mytitle'))
     * @return void
     */
    public function addOptions($options = array()) {

        foreach($options as $option) {
            if (is_array($option)) {

                if (!isset($option['text']) && isset($option['name']))
                $option['text'] = $option['name'];

                $text 	= ($option['text'] != '') 	? $option['text'] 	: $option[0];
                $value 	= ($option['value'] != '') 	? $option['value'] 	: $option[1];
                $id 	= ($option['id'] != '') 	? $option['id'] 	: $option[2];
                $re_id 	= ($option['re_id'] != '') 	? $option['re_id'] 	: $option[3];
                $title 	= ($option['title'] != '') 	? $option['title'] 	: $option[4];
            }
            else {
                $text 	= $option;
                $value 	= '';
                $id  	= '';
                $re_id 	= '';
                $title 	= '';
            }

            $value = ($value == '') ? $text : $value;

            $this->addOption($text, $value, $id, $re_id, $title);
        }
    }

    /**
     * Adds the results of a mysql request as a set of options to the selectbox.
     * @param string $query sql query
     * @param booelan $sqldebug if true the request is executed in debug mode
     * @return booelan|string on success returns true, if a sql error occurs the error message is returned
     */
    public function addSqlOptions($query, $sqldebug = false) {

        $sql = new cjoSql();
        $result = $sql->getArray($query, PDO::FETCH_NUM);

        if ($sql->getError() != '') return $sql->getError();

        if ($sqldebug) {
            cjo_debug($sql,'ADD_SQLOPTIONS','lightgreen');
        }

        if (count($result[0]) > 2 && count($result[0]) > 5) {
            return false;
        }

        foreach ($result as $value) {
            if (count($result[0]) == 4 || count($result[0]) == 5) {
                $this->addOption($value[0], $value[1], $value[2], $value[3], $value[4]);

                if ($value[4] != '') {
                    $this->addTitles($value[4],$value[2]);
                }
            }
            elseif (count($result[0]) == 2) {
                $this->addOption($value[0], $value[1]);
            }
            elseif (count($result[0]) == 1) {
                $this->addOption($value[0], $value[0]);
            }
        }
        return true;
    }

    /**
     * Returns all values of selected options as a string.
     * @param string $seperator
     * @return string
     */
    public function getSelected($seperator=',') {

        $output = '';
        if (is_array($this->options[0])) {
            foreach($this->options[0] as $option) {
                if (in_array($option[1], $this->option_selected)) {
                    $output .= $output != '' ? $seperator.' ' : '';
                    $output .= $option[0];
                }
            }
            return $output;
        }
    }

    /**
     * Returns the generated selectbox.
     * @return string
     */
    public function get() {

        global $I18N;

        $label_left = '';
        $label_right = '';
        $multiple_note = '';

        if ($this->label_pos != 'right') {
            $label_left = $this->getLabel();
        }
        else {
            $label_right = $this->getLabel();
        }

        if (!empty($this->select_multiple)) {
            $multiple_note = '<span class="multiple_note">'.$I18N->msg("ctrl").'</span>';
            $this->select_style_class = $this->select_style_class ? str_replace('class="', ' class="multiple ', $this->select_style_class) : ' class="multiple"';
        }

        if (is_array($this->options)) {
            $options = $this->getGroup(0, $this->root);
        } else {
            if ($this->root != '') {
                $options = $this->getOption($this->root, 0, 0, false, false);
            }
        }

        return sprintf('%s<select name="%s" size="%s" id="%s"%s%s%s%s>%s</select>%s%s',
                       $label_left,
                       $this->select_name,
                       $this->select_size,
                       $this->getSelectId(),
                       $this->select_multiple,
                       $this->select_style,
                       $this->select_style_class,
                       $this->select_extra,
                       $options,
                       $multiple_note,
                       $label_right);
    }

    /**
     * Returns the options according to the hierarchical structure.
     * @param int $re_id id of the parent option
     * @param boolean $show_root if true a root option is a added
     * @param int $level current level
     * @return string
     */
    private function getGroup($re_id, $show_root = false, $level = 0) {

        if ($level > 100) {
            // nur mal so zu sicherheit .. man weiss nie ;)
            echo "select->out_group overflow ($groupname)";
            exit;
        }

        $output = '';
        $group = $this->_getGroup($re_id);

        if ($show_root != false && $re_id == 0)
        $group = array_merge(array(array ($show_root, 0, 0)), $group);

        if ($level == 0) {
            $subgroups = false;
            foreach (cjoAssistance::toArray($group) as $option) {
                if ($this->_getGroup($option[2], true) !== false) {
                    $subgroups = true;
                    break;
                }
            }
        } else {
            $subgroups = true;
        }

        foreach ($group as $key=>$option) {
            $text = $option[0];
            $value = $option[1];
            $id = $option[2];
            $title = $this->getTitle($id, $re_id);

            $root = ($value == '' && $re_id == 0) ? true : false;

            if ($show_root) {
                $output .= $this->getOption($text, $value, $level, $root, true, $title);
            }
            elseif ($level >= 1) {
                $output .= $this->getOption($text, $value, $level -1, $root, true, $title);
            } else {
                $output .= $this->getOption($text, $value, $level, $root, false, $title);
            }

            $subgroup = $this->_getGroup($id, true);
            if (!empty($subgroup)) {
                $output .= $this->getGroup($id, $show_root, $level +1);
            }
        }
        return $output;
    }

    /**
     * Returns the generated option.
     * @param string $text text of the option
     * @param mixed $value value of the option
     * @param int $level current level
     * @param boolean $root is root enabled
     * @param boolean $subgroups is the current option a subgroup member
     * @param string $title title of the option
     * @return
     */
    private function getOption($text, $value, $level = 0, $root = false, $subgroups, $title = '') {

        global $CJO;

        $bsps         = '';
        $style        = '';
        $pre          = '';
        $post         = '';
        $selected     = '';
        $disabled     = '';
        $select_path  = '';
        //$text = preg_replace('/^\s*(\[.*\])\s*/', '', $text);

        if (!$root && $subgroups) {
            for ($i = 0; $i < $level; $i++)
            $bsps .= "&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
            $pre = (strpos($text, '&rarr;') === false) ? $bsps . '&nbsp;&nbsp;|&rarr; ' : '&nbsp;&nbsp;';
        }
        elseif ($CJO['CONTEJO']) {
            $pre = '&nbsp;';
            $post = '&nbsp;';
        }

        if ($this->option_disabled !== null) {
            $disabled_style = ($CJO['CONTEJO']) ? ' style="background:#ddd"' : '';
            
             foreach($this->option_disabled as $temp) {
                if ($temp == $value) {
                    $disabled = $disabled_style . ' disabled="disabled"';
                    break;
                }
            }
        }

        if ($this->option_selected !== null) {
            $selected_style = ($CJO['CONTEJO'] && empty($disabled_style)) ? ' style="background:#ffe097"' : '';
            
            foreach($this->option_selected as $temp) {
                if ($temp == $value) {
                    $selected = $selected_style . ' selected="selected"';
                    break;
                }
            }
        }
        if ($this->isSelectedPath($value)) {
            $select_path = ' class="current"';
        }

        return sprintf("\t".'<option value="%s"%s%s%s%s%s>%s%s%s</option>'."\r\n",
        $value, $style, $selected, $disabled, $title, $select_path, $pre, $text, $post);
    }

    /**
     * Returns a set of sub options.
     * @param int $re_id id of the parent option
     * @param boolean $ignore_main_group if true the base options are ignored
     * @return array
     */
    private function _getGroup($re_id, $ignore_main_group = false) {

        if ($ignore_main_group && $re_id == 0) {
            return array();
        }

        foreach ($this->options as $gname => $group) {
            if ($gname == $re_id) {
                return $group;
            }
        }
        return array();
    }
}