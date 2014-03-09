<?php
/**
 * Utility for Mergin Gettext Pot and Po Files
 *
 * This script will merge the extracted pot file in the i18n directory and then
 * msgmrg it with all present .po files.
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
    print "Usage: i18nMerge[.php] [--help]\n\n";
    print "General:\n\n";
    print "  --help       Show this help.\n\n";
    goto EOF;
}

//Print a message to acknowledge deployment..
print "Beginning merging procedure...\n\n";

//Extract strings..
print "Merging gettext strings...\n";
$command  = "msgmerge -U %s cfp.pot --silent";
$olddir = getcwd();
chdir($prj_dir.'/i18n');
print "  Using command:\n    <$command>\n";

foreach(glob('./*.po') as $file) {
    print "  Merging File: $file\n";
    $cmd = sprintf($command, basename($file));
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
print "\nEnd of merging procedure.\n";

EOF:
?>