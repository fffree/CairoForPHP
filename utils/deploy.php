<?php
/**
 * Utility for Deployment of CairoForPHP
 *
 * This script will allow for the deployment of the source tree in different
 * setups from plain copies to phar archives and compression of deployment
 * packages etc.
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

//
// Setup
//

//Auxiliary functions
require_once(dirname(__FILE__).'/utilFuncs.php');

//Find directories..
$prj_dir = dirname(dirname(__FILE__));
$src_dir = $prj_dir.'/src';
$dply_dir = $prj_dir.'/deploy';

//
// Help
//

if(in_argv('--help') || empty($_SERVER['argv'])) {
    print "Usage: deploy[.php] --help|<mode> [<compression>] [<option>]\n\n";
    print "General:\n\n";
    print "  --help       Show this help.\n\n";
    print "Modes:\n";
    print "               This specifies the mode in which the deployment script\n";
    print "               is operating. You can only choose one mode at a time,\n";
    print "               duplicate specifications will be ignored.\n\n";
    print "  --clean      Just clean up the deployment directory.\n";
    print "  --plain      Make a plain copy of source tree, ready to use.\n";
    print "  --phar       Create a single phar archive from source tree.\n";
    print "  --bcomp      Use bcompiler to precompile all php files,\n";
    print "               otherwise behaving like --plain\n\n";
    print "Compression:\n";
    print "               All compression methods are switched off by default.\n";
    print "               Multiple compression methods may be specified.\n";
    print "               Compression of all but the --phar packages is dependant.\n";
    print "               on either tar or PEAR/Archive_Tar being installed\n\n";
    print "  gz=(0|1)     Switch Zlib compression of resulting package on/off.\n";
    print "  bz=(0|1)     Switch BZip compression of resulting package on/off.\n";
    print "  tar=(0|1)    Pack --plain and --bcomp packages into a tar archive. This\n";
    print "               is automatically turned on along with the gz=1 and bz=1\n";
    print "               options.\n\n";
    print "Options:\n";
    print "               These are additional options that affect the behaviour\n";
    print "               of this script. Multiple options may be specified.\n\n";
    print "  clean=(0|1)  Whether or not to clean the deployment directory before\n";
    print "               actually deploying anything. Default is on.\n";
    print "  recfg=(0|1)  Whether or not to restore the defaultConf.ini as\n";
    print "               cfp-conf.ini. Default is on.\n";
    goto EOF;
}

//
// Begin Of Procedures
//

//Print a message to acknowledge deployment..
print "Beginning deploymend procedure...\n\n";

//Clean deploy directory..
print "Cleaning deployment directory...\n";
if(!in_argv('clean=0')) {
    rrmdir($dply_dir);
    mkdir($dply_dir);
    print "  Done.\n";
} else {
    print "  Skipped.\n";
}

//Reset cfp-conf.ini
print "Restoring defaultConf.ini to cfp-conf.ini...\n";
if(!in_argv('recfg=0')) {
    if(file_exists($src_dir.'/cfp-conf.ini')) {
        unlink($src_dir.'/cfp-conf.ini');
    }
    copy($src_dir.'/res/defaultConf.ini', $src_dir.'/cfp-conf.ini');
    print "  Done.\n";
} else {
    print "  Skipped.\n";
}

//
// Phar Archive
//

if(in_argv('--phar')) {
    print "Building Phar archive...\n";
    mkdir($dply_dir.'/phar');
    $phar = new Phar($dply_dir.'/phar/cfp.phar');
    $phar->buildFromDirectory($src_dir);
    $phar_stub = '<?php
    if( !class_exists("Phar") ) {
      print "This program requires PHP to have Phar support enabled.\n";
      print "Class ``Phar\'\' could not be found.\n";
      exit(1);
    }
    Phar::mapPhar();
    Phar::interceptFileFuncs();
    define("CFP_IS_PHAR", true);

    include("phar://".__FILE__."/main.php");
    __HALT_COMPILER();';
    $phar->setStub($phar_stub);

    if(in_argv('gz=1')) {
        print "  Compressing Phar archive with zlib (gz)...\n";
        if( $phar->canCompress(Phar::GZ) ) {
          $phar->compress(Phar::GZ);
          //Fix the Bootstrap!
          $comp_phar = new Phar($dply_dir.'/phar/cfp.phar.gz');
          $comp_phar->setStub($phar_stub);
          unset($comp_phar);
          print "    Done.\n";
        } else {
          print "    Failed: zlib compression not available.\n";
        }
    }
    if(in_argv('bz=1')) {
        print "  Compressing Phar archive with bzip2 (bz2)...\n";
        if( $phar->canCompress(Phar::BZ2) ) {
          $phar->compress(Phar::BZ2);
          //Fix the Bootstrap!
          $comp_phar = new Phar($dply_dir.'/phar/cfp.phar.bz2');
          $comp_phar->setStub($phar_stub);
          unset($comp_phar);
          print "    Done.\n";
        } else {
          print "  ! Failed: bzip2 compression not available.\n";
        }
    }

    print "  Done.\n";
    unset($phar);
}

//
// Plain Copy
//

if(in_argv('--plain')) {
    print "Building plain copy...\n";
    mkdir($dply_dir.'/plain');
    if( rcopy($src_dir, $dply_dir.'/plain/cfp') ) {
        if(in_argv('tar=1', 'gz=1', 'bz=1')) {
            print "  Creating tarball...\n";
            if($tarball = tarballdir($dply_dir.'/plain/cfp')) {
                print "    Done.\n";
            } else {
                print "  ! Failed.\n";
            }
            if(in_argv('gz=1')) {
                print "  Compressing tar archive with zlib (gz)...\n";
                if(!$tarball || !function_exists('gzopen')) {
                    print "  ! Failed.\n";
                } else {
                    $fh = gzopen($dply_dir.'/plain/cfp.tar.gz', 'w');
                    if($fh) {
                        gzwrite($fh, file_get_contents($dply_dir.'/plain/cfp.tar'));
                        gzclose($fh);
                        print "    Done.\n";
                    } else {
                        print "    Failed: Failed to open file cfp.tar.gz for writing.\n";
                    }
                }
            }
            if(in_argv('bz=1')) {
                print "  Compressing tar archive with bzip2 (bz)...\n";
                if(!$tarball || !function_exists('bzopen')) {
                    print "  ! Failed.\n";
                } else {
                    $fh = bzopen($dply_dir.'/plain/cfp.tar.bz', 'w');
                    if($fh) {
                        bzwrite($fh, file_get_contents($dply_dir.'/plain/cfp.tar'));
                        bzclose($fh);
                        print "    Done.\n";
                    } else {
                        print "    Failed: Failed to open file cfp.tar.bz for writing.\n";
                    }
                }
            }
        }
        print "  Done.\n";
    } else {
        print "! Failed.\n";
    }
}

//
// Bcompiled Copy
//

if(in_argv('--bcomp')) {
    print "Building bytecode compiled copy...\n";
    print "! Failed: method not implemented.\n";
}

//
// End Of Procedures
//

//Let user know we have finished...
print "\nEnd of deployment procedure.\n";

EOF:
?>