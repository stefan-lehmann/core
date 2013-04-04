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
 * Use a SQLite table.
 * @package 	Addon_comments
 * @subpackage 	b8
 */
class storage_sqlite extends b8SharedFunctions
{

	# This contains the connection ID
	var $db;

	# Constructor
	# Looks if the SQLite binding is working and trys to create a new table if requested

	function storage_sqlite()
	{

		# Till now, everything's fine
		# Yes, I know that this is crap ;-)
		$this->constructed = TRUE;

		# Default values for the configuration
		$config[] = array("name" => "createDB",		"type" => "bool",	"default" => FALSE);
		$config[] = array("name" => "sqliteFile",	"type" => "path",	"default" => "wordlist.db");
		$config[] = array("name" => "tableName",	"type" => "string",	"default" => "b8_wordlist");

		# Get the configuration

		$configFile = "config_storage";

		if(!$this->loadConfig($configFile, $config)) {
			$this->echoError("Failed initializing the configuration.");
			$this->constructed = FALSE;
		}

		if($this->constructed) {

			# Check if we want to create a new database

			if($this->config['createDB']) {

				# Check if the file already exists

				if($this->checkFile($this->config['sqliteFile'], "e", FALSE)) {
					$this->echoError("<kbd>" . $this->config['sqliteFile'] . "</kbd> already exists. Please remove <kbd>createDB = TRUE</kbd> from <kbd>$configFile</kbd> to use this database or delete it to re-create it with no content.");
					$this->constructed = FALSE;
				}

				# Check if we have write permissions on the directory
				# where the database should be created

				$targetDir = dirname($this->config['sqliteFile']);

				if(!$this->checkFile($targetDir, "dw", TRUE)) {
					$this->echoError("A new database can't be created here. Please fix the permissions of this directory or choose another one.");
					$this->constructed = FALSE;
				}

			}

			else {

				# Check if the requested database exists

				if(!$this->checkFile($this->config['sqliteFile'], "efrw", TRUE)) {

					if($this->checkFile($this->config['sqliteFile'], "d", FALSE)) {
						$this->echoError("A directory can't be used as a database. Please fix your config.");
					}
					elseif(!$this->checkFile($this->config['sqliteFile'], "f", FALSE)) {
						$this->echoError("Please add <kbd>createDB = TRUE</kbd> to <kbd>$configFile</kbd> if you want to create a new database with this path.");
					}
					elseif(!$this->checkFile($this->config['sqliteFile'], "rw", FALSE)) {
						$this->echoError("The database has the wrong permissions. It has to be readable and writable. Please fix this file's permissions.");
					}

					$this->constructed = FALSE;

				}

			}

		}

		if($this->constructed) {

			# Get the SQLite link resource to use

			$arg = FALSE;

			if(func_num_args() > 0)
				$arg = func_get_arg(0);

			if($arg != FALSE) {

				# A resource was passed, so use this one ...
				$this->db = $arg;

				# ... and check if it's really a SQLite-link resource

				$argType = gettype($this->db);

				if(!is_resource($this->db)) {
					$this->echoError("The argument passed to b8 is not a resource (passed variable: \"$argType\"). Please be sure to pass a SQLite-link resource to b8 or pass nothing and make sure that all of the following values are set in <kbd>$configFile</kbd>: <i><kbd>sqliteFile</kbd></i> so that a separate SQLite connection can be set up by b8.");
					$this->constructed = FALSE;
				}

				$resType = get_resource_type($this->db);

				if($resType != "sqlite link" and $this->constructed) {
					$this->echoError("The passed resource is not a SQLite-link resource (passed resource: \"$resType\"). Please be sure to pass a SQLite-link resource to b8 or pass nothing and make sure that all of the following values are set in <kbd>$configFile</kbd>: <i><kbd>sqliteFile</kbd></i> so that a separate SQLite connection can be set up by b8.");
					$this->constructed = FALSE;
				}

			}

			else {

				# No resource was passed, so we want to set up our own connection

				# Set up the SQLite connection
				$this->db = sqlite_open($this->config['sqliteFile']);

				# Check if it's okay
				if($this->db == FALSE) {
					$this->echoError("Could not connect to SQLite.");
					$this->constructed = FALSE;
				}

			}

		}

		if($this->constructed) {

			# Here, we should have a working SQLite connection, so ...

			# Check if we want to create a new database

			if($this->config['createDB']) {

				# Check if the wordlist table already exists
				if(sqlite_single_query("SELECT type FROM SQLITE_MASTER WHERE name = '" . $this->config['tableName'] . "'", $this->db)) {
					$this->echoError("The table <kbd>" . $this->config['tableName'] . "</kbd> already exists in the selected database. Please remove <kbd>createDB = TRUE</kbd> from <kbd>$configFile</kbd> to use this table or drop it to re-create it with no content.");
					$this->constructed = FALSE;
				}

				else {

					# If not, create it

					if(sqlite_query(
						"CREATE TABLE " . $this->config['tableName'] . " (
							token TEXT NOT NULL PRIMARY KEY,
							count TEXT NOT NULL
						)", $this->db)) {

						$this->echoError("Successfully created the table <kbd>" . $this->config['tableName'] . "</kbd>.");

						# Try to put in the "version 2" tag
						if($this->put("bayes*dbversion", "2") == FALSE) {
							$this->echoError("Error accessing the new table.");
							$this->constructed = FALSE;
						}

						else {

							# Everything worked smoothly

							# Anyway -- don't let the user use b8 (although it would work now!)
							# before the "create database" flag isn't removed from the config file

							$this->echoError("Successfully created the new database. Please remove <kbd>createDB = TRUE</kbd> from <kbd>$configFile</kbd> to use b8.");

							$this->constructed = FALSE;

						}

					}

				}

			}

		}

		if($this->constructed) {

			# Check if the table is accessible
			if(!sqlite_query("SELECT type FROM SQLITE_MASTER WHERE name = '" . $this->config['tableName'] . "'", $this->db)) {
				$this->echoError("The table <kbd>" . $this->config['tableName'] . "</kbd> does not exist in the selected database. Please add <kbd>createDB = TRUE</kbd> to <kbd>$configFile</kbd> to create this table of select another one.");
				$this->constructed = FALSE;
			}

		}

		# If the above query worked, we now shoule be able to use b8.

	}

	# Get a token from the database

	function get($token)
	{

		$res = sqlite_fetch_all(sqlite_query("
			SELECT count
			FROM " . $this->config['tableName'] . "
			WHERE token='" . sqlite_escape_string($token) . "'
			", $this->db), SQLITE_ASSOC);

		if($res)
			return $res[0]['count'];
		else
			return FALSE;

	}

	# Store a token to the database

	function put($token, $count)
	{

		$res = @sqlite_query("
			INSERT INTO " . $this->config['tableName'] . " (
				token,
				count
				)
			VALUES(
				'" . sqlite_escape_string($token) . "',
				'$count'
				)
			", $this->db);

		return $res;

	}

	# Update an existing token

	function update($token, $count)
	{
		return sqlite_query(
			"UPDATE " . $this->config['tableName'] . "
			SET count='$count'
			WHERE token='" . sqlite_escape_string($token) . "'
			", $this->db);
	}

	# Remove a token from the database

	function del($token)
	{
		return sqlite_query("
			DELETE FROM " . $this->config['tableName'] . "
			WHERE token='" . sqlite_escape_string($token) . "'
			", $this);
	}

}