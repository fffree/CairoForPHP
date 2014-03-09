::::
:: Batch Launcher for i18nMerge.php
::
:: This script will launch the i18nMerge.php script with the php cli and pass
:: on any arguments, so the utils can be run directly from the command line.
::
:: PHP version 5.3.x
::
:: LICENSE: This source file is subject to version 3.01 of the PHP license
:: that is available through the world-wide-web at the following URI:
:: http://www.php.net/license/3_01.txt.  If you did not receive a copy of
:: the PHP License and are unable to obtain it through the web, please
:: send a note to license@php.net so we can mail you a copy immediately.
::
:: @package    CairoForPHP
:: @subpackage Utilities
:: @author     Florian F Freeman <florian@phpws.org>
:: @copyright  2009 Florian F Freeman
:: @license    http://www.php.net/license/3_01.txt  PHP License 3.01
:: @version    CVS: $Id:$
:: @link       http://gtk.php.net
:: @since      1.0.0
::
@echo off
::Change working directory to ./utils
set origin_dir=%CD%
cd %0\..\

::Get php path
call setPhpPath.bat

::Run script
call %phpPath% i18nMerge.php %*

::Reset original working directory
cd %origin_dir%