<?php
/**
 * Simple web template engine for the web interface
 *
 * This is a simple template engine which is used for the web interface.
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
 * @author     Florian F Freeman <florian@phpws.org>
 * @copyright  2009 Florian F Freeman
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id:$
 * @link       http://gtk.php.net
 * @since      1.0.0
 */

/**
 * Web Template Engine for CairoForPHP
 *
 * This class can be used to load and manipulate simple templates with the
 * possibility to buffer the output and dynamically inherit variables from
 * parent templates and to other templates as values to template variables.
 *
 * @package  CairoForPHP
 * @see      CfpMainWindow
 * @since    1.0.0
 */
class CfpWebTemplates {
    /**
     * Assigned Template Variables
     *
     * This is an associative array of named template variables and their
     * values which may be either a string or another CfpWebTemplates object.
     *
     * @see CfpWebTemplates::put()
     * @see CfpWebTemplates::putArray()
     * @see CfpWebTemplates::remove()
     * @see CfpWebTemplates::removeArray()
     * @access public
     * @var array array(string $name => string|CfpWebTemplates $value, ...)
     */
    public $vars = array();

    /**
     * Path to Template File
     *
     * @see CfpWebTemplates::setTemplatePath()
     * @access public
     * @var string
     */
    public $templatePath;

    /**
     * Parsed Result From Templates
     *
     * This is a buffer of all rendered templates belonging
     * to the object. This functions as an output buffer.
     *
     * @see CfpWebTemplates::render()
     * @see CfpWebTemplates::fetchResult()
     * @see CfpWebTemplates::flushResult()
     * @see CfpWebTemplates::displayResult()
     * @see CfpWebTemplates::clearResult()
     * @access public
     * @var string
     */
    public $templateResult;

    /**
     * Reference to Parent Template
     *
     * Reference to parent template used for fallback to variable values
     * not defined in child.
     *
     * @see CfpWebTemplates::setParent()
     * @see CfpWebTemplates::removeParent()
     * @access public
     * @var CfpWebTemplates
     */
    public $parentTemplate;

    /**
     * Class constructor
     *
     * Optionally sets the template path and parent template object to be used.
     *
     * @access public
     * @param string $tpl_path Path to the template to be used.
     * @param CfpWebTemplates $parent Parent object for this template object.
     */
    public function __construct($tpl_path=null, CfpWebTemplates $parent=null) {
        if($tpl_path !== null) {
            $this->setTemplatePath($tpl_path);
        }

        if($parent !== null) {
            $this->setParent($parent);
        }
    }

    public function put($name, $value, $encode=true) {
        if(!is_string($name)) {
            throw new BadMethodCallException('First argument for '.__METHOD__.'() must be of type string, '.gettype($name).' given.');
        }
        if($encode && !is_object($value)) {
            //Check whether it is utf-8 or not
            if(md5($value) == md5(iconv('UTF-8', 'UTF-8', $value))) {
                $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
            } else {
                $value = htmlspecialchars($value);
            }
        }
        $this->vars[strtoupper($name)] = $value;
    }

    public function putArray(array $variables, $encode=true) {
        foreach($variables as $name => $value) {
            if(is_array($value)) {
                $encode = (bool)$value[1];
                $value = $value[0];
            }
            $this->put($name, $value, $encode);
        }
    }

    public function remove($name) {
        if(!is_string($name)) {
            throw new BadMethodCallException('First argument for '.__METHOD__.'() must be of type string, '.gettype($name).' given.');
        }
        if(isset($this->vars[strtoupper($name)])) {
            unset($this->vars[strtoupper($name)]);
        }
    }

    public function removeArray(array $names) {
        foreach($names as $name) {
            $this->remove($name);
        }
    }

    public function setParent(CfpWebTemplates $parent) {
        $this->parentTemplate = $parent;
    }

    public function removeParent() {
        $this->parentTemplate = null;
    }

    public function setTemplatePath($path) {
        if(!file_exists($path)) {
            throw new CfpIoException('Unable to load template: file does not exist.');
        }
        $this->templatePath = $path;
    }

    public function isRendered() {
        return !empty($this->templateResult);
    }

    public function varsMergeParent($vars) {
        if(is_a($this->parentTemplate, __CLASS__)) {
            $vars = array_merge($this->parentTemplate->vars, $vars);
            $vars = $this->parentTemplate->varsMergeParent($vars);
        }
        return is_array($vars) ? $vars : array();
    }

    public function render($path=null) {
        if($path) {
            $this->setTemplatePath($path);
        }
        if(empty($this->templatePath)) {
            throw new RuntimeException(__METHOD__.' requires template path to be set, template path is empty.');
        }
        $template = file_get_contents($this->templatePath);
        $vars = $this->varsMergeParent($this->vars);
        foreach($vars as $name => $value) {
            if(is_a($value, __CLASS__)) {
                if(!$value->isRendered()) {
                    $value->render();
                }
                $value = $value->fetchResult();
            }
            $template = str_replace('{'.$name.'}', $value, $template);
        }
        $this->templateResult .= $template;
    }

    public function displayResult() {
        print $this->templateResult;
    }

    public function clearResult() {
        $this->templateResult = null;
    }

    public function flushResult() {
        $this->displayResult();
        $this->clearResult();
    }

    public function fetchResult() {
        return $this->templateResult;
    }
}

?>