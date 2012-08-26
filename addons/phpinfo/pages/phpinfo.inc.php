<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  phpinfo
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
?>

<style type="text/css">
.a22-cjolist-data {background: #fff;}
.a22-cjolist-data table {background: #fff;}
.a22-cjolist-data .center {}
.a22-cjolist-data .center table {}
.a22-cjolist-data .center th {}
.a22-cjolist-data th {padding: 5px; text-transform: uppercase; background-color: #fff;}
.a22-cjolist-data h1, .a22-cjolist-data h1 a {color: #000; background: #fff; font-size: 20px; margin: 10px; cursor: pointer}
.a22-cjolist-data h2, .a22-cjolist-data h2 a {background: #fff; color: #000; font-size: 16px; cursor: pointer}
.a22-cjolist-data .p {text-align: left;}
.a22-cjolist-data .e {background-color: #ccccff; font-weight: bold; color: #000000; width: 300px;}
.a22-cjolist-data .h,
.a22-cjolist-data .h h1 { font-weight: bold; color: #000000; background: #f2f2f2!important}
.a22-cjolist-data .v {color: #000000;}
.a22-cjolist-data .vr {text-align: right; color: #000000;}
.a22-cjolist-data img {float: right; border: 0px;}
.a22-cjolist-data hr {background-color: #cccccc; border: 0px; height: 1px; color: #000000;}

.a22-cjolist-data {height: 500px; overflow: auto; margin-bottom: 20px}
</style>


<div class="a22-cjolist">
      <div class="a22-cjolist-data">


<?php

ob_start () ;
phpinfo () ;
$pinfo = ob_get_contents () ;
ob_end_clean () ;
//cjo_debug($pinfo);
// the name attribute "module_Zend Optimizer" of an anker-tag is not xhtml valide, so replace it with "module_Zend_Optimizer"
echo ( str_replace ( "module_Zend Optimizer", "module_Zend_Optimizer", preg_replace ( '%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo ) ) ) ;
?>
    </div>
</div>