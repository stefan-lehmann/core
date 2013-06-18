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
 * cjoSubpages class
 *
 * The cjoSubpages class controles the creation of CONTEJOs
 * backend main and tab navigation.
 *
 * @package 	contejo
 * @subpackage 	core
 */
class cjoSubPages {

    public $subpage;

    public $mypage;

    public $params;

    public $subpages;

    public $debug;

	/**
     * Constructor. Generates the subpage navigation based on the users permissions.
     * If there is no permission to the called page or subpage the user
     * is redirected to CONTEJOs backend startpage.
     *
     * @param string $subpage name of the active subpage
     * @param string $mypage name of the active page
     * @param boolean $debug
     * @return void
     * @access public
     */
    public function __construct($subpage, $mypage, $debug=false) {

        $this->subpage   = $subpage;
        $this->mypage    = $mypage;
        $this->debug     = $debug;
        $this->params    = array();
        $this->subpages  = array();
    }

    /**
     * Generates the tab elements and inserts them
     * via output filter extensionpoint.
     *
     * @param string $subpage name of the active subpage
     * @param array $subpages settings of available subpages
     * @param string $mypage name of the active page
     * @return void
     * @access public
     */
    public static function setTabs($subpage, $subpages, $mypage) {

    	global $CJO, $I18N;

    	if (count($subpages) <= 1 && !array_key_exists('important', current($subpages))) {
    	    $title = (!$subpages[0]['title']) ? $I18N->msg('title_'.$subpages[0][0]) : $subpages[0]['title'];
    	    if (empty($CJO['title'])) $CJO['title'] = $title;
    		cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertTitle');
    	    return false;
    	}

    	$clang = cjo_request('clang', 'cjo-clang-id', 0);

    	$tabs = '';
    	foreach($subpages as $cur) {

    		$title = (empty($cur['title'])) ? $I18N->msg('title_'.$cur[0]) : $cur['title'];

    		if ($cur[0] == $subpage) {
    			$current = ' class="current"';
    			if (empty($CJO['title'])) {
    				$CJO['title'] = strip_tags($title);
    				cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertTitle');
    			}
    		}
    		else {
    			$current = '';
    		}
    		$cur['query_str'] = (empty($cur['query_str'])) ? 'page='.$mypage.'&subpage='.$cur[0].'&clang='.$clang : $cur['query_str'];
    		$url = (empty($cur['url'])) ? 'index.php?'.$cur['query_str'] : $cur['url'];

    		$tabs[$cur[0]] = '<a href="'.$url.'" title="'.strip_tags($title).'"'.$current.'>
        						<span class="left"></span>
        						<span class="center">'.$title.'</span>
        						<span class="right"></span>
        					</a>'."\r\n";
    	}


    	if (empty($CJO['cjo_tabs'])) {
    		$CJO['cjo_tabs'] = $tabs;
    		cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertTabs');
    	}
    	else {
    		$CJO['cjo_sub_tabs'] = $tabs;
    		cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertSubTabs');
    	}
    }

    /**
     * Method to insert main tabs called by output filter extensionpoint.
     *
     * @param array $params output filter parameters
     * @return void
     * @access public
     */
    public static function insertTabs($params) {
        
    	global $CJO;
    	
    	$tabs = '<ul class="tabnmenu">';
    	foreach($CJO['cjo_tabs'] as $key=>$tab) {
            $tabs .= '<li id="cjo_tabs_'.$key.'">'.$tab.'</li>';
    	}
    	$tabs .= '</ul>';

    	$content = preg_replace('/<div([^>]*)id="cjo_tabs"([^>]*)>/i','$0'.$tabs,$params['subject']);
    	$content = cjoExtension::registerExtensionPoint('OUTPUT_FILTER[TABS_INSERTED]', $content);
    	return $content;
    }

    /**
     * Method to insert sub tabs called by output filter extensionpoint.
     *
     * @param array $params output filter parameters
     * @return void
     * @access public
     */
    public static function insertSubTabs($params) {
        
    	global $CJO;

    	$tabs = '<div id="cjo_sub_tabs" class="floatbox"><ul class="tabnmenu">';
        foreach($CJO['cjo_sub_tabs'] as $key=>$tab) {
            $tabs .= '<li id="cjo_sub_tabs_'.$key.'">'.$tab.'</li>';
        }
        $tabs .= '</ul></div>';
    	
    	$content = preg_replace('/<div([^>]*)id="cjo_sub_tabs"([^>]*)><\/div>/i',$tabs,$params['subject']);
    	$content = cjoExtension::registerExtensionPoint('OUTPUT_FILTER[SUB_TABS_INSERTED]', $content);
    	return $content;
    }

    /**
     * Method to insert a title attribute called by output filter extensionpoint.
     *
     * @param array $params output filter parameters
     * @return void
     * @access public
     */
    public static function insertTitle($params) {
    	global $CJO;
    	$content = preg_replace('/<\/title>/',' | '.$CJO['title'].'\0',$params['subject'],1);
    	$content = cjoExtension::registerExtensionPoint('OUTPUT_FILTER[TITLE_INSERTED]', $content);
    	return $content;
    }

    /**
     * Reads all addons that have been connected to
     * the currend page by the "Show Addon in"-Dialog
     *
     * @param string $mypage name of the active page
     * @return array connected addons
     * @access public
     */
    public function getAddonSubPages() {

    	global $CJO;

    	$addon_subpages = array();

    	if (empty($CJO['USER'])) return $addon_subpages;

    	foreach (cjoAssistance::toArray($CJO['ADDON']['status']) as $key => $item) {

    		if (!$CJO['ADDON']['status'][$key] ||
    			!$CJO['ADDON']['menu'][$key] ||
    			$CJO['ADDON']['menu'][$key] != $this->mypage) continue;

    		$name = (isset($CJO['ADDON']['tabname'][$key])) ? $CJO['ADDON']['tabname'][$key] : false;
    		$name = (!$name && isset($CJO['ADDON']['name'][$key])) ? $CJO['ADDON']['name'][$key] : $name;

    		if ($CJO['ADDON']['status'][$key] && $name && $CJO['USER']->hasAddonPerm($key, true)) {
    			$addon_subpages[] = array($key, 'title' => $name, 'addon'=>true);
    		}
    	}
    	return $addon_subpages;
    }

    /**
     * Adds a subpage.
     * @param array $params parameters of the subpage
     * @return void
     * @access public
     */
    public function addPage($params) {
        if (is_array($params))
            $this->params[] = $params;
    }

    /**
     * Returns the include path of the current page file.
     * @return string
     * @access public
     */
    public function getPage() {

        global $CJO, $I18N, $cur_page, $subpage;

    	$subpage = '';
    	$addon_index = false;

    	if (is_array($this->params) && empty($cur_page['popup'])) {

    		if (empty($CJO['cjo_tabs'])) {
    			$this->params = array_merge($this->params, $this->getAddonSubPages());
    		}
    		
    		$subpage = !empty($subpages[0][0]) ? $subpages[0][0] : '';
    		foreach($this->params as $cur) {

    			$per = true;
    			if (isset($cur['rights']) && is_array($cur['rights'])) {

    				foreach($cur['rights'] as $right) {
    					if (!$CJO['USER']->hasPerm($right)) {
    						$per = false;
    						break;
    					}
    				}
    			}
    			if ($per) {
    				$this->subpages[] = $cur;
    				if ($cur[0] == $this->subpage) {
    					$subpage = $cur[0];
    					if (!empty($cur['addon'])) $addon_index = true;
    				}
    			}
    			else {
    				if ($cur[0] == $this->subpage)
    				    cjoMessage::addError($I18N->msg("msg_no_permissions"));
    			}
    		}

    		if (empty($this->subpages))
    		    cjoMessage::addError($I18N->msg("msg_no_permissions"));

    		if ($subpage == '' && !cjoMessage::hasError($I18N->msg("msg_no_permissions")))
    		    $subpage = $this->subpages[0][0];

    		if (isset($CJO['ADDON']['curr_subpage']) && 
    		    isset($CJO['ADDON']['curr_subpage'][$this->mypage]) && 
    		    isset($CJO['cjo_tabs'])) {
    			$subpage = $CJO['ADDON']['curr_subpage'][$this->mypage];
    		}
    		if (!cjoMessage::hasError($I18N->msg("msg_no_permissions")))
    		$this->setTabs($subpage, $this->subpages, $this->mypage);
    	}

    	if (!empty($cur_page['popup'])) {
    		$this->mypage =  cjo_request('page', 'string', '');
    		$subpage = cjo_request('subpage', 'string', '');
    	}

    	if ($addon_index) {
    	    
            $CJO['page']    = $subpage;
            $CJO['subpage'] = 'index';
            
    		if ($this->debug) echo '1.) '.$CJO['ADDON_PATH'].'/'.$subpage.'/pages/index.inc.php<br/>';
    		
    		if (file_exists($CJO['ADDON_PATH'].'/'.$subpage.'/pages/index.inc.php'))
    		    return $CJO['ADDON_PATH'].'/'.$subpage.'/pages/index.inc.php';
    		  
            if (file_exists($CJO['ADDON_CONFIG_PATH'].'/'.$subpage.'/pages/index.inc.php'))
                return $CJO['ADDON_CONFIG_PATH'].'/'.$subpage.'/pages/index.inc.php';                   
            
            return $CJO['INCLUDE_PATH'].'/pages/edit/structure.inc.php'; 
              		  
    	}
    	elseif (!empty($CJO['ADDON']['status'][$this->mypage]) && $subpage != '') {
    	    
            $CJO['page']    = $this->mypage;
            $CJO['subpage'] = $subpage;
    	    
    		if ($this->debug) echo '2.) '.$CJO['ADDON_PATH'].'/'.$this->mypage.'/pages/'.$subpage.'.inc.php<br/>';
    		
            if (file_exists($CJO['ADDON_PATH'].'/'.$this->mypage.'/pages/'.$subpage.'.inc.php'))
                return $CJO['ADDON_PATH'].'/'.$this->mypage.'/pages/'.$subpage.'.inc.php';      		

            if (file_exists($CJO['ADDON_CONFIG_PATH'].'/'.$this->mypage.'/pages/'.$subpage.'.inc.php'))
                return $CJO['ADDON_CONFIG_PATH'].'/'.$this->mypage.'/pages/'.$subpage.'.inc.php';  
                
            return $CJO['INCLUDE_PATH'].'/pages/edit/structure.inc.php';      
              
    	}
    	else if ($subpage != '' || $subpage === 0) {
    	    
            $CJO['page']    = $this->mypage;
            $CJO['subpage'] = $subpage;
            
    		if ($this->debug) echo '3.) '.$CJO['INCLUDE_PATH'].'/pages/'.$this->mypage.'/'.$subpage.'.inc.php<br/>';
    		return $CJO['INCLUDE_PATH'].'/pages/'.$this->mypage.'/'.$subpage.'.inc.php';
    	}
    	else if ($this->mypage != 'login') {
            
            $CJO['page']    = 'edit';
            $CJO['subpage'] = 'structure';

            $article_id = cjo_request('article_id', 'cjo-article-id');
            //[translate: msg_no_permissions_redirected]
            $local_params =  array('page'=>'edit',  'subpage' => 'structure',
                                   'article_id'=> $article_id, 'err_msg'=>'msg_no_permissions_redirected');

            if ($this->debug) {
                echo '4.) '.cjoAssistance::createBEUrl($local_params).'<br/>';
            }
            else {
                cjoAssistance::redirectBE($local_params);
            }
        }
    }
}