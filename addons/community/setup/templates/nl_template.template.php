<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  community
 * @version     2.6.0
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

$base = 'http://'.$_SERVER['SERVER_NAME'].substr($_SERVER['PHP_SELF'],0,(strrpos($_SERVER['PHP_SELF'],'/')+1));

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>CJO_META[title]</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<base href="<?php echo $base; ?>">
<style type="text/css">
<!--
A {color: #000}
.text,P {font-family: Arial, Helvetica, sans-serif;font-size: 12px;}
.headline,h2 {color: #EC193A;font-weight: bold; font-family: Arial, Helvetica, sans-serif; margin-top: 1em}
.style1 {font-size: 14px; display: block;}
.style2 {color: #666; font-size: 14px; display: block;}
.style4 {font-size: 14px;font-family: Arial, Helvetica, sans-serif;}
.style5 {font-size: 10px; color: #999; margin-top: 20px; paddding-top: 20px; width: 60%; border-top: 1px solid #999}
.block {display: block;}
#unsubscribe {margin-top: 40px; width: 70%; border-top: 1px solid #999}
#unsubscribe p {text-align: justify; font-size: 10px; color: #999;}
-->
</style>
</head>
<body text="#666666" link="#000000" vlink="#000000" alink="#000000">
<div align="center">

CJO_ARTICLE_CTYPE[0]

<div id="unsubscribe">
    <br>
    <p>
         Diesen Newsletter haben Sie erhalten, weil Ihre
         eMail-Adresse in unsere Mailingliste eingetragen wurde.
         Falls dies ohne Ihr Einverständnis erfolgt ist oder wenn Sie
         keine weiteren Newsletter erhalten möchten, klicken Sie bitte
         auf folgenden Link, um Ihre eMail-Adresse aus der Mailingliste
         auszutragen. eMail-Adresse austragen: %link%
     </p>
</div>
</div>
</body>
</html>