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

if (!cjoProp::isBackend()) return false;

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

    private static $level       = 0;
    private static $subpages    = false; 
    private static $initialized = false;  
    private static $call        = '';
    private $subpage;
    private $page;
    private $page_settings;
    private $params;
    private $debug;

	/**
     * Constructor. Generates the subpage navigation based on the users permissions.
     * If there is no permission to the called page or subpage the user
     * is redirected to CONTEJOs backend startpage.
     *
     * @param boolean $debug
     * @return void
     * @access public
     */
    public function __construct($debug=false) {

        $this->subpage       = cjoProp::getSubpage();
        $this->page          = self::$level > 0 ? cjoProp::getSubpage() : cjoProp::getPage();
        $this->debug         = $debug;
        $this->page_settings = array();
        $this->params        = array();
    }

    /**
     * Generates the tab elements and inserts them
     * via output filter extensionpoint.
     *
     * @param string $subpage name of the active subpage
     * @param array $params settings of available subpages
     * @param string $page name of the active page
     * @return void
     * @access public
     */
    public static function setTabs($subpage, $params, $page) {

    	if (count($params) <= 1 && !array_key_exists('important', current($params))) {
    	    $title = (!$params[0]['title']) ? cjoI18N::translate('title_'.$params[0][0].'s') : $params[0]['title'];
    	    if (!cjoProp::get('title')) cjoProp::set('title', $title);
    		cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertTitle');
    	    return false;
    	}

    	$clang = cjo_request('clang', 'cjo-clang-id', 0);

    	$tabs = '';
    	foreach($params as $param) {

    		if (empty($param['title'])) {
    		     $param['title'] = cjoI18N::translate('title_'.$param[0]);
            }
            if (strpos($param['title'], 'title_'.$param[0]) !== false) {
                 $param['title'] = cjoI18N::translate('title_'.$param[0].'s');
            }
            $title = $param['title'];

    		if ($param[0] == $subpage) {
    			$current = ' class="current"';
    			if (!cjoProp::get('title')) {
    				cjoProp::set('title', strip_tags($title));
    				cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertTitle');
    			}
    		} else {
    			$current = '';
    		}

    		if (!is_array($param['params'])) {
		        if(is_string($param['query_str'])) {
		            parse_str(parse_str($param['query_str']), $param['params']);
		        }
                else {
                    $param['params'] = array('page'=>$page, 'subpage'=>$param[0], 'clang'=>$clang);
                }
            }
            
            if(!isset($param['params']['oid']))      $param['params']['oid']      = NULL;
            if(!isset($param['params']['function'])) $param['params']['function'] = NULL;
            if(!isset($param['params']['mode']))     $param['params']['mode']     = NULL;
            if(!isset($param['params']['stepping'])) $param['params']['stepping'] = NULL;
            if(!isset($param['params']['next']))     $param['params']['next']     = NULL;
            
    		$url = (empty($param['url'])) ? cjoUrl::createBEUrl($param['params']) : $param['url'];

    		$tabs[$param[0]] = '<a href="'.$url.'" title="'.strip_tags($title).'"'.$current.'>
        						<span class="left"></span>
        						<span class="center">'.$title.'</span>
        						<span class="right"></span>
        					</a>'."\r\n";
                            
    	}


    	if (!cjoProp::get('cjo_tabs')) {
    		cjoProp::set('cjo_tabs',$tabs);
    		cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertTabs');
    	}
    	else {
            cjoProp::set('cjo_sub_tabs',$tabs);
    		cjoExtension::registerExtension('OUTPUT_FILTER', 'cjoSubPages::insertSubTabs');
    	}
    }

    /**
     * Method to insert main tabs called by output filter extensionpoint.
     *
     * @param array $page_settings output filter parameters
     * @return void
     * @access public
     */
    public static function insertTabs($params) {
    	
    	$tabs = '<ul class="tabnmenu">';
    	foreach(cjoProp::get('cjo_tabs') as $key=>$tab) {
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

    	$tabs = '<div id="cjo_sub_tabs" class="floatbox"><ul class="tabnmenu">';
        foreach(cjoProp::get('cjo_sub_tabs') as $key=>$tab) {
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
    	$content = preg_replace('/<\/title>/',' | '.cjoProp::get('title').'\0',$params['subject'],1);
    	$content = cjoExtension::registerExtensionPoint('OUTPUT_FILTER[TITLE_INSERTED]', $content);
    	return $content;
    }

    /**
     * Reads all addons that have been connected to
     * the currend page by the "Show Addon in"-Dialog
     *
     * @param string $page name of the active page
     * @return array connected addons
     * @access public
     */
    public function getAddonSubPages() {

    	$addon_subpages = array();
        
    	if (!cjoProp::getUser()) return $addon_subpages;

    	foreach (cjoAssistance::toArray(cjoAddon::getProperty('status')) as $addon => $item) {

    		if (!cjoAddon::getProperty('status', $addon) ||
    			!cjoAddon::getProperty('menu', $addon) ||
    			cjoAddon::getProperty('menu', $addon) != $this->page) continue;

    		$name = (cjoAddon::getProperty('name', $addon, false)) ? cjoAddon::getProperty('name', $addon) : false;

    		if (cjoAddon::isActivated($addon) && $name && cjoProp::getUser()->hasAddonPerm($addon, true)) {
    			$addon_subpages[] = array($addon, 'title' => $name, 'addon'=>true);
    		}
    	}
    	
    	return $addon_subpages;
    }

    /**
     * Returns the include path of the current page file.
     * @return string
     * @access public
     */
    
    private function getPage() {

    	$subpage = false;
        $default_path = cjoPath::inc('pages/edit/structure.inc.php');
    	$addon_index = false;

    	if (is_array($this->page_settings) && !cjoProp::get('PAGE_POPUP')) {

    		if (!cjoProp::get('cjo_tabs')) {
    			$this->page_settings = array_merge($this->page_settings, $this->getAddonSubPages());
    		}

    		foreach($this->page_settings as $param) {

    			$per = true;
    			if (isset($param['rights']) && is_array($param['rights'])) {

    				foreach($param['rights'] as $right) {
    					if (!empty($right) && !cjoProp::getUser()->hasPerm($right)) {
    						$per = false;
    						break;
    					}
    				}
    			}
                      
    			if ($per) {
    				$this->params[] = $param;
    				
    				if ($this->subpage && $param[0] == $this->subpage) {
    					$subpage = $param[0];
    					if (!empty($param['addon'])) $addon_index = true;
    				}
    			}
    			else {
    				if ($param[0] == $this->subpage)
    				    cjoMessage::addError(cjoI18N::translate("msg_no_permissions"));
    			}
    		}
    		// if (empty($this->params))
    		    // cjoMessage::addError(cjoI18N::translate("msg_no_permissions"));

    		if (!$subpage && !cjoMessage::hasError(cjoI18N::translate("msg_no_permissions")))
    		    $subpage = $this->params[0][0];

    		if (cjoAddon::getProperty('curr_subpage') && 
    		    cjoAddon::getProperty('curr_subpage', $this->page) && 
    		    cjoProp::get('cjo_tabs')) {
    			$subpage = cjoAddon::getProperty('curr_subpage', $this->page);
    		}
    		if (!cjoMessage::hasError(cjoI18N::translate("msg_no_permissions"))) {
    		    $this->setTabs($subpage, $this->params, $this->page);
            }
    	}
    	if (cjoProp::get('PAGE_POPUP')) {
    		$this->page = cjo_request('page', 'string', '');
    		$subpage    = cjo_request('subpage', 'string', '');
    	}

        cjoProp::setPage($this->page); 
        cjoProp::setSubpage($subpage);

    	if ($addon_index) {
    	    
            $path1 = cjoPath::addon($subpage, 'pages/index.inc.php');
            $path2 = cjoPath::addonAssets($subpage, 'pages/index.inc.php');

            if ($this->debug) echo '1.) '.$path1.'<br/>';
            if (file_exists($path1)) return $path1;   
            if (file_exists($path2)) return $path2;  
            return $default_path; 
              		  
    	}
        elseif (cjoAddon::isActivated($this->page) && $subpage) {
    	    
            $path1 = cjoPath::addon($this->page, 'pages/'.$subpage.'.inc.php');
            $path2 = cjoPath::addonAssets($this->page, 'pages/'.$subpage.'.inc.php');

    		if ($this->debug) echo '2.) '.$path1.'<br/>';
            self::$call = $subpage;
            if (file_exists($path1)) return $path1;  
            if (file_exists($path2)) return $path2;  
            self::$call = 'EditStructure';
            return $default_path;      
              
    	}
    	else if ($subpage || $subpage === 0) {
    	    
            $path1 = cjoPath::inc('pages/'.$this->page.'/'.$subpage.'.inc.php');
            self::$call = $this->page.$subpage;
    		if ($this->debug) echo '3.) '.$path1.'<br/>';
    		return $path1;
    	}
    	else if ($this->page != 'login') {

            $article_id = cjo_request('article_id', 'cjo-article-id');
            //[translate: msg_no_permissions_redirected]
            $local_params =  array('page'=>'edit',  'subpage' => 'structure',
                                   'article_id'=> $article_id, 'err_msg'=>'msg_no_permissions_redirected');

            if ($this->debug) {
                echo '4.) '.cjoUrl::createBEUrl($local_params).'<br/>';
            }
            else {
                cjoUrl::redirectBE($local_params);
            }
        }
    }

    /**
     * Adds a number of subpages.
     * @param array $page_settings parameters of the subpages
     * @return void
     * @access public
     */
    public static function addPages($page_settings) {
        if (!is_array($page_settings)) {
            throw new cjoException('Expecting $page_settings to be array, but ' . gettype($page_settings) . ' given!');
        }
        
        foreach($page_settings as $page_setting) {
            self::addPage($page_setting);
        }
    }
    
    /**
     * Adds a subpage.
     * @param array $page_setting parameters of the subpage
     * @return void
     * @access public
     */
    public static function addPage($page_setting) {

        if (!is_array($page_setting)) {
            throw new cjoException('Expecting $page_settings to be array, but ' . gettype($page_setting) . ' given!');
        }               
        
        self::init(); 
        
        if (is_array($page_setting))
            self::$subpages->page_settings[] = $page_setting;
    }
    
    public static function generatePage() {
        
        self::init();

        if (cjoProp::get('PAGE_HEADER')){
            require_once cjoPath::inc('layout/top.php');
        }

        include_once cjoProp::get('PAGE_PATH');
        self::$subpages->getPage();
        $class_name = 'cjoPage'.self::$call;

            
        if (class_exists($class_name)) {
            new $class_name();
        }

        if (cjoProp::get('PAGE_HEADER')) {
            require_once cjoPath::inc('/layout/bottom.php');
        }
    }
    
    /**
     * Adds a subpage.
     * @param array $page_setting parameters of the subpage
     * @return void
     * @access public
     */
    public static function getPagePath() {
        self::$level++;
        self::$initialized = false;
        $page_path = self::$subpages->getPage();
        if (cjoFile::isReadable($page_path)) {
            return $page_path;
        }
    }    
    
    public static function init() {
        if (self::$initialized) return false;
        self::$subpages = new cjoSubPages();
        self::$initialized = true;
    }
}