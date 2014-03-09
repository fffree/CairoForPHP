<?php
/**
 * Utility for Cleaning the Source Tree
 *
 * This script will remove all generated deployment data, cached images etc. and
 * also restore the defaultConf.ini to the cfp-conf.ini so the source tree is
 * clean for CVS or packacking otherwise.
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
//Auxiliary functions
require_once(dirname(__FILE__).'/utilFuncs.php');

//Find directories..
$prj_dir = dirname(dirname(__FILE__));
$src_dir = $prj_dir.'/src';
$dply_dir = $prj_dir.'/deploy';
$cache_dir = $prj_dir.'/res/cached';

//Show help
if($_SERVER['argv'][1] == '--help') {
    print "Usage: clean[.php] [--help]\n\n";
    print "General:\n\n";
    print "  --help       Show this help.\n";
    goto EOF;
}

//Print a message to acknowledge cleaning..
print "Beginning cleaning procedure...\n\n";

//Reset cfp-conf.ini
print "Restoring defaultConf.ini to cfp-conf.ini...\n";
if(file_exists($src_dir.'/cfp-conf.ini')) {
    unlink($src_dir.'/cfp-conf.ini');
}
copy($src_dir.'/res/defaultConf.ini', $src_dir.'/cfp-conf.ini');
print "  Done.\n";

//Clean deploy directory..
if(!array_search('clean=0', $_SERVER['argv'])) {
    print "Cleaning deployment directory...\n";
    rrmdir($dply_dir);
    mkdir($dply_dir);
    print "  Done.\n";
} else {
    print "  Skipped.\n";
}

//Clean cache directory..
if(!array_search('clean=0', $_SERVER['argv'])) {
    print "Cleaning cache directory...\n";
    rrmdir($src_dir.'/res/cached');
    mkdir($src_dir.'/res/cached');
    print "  Done.\n";
}

//Clean locale directory..
if(!array_search('clean=0', $_SERVER['argv'])) {
    print "Cleaning locale directory...\n";
    rrmdir($src_dir.'/res/locale');
    mkdir($src_dir.'/res/locale');
    print "  Done.\n";
} else {
    print "  Skipped.\n";
}

//Clean i18n directory..
if(!array_search('clean=0', $_SERVER['argv'])) {
    print "Cleaning i18n directory...\n";
    //Important: Only remove the *.mo and *.po~ files!
    foreach(glob($prj_dir.'/i18n/*.mo') as $file) {
        unlink($file);
    }
    foreach(glob($prj_dir.'/i18n/*.po~') as $file) {
        unlink($file);
    }
    print "  Done.\n";
} else {
    print "  Skipped.\n";
}

print "\nEnd of cleaning procedure.\n";

EOF:
?>