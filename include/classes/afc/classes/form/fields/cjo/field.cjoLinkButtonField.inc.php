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

		global $I18N;

		if (empty ($attributes['style'])) {
			$attributes['style'] = 'clear:none; width: 346px!important;';
		}
		
		$attributes['class'] = (empty ($attributes['class'])) ? 'custom_select' : $attributes['class'].' custom_select';
		
		$this->popupButtonField($name, $label, $attributes, $id);
		$this->setDisconnectAction('cjo.jconfirm(\''.$I18N->msg('label_remove_link').' ?\', \'cjo.disconnectLink\', [$(this)]); return false;');
	}

	public function getInputFields() {

		global $CJO, $I18N;

        $attributes = $this->getValue() ? str_replace('style="', 'style="background-image:none!important;', $this->getAttributes()) : $this->getAttributes();

		if (!is_object($CJO['SEL_ARTICLE'])) {
			new cjoSelectArticle();
		}

		$CJO['SEL_ARTICLE']->resetDisabled();
		$CJO['SEL_ARTICLE']->resetSelected();
		$CJO['SEL_ARTICLE']->resetSelectedPath();
		$CJO['SEL_ARTICLE']->setLabel('');
		$CJO['SEL_ARTICLE']->resetStyle();

		$select_id = (int) $this->getValue();
		$selected_article = OOArticle::getArticleById($select_id, $CJO['CUR_CLANG']);
		$selected_path = ($selected_article->_id != '') ? $selected_article->_path.'|'.$select_id : $select_id;

		$CJO['SEL_ARTICLE']->setName($this->getName());
        $CJO['SEL_ARTICLE']->setId($this->getName());
		$CJO['SEL_ARTICLE']->setStyle($attributes);
		$CJO['SEL_ARTICLE']->setSize(1);
		$CJO['SEL_ARTICLE']->setDisabled(0);
		$CJO['SEL_ARTICLE']->setSelected($select_id);

		$validators = $this->getValidators();
		if (is_array($validators)) {
		    foreach($validators as $validator) {
		        if ($validator['criteria'] == 'notEmptyOrNull' ||
		            $validator['criteria'] == 'notEmpty') {
		            $CJO['SEL_ARTICLE']->resetDisabled();
		        }
		    }
		}

		if ($selected_article->_id != '') {
			$CJO['SEL_ARTICLE']->setSelectedPath($selected_path);
		}
		$s = $CJO['SEL_ARTICLE']->_get();
		$s .= '<script type="text/javascript">'."\r\n".
			  '	/* <![CDATA[ */'."\r\n".
			  '	$(function(){'."\r\n".
			  '		$(\'#'.$CJO['SEL_ARTICLE']->getSelectId().'\').selectpath({path_len: \'short\', selected: 0});'."\r\n".
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

    	global $CJO, $I18N;


		if ($this->getValue()) {
		    
	        $this->addButton($I18N->msg('label_remove_link'), $this->getDisconnectAction(), 'img/silk_icons/cross.png', 'class="small"');
		    
			$url = cjoAssistance::createBEUrl(array('page' => 'edit','subpage' => 'content', 'article_id' => $this->getValue(), 'clang'=>$CJO['CUR_CLANG']), array(), '&amp;');
			$this->addButton($I18N->msg('label_edit_now'),
							 'cjo.openShortPopup(\''.$url.'\'); return false;',
							 'img/silk_icons/page_white_edit.png',
							 'class="small"');
		}
	    return '<div class="cjo_article_path">'.parent::get().'</div>';
	}
}