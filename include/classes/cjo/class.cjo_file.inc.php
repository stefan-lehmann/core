<?php

/**
 * Class for handling files
 */
class cjoFile {
    /**
     * Returns the content of a file
     *
     * @param string $file    Path to the file
     * @param mixed  $default Default value
     * @return mixed Content of the file or default value if the file isn't readable
     */
    static public function get($file, $default = null) {
        return self::isReadable($file) ? file_get_contents($file) : $default;
    }

    /**
     * Returns the content of a config file
     *
     * @param string $file    Path to the file
     * @param mixed  $default Default value
     * @return mixed Content of the file or default value if the file isn't readable
     */
    static public function getConfig($file, $default = array()) {
        $content = self::get($file);
        if ($content === null) return $default;
        return json_decode($content, true);
    }

    /**
     * Puts content in a file
     *
     * @param string $file    Path to the file
     * @param string $content Content for the file
     * @return boolean TRUE on success, FALSE on failure
     */
     public static function put($file, $content) {

        if (file_exists($file)) {
            if (!self::isWritable($file)) return false;
        }
        elseif (is_dir($file.'../')) {
            if (!self::isWritable($file.'../')) return false;
        }

        $temp_file = $file.'.'.@getmypid();
        $state = file_put_contents($temp_file, $content);
        @chmod($temp_file, cjoProp::getFilePerm());


        if ($state != false) {
            @unlink($file);
            @rename($temp_file, $file);
            @chmod($temp_file, cjoProp::getFilePerm());           
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Puts content in a config file
     *
     * @param string $file    Path to the file
     * @param mixed  $content Content for the file
     * @return boolean TRUE on success, FALSE on failure
     */
    static public function putConfig($file, $content) {
        return self::put($file, cjoAssistance::toJson($content));
    }

    /**
     * Deletes a file
     *
     * @param string $file Path of the file
     * @return boolean TRUE on success, FALSE on failure
     */
    static public function delete($file) {
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * Extracts the extension of the given filename
     *
     * @param string $file Filename
     * @return string Extension of $file
     */
    static public function getExtension($file) {
        return substr(strrchr($file, '.'), 1);
    }

    /**
     * Formates the filesize of the given file into a userfriendly form
     *
     * @params string|int $fileOrSize Path to the file or filesize
     * @return string Formatted filesize
     */
    static public function getFormattedSize($fileOrSize, $format = array()) {

        $size = is_file($fileOrSize) ? filesize($fileOrSize) : $fileOrSize;

        $kb = 1024;
        // kB
        $mb = 1024 * $kb;
        // MB
        $gb = 1024 * $mb;
        // GB
        $tb = 1024 * $gb;
        // TB

        if ($size < $kb)
            return $size . ' Bytes';
        else if ($size < $mb)
            return round($size / $kb, 2) . ' kB';
        else if ($size < $gb)
            return round($size / $mb, 2) . ' MB';
        else if ($size < $tb)
            return round($size / $gb, 2) . ' GB';
        else
            return round($size / $tb, 2) . ' TB';
    }

    /**
     * Gets executed content of given file
     *
     * @param string $file Path of the file
     * @return string executed Content
     */
    static public function getOutput($file) {
        ob_start();
        require $file;
        return ob_get_clean();
    }

    public static function absPath($rel_path) {

        $path = realpath('.');
        $stack = explode(DIRECTORY_SEPARATOR, $path);

        foreach (explode('/', $rel_path) as $dir) {
            if ($dir == '.') {
                continue;
            }

            if ($dir == '..') {
                array_pop($stack);
            } else {
                array_push($stack, $dir);
            }
        }
        return implode('/', $stack);
    }

    /**
     * Tells whether a file exists and is readable.
     * If not adds an error message.
     * @param string $file
     * @return boolean
     * @access public
     */
    public static function isReadable($file) {

        if (!is_dir($file) && !is_readable($file)) {
            cjoMessage::addError(cjoI18N::translate('msg_file_not_found', $file));
            return false;
        }
        return true;
    }

    /**
     * Tells whether a file exists and is writeable.
     * If not adds an error message.
     * @param string $file
     * @return boolean
     * @access public
     */
    public static function isWritable($file) {

        if (is_dir($file. "/.")) {
            if (!is_writable($file . "/.")) {
                cjoMessage::addError(cjoI18N::translate("msg_folder_no_chmod", $file));
                return false;
            }
        } elseif (@is_file($file)) {
            if (!@is_writable($file)) {
                cjoMessage::addError(cjoI18N::translate("msg_file_no_chmod", $file));
                return false;
            }
        } else {
            cjoMessage::addError(cjoI18N::translate("msg_not_existing", $file));
            return false;
        }
        return true;
    }

    /**
     * Makes a copy of a file. If the destination file already exists,
     * it will be renamed.
     * @param string $source path to the source file
     * @param string $dest destination path
     * @params bool $backup If destination file already exists Create backup or overwrite it
     * @return boolean
     * @access public
     */
    public static function copyFile($source, $dest, $backup = true) {

        $dest_path = pathinfo($dest, PATHINFO_DIRNAME);

        if (!self::isReadable($source) || !self::isWritable($dest_path))
            return false;

        if ($backup && file_exists($dest)) {
            if (!rename($dest, $dest . '_' . date('Y-m-d_H-i-s'))) {
                cjoMessage::addError(cjoI18N::translate('msg_err_copy_file', $source, $dest));
                return false;
            }
        }
        if (!copy($source, $dest)) {
            cjoMessage::addError(cjoI18N::translate('msg_err_copy_file', $source, $dest));
            return false;
        }

        @chmod($dest, cjoProp::getFilePerm());
        return true;
    }

    /**
     * Makes a copy of a directory including subdirectories.
     * @param string $source path to the source file
     * @param string $dest destination path
     * @param string $overwrite overwrite existing files
     * @param int $offset offset count for the possibilty that it somehow miscounts the files
     * @param bool $verbose
     * @return string
     */
    public static function copyDir($srcdir, $dstdir, $overwrite = false, $offset = '', $verbose = false) {

        // A function to copy files from one directory to another one, including subdirectories and
        // nonexisting or newer files. Function returns number of files copied.
        // This function is PHP implementation of Windows xcopy  A:\dir1\* B:\dir2 /D /E /F /H /R /Y
        // Syntaxis: [$returnstring =] dircopy($sourcedirectory, $destinationdirectory [, $offset] [, $verbose]);
        // Example: $num = dircopy('A:\dir1', 'B:\dir2', 1);

        // Original by SkyEye.  Remake by AngelKiha.
        // Linux compatibility by marajax.
        // ([danbrown AT php DOT net): *NIX-compatibility noted by Belandi.]
        // Offset count added for the possibilty that it somehow miscounts your files.  This is NOT required.
        // Remake returns an explodable string with comma differentiables, in the order of:
        // Number copied files, Number of files which failed to copy, Total size (in bytes) of the copied files,
        // and the files which fail to copy.  Example: 5,2,150000,\SOMEPATH\SOMEFILE.EXT|\SOMEPATH\SOMEOTHERFILE.EXT
        // If you feel adventurous, or have an error reporting system that can log the failed copy files, they can be
        // exploded using the | differentiable, after exploding the result string.

        if (!isset($offset))
            $offset = 0;
        $num = 0;
        $fail = 0;
        $sizetotal = 0;
        $fifail = '';
        if (!is_dir($dstdir))
            mkdir($dstdir, cjoProp::getDirPerm());
        if ($curdir = opendir($srcdir)) {
            while ($file = readdir($curdir)) {
                if ($file != '.' && $file != '..' && $file != '.svn') {
                    $srcfile = $srcdir . '/' . $file;
                    # added by marajax
                    $dstfile = $dstdir . '/' . $file;
                    # added by marajax
                    if (is_file($srcfile)) {
                        if (is_file($dstfile))
                            $ow = filemtime($srcfile) - filemtime($dstfile);
                        else
                            $ow = 1;
                        if ($overwrite || $ow > 0) {
                            if ($verbose)
                                echo "Copying '$srcfile' to '$dstfile'...<br />";
                            if (copy($srcfile, $dstfile)) {
                                touch($dstfile, filemtime($srcfile));
                                $num++;
                                chmod($dstfile, cjoProp::getFilePerm());
                                # added by marajax
                                $sizetotal = ($sizetotal + filesize($dstfile));
                                if ($verbose)
                                    echo "OK\n";
                            } else {
                                cjoMessage::addError(cjoI18N::translate('msg_err_copy_file', $srcfile, $dstfile));
                                $fail++;
                                $fifail = $fifail . $srcfile . '|';
                            }
                        }
                    } else if (is_dir($srcfile)) {
                        $res = explode(',', $ret);
                        $ret = self::copyDir($srcfile, $dstfile, $verbose);
                        # added by patrick
                        $mod = explode(',', $ret);
                        $imp = array($res[0] + $mod[0], $mod[1] + $res[1], $mod[2] + $res[2], $mod[3] . $res[3]);
                        $ret = implode(',', $imp);
                    }
                }
            }
            closedir($curdir);
        }
        $red = explode(',', $ret);
        $ret = ($num + $red[0]) . ',' . (($fail - $offset) + $red[1]) . ',' . ($sizetotal + $red[2]) . ',' . $fifail . $red[3];
        return $ret;
    }

    /**
     * Deletes a file
     *
     * @param string $file Path of the file
     * @return boolean TRUE on success, FALSE on failure
     */
    static public function deleteFile($file) {
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * Deletes a file or a directory recursively.
     * @param string $file
     * @param boolean $delete_folders if true deletes directories too
     * @param array $exclude filenames to exclude from delete
     * @return boolean
     * @access public
     */
    public static function deleteDir($file, $delete_folders = false, $exclude = array()) {

        $state = true;

        if (!file_exists($file))
            return false;

        if (@is_dir($file)) {

            $handle = opendir($file);
            if (!$handle)
                return false;

            while ($filename = readdir($handle)) {
                if (in_array($filename, array('.', '..', '.svn', '.gitignore')) || (!empty($exclude) && in_array(OOMedia::getExtension($filename), $exclude)))
                    continue;

                if (($state = self::deleteDir($file . "/" . $filename, $delete_folders)) !== true) {
                    // Schleife abbrechen, dir_hanlde schließen und danach erst false zurückgeben
                    break;
                }
            }
            closedir($handle);

            if ($state !== true)
                return false;

            // remove folders to?
            if ($delete_folders) {
                if (!@rmdir($file))
                    return false;
            }
        } else {
            // delete file
            if (!@unlink($file))
                return false;
        }
        return true;
    }

    /**
     * find files matching a pattern
     * using PHP "glob" function and recursion
     *
     * @return array containing all pattern-matched files
     *
     * @param string $dir     - directory to start with
     * @param string $pattern - pattern to glob for
     */
    public static function rglob($dir, $pattern = '*') {

        if (empty($dir) || !self::isReadable($dir)) return array();

        // escape any character in a string that might be used to trick
        // a shell command into executing arbitrary commands
        $dir = @escapeshellcmd($dir);
        // get a list of all matching files in the current directory
        $files = glob("$dir/$pattern");

        // find a list of all directories in the current directory
        // directories beginning with a dot are also included
        foreach (glob("$dir/{.[^.]*,*}", GLOB_BRACE|GLOB_ONLYDIR) as $sub_dir) {
            $arr = self::rglob($sub_dir, $pattern);
            // resursive call
            $files = array_merge($files, $arr);
            // merge array with files from subdirectory
        }
        // return all found files
        return $files;
    }

}
