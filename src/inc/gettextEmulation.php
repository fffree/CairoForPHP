<?php
/**
 * Conditional Emulation of Gettext Functions
 *
 * This file will replace all gettext function that do not exist with dummy
 * functions so the program can still be used if gettext is not installed.
 * It does however not really emulate gettext, it just bounces back the
 * input.
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
 * @subpackage WebInterface
 * @author     Florian Breit <mail@florian.me.uk>
 * @copyright  2009-2014 Florian Breit
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link       http://pecl.php.net/cairo
 * @since      1.0.0
 */

if(!function_exists('bind_textdomain_codeset')) {
    /**
     * @ignore
     */
    function bind_textdomain_codeset($domain, $codeset) {
        return $codeset;
    }
}

if(!function_exists('bindtextdomain')) {
    /**
     * @ignore
     */
    function bind_textdomain_codeset($domain, $directory) {
        return $directory;
    }
}

if(!function_exists('dcgettext')) {
    /**
     * @ignore
     */
    function dcgettext($domain, $message, $category) {
        return $message;
    }
}

if(!function_exists('dcngettext')) {
    /**
     * @ignore
     */
    function dcngettext($domain, $msgid1, $msgid2, $n, $category) {
        if($n == 1) {
            return $msgid1;
        } else {
            return $msgid2;
        }
    }
}

if(!function_exists('dgettext')) {
    /**
     * @ignore
     */
    function gettext($domain, $message) {
        return $message;
    }
}

if(!function_exists('dngettext')) {
    /**
     * @ignore
     */
    function dngettext($domain, $msgid1, $msgid2, $n) {
        if($n == 1) {
            return $msgid1;
        } else {
            return $msgid2;
        }
    }
}

if(!function_exists('ngettext')) {
    /**
     * @ignore
     */
    function ngettext($msgid1, $msgid2, $n) {
        if($n == 1) {
            return $msgid1;
        } else {
            return $msgid2;
        }
    }
}

if(!function_exists('gettext')) {
    /**
     * @ignore
     */
    function gettext($message) {
        return $message;
    }
}

if(!function_exists('_')) {
    /**
     * @ignore
     */
    function _($message) {
        return $message;
    }
}

if(!function_exists('textdomain')) {
    /**
     * @ignore
     */
    function textdomain($text_domain) {
        return $text_domain;
    }
}
?>