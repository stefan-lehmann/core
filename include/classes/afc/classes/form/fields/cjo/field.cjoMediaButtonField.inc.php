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

class cjoMediaButtonField extends popupButtonField {

	public $connectaction;
	public $disconnectaction;
	public $preview;			

	public function cjoMediaButtonField($name, $label, $attributes = array(), $id = '') {

		global $I18N;

		if (empty($attributes['style'])) {
			$attributes['style'] = 'clear:none;left:200px;cursor:pointer';
		}

		$this->preview = array('enabled' => false,
		                       'width'   => 348,
		                       'height'  => 348);
		
		if (isset($attributes['preview'])) {
		    $this->preview = array_merge($this->preview , $attributes['preview']);
		    unset($attributes['preview']);
		}

		$this->popupButtonField($name, $label, $attributes, $id);
		$this->setConnectAction("cjo.connectMedia($(this),'". $this->getId() ."'); return false;");
		$this->setDisconnectAction('cjo.jconfirm(\''.$I18N->msg('label_remove_media').' ?\', \'cjo.disconnectMedia\', [$(this)]); return false;');
	}

	public function getInputFields() {

	    $image = '';
	    
	    if ($this->preview['enabled'] !== false) {
	        
    	        if( $this->preview['enabled'] === true || $this->getValue()) {

    	        $image = OOMedia::toResizedImage($this->getValue(), array('img'=> $this->preview), false, true, false);

                $w = str_replace('px','',$image['width']);	                        

    	        if ((int) $w > $this->preview['width']) {
    	            $this->attributes['style'] .= 'width:'.($w+2).'px';
    	        }
    	        
                if ($image) {
        	        $image = sprintf('<a href="#" onclick="%s" style="width:%s" class="cjo_select_button_preview" >%s</a>', 
        	                        $this->getConnectAction(), $image['width'], $image['image']);
                }
	        }	          	    
	    }
	    
		return sprintf('%s<input onclick="%s" type="text" name="%s" value="%s" id="%s" readonly="readonly"%s />',
						$image, $this->getConnectAction(), $this->getName(), $this->getValue(), $this->getId(), $this->getAttributes());
	}

	public function setConnectAction($connectaction) {
		$this->connectaction = $connectaction;
	}

	public function getConnectAction() {
		return $this->connectaction;
	}

	public function setDisconnectAction($disconnectaction) {
		$this->disconnectaction = $disconnectaction;
	}

	public function getDisconnectAction() {
		return $this->disconnectaction;
	}

	public function get() {

		global $I18N;


        if ($this->getValue()) {
		    $this->addButton($I18N->msg('label_remove_media'), $this->getDisconnectAction(), 'img/silk_icons/cross.png', 'class="small"');		
			$url = cjoAssistance::createBEUrl(array('page' => 'media','subpage' => 'details', 'filename' => $this->getValue()),array(),'&amp;');
			$this->addButton($I18N->msg('label_edit_media'),
							 'cjo.openShortPopup(\''.$url.'\'); return false;',
							 'img/silk_icons/page_white_edit.png',
							 'class="small"');
        }
        else {
		    $this->addButton($I18N->msg('label_open_media'), $this->getConnectAction(), 'img/silk_icons/add.png', 'class="small"');
        }

		return str_replace('id=""','',parent::get());
	}
}