<?php
/**
 * Utility for Formating PO Files
 *
 * This script will format the translated po files in the i18n directory and then
 * copy them into the right locale directories.
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
 * @author     Florian Breit <mail@florian.me.uk>
 * @copyright  2009-2014 Florian Breit
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link       http://pecl.php.net/cairo
 * @since      1.0.0
 */
//Auxiliary functions
require_once(dirname(__FILE__).'/utilFuncs.php');

//Find directories..
$prj_dir = dirname(dirname(__FILE__));
$src_dir = $prj_dir.'/src';

//Show help
if($_SERVER['argv'][1] == '--help') {
    print "Usage: i18nMerge[.php] [--help]\n\n";
    print "General:\n\n";
    print "  --help       Show this help.\n\n";
    goto EOF;
}

//Print a message to acknowledge deployment..
print "Beginning formating procedure...\n\n";

//Format files..
print "Formating gettext files...\n";
$command  = "msgfmt -f %s -o %s";
$olddir = getcwd();
chdir($prj_dir.'/i18n');
print "  Using command:\n    <$command>\n";

foreach(glob('./*.po') as $file) {
    print "  Formating File: $file\n";
    $cmd = sprintf($command, basename($file), substr($file, 0, -2).'mo');
    system($cmd, $return);
    if($return == 0) {
        print "    Done.\n";
    } else {
        print "  ! Failed: $return.\n";
    }
}
chdir($olddir);
print "  Done.\n";

//Move files..
print "Copying compiled files...\n";
chdir($prj_dir.'/i18n');

foreach(glob('./*.mo') as $file) {
    print "  Copying File: $file\n";
    $locale = substr($file, 2, -2);
    if(!is_dir($src_dir.'/res/locale/'.$locale)) {
        mkdir($src_dir.'/res/locale/'.$locale);
    }
    if(!is_dir($src_dir.'/res/locale/'.$locale.'/LC_MESSAGES')) {
        mkdir($src_dir.'/res/locale/'.$locale.'/LC_MESSAGES');
    }
    if(copy($file, $src_dir.'/res/locale/'.$locale.'/LC_MESSAGES/cfp.mo')) {
        print "    Done.\n";
    } else {
        print "  ! Failed.\n";
    }
}
chdir($olddir);
print "  Done.\n";

EOC:
print "\nEnd of formating procedure.\n";

EOF:
?>