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

if ($subpage == 'details' && 
    (!cjo_request('oid', 'bool') && !cjo_request('filename', 'bool'))) {
	$subpage = 'media';
}

$mypage            = $cur_page['page'];
$oid               = cjo_request('oid', 'int');
$function          = cjo_request('function', 'string');
$mode              = cjo_request('mode', 'string');
$filename          = cjo_request('filename', 'string');
$media_category    = cjo_request('media_category', 'cjo-mediacategory-id', 0);

$media_perm        = array();
$media_perm['r']   = $CJO['USER']->hasMediaPerm('this');
$media_perm['w']   = $CJO['USER']->hasMediaPerm('this') && !$CJO['USER']->hasPerm('editContentOnly[]');

if (empty($oid) &&
	!empty($filename) &&
	file_exists($CJO['MEDIAFOLDER'].'/'.$filename)){
		$media_obj = OOMedia::getMediaByName($filename);
		cjoAssistance::redirectBE(array('subpage'=>'details', 'oid'=> $media_obj->getId(),
		                                'media_category'=>$media_obj->getCategoryId(),
		                                'filename' => ''));
}

if ($subpage == '' || $subpage == 'media' ||
	$subpage == 'details' || $subpage == 'categories') {
    new cjoSelectMediaCat();
    $CJO['SEL_MEDIA']->get(true);
}

$subpages = new cjoSubPages($subpage, $mypage);
$subpages->addPage( array('categories',
					'title' => $I18N->msg("title_media_categories"),
					'rights' => array('media[categories]'),
					'query_str' => 'page=media&subpage=categories&media_category='.$media_category,
                    'important' => true));

if (!$oid && !$filename) {
    $subpages->addPage( array('media',
						'query_str' => 'page=media&subpage=media&media_category='.$media_category,
						'important' => true));
}
else {
    $subpages->addPage( array('details',
						'title' => $I18N->msg("title_media"),
						'query_str' => 'page=media&subpage=details&media_category='.$media_category,
						'important' => true));
}

$subpages->addPage(array('addmedia',
					'rights' => array('media[addmedia]'),
					'query_str' => 'page=media&subpage=addmedia&media_category='.$media_category,
                    'important' => true));

require_once $subpages->getPage();

/**
 * Do not delete translate values for cjoI18N collection!
 * [translate: title_categories]
 * [translate: title_media]
 * [translate: title_details]
 * [translate: title_addmedia]
 */