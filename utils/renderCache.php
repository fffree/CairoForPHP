<?php
/**
 * Utility for Rendering of Expected Images Cache
 *
 * This script will simulate the application being run in the cli and render all
 * samples from the sample index into the ./src/res/cached/ directory with an
 * added note "Expected Output" at the image's bottom.
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
if(isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == '--help') {
    print "Usage: renderCache[.php] [--help|<option>]\n\n";
    print "General:\n\n";
    print "  --help       Show this help.\n\n";
    print "Options:\n";
    print "               These are additional options that affect the behaviour\n";
    print "               of this script. Multiple options may be specified.\n\n";
    print "  clean=(0|1)  Whether or not to clean the deployment directory before\n";
    print "               actually deploying anything. Default is on.\n";
    goto EOF;
}

//Print a message to acknowledge deployment..
print "Beginning caching procedure...\n\n";

//Clean cache directory..
if(!array_search('clean=0', $_SERVER['argv'])) {
    print "Cleaning cache directory...\n";
    rrmdir($src_dir.'/res/cached');
    mkdir($src_dir.'/res/cached');
    print "  Done.\n";
}

//Make CfpMainWindow work...
require($src_dir.'/inc/CfpPreferences.php');
require($src_dir.'/inc/CfpExceptions.php');
require($src_dir.'/inc/CfpSampleIndex.php');
require($src_dir.'/inc/CfpMainWindow.php');

print "Extending CfpMainWindow with renderCache() method...\n";
/**
 * @ignore
 */
class CfpMainWindowRenderer extends CfpMainWindow {
    private function gtkMain() {
        while(Gtk::events_pending()) {
            gtk::main_iteration();
        }
    }
    public function renderCache() {
        $wnd = new GtkWindow();
        $canv = new GtkDrawingArea();
        $canv->set_size_request(256, 256);
        $wnd->add($canv);
        $wnd->show_all();
        $this->gtkMain();
        foreach($this->sampleIndex as $sname => $sdata) {
            $img_path = $this->conf->res_path.'/cached/'.$sdata['file'].'.dat';
            $data = call_user_func(
                function($widget, $filename) {
                    $widget->window->clear();
                    $context = $widget->window->cairo_create();

                    include(CFP_INC_PATH.'/'.$filename);

                    $pixbuf = new GdkPixbuf(
                        Gdk::COLORSPACE_RGB,
                        false,
                        8,
                        256, 256
                    );
                    $pixbuf->get_from_drawable(
                        $widget->window,
                        $widget->window->get_colormap(),
                        0,   0,
                        0,   0,
                        256, 256
                    );
                    $tmp_file = tempnam(sys_get_temp_dir(), 'cfp.');
                    $pixbuf->save($tmp_file, 'png');

                    $surface = CairoImageSurface::createFromPng($tmp_file);
                    $context = new CairoContext($surface);

                    $context->setFontSize(11);
                    $extents = $context->textExtents("(Expected Output)");
                    $context->setSourceRgba(1, 1, 1, 0.5);
                    $context->rectangle(
                        0,   246-$extents['height'],
                        256, $extents['height']+10
                    );
                    $context->fill();
                    $context->moveTo(
                        (256/2)-($extents['width']/2),
                        253-($extents['height']/2)
                    );
                    $context->setSourceRgba(0, 0, 0, 0.5);
                    $context->showText("(Expected Output)");
                    $surface->writeToPng($tmp_file);

                    $img_data = file_get_contents($tmp_file);
                    @unlink($tmp_file);
                    return $img_data;
                },
                $canv,
                $sdata['file']
            );
            $fh = fopen($img_path, 'w+');
            fputs($fh, $data);
            fclose($fh);
        }
        $wnd->destroy();
    }
}
print "  Done.\n";

print "Simulating application run...\n";
$CFP_CONF = new CfpPreferences();
$CFP_CONF->sample_index = $src_dir.'/res/sampleIndex.xml';
$CFP_CONF->res_path = $src_dir.'/res';
define('CFP_INC_PATH', $src_dir.'/inc');
$GLOBALS['CFP_STRINGS']['APP_TITLE'] = "Cairo For PHP Simulation";
$wnd = new CfpMainWindowRenderer();
$wnd->show_all();
$wnd->hide_all();
print "  Done.\n";

//Render cache
print "Rendering cache...\n";
$wnd->renderCache();
print "  Done.\n";

print "\nEnd of caching procedure.\n";

EOF:
?>