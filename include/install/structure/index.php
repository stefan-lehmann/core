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

error_reporting(E_ALL ^ E_NOTICE);
// ----- ob caching start für output filter

ob_start();

// ----------------- MAGIC QUOTES CHECK
require_once "./contejo/include/functions/function.cjo_mquotes.inc.php";

// --------------------------- globals
unset($CJO);

// Flag ob Inhalte mit CONTEJO aufgerufen oder
// von der Webseite aus
// Kann wichtig für die Darstellung sein
// Sollte immer false bleiben
$CJO['CONTEJO'] = false;

// Wenn $CJO[GG] = true; dann wird der
// Content aus den contejo/include/generated/
// genommen
$CJO['GG'] = true;

// setzte pfad und includiere klassen und funktionen
$CJO['HTDOCS_PATH'] = "./";
include $CJO['HTDOCS_PATH']."contejo/include/master.inc.php";

// Starte einen neuen Artikel und setzte die aktuelle
// artikel id. wenn nicht vorhanden, nimm einen
// speziellen artikel. z.b. fehler seite oder home seite

if ($CJO['SETUP']) {
	header('Location: contejo/index.php');
	exit();
} 

if (cjo_get('process_image', 'bool')) {
    cjoGenerate::processImage();
    exit();
}

if (cjo_get('cjo_anchor', 'bool')) {
    cjoAssistance::redirectAchor();
}

if (!cjo_request('article_id','cjo-article-id') || 
    !$CJO['CLANG'][cjo_request('clang','cjo-clang-id', -1)]) {
    cjoAssistance::redirectFE($CJO['ARTICLE_ID'], cjo_request('clang','cjo-clang-id', false));
}

$CJO_ARTICLE = new cjoArticle();
$CJO_ARTICLE->setCLang($CJO['CUR_CLANG']);
$CJO_ARTICLE->setArticleId($CJO['ARTICLE_ID']);
$CONTENT = $CJO_ARTICLE->getArticleTemplate();

cjoClientCache::sendArticle($CJO_ARTICLE, $CONTENT, 'frontend', true);
