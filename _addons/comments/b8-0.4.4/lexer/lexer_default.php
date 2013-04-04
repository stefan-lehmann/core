<?php
/**
 * This file is part of the b8 package
 *
 * @package 	Addon_comments
 * @subpackage 	b8
 * @version   	SVN: $Id$
 *
 * @author 		Tobias Leupold
 * @copyright	(C) 2006-2009 Tobias Leupold <tobias.leupold@web.de>
 * @link      	http://contejo.com
 *
 * @license 	http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * CONTEJO is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See _copyright.txt for copyright notices and
 * details.
 * @filesource
 */

/**
 * Get the shared functions class file (if not already loaded)
 */
require_once dirname(__FILE__) . "/../shared_functions.php";

/**
 * The default class to split a text into tokens.
 * @package 	Addon_comments
 * @subpackage 	b8
 */
class lexer_default extends b8SharedFunctions
{

	# Constructor

	function lexer_default()
	{

		# Till now, everything's fine
		# Yes, I know that this is crap ;-)
		$this->constructed = TRUE;

		# Config parts we need
		$config[] = array("name" => "minSize",		"type" => "int",	"default" => 3);
		$config[] = array("name" => "maxSize",		"type" => "int",	"default" => 15);
		$config[] = array("name" => "allowNumbers",	"type" => "bool",	"default" => FALSE);

		# Get the configuration

		$configFile = "config_lexer";

		if(!$this->loadConfig($configFile, $config)) {
			$this->echoError("Failed initializing the configuration.");
			$this->constructed = FALSE;
		}

	}

	# Split the text up to tokens

	function getTokens($text)
	{

		# Check if we have a string here

		if(!is_string($text)) {
			$this->echoError("The given parameter is not a string (<kbd>" . gettype($text) . "</kbd>). Cannot lex it.");
			return FALSE;
		}

		$tokens = "";

		# Get internet and IP addresses

		preg_match_all("/([A-Za-z0-9\_\-\.]+)/", $text, $raw_tokens);

		foreach($raw_tokens[1] as $word) {

			if(strpos($word, ".") === FALSE)
				continue;

			if(!$this->isValid($word))
				continue;

			if(!isset($tokens[$word]))
				$tokens[$word] = 1;
			else
				$tokens[$word]++;

			# Delete the processed parts
			$text = str_replace($word, "", $text);

			# Also process the parts of the urls

			$url_parts = preg_split("/[^A-Za-z0-9!?\$¤¥£'`ÄÖÜäöüßÉéÈèÊêÁáÀàÂâÓóÒòÔôÇç]/", $word);

			foreach($url_parts as $word) {

				if(!$this->isValid($word))
					continue;

				if(!isset($tokens[$word]))
					$tokens[$word] = 1;
				else
					$tokens[$word]++;

			}

		}

		# Raw splitting of the remaining text

		$raw_tokens = preg_split("/[^A-Za-z0-9!?\$¤¥£'`ÄÖÜäöüßÉéÈèÊêÁáÀàÂâÓóÒòÔôÇç]/", $text);

		foreach($raw_tokens as $word) {

			if(!$this->isValid($word))
				continue;

			if(!isset($tokens[$word]))
				$tokens[$word] = 1;
			else
				$tokens[$word]++;

		}

		# Get HTML

		preg_match_all("/(<.+?>)/", $text, $raw_tokens);

		foreach($raw_tokens[1] as $word) {

			if(!$this->isValid($word))
				continue;

			# If the text has parameters, just use the tag

			if(strpos($word, " ") !== FALSE) {
				preg_match("/(.+?)\s/", $word, $tmp);
				$word = "{$tmp[1]}...>";
			}

			if(!isset($tokens[$word]))
				$tokens[$word] = 1;
			else
				$tokens[$word]++;

		}

		# Return a list of all found tokens
		return($tokens);

	}

	# Check if a token is valid

	function isValid($token)
	{

		# Check for a proper length
		if(strlen($token) < $this->config['minSize'] or strlen($token) > $this->config['maxSize'])
			return FALSE;

		# If wanted, exclude pure numbers
		if($this->config['allowNumbers'] == FALSE) {
			if(preg_match("/^[0-9]+$/", $token))
				return FALSE;
		}

		# Otherwise, the token is okay
		return TRUE;

	}
}