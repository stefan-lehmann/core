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

cjoSubPages::addPage( array('categories',
        					'title' => cjoI18N::translate("title_media_categories"),
        					'rights' => array('media[categories]'),
        					'params' => array('page'=>'media', 'subpage'=>'categories', 'media_category'=>cjoMedia::getCategoryId()),
                            'important' => true));

cjoSubPages::addPage( array('media',
                            'params' => array('page'=>'media', 'subpage'=>'media', 'media_category'=>cjoMedia::getCategoryId()),
					        'important' => true));


cjoSubPages::addPage(array('addmedia',
					'rights' => array('media[addmedia]'),
                    'params' => array('page'=>'media', 'subpage'=>'addmedia', 'media_category'=>cjoMedia::getCategoryId()),
                    'important' => true));           


/**
 * Do not delete translate values for cjoI18N collection!
 * [translate: title_categories]
 * [translate: title_media]
 * [translate: title_details]
 * [translate: title_addmedia]
 */