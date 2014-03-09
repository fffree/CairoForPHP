<?php
/**
 * Dialog and functions for the application's preferences management
 *
 * This file contains the class used to edit the preferences in the CairoForPHP
 * application. It manages the dialog and shows all the approptiate settings
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

/**
 * Preferences management dialog for CairoForPHP
 *
 * This class shows a GtkDialog for the user editing of CFP's preferences and on
 * top of this provides the methods required to store such settings in an INI
 * style configuration file.
 *
 * @package  CairoForPHP
 * @see      CfpMainWindow
 * @since    1.0.0
 */
class CfpPreferencesDialog extends GtkDialog {
    /**
     * Reference to global preferences object
     *
     * @access protected
     * @var CfpPreferences
     */
    protected $conf;

    /**
     * Contains the GtkNotebook widget of the dialog
     *
     * @access public
     * @var GtkNotebook
     */
    public $notebook;

    /**
     * Contains the GtkTable used to outline the display settings
     *
     * @access public
     * @var GtkTable
     */
    public $d_table;

    /**
     * Contains the GtkWidgets of the display settings page
     *
     * In order to read the settings use CfpPreferencedDialog::saveIniString()
     * or see the respective Gtk* class documentations to find out how to read
     * out the values yourself.
     *
     * @link http://gtk.php.net PHP-GTK Website with documentation for Gtk widgets
     * @see CfpPreferencedDialog::saveIniString()
     * @access public
     * @var array An array of GtkWidget deruved objects
     */
    public $d_values = array();

    /**
     * Contains the GtkLabels for the display settings page
     *
     * @access public
     * @var array An array of GtkLabel objects
     */
    public $d_labels = array();

    /**
     * Contains the GtkTable used to outline the path settings
     *
     * @access public
     * @var GtkTable
     */
    public $p_table;

    /**
     * Contains the GtkWidgets of the path settings page
     *
     * In order to read the settings use CfpPreferencedDialog::saveIniString()
     * or see the respective Gtk* class documentations to find out how to read
     * out the values yourself.
     *
     * @link http://gtk.php.net PHP-GTK Website with documentation for Gtk widgets
     * @see CfpPreferencedDialog::saveIniString()
     * @access public
     * @var array An array of GtkWidget deruved objects
     */
    public $p_values = array();

    /**
     * Contains the GtkLabels for the path settings page
     *
     * @access public
     * @var array An array of GtkLabel objects
     */
    public $p_labels = array();


    /**
     * Class constructor
     *
     * Constructs all the necessary child widgets and adjusts the initial values
     * of the object upon initialization.
     *
     * @access public
     */
    public function __construct() {
        //Shortcut reference to CFP_CONF global
        if(is_a($GLOBALS['CFP_CONF'], 'CfpPreferences')) {
            $this->conf = $GLOBALS['CFP_CONF'];
        } else {
            $this->conf = new CfpPreferences();
        }

        parent::__construct();

        $this->set_title(_('Preferences'));
        $this->set_resizable(false);

        $this->set_icon(
            $this->render_icon(
                Gtk::STOCK_PREFERENCES,
                Gtk::ICON_SIZE_DIALOG
            )
        );

        $this->notebook = new GtkNotebook();
        $this->notebook->set_border_width(3);
        $this->vbox->add($this->notebook);

        $this->buildDisplayPage();
        $this->buildPathsPage();

        $this->add_button(_('Save'), Gtk::RESPONSE_YES);
        $this->add_button(_('Cancel'), Gtk::RESPONSE_CANCEL);
    }

    /**
     * Builds the display page of the dialog
     *
     * This method is responsible for building up the display settings page of
     * the dialog and attaching it to the GtkNotebook. It constructs all the
     * widgets necessary and sets them to the values of the currtly applicable
     * configuration.
     *
     * @access protected
     */
    protected function buildDisplayPage() {
        $this->d_table = new GtkTable();
        $this->d_table->set_homogeneous(false);

        $label = new GtkHBox();
        $label->pack_start(
            GtkImage::new_from_stock(
                Gtk::STOCK_SELECT_FONT,
                Gtk::ICON_SIZE_MENU
            )
        );
        $label->pack_start(new GtkLabel(_('Display')));
        $label->show_all();

        $this->notebook->append_page($this->d_table, $label);

        $this->d_labels['source_font'] = new GtkLabel('Font for displaying source code:');
        $lalign = new GtkAlignment(1, 0.5, 0, 0);
        $lalign->add($this->d_labels['source_font']);
        $this->d_labels['source_font']->set_justify(Gtk::JUSTIFY_RIGHT);
        $this->d_values['source_font'] = new GtkFontButton();
        $this->d_values['source_font']->set_font_name($this->conf->source_font);
        $this->d_values['source_font']->set_use_font(true);
        $this->d_values['source_font']->set_show_style(false);
        $this->d_table->attach(
            $lalign,
            0, 1, 0, 1,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->d_table->attach(
            $this->d_values['source_font'],
            1, 3, 0, 1,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );

        $this->d_labels['tab_pos'] = new GtkLabel(_('Tab position:'));
        $lalign = new GtkAlignment(1, 0.5, 0, 0);
        $lalign->add($this->d_labels['tab_pos']);
        $this->d_labels['tab_pos']->set_justify(Gtk::JUSTIFY_RIGHT);
        $this->d_values['tab_pos'] = GtkComboBox::new_text();
        $this->d_values['tab_pos']->append_text(_('Left'));
        $this->d_values['tab_pos']->append_text(_('Right'));
        $this->d_values['tab_pos']->append_text(_('Top'));
        $this->d_values['tab_pos']->append_text(_('Bottom'));
        $this->d_values['tab_pos']->set_active($this->conf->tab_pos);
        $this->d_table->attach(
            $lalign,
            0, 1, 1, 2,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->d_table->attach(
            $this->d_values['tab_pos'],
            1, 3, 1, 2,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );

        $this->d_labels['show_toolbar'] = new GtkLabel(_('Show toolbar:'));
        $lalign = new GtkAlignment(1, 0.5, 0, 0);
        $lalign->add($this->d_labels['show_toolbar']);
        $this->d_labels['show_toolbar']->set_justify(Gtk::JUSTIFY_RIGHT);
        $this->d_values['show_toolbar']['yes'] = new GtkRadioButton(null, _('Yes'));
        $this->d_values['show_toolbar']['no'] = new GtkRadioButton(
            $this->d_values['show_toolbar']['yes'],
            _('No')
        );
        if($this->conf->show_toolbar) {
            $this->d_values['show_toolbar']['yes']->set_active(true);
        } else {
            $this->d_values['show_toolbar']['no']->set_active(true);
        }
        $this->d_table->attach(
            $lalign,
            0, 1, 2, 3,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->d_table->attach(
            $this->d_values['show_toolbar']['yes'],
            1, 2, 2, 3,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->d_table->attach(
            $this->d_values['show_toolbar']['no'],
            2, 3, 2, 3,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );

        $this->d_labels['toolbar_style'] = new GtkLabel(_('Toolbar style:'));
        $lalign = new GtkAlignment(1, 0.5, 0, 0);
        $lalign->add($this->d_labels['toolbar_style']);
        $this->d_labels['toolbar_style']->set_justify(Gtk::JUSTIFY_RIGHT);
        $this->d_values['toolbar_style'] = GtkComboBox::new_text();
        $this->d_values['toolbar_style']->append_text(_('Icon only'));
        $this->d_values['toolbar_style']->append_text(_('Text only'));
        $this->d_values['toolbar_style']->append_text(_('Icon above text'));
        $this->d_values['toolbar_style']->append_text(_('Icon left of text'));
        $this->d_values['toolbar_style']->set_active($this->conf->toolbar_style);
        $this->d_table->attach(
            $lalign,
            0, 1, 3, 4,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->d_table->attach(
            $this->d_values['toolbar_style'],
            1, 3, 3, 4,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );

        $this->d_labels['show_statbar'] = new GtkLabel('Show status bar:');
        $lalign = new GtkAlignment(1, 0.5, 0, 0);
        $lalign->add($this->d_labels['show_statbar']);
        $this->d_labels['show_statbar']->set_justify(Gtk::JUSTIFY_RIGHT);
        $this->d_values['show_statbar']['yes'] = new GtkRadioButton(null, _('Yes'));
        $this->d_values['show_statbar']['no'] = new GtkRadioButton(
            $this->d_values['show_statbar']['yes'],
            _('No')
        );
        if($this->conf->show_statbar) {
            $this->d_values['show_statbar']['yes']->set_active(true);
        } else {
            $this->d_values['show_statbar']['no']->set_active(true);
        }
        $this->d_table->attach(
            $lalign,
            0, 1, 4, 5,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->d_table->attach(
            $this->d_values['show_statbar']['yes'],
            1, 2, 4, 5,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->d_table->attach(
            $this->d_values['show_statbar']['no'],
            2, 3, 4, 5,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
    }

    /**
     * Builds the paths page of the dialog
     *
     * This method is responsible for building up the path settings page of
     * the dialog and attaching it to the GtkNotebook. It constructs all the
     * widgets necessary and sets them to the values of the currtly applicable
     * configuration.
     *
     * @access protected
     */
    protected function buildPathsPage() {
        $this->p_table = new GtkTable();
        $this->p_table->set_homogeneous(false);

        $label = new GtkHBox();
        $label->pack_start(
            GtkImage::new_from_stock(
                Gtk::STOCK_OPEN,
                Gtk::ICON_SIZE_MENU
          )
        );
        $label->pack_start(new GtkLabel(_('Paths')));
        $label->show_all();

        $this->notebook->append_page($this->p_table, $label);

        $this->p_labels['caution'] = new GtkLabel(
            _(
                '<b>Caution:</b> It is recommended that you do not modify the '
                . 'settings on this page unless you know how this will affect '
                . 'the working of this application. Should you encounter any '
                . 'problems you can always manually  edit the file '
                . '<tt>cfp-conf.ini</tt> in the application\'s main directory.'
            )
        );
        $this->p_labels['caution']->set_use_markup(true);
        $this->p_labels['caution']->set_line_wrap(true);
        $this->p_labels['caution']->connect(
          'size-allocate',
          function($label, $alloc) {
            static $invoked=false;
            if( !$invoked )
              $label->set_size_request($alloc->width, -1);
            $invoked = true;
          }
        );
        $lalign = new GtkAlignment(0.5, 0.5, 1, 1);
        $lalign->add($this->p_labels['caution']);
        $this->p_table->attacH(
          $lalign,
          0, 2, 0, 1,
          Gtk::FILL|Gtk::EXPAND,
          Gtk::SHRINK
        );

        $this->p_labels['res_path'] = new GtkLabel(_('Application resources:'));
        $lalign = new GtkAlignment(1, 0.5, 0, 0);
        $lalign->add($this->p_labels['res_path']);
        $this->p_values['res_path'] = new GtkFileChooserButton(
            _('Select path to application resources'),
            Gtk::FILE_CHOOSER_ACTION_SELECT_FOLDER
        );
        if(defined('CFP_IS_PHAR') && !CFP_IS_PHAR) {
            $this->p_values['res_path']->set_current_folder($this->conf->res_path);
        }
        $this->p_table->attach(
            $lalign,
            0, 1, 1, 2,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->p_table->attach(
            $this->p_values['res_path'],
            1, 2, 1, 2,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );

        $this->p_labels['sample_index'] = new GtkLabel(_('Sample index:'));
        $lalign = new GtkAlignment(1, 0.5, 0, 0);
        $lalign->add($this->p_labels['sample_index']);
        $this->p_values['sample_index'] = new GtkFileChooserButton(
            _('Select path to sample index'),
            Gtk::FILE_CHOOSER_ACTION_OPEN
        );
        if(defined('CFP_IS_PHAR') && !CFP_IS_PHAR) {
            $this->p_values['sample_index']->set_filename($this->conf->sample_index);
        }
        $filter = new GtkFileFilter();
        $filter->set_name(_('XML Document (*.xml)'));
        $filter->add_mime_type('text/xml');
        $filter->add_mime_type('application/xml');
        $this->p_values['sample_index']->add_filter($filter);
        $filter = new GtkFileFilter();
        $filter->set_name(_('All Files (*.*)'));
        $filter->add_pattern('*');
        $this->p_values['sample_index']->add_filter($filter);
        $this->p_table->attach(
            $lalign,
            0, 1, 2, 3,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );
        $this->p_table->attach(
            $this->p_values['sample_index'],
            1, 2, 2, 3,
            Gtk::FILL|Gtk::EXPAND,
            Gtk::SHRINK
        );

        if(defined('CFP_IS_PHAR') &&  CFP_IS_PHAR) {
            $this->p_table->set_sensitive(false);
        }
    }

    /**
     * Display the dialog and run it modal to it's parent
     *
     * This will display the dialog, make it modal and wait for user input, then
     * return the user response to allow for appropriate action.
     *
     * @access public
     * @return GtkResponseType Either Gtk::RESPONSE_CANCEL or Gtk::RESPONSE_YES
     */
    public function run() {
        $this->show_all();
        return parent::run();
    }

    /**
     * Fetch the current values of the preferences dialog
     *
     * This method returns an array similar to that returned by the internal
     * php function parse_ini_string() representing the values of the
     * preferences dialog.
     *
     * @access public
     * @return array A two dimensional array representing the current preferences
     */
    public function fetchArray() {
        $values = array();

        $values['source_font'] = trim($this->d_values['source_font']->get_font_name());
        if( empty($values['source_font']) ) {
            $values['source_font'] = "Monospace 10";
        }
        $values['tab_pos'] = $this->d_values['tab_pos']->get_active();
        $values['show_toolbar'] = $this->d_values['show_toolbar']['yes']->get_active() ? true : false;
        $values['toolbar_style'] = $this->d_values['toolbar_style']->get_active();
        $values['show_statbar'] = $this->d_values['show_statbar']['yes']->get_active() ? true : false;

        if(defined('CFP_IS_PHAR') && !CFP_IS_PHAR) {
            $values['res_path'] = $this->p_values['res_path']->get_current_folder();
            $values['sample_index'] = $this->p_values['sample_index']->get_filename();
        } else {
            $values['res_path'] = "./res";
            $values['sample_index'] = "./res/sampleIndex.xml";
        }

        return $values;
    }
}
?>