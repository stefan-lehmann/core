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
 * cjoSelectMediaCat class
 *
 * The cjoSelectMediaCat class creates a selectbox object
 * that represents the media category structure. At the
 * same time it provides access to the media categories,
 * depending on the user permissions.
 * @package 	contejo
 * @subpackage 	core
 */
class cjoSelectMediaCat extends cjoSelect {

    /**
     * Constructor.
     * @return void
     */
    function __construct() {

        global $CJO, $I18N, $media_category;

        if (!$CJO['CONTEJO'] || !$CJO['USER']) return false;

        parent :: __construct();

        if (empty($media_category) && $media_category !== 0) {
            $media_category = cjo_request('media_category',
                                          'cjo-mediacategory-id',
                                          cjo_session('MEDIA_CATEGORY',
                                                      'cjo-mediacategory-id',
                                                       0));
        }
        
        $this->setName('custom_select');
        $this->setStyle('class="custom_select"');
        $this->setStyle('width: 928px');
        $this->showRoot($I18N->msg('label_media_root'), 'root');
        $this->setSize(1);
        $this->setSelected($this->getCurrMediaCategory());
        $this->setSelectedPath(OOMediaCategory::getPath($media_category));

        $query = "SELECT
                    a.name AS name, a.id AS value, a.id AS id, a.re_id as re_id,
 					IF((SELECT count(re_id)
 						FROM ".TBL_FILE_CATEGORIES."
 						WHERE re_id = a.id
 						GROUP BY re_id), 'folder', 'file') AS title
                  FROM
                    ".TBL_FILE_CATEGORIES." a
                  ORDER BY name";

        if ($this->addSqlOptions($query)) {
            $CJO['SEL_MEDIA'] = $this;
        }
    }

    /**
     * Adds the results of a mysql request as a set of options to the selectbox.
     * @param string $query sql query
     * @param booelan $sqldebug if true the request is executed in debug mode
     * @return booelan|string on success returns true, if a sql error occurs the error message is returned
     */
    public function addSqlOptions($query, $sqldebug = false) {

        global $CJO;

        $sql = new cjoSql();
        $result = $sql->getArray($query, PDO::FETCH_NUM);

        if ($sql->getError() != '') return $sql->getError();

        if ($sqldebug) {
            cjo_debug($sql,'ADD_SQLOPTIONS','lightgreen');
        }

        if (count($result[0]) < 2 && count($result[0]) > 5) {
            return false;
        }

        foreach ($result as $value) {

            if (!$CJO['USER']->hasMediaPerm($value[1])) {
            	$value[4]	.= '_locked';
            }

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
     * Validates the current media category.
     * @return void
     */
    function getCurrMediaCategory() {

    	global $CJO, $media_category;

    	if (cjo_get('category_id', 'bool')) $media_category = cjo_request('category_id', 'int');

    	if ($media_category) {
    		$sql = new cjoSql();
    		$sql->setQuery("SELECT * FROM ".TBL_FILE_CATEGORIES." WHERE id='".$media_category."'");
    		if ($sql->getRows() == 0) {
    			$media_category = 0;
    		}
    	}
    	else {
    		$media_category = 0;
    	}

//    	if (!$CJO['USER']->hasMediaPerm($media_category)) {
//
//    		$media_category = -1;
//
//    		$sql = new cjoSql();
//    		$sql->setQuery("SELECT id FROM ".TBL_FILE_CATEGORIES." ORDER BY path");
//
//    		for ($i = 0; $i < $sql->getRows(); $i++) {
//    			if ( $CJO['USER']->hasMediaPerm($sql->getValue('id'))) {
//    				$media_category = $sql->getValue('id');
//    				break;
//    			}
//    			$sql->next();
//    		}
//    	}
        
    	if ($media_category) {
   	        cjo_set_session('MEDIA_CATEGORY', $media_category);
    	}
    	else {
   	        cjo_unset_session('MEDIA_CATEGORY');
    	}
    	$this->setSelected($media_category);
    }

    /**
     * Writes the generated $CJO['SEL_MEDIA'] object.
     * @param boolean $render
     * @return void|string
     */
    public function get($render=false){

        global $CJO, $subpage;

        $s  = $CJO['SEL_MEDIA']->_get();

        if ($this->getSelectId() == 'custom_select') {

        $s .= '<script type="text/javascript">'."\r\n".
              '/* <![CDATA[ */'."\r\n".
              '	$(function() {'."\r\n".
              '    $(\'#custom_select\').selectpath({'."\r\n".
              '			action : {root   	  			: "location.href=\'index.php?page=media&subpage='.$subpage.'&media_category=0\'",'."\r\n".
              '					  categories 			: "location.href=\'index.php?page=media&subpage='.$subpage.'&media_category=\'+id",'."\r\n".
              '					  category 	 			: "location.href=\'index.php?page=media&subpage='.$subpage.'&media_category=\'+id",'."\r\n".
              '					  \'categories locked\' : "location.href=\'index.php?page=media&subpage='.$subpage.'&media_category=\'+id",'."\r\n".
              '					  \'category locked\'   : "location.href=\'index.php?page=media&subpage='.$subpage.'&media_category=\'+id"'."\r\n".
              '				      },'."\r\n".
              '			types  : {root	 		: \'root\','."\r\n".
              '					  folder 		: \'categories\','."\r\n".
              '					  file	 		: \'category\','."\r\n".
              '					  folder_locked : \'categories locked\','."\r\n".
              '					  file_locked 	: \'category locked\''."\r\n".
              '					  }'."\r\n".
              '		});'."\r\n".
              '	});'."\r\n".
              '/* ]]> */'."\r\n".
              '</script>'."\r\n";
        }

        if ($render == true) {
            echo '<div id="cjo_cat_path">'.$s.'</div>';
        } else {
            return $s;
        }
    }

    public function _get(){
        return parent::get();
    }
}