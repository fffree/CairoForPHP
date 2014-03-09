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
 * @author     Florian F Freeman <florian@phpws.org>
 * @copyright  2009 Florian F Freeman
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id:$
 * @link       http://gtk.php.net
 * @since      1.0.0
 */

class CfpIoException extends Exception {

}

class CfpXmlValidationException extends Exception {
    public function __construct($message=null, $code=0, $filename=null, $lineno=null) {
        parent::__construct($message, $code);
        if($filename !== null) {
            $this->file = $filename;
        }
        if($lineno !== null) {
            $this->line = $lineno;
        }
    }

    public static function newFromLibXMLError(LibXMLError $error) {
        return new self($error->message, $error->code, $error->file, $error->line);
    }
}

class CfpHttpException extends RuntimeException {
}
?>