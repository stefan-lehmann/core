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

function cjoSetupImport($import_sql) {

	$export_addon_dir = cjoPath::addon(import_export);

	if (!is_dir($export_addon_dir)) {
		cjoMessage::addError(cjoI18N::translate("msg_im_export_addon_missing"));
		return false;
	}
	else {
		if (file_exists($import_sql) && ($import_tar === null || file_exists($import_tar))) {

			// Set DB to UTF-8
			$sql = new cjoSql();
			$sql->setDirectQuery('ALTER DATABASE '.$sql->connection['db_name'].' DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci');

			// DB Import
			$replace_cjo = false;
			if (cjoProp::getTablePrefix() != "cjo_") $replace_cjo = true;
			cjoImportExport::importSqlFile($import_sql, $replace_cjo);

			if (cjoMessage::hasErrors()) {
			    return false;
			}
		}
		else {
			cjoMessage::addError(cjoI18N::translate("msg_no_exports_found"));
			return false;
		}
	}
	return true;
}