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

class cjoLinkButtonField extends popupButtonField {

	public $disconnectaction;

	public function cjoLinkButtonField($name, $label, $attributes = array (), $id = '') {

		if (empty ($attributes['style'])) {
			$attributes['style'] = 'clear:none; width: 346px!important;';
		}
		
		$attributes['class'] = (empty ($attributes['class'])) ? 'custom_select' : $attributes['class'].' custom_select';
		
		$this->popupButtonField($name, $label, $attributes, $id);
		$this->setDisconnectAction('cjo.jconfirm(\''.cjoI18N::translate('label_remove_link').' ?\', \'cjo.disconnectLink\', [$(this)]); return false;');
	}

	public function getInputFields() {

        $attributes = $this->getValue() ? str_replace('style="', 'style="background-image:none!important;', $this->getAttributes()) : $this->getAttributes();

		if (!is_object(cjoSelectArticle::$sel_article)) {
			cjoSelectArticle::init();
		}

		cjoSelectArticle::$sel_article->resetDisabled();
		cjoSelectArticle::$sel_article->resetSelected();
		cjoSelectArticle::$sel_article->resetSelectedPath();
		cjoSelectArticle::$sel_article->setLabel('');
		cjoSelectArticle::$sel_article->resetStyle();

		$select_id = (int) $this->getValue();
		$selected_article = OOArticle::getArticleById($select_id, cjoProp::getClang());
		$selected_path = ($selected_article->_id != '') ? $selected_article->_path.'|'.$select_id : $select_id;

		cjoSelectArticle::$sel_article->setName($this->getName());
		cjoSelectArticle::$sel_article->setStyle($attributes);
		cjoSelectArticle::$sel_article->setSize(1);
		cjoSelectArticle::$sel_article->setDisabled(0);
		cjoSelectArticle::$sel_article->setSelected($select_id);

		$validators = $this->getValidators();
		if (is_array($validators)) {
		    foreach($validators as $validator) {
		        if ($validator['criteria'] == 'notEmptyOrNull' ||
		            $validator['criteria'] == 'notEmpty') {
		            cjoSelectArticle::$sel_article->resetDisabled();
		        }
		    }
		}

		if ($selected_article->_id != '') {
			cjoSelectArticle::$sel_article->setSelectedPath($selected_path);
		}
        //cjo_debug(cjoSelectArticle::$sel_article, $select_id); die();
		$s = cjoSelectArticle::$sel_article->_get();
		$s .= '<script type="text/javascript">'."\r\n".
			  '	/* <![CDATA[ */'."\r\n".
			  '	$(function(){'."\r\n".
			  '		$(\'#'.cjoSelectArticle::$sel_article->getSelectId().'\').selectpath({path_len: \'short\', selected: 0});'."\r\n".
			  '	});'."\r\n".
			  '/* ]]> */ '."\r\n".
			  '</script>'."\r\n";

		return $s;
	}

	public function setDisconnectAction($disconnectaction){
		$this->disconnectaction = $disconnectaction;
	}

	public function getDisconnectAction(){
		return $this->disconnectaction;
	}

    public function get() {

		if ($this->getValue()) {
		    $invalid = strpos($this->getAttributes(), 'invalid') !== false ? ' invalid' : '';
	        $this->addButton(cjoI18N::translate('label_remove_link'), $this->getDisconnectAction(), 'img/silk_icons/cross.png', 'class="small'.$invalid.'"');
		    
			$url = cjoUrl::createBEUrl(array('page' => 'edit','subpage' => 'content', 'article_id' => $this->getValue(), 'clang'=>cjoProp::getClang()), array(), '&amp;');
			$this->addButton(cjoI18N::translate('label_edit_now'),
							 'cjo.openShortPopup(\''.$url.'\'); return false;',
							 'img/silk_icons/page_white_edit.png',
							 'class="small'.$invalid.'"');
		}
	    return '<div class="cjo_article_path">'.parent::get().'</div>';
	}
}