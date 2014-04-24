<?php
/**
 * The application's main file
 *
 * This is the file being run in order to execute the program, it should do all
 * the setup of the configuration files, adjust the error reporting methods and
 * then make all the includes necessary and finally execute the application.
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
 * @author     Florian Breit <mail@florian.me.uk>
 * @copyright  2009-2014 Florian Breit
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link       http://pecl.php.net/cairo
 * @see        CfpMainWindow
 * @since      1.0.0
 */

//
// Define Constants
//

/**
 * Indicate whether the current deploymend is a Phar or not
 */
if(!defined('CFP_IS_PHAR')) {
    define('CFP_IS_PHAR', (bool) false);
}
/**
 * Interface type for dektop interface
 */
define('CFP_INTERFACE_GTK', 0);
/**
 * Interface type for web interface
 */
define('CFP_INTERFACE_WEB', 1);
/**
 * Indicate whether to run as a desktop or web application
 */
if(PHP_SAPI == 'cli') {
    define('CFP_INTERFACE', CFP_INTERFACE_GTK);
} else {
    define('CFP_INTERFACE', CFP_INTERFACE_WEB);
}
/**
 * Currently used version of the application
 */
define('CFP_VERSION',  (string) '1.0.3');
/**
 * Installation path of the application
 */
define('CFP_APP_PATH', (string) dirname(__FILE__));
/**
 * Inclusion path for other PHP files
 */
define('CFP_INC_PATH', (string) CFP_APP_PATH.'/inc');

//
// Globally Relevant Strings
//

$CFP_STRINGS = array(
    'APP_TITLE'   => 'Cairo For PHP Samples',
    'APP_DESC'    =>
        "An application demonstrating the use of the Cairo Graphics Engine "
        . "in combination with PHP.\n"
        . "Inspired by Øyvind Kolås' Cairo samples.",
    'APP_COPY'    => 'Copyright (C) 2009-2014 Florian Breit',
    'APP_WEBSITE' => 'http://pecl.php.net/cairo'
);

$GLOBALS['CFP_STRINGS'] = &$CFP_STRINGS;

//
// Check Version Compatibility
//

//Check for the right php version, 5.3 is necessary because of closures
if(!version_compare(PHP_VERSION, '5.3.0a', '>')) {
    print $CFP_STRINGS['APP_TITLE'].' '.CFP_VERSION."\n\n";
    print "Error: This program requires PHP Version 5.3.x to run.\n";
    print "Version installed: ".phpversion()."\n";
    exit(1);
}
//Check that cairo is available
if(!extension_loaded('cairo')) {
    print $CFP_STRINGS['APP_TITLE'].' '.CFP_VERSION."\n\n";
    print "Error: This program requires the PHP extension for Cairo to run.\n";
    print "For more information check <http://pecl.php.net/cairo>.\n";
    exit(1);
}
//Check that PHP-GTK2 is available
if(CFP_INTERFACE == CFP_INTERFACE_GTK && !extension_loaded('php-gtk')) {
    print $CFP_STRINGS['APP_TITLE'].' '.CFP_VERSION."\n\n";
    print "Error: This program requires the PHP-GTK extension to run in cli mode.\n";
    print "You can however run this application without PHP-GTK in web mode; for\n";
    print "this you will need to run this application in a web server environment.\n";
    print "For more information check <http://gtk.php.net/>.\n";
    exit(1);
}

//
// Enforce Exception Error Reporting
//

//Throw ErrorExceptions instead of classical error reporting
set_error_handler(
    /**
     * @ignore
     */
    function($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    },
    E_STRICT | E_USER_ERROR | E_RECOVERABLE_ERROR
);

//
// Inclusion of Gerneral Resources
//

/**
 * Include the class used management of sample index
 */
require_once(CFP_INC_PATH.'/CfpSampleIndex.php');
/**
 * Include the class used for mangaging the preferences
 */
require_once(CFP_INC_PATH.'/CfpPreferences.php');
/**
 * Include all custom exceptions used by the application
 */
require_once(CFP_INC_PATH.'/CfpExceptions.php');
/**
 * Include coditional emulation of gettext functionality
 */
//require_once(CFP_INC_PATH.'/gettextEmulation.php');

//
// Load Configuration
//

/**
 * Configuration object for the application's preferences
 *
 * @global CfpPreferences $GLOBALS['CFP_CONF']
 * @name $CFP_CONF
 */
$CFP_CONF = new CfpPreferences(CFP_APP_PATH, CFP_INC_PATH);
if($CFP_CONF->detectIniPath()) {
    $CFP_CONF->loadIniFile();
}
$GLOBALS['CFP_CONF'] = $CFP_CONF;

//
// Internationalisation Setup
//

//$locale = 'cy';
//setlocale(LC_ALL, $locale);
//putenv('LANG='.$locale);
//putenv('LANGUAGE='.$locale);
bindtextdomain('cfp', $CFP_CONF->res_path.'/locale');
bind_textdomain_codeset('cfp', 'UTF-8');
textdomain('cfp');

//
// Initialise Interface and Run Application
//

if(CFP_INTERFACE == CFP_INTERFACE_GTK) {

    //
    // Interface Specific Includes
    //

    /**
     * Include the class used for displaying the main window
     */
    require_once(CFP_INC_PATH.'/CfpMainWindow.php');
    /**
     * Include the class used for the preferences dialog
     */
    require_once(CFP_INC_PATH.'/CfpPreferencesDialog.php');
    /**
     * Include the class for graphical error reporting
     */
    require_once(CFP_INC_PATH.'/CfpExceptionDialog.php');

    //
    // Graphical Error Reporting
    //

    CfpExceptionDialog::register();

    //
    // Application Execution
    //

    ini_set('php-gtk.codepage', 'UTF-8');

    $wnd = new CfpMainWindow();

    $wnd->connect_simple('destroy', array('gtk', 'main_quit'));
    $wnd->show_all();
    $wnd->pushStatusMessage(_('Application loading complete...'));

    gtk::main();

    //
    // Save User Preferences
    //

    if($CFP_CONF->getIniPath() !== null) {
        $CFP_CONF->saveIniFile();
    }

} elseif(CFP_INTERFACE == CFP_INTERFACE_WEB) {

    //
    // Interface Specific Includes
    //

    /**
     * Include the web interface class
     */
    require_once(CFP_INC_PATH.'/CfpWebInterface.php');
    /**
     * Include the templating class
     */
    require_once(CFP_INC_PATH.'/CfpWebTemplates.php');

    //
    // Application Execution
    //

    $inf = new CfpWebInterface();
    $inf->processRequest();
    $inf->displayPage();

}
?>