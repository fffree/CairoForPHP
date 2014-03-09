<?php
/**
 * Auxiliary Functions for Utilities
 *
 * These are auxiliary functions used by most of the utility scripts.
 *
 * PHP version 5.3.x
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package    CairoForPHP
 * @subpackage Utilities
 * @author     Florian F Freeman <florian@phpws.org>
 * @copyright  2009 Florian F Freeman
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id:$
 * @link       http://gtk.php.net
 * @since      1.0.0
 */

/**
 * Match argv against pattern
 *
 * This will try to find $search in $_SERVER['argv'], you may
 * specify several $search values as ($s1, $s2, $s3, ...)
 *
 * @param string $search the pattern to match
 */
function in_argv() {
    foreach(func_get_args() as $arg) {
        if(in_array($arg, $_SERVER['argv'])) {
            return true;
        }
    }
    return false;
}

/**
 * Recursively Remove Directory
 *
 * Will remove a directory and all its files recursively.
 *
 * @param string $current_dir The directory you want to remove.
 */
function rrmdir($current_dir) {
    if($dir = @opendir($current_dir)) {
        while(($f = readdir($dir)) !== false) {
            if($f > '0' && filetype($current_dir.'/'.$f) == 'file') {
                unlink($current_dir.'/'.$f);
            } elseif($f > '0' && filetype($current_dir.'/'.$f) == 'dir') {
                call_user_func(__FUNCTION__, $current_dir.'/'.$f);
            }
        }
        closedir($dir);
        return rmdir($current_dir);
    } else {
        return false;
    }
}

/**
 * Recursively Copy Directory
 *
 * Will copy a directory and all its files and subdirectories recursively.
 *
 * @param string $source The directory you want to copy.
 * @param string $dest The target directory.
 */
function rcopy($source, $dest) {
    if($dir = @opendir($source)) {
        @mkdir($dest);
        while(($f = readdir($dir)) !== false) {
            if($f != '.' && $f != '..') {
                if(is_dir($source.'/'.$f)) {
                    if(!call_user_func(__FUNCTION__,$source.'/'.$f, $dest.'/'.$f))
                    return false;
                } elseif(!copy($source.'/'.$f, $dest.'/'.$f)) {
                        return false;
                }
            }
        }
        return true;
    } else {
        return false;
    }
}

/**
 * Archive A Directory As TAR
 *
 * Will create a tarball with an image of a complete directory.
 *
 * @param string $path The directory you want to archive
 */
function tarballdir($path) {
    if(file_exists($path.'.tar')) {
        trigger_error("The file $path already exists.", E_USER_WARNING);
        return false;
    }
    if(!is_dir($path)) {
        trigger_error("The directory $path does not exist.", E_USER_WARNING);
        return false;
    }

    //Test for PEAR Archive_Tar
    @include_once('Archive/Tar.php');
    if(class_exists('Archive_Tar')) {
        //Using Archive_Tar
        return false;
    } else {
        $output = array();
        $return_code = 0;
        @exec('tar --help', $output, $return_code);
        if($return_code == 0) {
            $basedir = dirname($path);
            $old_cwd = getcwd();
            chdir($basedir);
            $source = basename($path);
            $dest = $source.'.tar';
            $command = "tar -cvf \"$dest\" \"$source\"";
            $return_code = shell_exec($command);
            chdir($old_cwd);
            if($return_code == 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}
?>