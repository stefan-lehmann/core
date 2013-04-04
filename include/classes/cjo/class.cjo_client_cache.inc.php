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

class cjoClientCache {


    /**
     * Sendet einen rex_article zum Client,
     * fügt ggf. HTTP1.1 cache headers hinzu
     *
     * @param $REX_ARTICLE rex_article Den zu sendenen Artikel
     * @param $content string Inhalt des Artikels
     * @param $environment string Die Umgebung aus der der Inhalt gesendet wird
     * (frontend/backend)
     */
    public static function sendArticle($article, $content, $environment, $sendcharset = false) {

        // ----- EXTENSION POINT
        $content = cjoExtension::registerExtensionPoint('OUTPUT_FILTER', array('subject' => $content, 'environment' => $environment, 'sendcharset' => $sendcharset));

        if ($environment == 'frontend') {
            $content = cjoOutput::replaceLinks($content);
            $content = cjoOutput::encryptEmails($content);         
            $content = cjoOpfLang::translate($content);
        }
        else {
            $content = cjoOutput::replaceHTML5Tags($content);
        }
        
        if (cjoProp::get('ADJUST_PATH')) {
            $content = preg_replace('#(?<!\.)'.preg_quote(cjoPath::htdocs()).'#', 
                                    cjoProp::get('ADJUST_PATH'),
                                    $content);
            $content = preg_replace('#(?<!\.)'.str_replace('/', '\\\/', preg_quote(cjoPath::htdocs())).'#', 
                                    str_replace('/', '\/', cjoProp::get('ADJUST_PATH')),
                                    $content);
        }

        $content = cjoOutput::prettifyOutput($content);
        
        // ----- EXTENSION POINT - keine Manipulation der Ausgaben ab hier (read only)
        cjoExtension::registerExtensionPoint('OUTPUT_FILTER_CACHE', $content, '', true);

        // dynamische teile sollen die md5 summe nicht beeinflussen
        $etag = md5($content);

        if ($article) {
            $last_modified = $article->getValue('updatedate');
            $etag .= $article->getValue('pid');

            if ($article->getArticleId() == cjoProp::get('NOTFOUND_ARTICLE_ID') &&
                $article->getArticleId() != cjoProp::get('START_ARTICLE_ID')) {
                header("HTTP/1.0 404 Not Found");
            }
        } else {
            $last_modified = time();
        }

        self::sendContent($content, $last_modified, $etag, $environment, $sendcharset);
    }

    /**
     * Sendet eine Datei zum Client
     *
     * @param $file string Pfad zur Datei
     * @param $contentType ContentType der Datei
     * @param $environment string Die Umgebung aus der der Inhalt gesendet wird
     * (frontend/backend)
     */
    public static function sendImage($filename, $content_type = false, $environment = 'frontend') {

        cjoProp::set('USE_GZIP',false);
        self::sendFile($filename, $content_type, $environment);
    }

    /**
     * Sendet eine Datei zum Client
     *
     * @param $file string Pfad zur Datei
     * @param $contentType ContentType der Datei
     * @param $environment string Die Umgebung aus der der Inhalt gesendet wird
     * (frontend/backend)
     */
    public static function sendFile($filename, $content_type = false, $environment = 'frontend') {

        while(@ob_end_clean());

        if (!is_file($filename)) {
           header("HTTP/1.0 404 Not Found");
           exit();
        }

        if(!is_readable($filename)) {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }

        if ($content_type === false) {
            $content_type = cjoMedia::detectMime($filename);
        }

        $stat = @stat($filename);
        

        header('Content-Type: '. $content_type);
        header('Content-Length:' . $stat['size']);
        header('Content-Disposition: filename="'.pathinfo($filename,PATHINFO_BASENAME).'"');

        // ----- Last-Modified
        self::sendLastModified($stat['mtime']);

        // ----- ETAG
        self::sendEtag(sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000));


        if (@readfile($filename) === false) {
            header('HTTP/1.0 500 Internal Server Error');
        }

        exit();
    }


    /**
     * Sendet den Content zum Client,
     * fügt ggf. HTTP1.1 cache headers hinzu
     *
     * @param $content string Inhalt des Artikels
     * @param $last_modified integer Last-Modified Timestamp
     * @param $cache_md5 string Cachekey zur identifizierung des Caches
     * @param $environment string Die Umgebung aus der der Inhalt gesendet wird
     * (frontend/backend)
     */
    public static function sendContent($content, $last_modified, $etag, $environment, $sendcharset = false) {

        while(@ob_end_clean());

        // Cachen erlauben, nach revalidierung
        // see http://xhtmlforum.de/35221-php-session-etag-header.html#post257967
        session_cache_limiter('none');
        header('Cache-Control: must-revalidate, proxy-revalidate, private');

        if ($sendcharset) {
            header('Content-Type: text/html; charset='.cjoI18N::translate('htmlcharset'));
        }

        // ----- Last-Modified
        if (cjoProp::get('USE_LAST_MODIFIED') === 'true' ||
            cjoProp::get('USE_LAST_MODIFIED') == $environment) self::sendLastModified($last_modified);

        // ----- ETAG
        if (cjoProp::get('USE_ETAG') === 'true' ||
            cjoProp::get('USE_ETAG') == $environment) self::sendEtag($etag);

        // ----- GZIP
        if (cjoProp::get('USE_GZIP') === 'true' ||
            cjoProp::get('USE_GZIP') == $environment) $content = self::sendGzip($content);

        // ----- MD5 Checksum
        // dynamische teile sollen die md5 summe nicht beeinflussen
        if (cjoProp::get('USE_MD5') === 'true' ||
            cjoProp::get('USE_MD5') == $environment) self::sendChecksum($content);

        // content length schicken, damit der browser einen ladebalken anzeigen kann
        header('Content-Length: '. strlen($content));

        echo $content;
        exit();
    }

    /**
     * Pr�ft, ob sich dateien geändert haben
     *
     * XHTML 1.1: HTTP_IF_MODIFIED_SINCE feature
     *
     * @param $last_modified integer Last-Modified Timestamp
     */
    public static function sendLastModified($last_modified = null)  {

        if (!$last_modified) $last_modified = time();

        $last_modified = date('r', $last_modified);

        // Sende Last-Modification time
        header('Last-Modified: ' . $last_modified);

        // Last-Modified Timestamp gefunden
        // => den Browser anweisen, den Cache zu verwenden
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $last_modified) {
            while(@ob_end_clean());
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }

    /**
     * Pr�ft ob sich der Inhalt einer Seite im Cache des Browsers befindet und
     * verweisst ggf. auf den Cache
     *
     * XHTML 1.1: HTTP_IF_NONE_MATCH feature
     *
     * @param $cache_md5 string Cachekey zur identifizierung des Caches
     */
    public static function sendEtag($etag) {

        // Laut HTTP Spec muss der Etag in " sein
        $etag = '"'. $etag .'"';

        // Sende CacheKey als ETag
        header('ETag: '. $etag);

        // CacheKey gefunden
        // => den Browser anweisen, den Cache zu verwenden
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
            while(@ob_end_clean());
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }

    /**
     * Kodiert den Inhalt des Artikels in GZIP/X-GZIP, wenn der Browser eines der
     * Formate unterst�tzt
     *
     * XHTML 1.1: HTTP_ACCEPT_ENCODING feature
     *
     * @param $content string Inhalt des Artikels
     */
    public static function sendGzip($content) {

        $enc = '';
        $encodings = array();
        $gzip_supported = false;

        // Check if it supports gzip
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
            $encodings = explode(',', strtolower(preg_replace('/\s+/', '', $_SERVER['HTTP_ACCEPT_ENCODING'])));

        if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings) ||
            isset($_SERVER['---------------'])) &&
            function_exists('ob_gzhandler') &&
            !ini_get('zlib.output_compression')) {
            $enc = in_array('x-gzip', $encodings) ? 'x-gzip' : 'gzip';
            $gzip_supported = true;
        }

        if ($gzip_supported)  {
            header('Content-Encoding: '. $enc);
            $content = gzencode($content, 9, FORCE_GZIP);
        }
        return $content;
    }

    /**
     * Sendet eine MD5 Checksumme als HTTP Header, damit der Browser validieren
     * kann, ob übertragungsfehler aufgetreten sind
     *
     * XHTML 1.1: HTTP_CONTENT_MD5 feature
     *
     * @param $md5 string MD5 Summe des Inhalts
     */
    public static function sendChecksum($content) {
        header('Content-MD5: '. md5(content));
    }
}