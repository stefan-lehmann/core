<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  search
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@raumsicht.com>
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

$mypage = 'search';

// --- DYN

$CJO['ADDON']['settings'][$mypage]['SETUP'] = "false";

$CJO['ADDON']['settings'][$mypage]['ELEMENTS_PER_PAGE'] = "10";
$CJO['ADDON']['settings'][$mypage]['LIMIT_RESULTS'] = "35";
$CJO['ADDON']['settings'][$mypage]['MIN_LENGTH'] = "4";
$CJO['ADDON']['settings'][$mypage]['RELEVANCE'] = '0.5';
$CJO['ADDON']['settings'][$mypage]['SOURROUND_TAG_LENGTH'] = "60";

$CJO['ADDON']['settings'][$mypage]['SEARCH_ARTICLE_ID'] = "217";
$CJO['ADDON']['settings'][$mypage]['SEARCH_TEMPLATE_ID'] = "";
$CJO['ADDON']['settings'][$mypage]['EXCLUDE_ARTICLES'] = "132|52|9|217|59|102|105";
$CJO['ADDON']['settings'][$mypage]['INCLUDE_MODULES'] = "1|2";
$CJO['ADDON']['settings'][$mypage]['ARTICLE_VALUES'] = "name|title";
$CJO['ADDON']['settings'][$mypage]['SLICE_VALUES'] = "value1|value2|value3|value4|value5";

// --- /DYN