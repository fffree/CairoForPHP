::::
:: PHP Batch Path Setter
::
:: This script will set the environment variable phpPath to the path of the
:: php cli under windows, it is called for this by other batch files in the
:: ./utils directory.
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
set phpPath="%ProgramFiles%\PHP\php.exe"