<?php
/**
 * Utility for Extraction of Gettext Strings
 *
 * This script will extract all gettext strings from all *.php files in ./src
 * and ./src/inc directory and create the initial pot file.
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

//Show help
if($_SERVER['argv'][1] == '--help') {
    print "Usage: i18nExtract[.php] [--help]\n\n";
    print "General:\n\n";
    print "  --help       Show this help.\n\n";
    goto EOF;
}

//Print a message to acknowledge deployment..
print "Beginning extraction procedure...\n\n";

//Create pot file...
print "Creating pot file...\n";

//Create File
print "  Creating file ./i18n/cfp.pot\n";
$fh = fopen($prj_dir.'/i18n/cfp.pot', 'w+');
if($fh) {
    print "    Done.\n";
} else {
    print "  ! Failed.\n";
    goto EOC;
}

//Write header
print "  Writing header...\n";
$Id = '$Id';
$version = '1.0.0';
$date = @date('Y-m-d H:i+e');
$header = <<<HEADER
##
# Default pot file for CairoForPHP
#
# This is the automatically generated default pot file for CairoForPHP, this
# file should be used as the basis for msgmrg and translating, it may, plainly,
# be used as the "en" file as well.
#
# When translating this file please remember to change the heading of this
# description to "LOCALE pot file for CairoForPHP", LOCALE being the locale you
# are translating to. Also add your name in a new @author line below and change
# the meta Last-Translator and PO-Revision-Date to reflect your work (as far as
# the program you are using will not do this for you).
#
# PHP version 5.3.x
#
# LICENSE: This source file is subject to version 3.01 of the PHP license
# that is available through the world-wide-web at the following URI:
# http://www.php.net/license/3_01.txt.  If you did not receive a copy of
# the PHP License and are unable to obtain it through the web, please
# send a note to license@php.net so we can mail you a copy immediately.
#
# @package    CairoForPHP
# @author     Florian F Freeman <florian@phpws.org>
# @copyright  2009 Florian F Freeman
# @license    http://www.php.net/license/3_01.txt  PHP License 3.01
# @version    CVS: $Id:$
# @link       http://gtk.php.net
# @since      $version
#

msgid ""
msgstr ""
"Project-Id-Version: CairoForPHP $version\\n"
"Report-Msgid-Bugs-To: \\n"
"POT-Creation-Date: $date\\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"X-Poedit-Basepath: ../\\n"

HEADER;
fwrite($fh, $header);
fclose($fh);
print "    Done.\n";
print "  Done.\n";

//Extract strings..
print "Extracting gettext strings...\n";
$xgettext = 'xgettext';
$keyword  = '--omit-header';
$domain   = '-d cfp';
$encoding = '--from-code=UTF-8';
$language = '-L PHP';
$output   = '-o "./i18n/cfp.pot" -j';
$input    = '"./src/%s"';
$command  = "$xgettext $keyword $domain $encoding $language $output $input";
$olddir = getcwd();
chdir($prj_dir);
print "  Using command:\n    <$command>\n";

foreach(glob('./src/*.php') as $file) {
    print "  Extracting file: $file\n";
    $cmd = sprintf($command, basename($file));
    system($cmd, $return);
    if($return == 0) {
        print "    Done.\n";
    } else {
        print "  ! Failed: $return.\n";
    }
}
foreach(glob('./src/inc/*.php') as $file) {
    print "  Extracting file: $file\n";
    $cmd = sprintf($command, 'inc/'.basename($file));
    system($cmd, $return);
    if($return == 0) {
        print "    Done.\n";
    } else {
        print "  ! Failed: $return.\n";
    }
}
chdir($olddir);
print "  Done.\n";

EOC:
print "\nEnd of extraction procedure.\n";

EOF:
?>