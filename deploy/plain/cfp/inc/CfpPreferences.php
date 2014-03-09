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

class CfpPreferences {
    //Internals
    protected $ini_path   = null;
    protected $app_path   = '.';
    protected $inc_path   = './inc';

    //Display preferences
    public $source_font   = 'Monospace 10';
    public $tab_pos       = 0;
    public $show_toolbar  = true;
    public $toolbar_style = 2;
    public $show_statbar  = true;

    //Paths
    public $res_path      = './res';
    public $sample_index  = './res/sampleIndex.xml';

    public function __construct($app_path=null, $inc_path=null, $ini_path=null) {
        //Fix paths defaults
        if($app_path !== null) {
            $this->app_path = $app_path;
            if($inc_path !== null) {
                $this->inc_path = $inc_path;
            } else {
                $this->inc_path = $app_path.'/inc';
            }
            $this->res_path = $app_path.'/res';
            $this->sample_index = $this->res_path.'/sampleIndex.xml';
            if($ini_path !== null) {
                $this->ini_path = $ini_path;
            }
        }
    }

    public function detectIniPath() {
        if(file_exists($this->app_path.'/cfp-conf.ini')) {
            $this->ini_path = $this->app_path.'/cfp-conf.ini';
            return true;
        } elseif(file_exists('./cfp-conf.ini')) {
            $this->ini_path = './cfp-conf.ini';
            return true;
        }
        return false;
    }

    public function setIniPath($path=null) {
        $old_path = $this->ini_path;
        $this->ini_path = $path;
        return $old_path;
    }

    public function setAppPath($path='.') {
        $old_path = $this->app_path;
        $this->app_path = $path;
        return $old_path;
    }

    public function setIncPath($path='./inc') {
        $old_path = $this->inc_path;
        $this->inc_path = $path;
        return $old_path;
    }

    public function getIniPath() {
        return $this->ini_path;
    }

    public function getAppPath() {
        return $this->app_path;
    }

    public function getIncPath() {
        return $this->inc_path;
    }

    public function loadIniFile() {
        if($this->ini_path === null) {
            throw new RuntimeException("Path to ini file is unspecified.");
        } elseif(!file_exists($this->ini_path)) {
            throw new CfpIoException("Failed to load file: \"".$this->ini_path."\"");
        }
        $this->loadIniString(file_get_contents($this->ini_path));
    }

    public function loadIniString($ini_string) {
        $ini_data = parse_ini_string($ini_string);
        $this->loadArray($ini_data);
    }

    public function loadArray(array $data) {
        if(isset($data['source_font']))
            $this->source_font = (string) $data['source_font'];
        if(isset($data['tab_pos']))
            $this->tab_pos = (int) $data['tab_pos'];
        if(isset($data['show_toolbar']))
            $this->show_toolbar = (bool) $data['show_toolbar'];
        if(isset($data['toolbar_style']))
            $this->toolbar_style = (int) $data['toolbar_style'];
        if(isset($data['show_statbar']))
            $this->show_statbar = (bool) $data['show_statbar'];

        if(isset($data['res_path']))
            $this->res_path = (string) $data['res_path'];
        if(isset($data['sample_index']))
            $this->sample_index = (string) $data['sample_index'];
    }

    /**
     * Save the current preferences as a ini style file
     *
     * This method writes a file in the ini configuration file format that
     * is representative of the current preferences in the dialog. The format
     * is compatible to php's internal function parse_ini_file().
     *
     * @see CfpPreferencesDialog::saveIniFile(), CfpPreferencesDialog::fetchValues()
     * @access public
     * @param string $filename Path to the file that should be used for writing
     */
    public function saveIniFile() {
        if($this->ini_path === null) {
            throw new RuntimeException("Path to ini file is unspecified.");
        } elseif(!$fh = @fopen($this->ini_path, 'w+')) {
            throw new CfpIoException("Failed to open file: \"".$this->ini_path."\"");
        }
        fputs($fh, $this->saveIniString());
        fclose($fh);
    }

    /**
     * Save the current preferences as a ini style string
     *
     * This method returns a string in the ini configuration file format that
     * can either be processed further or saved into a .ini file. The format
     * is compatible to php's internal function parse_ini_string().
     *
     * @see CfpPreferences::saveIniFile(), CfpPreferences::saveArray()
     * @access public
     * @return string ini formatted string representing the current preferences
     */
    public function saveIniString() {
        $ini = ";Configuration file for PHP-GTK Cairo Samples 1.0.0\n";
        $ini.= ";This file is automatically generated and should\n";
        $ini.= ";preferably be modified by using the application's\n";
        $ini.= ";preferences dialog.\n";
        $ini.= "\n";

        $ini.= "[display]\n";
        $ini.= "source_font = \"".addslashes((string)$this->source_font)."\"\n";
        $ini.= "tab_pos = ".(int)$this->tab_pos."\n";
        $ini.= "show_toolbar = ".(int)$this->show_toolbar."\n";
        $ini.= "toolbar_style = ".(int)$this->toolbar_style."\n";
        $ini.= "show_statbar = ".(int)$this->show_statbar."\n";
        $ini.= "\n";

        $ini.= "[paths]\n";
        $cond_com = (defined('CFP_IS_PHAR') && CFP_IS_PHAR) ? ';' : '';
        $ini.= $cond_com."res_path = \"".addslashes((string)$this->res_path)."\"\n";
        $ini.= $cond_com."sample_index = \"".addslashes((string)$this->sample_index)."\"\n";

        return $ini;
    }

    /**
     * Save the current values of the preferences object as an array
     *
     * This method returns an array similar to that returned by the internal
     * php function parse_ini_string() representing the values current
     * preferences.
     *
     * @see CfpPreferencesDialog::saveIniFile(), CfpPreferencesDialog::saveIniFile()
     * @access public
     * @return array An array representing the current preferences
     */
    public function saveArray() {
        $data = array();

        $data['source_font']   = (string) $this->source_font;
        $data['tab_pos']       = (int)    $this->tab_pos;
        $data['show_toolbar']  = (bool)   $this->show_toolbar;
        $data['toolbar_style'] = (int)    $this->toolbar_style;
        $data['show_statbar']  = (bool)   $this->show_statbar;

        $data['res_path']      = (string) $this->res_path;
        $data['sample_index']  = (string) $this->sample_index;

        return $data;
    }
}
?>