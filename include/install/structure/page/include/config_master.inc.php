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

// ----------------- SERVER VARS

$CJO['SETUP'] 				= true; 				// Setupservicestatus - if everything ok -> false; if problem set to true;
$CJO['SERVER'] 				= "meine-domain.de";	//meine-domain.de
$CJO['SERVERNAME'] 			= "MEINE DOMAIN";		//Project XY
$CJO['ERROR_EMAIL'] 		= "info@meine-domain.de";
$CJO['LANG'] 				= "de"; // select default language
$CJO['FILEPERM'] 			= octdec(777); // oktaler wert
$CJO['INSTNAME'] 			= "cjo00000000000000";
$CJO['SESSIONTIME']         = 3000;
$CJO['RELOGINDELAY'] 		= 5; // bei fehllogin 5 sekunden kein relogin möglich
$CJO['MAXLOGINS'] 			= 5; // maximal erlaubte versuche
$CJO['LOCALHOST']			= 'contejo.localhost'; // Host für lokale Arbeitsumgebung

$CJO['TMPL_FILE_TYPE']             		  = 'html';
$CJO['LOGIN_ENABLED'] 		       		  = true;
$CJO['ONLINE_FROM_TO_ENABLED']     		  = true;
$CJO['TEASER_ENABLED']		       		  = true;

$CJO['IMAGE_LIST_BUTTON']['1']['FROM']    = 1;
$CJO['IMAGE_LIST_BUTTON']['1']['TO']      = 5;
$CJO['IMAGE_LIST_BUTTON']['2']['FROM']    = 6;
$CJO['IMAGE_LIST_BUTTON']['2']['TO']      = 10;
$CJO['IMAGE_LIST_BUTTON']['DESCRIPTION']  = true;
$CJO['IMAGE_LIST_BUTTON']['BRAND_IMG'] 	  = false;
$CJO['IMAGE_LIST_BUTTON']['FUNCTIONS'] 	  = true;
$CJO['IMAGE_LIST_BUTTON']['IMAGEBOX'] 	  = true;
$CJO['IMAGE_LIST_BUTTON']['FLASHBOX'] 	  = false;
$CJO['IMAGE_LIST_BUTTON']['VIDEOBOX']	  = false;
$CJO['IMAGE_LIST_BUTTON']['GALLERY_LINK'] = false;
$CJO['IMAGE_LIST_BUTTON']['INT_LINK'] 	  = true;
$CJO['IMAGE_LIST_BUTTON']['EXT_LINK'] 	  = true;
$CJO['IMAGE_LIST_BUTTON']['STYLE'] 		  = false;

// default article id
$CJO['START_ARTICLE_ID'] = 1;

// if there is no article -> change to this article
$CJO['NOTFOUND_ARTICLE_ID'] = 1;

// default clang id
$CJO['START_CLANG_ID'] = 0;

// activate frontend mod_rewrite support for url-rewriting
// Boolean: true/false
$CJO['MODREWRITE']['ENABLED']         = true;

// direct link to redirected articles instead of redirecting to them
// Boolean: true/false
$CJO['MODREWRITE']['LINK_REDIRECT']   = false;

// formating of generated links
// string default '%name%.%article_id%.%clang%.html'
$CJO['MODREWRITE']['DEEPLINK_FORMAT'] = '%name%.%article_id%.%clang%.html';

// activate gzip output support
// reduces amount of data need to be send to the client, but increases cpu load of the server
$CJO['USE_GZIP'] = "false"; // String: "true"/"false"/"fronted"/"backend"

// activate e-tag support
// tag content with a cache key to improve usage of client cache
$CJO['USE_ETAG'] = "false"; // String: "true"/"false"/"frontend"/"backend"

// activate last-modified support
// tag content with a last-modified timestamp to improve usage of client cache
$CJO['USE_LAST_MODIFIED'] = "false"; // String: "true"/"false"/"frontend"/"backend"

// activate md5 checksum support
// allow client to validate content integrity
$CJO['USE_MD5'] = "false"; // String: "true"/"false"/"frontend"/"backend"

// ----------------- MEDIENVERWALTUNG
$CJO['MEDIAFOLDER']         = $CJO['HTDOCS_PATH']."files";
$CJO['SECURE_MEDIAFOLDER']  = $CJO['HTDOCS_PATH']."mediafolder";
$CJO['CACHEFOLDER']         = $CJO['HTDOCS_PATH']."cache";
$CJO['UPLOADFOLDER']        = $CJO['MEDIAFOLDER']."/uploads";
$CJO['TEMPFOLDER']          = $CJO['MEDIAFOLDER']."/temp";
$CJO['UPLOAD_LIMIT'] 		= 10;	// MB

$CJO['FILE_CONFIG_DB'] 		= $CJO['FILE_CONFIG_PATH']."/config_databases.inc.php";
$CJO['FILE_CONFIG_ADDONS'] 	= $CJO['FILE_CONFIG_PATH']."/config_addons.inc.php";
$CJO['FILE_CONFIG_CTYPES'] 	= $CJO['FILE_CONFIG_PATH']."/config_ctypes.inc.php";
$CJO['FILE_CONFIG_LANGS'] 	= $CJO['FILE_CONFIG_PATH']."/config_clangs.inc.php";

$CJO['FOLDER_GENERATED'] 		   = $CJO['CACHEFOLDER']."/generated";
$CJO['FOLDER_GENERATED_ARTICLES']  = $CJO['CACHEFOLDER']."/generated/articles";
$CJO['FOLDER_GENERATED_TEMPLATES'] = $CJO['CACHEFOLDER']."/generated/templates";

$CJO['UPLOAD_EXTENSIONS'] 	= array (".zip", ".ez", ".hqx", ".cpt", ".doc", ".bin", ".dms", ".lha", ".lzh",
									 ".exe", ".class", ".so", ".dll", ".oda", ".pdf", ".ai", ".eps", ".ps",
									 ".smi", ".smil", ".xls", ".ppt", ".wbxml", ".wmlc", ".wmlsc", ".bcpio",
									 ".vcd", ".pgn", ".cpio", ".csh", ".dcr", ".dir", ".dxr", ".dvi", ".spl",
									 ".gtar", ".hdf", ".js", ".skp", ".skd", ".skt", ".skm", ".latex", ".nc",
									 ".cdf", ".sh", ".shar", ".swf", ".sit", ".sv4cpio", ".sv4crc", ".tar",
									 ".tcl", ".tex", ".texinfo", ".texi", ".t", ".tr", ".roff", ".man", ".me",
									 ".ms", ".ustar", ".src", ".xhtml", ".xht", ".zip", ".au", ".snd", ".mid",
									 ".midi", ".kar", ".mpga", ".mp2", ".mp3", ".aif", ".aiff", ".aifc",
									 ".m3u", ".ram", ".rm", ".rpm", ".ra", ".wav", ".pdb", ".xyz", ".bmp",
									 ".gif", ".ief", ".jpeg", ".jpg", ".jpe", ".png", ".tiff", ".tif", ".ice",
									 ".djvu", ".djv", ".wbmp", ".ras", ".pnm", ".pbm", ".pgm", ".ppm", ".ttf",
									 ".rgb", ".xbm", ".xpm", ".xwd", ".igs", ".iges", ".msh", ".mesh", ".pfm",
									 ".silo", ".wrl", ".vrml", ".css", ".html", ".htm", ".asc", ".txt", ".rtx", 
									 ".rtf", ".sgml", ".sgm", ".tsv", ".wml", ".wmls", ".etx", ".xml", ".xsl", 
									 ".mpeg", ".mpeg4", ".mpg", ".mpe", ".qt", ".mov", ".avi",".flv", ".f4v", 
									 ".mp4", ".webm", ".ogg", ".ogv", ".movie", ".asf", ".asx", ".wm", ".wmv", 
									 ".wvx", ".docx", ".pptx", ".xlsx", ".xltx", ".xltm", ".dotx", ".potx", ".ppsx", 
									 ".odt",  ".ott", ".oth", ".odm", ".odg", ".otg", ".odp", ".otp",  ".ods", 
									 ".ots", ".odc", ".odf", ".odb", ".odi", ".oxt", ".sxw", ".stw", ".sxc",
									 ".stc", ".sxd", ".std", ".sxi", ".sti", ".sxg", ".sxm" );
