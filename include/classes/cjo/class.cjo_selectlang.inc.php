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

if (!$CJO['CONTEJO']) return false;

/**
 * cjoSelectLang class
 *
 * The cjoSelectLang class generates the language selection in CONTEJO.
 * @package 	contejo
 * @subpackage 	core
 */
class cjoSelectLang {

    /**
     * generated selection
     * @var string
     */
    public $items = '';

	/**
     * Constructor.
     * @return void
     */
    public function __construct() {

        global $CJO, $I18N, $page;

        if (!$CJO['CONTEJO'] || !$CJO['USER']) return false;

        if (count($CJO['CLANG']) > 1) {
            foreach ($CJO['CLANG'] as $id => $name) {
                if (is_object($CJO['USER']) &&
                    !$CJO['USER']->isAdmin() &&
                    !$CJO['USER']->hasPerm("developer[]") &&
                    !$CJO['USER']->hasPerm("clang[all]") &&
                    !$CJO['USER']->hasPerm("clang[".$id."]")) {

                    if ($_REQUEST['clang'] == $id) $this->setBackButton();
                }
                else {
                    $this->items .= $this->addItem($id);
                }
            }
        }
        else {
            $clang = 0;
        }

        if ($page == 'edit'){
            $url = (!empty($CJO['ARTICLE_ID'])) ? cjoRewrite::getUrl($CJO['ARTICLE_ID'], $CJO['CUR_CLANG']) : $CJO['HTDOCS_PATH'];

            $view_frontend = $this->addItem(null,
                                            $I18N->msg('label_article_view'),
                                            '#',
                                            $this->getLinkIcon('zoom','img/silk_icons', $I18N->msg('label_article_view')));

            $replace = array('<li' => '<li id="view_frontend"',
                             '<a' => '<a onclick="cjo.openShortPopup(\''.$url.'\').focus();"');

            $this->items .=  str_replace(array_keys($replace), array_values($replace), $view_frontend);
        }

        $CJO['SEL_LANG'] = $this;
    }

    /**
     * Generates a flag for the language selection.
     *
     * @param int $id language id
     * @param string|boolean $name link name
     * @param string|boolean $url url for changing the language
     * @param string|boolean $icon link icon
     * @return string
     */
    public function addItem($id, $name=false, $url=false, $icon=false ) {

        global $CJO;

        $article_id = cjo_request('article_id', 'cjo-article-id');
        $ctype      = cjo_request('ctype', 'cjo-ctype-id');

        if ($name === false) $name = $CJO['CLANG'][$id];
        if ($url === false) $url = cjoAssistance::createBEUrl(array('article_id' => $article_id, 'clang' =>$id, 'ctype' =>$ctype, 'msg' => '', 'err_msg' => ''));
        if ($icon === false) $icon = $this->getLinkIcon($CJO['CLANG_ISO'][$id]);

        $current = (cjo_request('clang', 'cjo-clang-id') == $id && $id !== null) ? ' class="current"' : '';

        return sprintf('<li><a href="%s" title="%s"%s>%s</a></li>',
                       $url,
                       $name,
                       $current,
                       $icon);
    }

    /**
     * Returns the flag image.
     *
     * @param string $iso iso language code or file name
     * @param string|boolean $path path to the icon file
     * @param string|boolean $alt alt attribute for the link
     * @param string|boolean $title title attribute for the link
     * @param string $ext file extension
     * @return string
     */
    public function getLinkIcon($iso, $path = false, $alt = false, $title = false, $ext = 'png'){

        if ($path === false) $path = 'img/flags';
        if ($alt === false) $alt = $iso;
        if ($title === false) $title = $alt;

        return '<img src="'.$path.'/'.$iso.'.'.$ext.'" alt="'.$alt.'" title="'.$title.'"/>';
    }

    /**
     * Prevent access and Writes a back button
     * if the user has no permission to edit
     * the selected language.
     * @return void
     */
    public function setBackButton() {
        
        global $CJO, $I18N;
        
        global $CJO, $I18N;
        
        $back_button = new buttonField();
		$back_button->addButton('back_button', $I18N->msg('label_go_back'), true, 'img/silk_icons/arrow_undo.png');
		$back_button->setButtonAttributes('back_button', 'onclick="history.back(); return false;" style="margin-left: 10px;"');

		echo '<div style="width: 960px;">'."\r\n".
             '	<span class="warning" style="margin-top: 1em; padding: 10px 0;">'.$I18N->msg("msg_no_rights_to_edit").'</span>'."\r\n".
             '	'.$back_button->_get()."\r\n".
             '</div>';
		
		require_once $CJO['INCLUDE_PATH'].'/layout/bottom.php';
		exit();
    }

    /**
     * Inserts the generated language selection via
     * output filter called by an extensionpoint.
     * @param array $params parameter set by output filter
     * @return string
     */
    public static function insertLangTabs($params) {
    	global $CJO;
    	$tabs = '<ul class="v_tabnmenu">'.$CJO['SEL_LANG']->items.'</ul>'."\n\r";
    	return preg_replace('/<div([^>]*)id="cjo_lang_tabs"([^>]*)>/i','$0'.$tabs,$params['subject']);
    }

    /**
     * Registers an extensionpoint in order to
     * insert the language selection via output filter.
     * @return void;
     */
    public function get(){
        cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSelectLang::insertLangTabs');
    }
}