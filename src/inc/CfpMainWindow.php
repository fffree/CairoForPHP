<?php
/**
 * Main window for the gtk interface
 *
 * This file contains the GtkWindow extended class that is used as the driver
 * and main window for the gtk interface.
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
 * @author     Florian Breit <mail@florian.me.uk>
 * @copyright  2009-2014 Florian Breit
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link       http://pecl.php.net/cairo
 * @since      1.0.0
 */

class CfpMainWindow extends GtkWindow {
    protected $conf;

    public $statusbar;
    public $notebook;
    public $toolbarWidget;
    public $toolbarButtons;
    public $sampleIndex;
    public $sampleWidgets = array();
    public $accelGroup;

    public function __construct($title=null) {
        //Shortcut reference to CFP_CONF global
        if(is_a($GLOBALS['CFP_CONF'], 'CfpPreferences')) {
            $this->conf = $GLOBALS['CFP_CONF'];
        } else {
            $this->conf = new CfpPreferences();
        }

        //Set up window
        parent::__construct();
        if(empty($title)) {
            $title = $GLOBALS['CFP_STRINGS']['APP_TITLE'];
        }
        $this->set_title($title);

        //Set up AccelGroup
        $this->accelGroup = new GtkAccelGroup();
        $this->add_accel_group($this->accelGroup);
        /*
         * Add this once GtkAccelGroup is wholly wrapped,
         * remember to remove the accelerators from the
         * buildToolbar() function then!
        $this->accelGroup->connect(Gdk::KEY_A, Gdk::MOD1_MASK, 0, array($this, 'eShowAboutDialog'));
        $this->accelGroup->connect(Gdk::KEY_R, Gdk::MOD1_MASK, 0, array($this, 'eShowPreferences'));
        $this->accelGroup->connect(Gdk::KEY_N, Gdk::MOD1_MASK, 0, array($this, 'eSwitchTabNext'));
        $this->accelGroup->connect(Gdk::KEY_Page_Down, 0, 0, array($this, 'eSwitchTabNext'));
        $this->accelGroup->connect(Gdk::KEY_P, Gdk::MOD1_MASK, 0, array($this, 'eSwitchTabPrev'));
        $this->accelGroup->connect(Gdk::KEY_Page_Up, 0, 0, array($this, 'eSwitchTabPrev'));
        */

        //Create VBox
        $vbox = new GtkVBox();
        $this->add($vbox);

        //Create Toolbar
        $this->buildToolbar();
        $vbox->add($this->toolbarWidget);
        $vbox->set_child_packing(
            $this->toolbarWidget,
            false,
            true,
            0,
            Gtk::PACK_START
        );

        //Create Notebook
        $this->notebook = new GtkNotebook();
        $vbox->add($this->notebook);
        $this->notebook->set_border_width(3);

        $this->notebook->connect_simple(
            'switch-page',
            array($this, 'eSwitchTab'),
            $this->toolbarButtons['prev'],
            $this->toolbarButtons['next']
        );

        //Create satus bar
        $this->statusbar = new GtkStatusBar();
        $this->statusbar->set_has_resize_grip(true);
        $vbox->add($this->statusbar);
        $vbox->set_child_packing($this->statusbar, false, false, 0, GTK::PACK_START);

        //Define font
        $this->srcvFontDesc = PangoFontDescription::from_string($this->conf->source_font);

        //Create sampleIndex and sampleWidgets
        $this->sampleIndex = new CfpSampleIndex();
        $this->sampleIndex->parseSampleIndex();
        $this->buildSampleWidgets();

        //Display status message..
        $this->eResetStatusMessage(_('Ready'));

        //Apply preferences
        $this->eApplyPreferences();
    }

    public function show_all() {
        static $first_show_all=true;

        parent::show_all();
        //Ensure preferences...
        $this->eApplyPreferences();
    }

    public function eApplyPreferences() {
        //Toolbar
        if($this->conf->show_toolbar) {
            $this->toolbarWidget->show_all();
        } else {
            $this->toolbarWidget->hide_all();
        }
        $this->toolbarWidget->set_toolbar_style($this->conf->toolbar_style);
        //Statbar
        if($this->conf->show_statbar) {
            $this->statusbar->show_all();
        } else {
            $this->statusbar->hide_all();
        }
        //Notebook
        $this->notebook->set_tab_pos($this->conf->tab_pos);
        //Source font
        $this->srcvFontDesc = PangoFontDescription::from_string($this->conf->source_font);
        foreach($this->sampleWidgets as $sample) {
            $sample['srcv']->modify_font($this->srcvFontDesc);
        }
    }

    protected function buildSampleWidgets() {
        foreach($this->sampleIndex as $sname => $sdata) {

            //Create widgets
            $this->sampleWidgets[$sname]['prev'] = new GtkAlignment();
            $this->sampleWidgets[$sname]['canv'] = new GtkDrawingArea();
            $this->sampleWidgets[$sname]['imgv'] = new GtkImage();
            $this->sampleWidgets[$sname]['srcv'] = new GtkTextView();
            $this->sampleWidgets[$sname]['desc'] = new GtkLabel();

            //Set up canvas
            $this->sampleWidgets[$sname]['canv']->set_size_request(256, 256);

            //Set up image
            $this->sampleWidgets[$sname]['imgv']->set_size_request(256, 256);
            $img_path = $this->conf->res_path.'/cached/'.$sdata->file.'.dat';
            if(file_exists($img_path)) {
                //Workaround for PHP-GTK phar file path interception...
                if(defined('CFP_IS_PHAR') && CFP_IS_PHAR) {
                    $tmp_path = tempnam(sys_get_temp_dir(), 'cfp.');
                    $tmp_fh = fopen($tmp_path, 'w+');
                    $img_fh = fopen($img_path, 'r');
                    while(!feof($img_fh)) {
                        fwrite($tmp_fh, fread($img_fh, 256));
                    }
                    fclose($tmp_fh);
                    fclose($img_fh);
                    $img_path = $tmp_path;
                }
                $this->sampleWidgets[$sname]['imgv']->set_from_file($img_path);
                if(isset($tmp_path)) {
                    @unlink($tmp_path);
                }
            } else {
                $this->sampleWidgets[$sname]['imgv']->set_from_stock(
                    Gtk::STOCK_MISSING_IMAGE,
                    Gtk::ICON_SIZE_DIALOG
                );
            }

            if($this->toolbarButtons['rmode']->get_active()) {
                $this->sampleWidgets[$sname]['prev']->add(
                    $this->sampleWidgets[$sname]['canv']
                );
            } else {
                $this->sampleWidgets[$sname]['prev']->add(
                    $this->sampleWidgets[$sname]['imgv']
                );
            }

            //Set up source view
            $this->sampleWidgets[$sname]['srcv']->set_size_request(524, 500);
            $this->sampleWidgets[$sname]['srcv']->set_editable(false);
            $this->sampleWidgets[$sname]['srcv']->modify_font($this->srcvFontDesc);
            $buffer = $this->sampleWidgets[$sname]['srcv']->get_buffer();
            $buffer->set_text(file_get_contents(CFP_INC_PATH.'/'.$sdata->file));

            //Set up desc label
            $this->sampleWidgets[$sname]['desc']->set_size_request(256, -1);
            $this->sampleWidgets[$sname]['desc']->set_use_markup(true);
            $this->sampleWidgets[$sname]['desc']->set_selectable(true);
            $this->sampleWidgets[$sname]['desc']->set_justify(Gtk::JUSTIFY_LEFT);
            $this->sampleWidgets[$sname]['desc']->set_line_wrap(true);
            $this->sampleWidgets[$sname]['desc']->set_markup($sdata->desc);

            //Layout canvas, source view and desc label
            $hbox = new GtkHBox();
            $hbox->set_spacing(0);

            //Left hand vbox
            $vbox = new GtkVBox();
            $vbox->set_spacing(5);
            //Top label
            $labl = new GtkLabel();
            $labl->set_markup('<b>'._('Source code:').'</b>');
            $vbox->add($labl);
            $vbox->set_child_packing($labl, false, true, 0, GTK::PACK_START);
            //Source view inside scrolled window
            $scrl = new GtkScrolledWindow();
            $scrl->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
            $scrl->add($this->sampleWidgets[$sname]['srcv']);
            $vbox->add($scrl);
            $vbox->set_child_packing($scrl, true, true, 1, GTK::PACK_START);
            //Add to hbox
            $hbox->add($vbox);

            //Right hand vbox
            $vbox = new GtkVBox();
            $vbox->set_spacing(5);
            //Top label
            $labl = new GtkLabel();
            $labl->set_markup('<b>'._('Result:').'</b>');
            $vbox->add($labl);
            $vbox->set_child_packing($labl, false, true, 0, GTK::PACK_START);
            //Preview
            $vbox->add($this->sampleWidgets[$sname]['prev']);
            $vbox->set_child_packing(
                $this->sampleWidgets[$sname]['prev'],
                false,
                false,
                0,
                GTK::PACK_START
            );
            //Description label
            $vbox->add($this->sampleWidgets[$sname]['desc']);
            $vbox->set_child_packing(
                $this->sampleWidgets[$sname]['desc'],
                false,
                false,
                0,
                GTK::PACK_START
            );
            //Add to hbox
            $hbox->add($vbox);
            $hbox->set_child_packing($vbox, false, false, 1, GTK::PACK_START);

            //Add hbox as new notebook page
            $this->notebook->append_page($hbox, new GtkLabel($sdata->title));

            //Connect events
            $this->sampleWidgets[$sname]['canv']->connect_simple('expose-event',
                function($widget, $path) {
                    //Fetch cairo context
                    $context = $widget->window->cairo_create();
                    include($path);
                },
                $this->sampleWidgets[$sname]['canv'],
                CFP_INC_PATH.'/'.$sdata->file
            );
        }
    }

    protected function buildToolbar() {
        //Set up Toolbar
        $toolbar = new GtkToolbar();
        $this->toolbarWidget = &$toolbar;

        //Add About button
        $about = new GtkToolButton();
        $about->set_label(_('About'));
        $about->set_stock_id(Gtk::STOCK_ABOUT);
        $about->connect_simple('clicked', array($this, 'eShowAboutDialog'));
        $about->set_tooltip_text(_('Show more information about this application').' (Alt+A)');
        $about->add_accelerator('clicked', $this->accelGroup, Gdk::KEY_A, Gdk::MOD1_MASK, 0);
        $toolbar->insert($about, 0);
        $this->toolbarButtons['about'] = &$about;

        //Add Settings button
        $setup = new GtkToolButton();
        $setup->set_label(_('Preferences'));
        $setup->set_stock_id(Gtk::STOCK_PREFERENCES);
        $setup->connect_simple('clicked', array($this, 'eEditPreferences'));
        $setup->set_tooltip_text(_('Edit this application\'s preferences').' (Alt+R)');
        $setup->add_accelerator('clicked', $this->accelGroup, Gdk::KEY_R, Gdk::MOD1_MASK, 0);
        $toolbar->insert($setup, 0);
        $this->toolbarButtons['setup'] = &$setup;

        //Add Separator
        $sep = new GtkSeparatorToolItem();
        $toolbar->insert($sep, 0);

        //Add Render Mode Toggle
        $rmode = new GtkToggleToolButton();
        $rmode->set_label(_('Render Live'));
        $rmode->set_stock_id(Gtk::STOCK_CONVERT);
        $rmode->connect('toggled', array($this, 'eSwitchRenderMode'), false);
        $rmode->connect('clicked', array($this, 'eSwitchRenderMode'), true);
        $rmode->set_tooltip_text(_('Switch between live rendering and expected-display mode').' (Alt+L)');
        $rmode->add_accelerator('clicked', $this->accelGroup, Gdk::KEY_L, Gdk::MOD1_MASK, 0);
        $toolbar->insert($rmode, 0);
        $this->toolbarButtons['rmode'] = &$rmode;

        //Add Separator
        $sep = new GtkSeparatorToolItem();
        $toolbar->insert($sep, 0);

        //Add Next button
        $next = new GtkToolButton();
        $next->set_label(_('Next'));
        $next->set_stock_id(Gtk::STOCK_GO_FORWARD);
        $next->set_tooltip_text(_('Display the next example').' (Alt+N)');
        $next->add_accelerator('clicked', $this->accelGroup, Gdk::KEY_N, Gdk::MOD1_MASK, 0);
        $next->add_accelerator('clicked', $this->accelGroup, Gdk::KEY_Page_Down, 0, 0);
        $toolbar->insert($next, 0);
        $this->toolbarButtons['next'] = &$next;

        //Add Previous button
        $prev = new GtkToolButton();
        $prev->set_label(_('Previous'));
        $prev->set_stock_id(Gtk::STOCK_GO_BACK);
        $prev->set_tooltip_text(_('Display the previous example').' (Alt+P)');
        $prev->add_accelerator('clicked', $this->accelGroup, Gdk::KEY_P, Gdk::MOD1_MASK, 0);
        $prev->add_accelerator('clicked', $this->accelGroup, Gdk::KEY_Page_Up, 0, 0);
        $toolbar->insert($prev, 0);
        $this->toolbarButtons['prev'] = &$prev;

        //Connect Next & Previous buttons
        $prev->connect_simple(
            'clicked',
            array($this, 'eSwitchTabPrev'),
            $prev, $next
        );
        $next->connect_simple(
            'clicked',
            array($this, 'eSwitchTabNext'),
            $prev, $next
        );
    }

    public function pushStatusMessage($message, $context_id=null, $with_reset=true) {
        if( $this->conf->show_statbar ) {
            if( $context_id === null ) {
                $context_id = $this->statusbar->get_context_id(__CLASS__);
            }
            $this->statusbar->push($context_id, $message);
            if( $with_reset ) {
                gtk::timeout_add(1500, array($this, 'eResetStatusMessage'));
            }
        }
    }

    public function eSwitchRenderMode(GtkToggleToolButton $button, $is_toggle) {
        static $ignore=false;
        //This will skip this instance, since it is a side effect..
        if( $ignore ) {
            $ignore = false;
            return;
        }
        if( $is_toggle ) {
            //Simulate the button being clicked, will emit a toggled singal
            $button->set_active(!$button->get_active());
        } else {
            //Switch render mode
            if($button->get_active()) {
                foreach($this->sampleWidgets as $widgets) {
                    $widgets['prev']->remove($widgets['prev']->get_child());
                    $widgets['prev']->add($widgets['canv']);
                    $widgets['prev']->show_all();
                }
            } else {
                foreach($this->sampleWidgets as $widgets) {
                    $widgets['prev']->remove($widgets['prev']->get_child());
                    $widgets['prev']->add($widgets['imgv']);
                    $widgets['prev']->show_all();
                }
            }
            //Will skip the clicked signal we emit as a side effect now!
            $ignore = true;
        }
    }

    public function eSwitchTab($prev, $next) {
        /*
         * There is a bug in GTK versions < 2.8.x that messes up the page count
         * when pages are hit by the users, for so long this functionality
         * should be disabled in order to leave things working...
         *
        switch($this->notebook->get_current_page()) {
            case 0:
                $prev->set_sensitive(false);
                $next->set_sensitive(true);
                break;
            case $this->notebook->get_n_pages()-1:
                $prev->set_sensitive(true);
                $next->set_sensitive(false);
                break;
            default:
                $prev->set_sensitive(true);
                $next->set_sensitive(true);
        }
        if($this->notebook->get_n_pages() <= 1) {
            $prev->set_sensitive(false);
            $next->set_sensitive(false);
        }
         */
    }

    public function eSwitchTabPrev($prev, $next) {
        $this->notebook->prev_page();
    }

    public function eSwitchTabNext($prev, $next) {
        $this->notebook->next_page();
    }

    public function eShowAboutDialog() {
        $dlg = new GtkAboutDialog();
        $dlg->set_transient_for($this);

        $dlg->set_program_name($this->get_title());
        $dlg->set_version(CFP_VERSION);

        $dlg->set_comments($GLOBALS['CFP_STRINGS']['APP_DESC']);

        $dlg->set_copyright($GLOBALS['CFP_STRINGS']['APP_COPY']);

        $dlg->set_license(file_get_contents($this->conf->res_path.'/license.txt'));

        $dlg->set_logo(
            $dlg->render_icon(
                Gtk::STOCK_ABOUT,
                Gtk::ICON_SIZE_DIALOG
            )
        );

        $dlg->set_icon(
            $this->render_icon(
                Gtk::STOCK_ABOUT,
                Gtk::ICON_SIZE_DIALOG
            )
        );

        $dlg->set_website($GLOBALS['CFP_STRINGS']['APP_WEBSITE']);

        $dlg->run();

        $dlg->destroy();
    }

    public function eEditPreferences() {
        $dlg = new CfpPreferencesDialog();
        $dlg->set_transient_for($this);

        $save = $dlg->run();

        if( $save == Gtk::RESPONSE_YES && $this->conf->getIniPath()) {
            $prefs = $dlg->fetchArray();
            $this->conf->loadArray($prefs);
            $this->eApplyPreferences();
            $this->pushStatusMessage(_('New preferences have been applied...'));
        } else {
            $this->pushStatusMessage(_('New preferenced have been discarded...'));
        }

        $dlg->destroy();
    }

    public function eResetStatusMessage() {
        if($this->conf->show_statbar)
            $this->pushStatusMessage(_('Ready'), null, false);
        return false;
    }
}
?>