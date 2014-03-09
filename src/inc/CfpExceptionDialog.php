<?php
/**
 * Dialog and functions for the application's preferences management
 *
 * This file contains the class used to edit the preferences in the Cairo For
 * PHP application. It manages the dialog and shows all the approptiate settings
 * and also provides the methods required to store the preferences in the INI
 * style configuration file.
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
 * @subpackage GtkInterface
 * @author     Florian F Freeman <florian@phpws.org>
 * @copyright  2009 Florian F Freeman
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id:$
 * @link       http://gtk.php.net
 * @since      1.0.0
 */

class CfpExceptionDialog {
    protected function __construct() {
    }

    public static function register() {
        return set_exception_handler(array(__CLASS__, 'handler'));
    }

    public static function handler(Exception $exception) {
        $dlg = new GtkMessageDialog(
            null,
            Gtk::DIALOG_MODAL,
            Gtk::MESSAGE_ERROR,
            Gtk::BUTTONS_OK
        );
        $dlg->set_title(_('Fatal Error: Unhandled Exception!'));

        $dlg->set_icon(
            $dlg->render_icon(
                Gtk::STOCK_DIALOG_ERROR,
                Gtk::ICON_SIZE_DIALOG
            )
        );

        $message  = '<b>'._('Fatal Error: Unhandled Exception!')."</b>\n\n";
        $message .= _('An unhandled error has occured whilst operating this '
                 . 'application and the application will now be terminated.');
        $message .= "\n\n<b>"._('Details:')."</b>\n\n<small><tt>";

        if( $exception->getMessage() != "" ) {
            $message .= $exception->getMessage()."\n\n";
        }
        $message .= _('Type:').' '.get_class($exception)."\n"
                 .  _('Code:').' '.$exception->getCode()."\n"
                 .  _('File:').' '.$exception->getFile()."\n"
                 .  _('Line:').' '.$exception->getLine()."\n"
                 .  _('Version:').' '.CFP_VERSION."\n\n"
                 .  _('Stack Trace:')."\n"
                 .  $exception->getTraceAsString()
                 .  "</tt></small>";
        $dlg->set_markup($message);

        $dlg->run();
        print $exception;
        exit(1);
    }
}
?>
