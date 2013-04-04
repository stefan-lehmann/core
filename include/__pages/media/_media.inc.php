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

if (cjoProp::getSubpage() == 'details' && 
    (!cjo_request('oid', 'bool') && !cjo_request('filename', 'bool'))) {
	cjoProp::setSubpage( 'media');
}
$oid               = cjo_request('oid', 'int');
$function          = cjo_request('function', 'string');
$mode              = cjo_request('mode', 'string');
$filename          = cjo_request('filename', 'string');
$media_category    = cjo_request('media_category', 'cjo-mediacategory-id', 0);

$media_perm        = array();
$media_perm['r']   = cjoProp::getUser()->hasMediaPerm('this');
$media_perm['w']   = cjoProp::getUser()->hasMediaPerm('this') && !cjoProp::getUser()->hasPerm('editContentOnly[]');

if (empty($oid) && !empty($filename) &&
	file_exists(cjoPath::media($filename))) {
		$media_obj = OOMedia::getMediaByName($filename);
		cjoUrl::redirectBE(array('subpage' => 'details', 'oid' => $media_obj->getId(),
		                         'media_category' => $media_obj->getCategoryId(),
		                         'filename' => ''));
}

if (!cjoProp::getSubpage() || cjoProp::getSubpage() == 'media' ||
	cjoProp::getSubpage() == 'details' || cjoProp::getSubpage() == 'categories') {
    cjoSelectMediaCat::$sel_media->get(true);
}


cjoSubPages::addPage( array('categories',
        					'title' => cjoI18N::translate("title_media_categories"),
        					'rights' => array('media[categories]'),
        					'params' => array('page'=>'media', 'subpage'=>'categories', 'media_category'=>$media_category),
                            'important' => true));

if (!$oid && !$filename) {
    cjoSubPages::addPage( array('media',
                                'params' => array('page'=>'media', 'subpage'=>'media', 'media_category'=>$media_category),
						        'important' => true));
}
else {
    cjoSubPages::addPage( array('details',
						'title' => cjoI18N::translate("title_media"),
                        'params' => array('page'=>'media', 'subpage'=>'details', 'media_category'=>$media_category),
						'important' => true));
}

cjoSubPages::addPage(array('addmedia',
					'rights' => array('media[addmedia]'),
                    'params' => array('page'=>'media', 'subpage'=>'addmedia', 'media_category'=>$media_category),
                    'important' => true));           

require_once cjoSubPages::getPagePath();

/**
 * Do not delete translate values for cjoI18N collection!
 * [translate: title_categories]
 * [translate: title_media]
 * [translate: title_details]
 * [translate: title_addmedia]
 */