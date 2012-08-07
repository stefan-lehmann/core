<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     Addons
 * @subpackage  image_processor
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

class garbagecollector {

    public $path;
    public $limit;

    //function __construct($path, $limit=5242880){ //5MB = 5242880
    public function garbagecollector($path, $limit = 5242880) { //5MB = 5242880
        $this->path = $path;
        $this->limit = $limit;
    }

    public function tidy() {

        if (!file_exists($this->path)) return false;

        $dirsize = $this->dirsize($this->path);

        if ($dirsize > $this->limit) {
            $datedfiles = $this->findoldest($this->path);
            $deletedsize = 0;
            while ($deletedsize < ($dirsize - $this->limit) && list ($key, $delfile) = each($datedfiles)) {
                $deletedsize += $delfile['size'];
                unlink($delfile['path']);
            }
        }
        //Leere Verzeichnisse löschen
        $dir = dir($this->path);
        while ($entry = $dir->read()) {
            if (is_dir($this->path.$entry) && $entry != '.' && $entry != '..') {
                $list = scandir($this->path.$entry);
                unset ($list[array_search(".", $list)]);
                unset ($list[array_search("..", $list)]);
                if (count($list) == 0) {
                    rmdir($this->path.$entry);
                }
            }
        }
        return $deletedsize;
    }

    public function findoldest($path, $filearr = array ()) {
        if (is_dir($path)) {
            $dir = dir($path);
            while ($entry = $dir->read()) {
                if (is_file($path.$entry)) {
                    $filearr[filemtime($path.$entry)." | ".$entry] = array (
                        "size" => filesize($path.$entry
                    ), "path" => $path.$entry);
                }
                if (is_dir($path.$entry) && $entry != "." && $entry != "..") {
                    $filearr = $this->findoldest($path.$entry, $filearr);
                }
            }
            ksort($filearr);
            return $filearr;
        } else {
            return NULL;
        }
    }

    public function dirsize($path) {
        if (is_dir($path)) {
            $dir = dir($path);
            $dirsize = 0;
            while ($entry = $dir->read()) {
                if (is_file($path.$entry)) {
                    $dirsize += filesize($path.$entry);
                }
                if (is_dir($path.$entry) && $entry != "." && $entry != "..") {
                    $dirsize += $this->dirsize($path.$entry);
                }
            }
            return $dirsize;
        } else {
            return NULL;
        }
    }
}