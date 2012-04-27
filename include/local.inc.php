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
 * @version     2.6.0
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

if ($CJO['CONTEJO'] && cjo_request('subpage','string') != 'content') return;

switch($CJO['CLANG_ISO'][$CJO['CUR_CLANG']]){

    case 'de':
        setlocale (LC_ALL, array('de_DE@euro', 'de_DE', 'de', 'ge'));
        $CJO['setlocal']['lang']          = array();
        $CJO['setlocal']['long_date']     = '%A, %d.&nbsp;%B %Y';
        $CJO['setlocal']['medium_date']   = '%a., %d.&nbsp;%b. %y';
        $CJO['setlocal']['short_date']    = '%d.%m.%y';
        $CJO['setlocal']['long_time']     = '%H:%M:%S';
        $CJO['setlocal']['short_time']    = '%H.%M Uhr';
        $CJO['setlocal']['short_day']     = '%a, ';
        $CJO['setlocal']['dec_point']     = ',';
        $CJO['setlocal']['thousands_sep'] = '.';
        break;

    case 'es':
        setlocale (LC_ALL, array('es_ES@euro', 'es_ES', 'es', 'ES'));
        $CJO['setlocal']['long_date']     = '%A, %d.&nbsp;%B %Y';
        $CJO['setlocal']['medium_date']   = '%a., %d.&nbsp;%b. %y';
        $CJO['setlocal']['short_date']    = '%d.%m.%y';
        $CJO['setlocal']['long_time']     = '%H:%M:%S';
        $CJO['setlocal']['short_time']    = '%H:%M Hrs.';
        $CJO['setlocal']['short_day']     = '%a, ';
        $CJO['setlocal']['dec_point']     = ',';
        $CJO['setlocal']['thousands_sep'] = ' ';
        break;

    case 'fr':
        setlocale (LC_ALL, array('fr_FR@euro', 'fr_FR', 'fra', 'FR'));
        $CJO['setlocal']['long_date']     = '%A, %d.&nbsp;%B %Y';
        $CJO['setlocal']['medium_date']   = '%a., %d.&nbsp;%b. %y';
        $CJO['setlocal']['short_date']    = '%d.%m.%y';
        $CJO['setlocal']['long_time']     = '%H:%M:%S';
        $CJO['setlocal']['short_time']    = '%H:%M Hrs.';
        $CJO['setlocal']['short_day']     = '%a, ';
        $CJO['setlocal']['dec_point']     = ',';
        $CJO['setlocal']['thousands_sep'] = ' ';
        break;

    default:
        setlocale (LC_ALL, array('us_US@euro', 'en_US', 'en', 'EN'));
        $CJO['setlocal']['long_date']     = '%A, %B&nbsp;%d, %Y';
        $CJO['setlocal']['medium_date']   = '%a., %b.&nbsp;%d, %y';
        $CJO['setlocal']['short_date']    = '%m-%d-%y';
        $CJO['setlocal']['long_time']     = '%H:%M:%S';
        $CJO['setlocal']['short_time']    = '%I:%M %p';
        $CJO['setlocal']['short_day']     = '%a, ';
        $CJO['setlocal']['dec_point']     = '.';
        $CJO['setlocal']['thousands_sep'] = ',';
}
