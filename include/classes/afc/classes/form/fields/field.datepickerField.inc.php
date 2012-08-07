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

class datepickerField extends readOnlyField {

	public $settings;
	public $default_settings;

	public function datepickerField($name, $label, $attributes = array (), $id = '') {

		if (is_array($id)) {
			$this->id2 = $id[1];
			$id = $id[0];
		}
		$this->cjoFormField($name, $label, $attributes, $id);
		$this->addAttribute('style', 'width: 120px;');
		$this->setFormat('strftime','dateformat');
		$this->default_settings = (($id[1]) ? "beforeShow: customRange," : "")."
							      changeMonth: true,
								  changeYear: true,
								  defaultDate: 'd',
								  minDate: 'd',
								  altFormat: '@',
								  dateFormat: 'dd.mm.yy',
								  yearRange: '%s:%s',";

	}

	public function addSettings($value, $unset_defaults = false) {

		if ($unset_defaults === true) $this->default_settings = '';
		$this->settings=$value;
	}

	public function getSettings() {

		if ($this->settings != '')
			return $this->settings.",";
	}

	public function get() {

		$value_unformated = $this->getValue(false);
		$value_formated   = cjoFormatter :: format($value_unformated, $this->format_type,$this->format);
        
		$yearRange_start = @strftime('%Y', $value_unformated);
		$yearRange_start = ($yearRange_start > date('Y')) ? date('Y') : $yearRange_start;
		$yearRange_end 	 = '2020';

		if ($this->default_settings != '') {
			$this->default_settings = sprintf($this->default_settings,$yearRange_start,$yearRange_end);
		}
		$out = "<script type=\"text/javascript\">
				/* <![CDATA[ */

					$(function() {
		                $('#".$this->getId()."')
		                	.datepicker({
								changeMonth: true,
								changeYear: true,
		                		showOn: 'both',
		                		buttonImageOnly: true,
		                		".$this->default_settings."
		                		".$this->getSettings()."
								altField: '#alt_".$this->getId()."'
							});
					});";

		if ($this->id2) {
			$out .= "	if (typeof customRange != 'function') {
							function customRange(input) {
						        return {minDate: (input.id == '".$this->id2."' ? $('#".$this->id."').datepicker('getDate') : null),
						                maxDate: (input.id == '".$this->id."' ? $('#".$this->id2."').datepicker('getDate') : null)};
						    }
						}";
		}
		$out .= "
				/* ]]> */
				</script>";

		$out .= sprintf('<input type="text" value="%s" id="%s" readonly="readonly"%s />%s',  $value_formated, $this->getId(), $this->getAttributes(), $this->getNote());
		$out .= sprintf('<input type="hidden" name="%s" value="%s" id="alt_%s" />', $this->getName(), $value_unformated, $this->getId());

		return $out;
	}
}
