<?php
/**
 * Web interface class
 *
 * This file contains the class that is used to drive the web interface.
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

class CfpWebInterface {
    protected $conf;

    public $requestData;
    public $pageType;
    public $sampleId;
    public $sampleIndex;
    public $imageMod;
    public $imageStockName;

    const PAGE_TYPE_EMPTY      =  1;
    const PAGE_TYPE_SAMPLE     =  2;
    const PAGE_TYPE_ABOUT      =  4;
    const PAGE_TYPE_STYLESHEET =  8;
    const PAGE_TYPE_IMAGE      = 16;

    const IMAGE_REQUEST_LIVE     =  1;
    const IMAGE_REQUEST_EXPECTED =  2;
    const IMAGE_REQUEST_STOCK    =  4;

    public function __construct(array $request_data=null) {
        //Shortcut reference to CFP_CONF global
        if(is_a($GLOBALS['CFP_CONF'], 'CfpPreferences')) {
            $this->conf = $GLOBALS['CFP_CONF'];
        } else {
            $this->conf = new CfpPreferences();
        }

        //Page type empty
        $this->pageType = self::PAGE_TYPE_EMPTY;

        //Image Modifier and Stock Name = none
        $this->imageMod = null;
        $this->imageStockName = null;

        //Pass on request data
        if(is_array($request_data)) {
            $this->setRequestData($request_data);
        }

        //Parse sample index
        $this->sampleIndex = new CfpSampleIndex();
        $this->sampleIndex->parseSampleIndex();
    }

    public function setRequestData(array $request_data) {
        $this->requestData = $request_data;
    }

    public function processRequest() {
        if(!is_array($this->requestData)) {
            $this->setRequestData($_REQUEST);
        }

        //Page type
        if(!empty($this->requestData['p'])) {
            switch((int)$this->requestData['p']) {
                case self::PAGE_TYPE_STYLESHEET:
                    $this->pageType = self::PAGE_TYPE_STYLESHEET;
                    break;
                case self::PAGE_TYPE_ABOUT:
                    $this->pageType = self::PAGE_TYPE_ABOUT;
                    break;
                case self::PAGE_TYPE_IMAGE:
                    $this->pageType = self::PAGE_TYPE_IMAGE;
                    break;
                case self::PAGE_TYPE_EMPTY:
                    $this->pageType = self::PAGE_TYPE_EMPTY;
                    break;
                default:
                    $this->pageType = self::PAGE_TYPE_SAMPLE;
            }
        } else {
            $this->pageType = self::PAGE_TYPE_SAMPLE;
        }


        //Sample id
        if($this->pageType&(self::PAGE_TYPE_SAMPLE|self::PAGE_TYPE_IMAGE)) {
            if(!empty($this->requestData['s'])) {
                if(isset($this->sampleIndex[$this->requestData['s']])) {
                    $this->sampleId = $this->requestData['s'];
                } else {
                    if($this->sampleIndex->getItemCount() > 0) {
                        $item = $this->sampleIndex->getNthItem(0);
                        $this->sampleId = $item->name;
                    } else {
                        $this->pageType = self::PAGE_TYPE_EMPTY;
                    }
                }
            } else {
                if($this->sampleIndex->getItemCount() > 0) {
                    $item = $this->sampleIndex->getNthItem(0);
                    $this->sampleId = $item->name;
                } else {
                    $this->pageType = self::PAGE_TYPE_EMPTY;
                }
            }
        }

        //Previous and Next function
        if($this->pageType == self::PAGE_TYPE_SAMPLE) {
            if(!empty($this->requestData['f'])) {
                if($this->requestData['f'] == 'p') {
                    $pos = $this->sampleIndex->getItemPos($this->sampleId);
                    if(($item = $this->sampleIndex->getNthItem(--$pos)) !== false) {
                        $this->sampleId = $item->name;
                    }
                } elseif($this->requestData['f'] = 'n') {
                    $pos = $this->sampleIndex->getItemPos($this->sampleId);
                    if(($item = $this->sampleIndex->getNthItem(++$pos)) !== false) {
                        $this->sampleId = $item->name;
                    }
                }
            }
        }

        //Image request type
        if($this->pageType == self::PAGE_TYPE_IMAGE) {
            if(!empty($this->requestData['m'])) {
                switch($this->requestData['m']) {
                    case 'l':
                        $this->imageMod = self::IMAGE_REQUEST_LIVE;
                        break;
                    case 'e':
                        $this->imageMod = self::IMAGE_REQUEST_EXPECTED;
                        break;
                    case 's':
                        $this->imageMod = self::IMAGE_REQUEST_STOCK;
                        if(!empty($this->requestData['n'])) {
                            $this->imageStockName = $this->requestData['n'];
                        }
                        break;
                    default:
                        $this->imageMod = null;
                }
            } else {
                $this->imageMod = null;
            }
        }
    }

    public function displayHeader() {
        $tpl = new CfpWebTemplates();
        $tpl->setTemplatePath($this->conf->res_path.'/web/header.tpl');
        $tpl->put('title', $GLOBALS['CFP_STRINGS']['APP_TITLE']);
        $tpl->put('stylesheet_uri', '?p='.self::PAGE_TYPE_STYLESHEET);
        $tpl->render();
        $tpl->flushResult();
    }

    public function displayFooter() {
        $tpl = new CfpWebTemplates();
        $tpl->setTemplatePath($this->conf->res_path.'/web/footer.tpl');
        $tpl->render();
        $tpl->flushResult();
    }

    public function displayToolbar() {
        $tpl = new CfpWebTemplates();
        $tpl->setTemplatePath($this->conf->res_path.'/web/toolbar.tpl');

        $items_tpl = new CfpWebTemplates();
        $items_tpl->setParent($tpl);
        $item_path = $this->conf->res_path.'/web/toolbarItem.tpl';
        $sepa_path = $this->conf->res_path.'/web/toolbarSeparator.tpl';
        //Previous button
        $items_tpl->putArray(
            array(
                'target' => '?p='.self::PAGE_TYPE_SAMPLE.'&amp;s='.$this->sampleId.'&amp;f=p',
                'image_path' => '?p='.self::PAGE_TYPE_IMAGE.'&amp;m=s&amp;n=prev',
                'label' => _('Previous')
            ),
            false
        );
        $items_tpl->render($item_path);
        //Next button
        $items_tpl->putArray(
            array(
                'target' => '?p='.self::PAGE_TYPE_SAMPLE.'&amp;s='.$this->sampleId.'&amp;f=n',
                'image_path' => '?p='.self::PAGE_TYPE_IMAGE.'&amp;m=s&amp;n=next',
                'label' => _('Next')
            ),
            false
        );
        $items_tpl->render($item_path);
        //Separator
        $items_tpl->render($sepa_path);
        //About button
        $items_tpl->putArray(
            array(
                'target' => '?p='.self::PAGE_TYPE_ABOUT,
                'image_path' => '?p='.self::PAGE_TYPE_IMAGE.'&amp;m=s&amp;n=about',
                'label' => _('About')
            ),
            false
        );
        $items_tpl->render($item_path);

        $tpl->put('toolbar_items', $items_tpl);
        $tpl->render();
        $tpl->flushResult();
    }

    public function displaySidebar() {
        $tpl = new CfpWebTemplates();
        $tpl->setTemplatePath($this->conf->res_path.'/web/sidebar.tpl');

        $items_tpl = new CfpWebTemplates();
        $items_tpl->setParent($tpl);
        $items_tpl->setTemplatePath($this->conf->res_path.'/web/sidebarItem.tpl');

        foreach($this->sampleIndex as $sname => $sdata) {
            if($this->sampleId == $sname) {
                $items_tpl->put('is_active', 'class="active"', false);
            } else {
                $items_tpl->put('is_active', '');
            }
            $items_tpl->putArray(
                array(
                    'target' => '?p='.self::PAGE_TYPE_SAMPLE.'&amp;s='.$sname,
                    'title' => htmlentities($sdata->title)
                ),
                false
            );
            $items_tpl->render();
        }

        $tpl->put('sidebar_items', $items_tpl);
        $tpl->render();
        $tpl->flushResult();
    }

    public function displayPage() {

        try {
            $this->writeHeader('HTTP/1.1 200 OK');
            $this->writeHeader('X-Powered-By: Cairo for PHP/'.CFP_VERSION);
            $this->writeHeader('X-Copyright: 2009-2014 Florian Breit');
            $this->writeHeader('X-License: PHP License 3.01: http://www.php.net/license/3_01.txt');
        } catch(Exception $e) {
        }

        $html_pages = self::PAGE_TYPE_ABOUT | self::PAGE_TYPE_SAMPLE;

        if($this->pageType & $html_pages) {
            try {
                $this->writeHeader('Content-Type: text/html; charset=UTF-8');
            } catch(Exception $e) {
            }

            $this->displayHeader();
            $this->displayToolbar();
            $this->displaySidebar();

            switch($this->pageType) {
                case self::PAGE_TYPE_SAMPLE:
                    $this->pageSample();
                    break;
                case self::PAGE_TYPE_ABOUT:
                    $this->pageAbout();
                    break;
            }

            $this->displayFooter();
        } elseif($this->pageType & self::PAGE_TYPE_EMPTY) {
            $this->writeHeader('HTTP/1.1 202 No Content');
            $this->writeHeader('Content-Type: text/plain; charset=UTF-8');
        } elseif($this->pageType & self::PAGE_TYPE_IMAGE) {
            $this->pageImage();
        } elseif($this->pageType & self::PAGE_TYPE_STYLESHEET) {
            $this->pageStylesheet();
        }
    }

    public function writeHeader($string, $replace=true, $http_response_code=null) {
        if(headers_sent()) {
            $lineno = $filename = '';
            headers_sent($lineno, $filename);
            throw new CfpHttpException("Unable to write HTTP headers, headers already sent in ``$filename'':($lineno).");
        }
        header($string, $replace, $http_response_code);
    }

    public function pageStylesheet() {
        $this->writeHeader('Content-Type: text/css');
        readfile($this->conf->res_path.'/web/style.css');
    }

    public function pageImage() {
        $found = false;
        if((!empty($this->sampleId) || $this->imageMod == self::IMAGE_REQUEST_STOCK) && !empty($this->imageMod)) {
            $this->writeHeader('Content-Type: image/png');
            switch($this->imageMod) {
                case self::IMAGE_REQUEST_LIVE:
                    $this->writeHeader('Cache-Control: no-cache, must-revalidate');
                    $path = trim(str_replace(array('\\', '/'), '', $this->sampleId));
                    $path = CFP_INC_PATH.'/'.$path.'.php';
                    if(file_exists($path)) {
                        $canv_path = $this->conf->res_path.'/web/canv.png';
                        $img_surface = CairoImageSurface::createFromPng($canv_path);
                        $img_context = new CairoContext($img_surface);
                        call_user_func(
                            function($context, $file) {
                                include($file);
                            },
                            $img_context,
                            $path
                        );
                        $img_surface->writeToPng('php://output');
                        $found = true;
                    }
                    break;
                case self::IMAGE_REQUEST_EXPECTED:
                    $path = trim(str_replace(array('\\', '/'), '', $this->sampleId));
                    $path = $this->conf->res_path.'/cached/'.$path.'.php.dat';
                    if(file_exists($path)) {
                        readfile($path);
                        $found = true;
                    }
                    break;
                case self::IMAGE_REQUEST_STOCK:
                    if($this->imageStockName === null) {
                        break;
                    }
                    $path = trim(str_replace(array('\\', '/'), '', $this->imageStockName));
                    $path = $this->conf->res_path.'/web/'.$path.'.png';
                    if(file_exists($path)) {
                        readfile($path);
                        $found = true;
                    }
                    break;
            }
        }
        if(!$found) {
            $this->writeHeader('HTTP/1.1 404 Not Found');
            $this->writeHeader('Status: 404 Not Found');
            $this->writeHeader('Content-Type: text/html; charset=UTF-8');
            $tpl = new CfpWebTemplates();
            $tpl->putArray(
                array(
                    'title' => _('Error 404: File Not Found.'),
                    'message' => _('The requested URI could not be found.'),
                    'uri' => $_SERVER['REQUEST_URI'],
                    'uri_label' => _('URI Requested:'),
                    'referer_uri' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '(empty)',
                    'referer_label' => _('Referer:'),
                    'host' => $_SERVER['HTTP_HOST'],
                    'host_label' => _('Host:'),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'user_agent_label' => _('User-Agent:'),
                    'signature' =>  'Cairo For PHP/'.CFP_VERSION.'; '.@date(DATE_RFC822)
                )
            );
            $tpl->put('uri', $_SERVER['REQUEST_URI']);
            $tpl->render($this->conf->res_path.'/web/error404.tpl');
            $tpl->flushResult();
        }
    }

    public function pageSample() {
        $tpl = new CfpWebTemplates();
        $tpl->setTemplatePath($this->conf->res_path.'/web/display.tpl');

        $item = $this->sampleIndex->getItem($this->sampleId);

        $dsp_tpl = new CfpWebTemplates();
        $dsp_tpl->setParent($tpl);
        $dsp_tpl->setTemplatePath($this->conf->res_path.'/web/sourcecode.tpl');
        $dsp_tpl->put('title', 'Sourcecode:');
        $sourcecode = highlight_file($item->file, true);
        $dsp_tpl->put('sourcecode', $sourcecode, false);
        $dsp_tpl->render();

        $dsp_tpl->setTemplatePath($this->conf->res_path.'/web/information.tpl');
        $dsp_tpl->putArray(
            array(
                'live_image_title' => _('Live Image:'),
                'live_image_desc' => _('Live Image'),
                'live_image_path' => '?p='.self::PAGE_TYPE_IMAGE.'&amp;s='.$this->sampleId.'&amp;m=l',
                'expected_image_title' => _('Expected Output:'),
                'expected_image_desc' => _('Expected Output'),
                'expected_image_path' => '?p='.self::PAGE_TYPE_IMAGE.'&amp;s='.$this->sampleId.'&amp;m=e'
            ),
            false
        );
        $dsp_tpl->put('description', $this->pangoMarkupToHtml($item->desc), false);
        $dsp_tpl->render();

        $tpl->put('display', $dsp_tpl);
        $tpl->render();
        $tpl->flushResult();
    }

    public function pageAbout() {
        $tpl = new CfpWebTemplates();
        $tpl->setTemplatePath($this->conf->res_path.'/web/about.tpl');

        $tpl->putArray(
            array(
                'title' => $GLOBALS['CFP_STRINGS']['APP_TITLE'],
                'version' => CFP_VERSION,
                'copyright' => $GLOBALS['CFP_STRINGS']['APP_COPY'],
                'website_uri' => $GLOBALS['CFP_STRINGS']['APP_WEBSITE'],
                'logo_uri' => '?p='.self::PAGE_TYPE_IMAGE.'&amp;m=s&amp;n=about'
            )
        );

        $tpl->put(
            'comments',
            str_replace("\n", "\n<br />", $GLOBALS['CFP_STRINGS']['APP_DESC']),
            false
        );

        $tpl->put('license_title', _('License Terms:'));
        $tpl->put('license_text', file_get_contents($this->conf->res_path.'/license.txt'));

        $tpl->render();
        $tpl->flushResult();
    }

    public function pangoMarkupToHtml($markup) {
        $patterns = array(
             0 => '/(.*)<markup>(.*?)<\/markup>(.*)/s',
             1 => '/(.*)<span (.*?)>(.*?)<\/span>(.*)/s',

             2 => '/(.*)<! (.*?)font_desc="(.*?)"(.*?) !>(.*)/s',
             3 => '/(.*)<! (.*?)font_family="(.*?)"(.*?) !>(.*)/s',
             4 => '/(.*)<! (.*?)face="(.*?)"(.*?) !>(.*)/s',
             5 => '/(.*)<! (.*?)size="(.*?)"(.*?) !>(.*)/s',
             6 => '/(.*)<! (.*?)style="(.*?)"(.*?) !>(.*)/s',
             7 => '/(.*)<! (.*?)weight="(.*?)"(.*?) !>(.*)/s',
             8 => '/(.*)<! (.*?)variant="(.*?)"(.*?) !>(.*)/s',
             9 => '/(.*)<! (.*?)stretch="(.*?)"(.*?) !>(.*)/s',
            10 => '/(.*)<! (.*?)foreground="(.*?)"(.*?) !>(.*)/s',
            11 => '/(.*)<! (.*?)background="(.*?)"(.*?) !>(.*)/s',
            12 => '/(.*)<! (.*?)underline="(.*?)"(.*?) !>(.*)/s',
            13 => '/(.*)<! (.*?)rise="(.*)"(.*?) !>(.*?)/s',
            14 => '/(.*)<! (.*?)strikethrough="(.*?)"(.*?) !>(.*)/s',
            15 => '/(.*)<! (.*?)fallback="(.*?)"(.*?) !>(.*)/s',
            16 => '/(.*)<! (.*?)lang="(.*?)"(.*?) !>(.*)/s',

            18 => '/(.*)<! (.*?)text-decoration:(single|double|low);(.*?) !>(.*)/s',
            19 => '/(.*)<! (.*?)text-decoration:(true);(.*?) !>(.*)/s',
            20 => '/(.*)<! (.*?)text-decoration:(false);(.*?) !>(.*)/s',
            21 => '/(.*)<! (.*?)text-variant:(smallcaps);(.*?) !>(.*)/s',
            22 => '/(.*)<! (.*?)font-stretch:(ultracondensed|extracondensed|semicondensed);(.*?) !>(.*)/s',
            23 => '/(.*)<! (.*?)font-stretch:(semiexpanded|extraexpanded|ultraexpanded);(.*?) !>(.*)/s',

            24 => '/(.*)<! (.*?) !>(.*)/s',
            25 => "/(.*)(\r?)\n(.*)/"
        );
        $replacements = array(
             0 => '$1$2$3',
             1 => '$1<span style="<! $2 !>">$3</span>$4',

             2 => '$1<! $2font:$3;$4 !>$5',
             3 => '$1<! $2font-family:$3;$4 !>$5',
             4 => '$1<! $2font-family:$3;$4 !>$5',
             5 => '$1<! $2font-size:$3;$4 !>$5',
             6 => '$1<! $2font-style:$3;$4 !>$5',
             7 => '$1<! $2font-weight:$3;$4 !>$5',
             8 => '$1<! $2font-variant:$3;$4 !>$5',
             9 => '$1<! $2font-stretch:$3;$4 !>$5',
            10 => '$1<! $2color:$3;$4 !>$5',
            11 => '$1<! $2background-color:$3;$4 !>$5',
            12 => '$1<! $2text-decoration:$3;$4 !>$5',
            13 => '$1<! $2line-height:$3;$4 !>$5',
            14 => '$1<! $2text-decoration:$3;$4 !>$5',
            15 => '$1<! $2$4 !>$5',
            16 => '$1<! $2$4 !>$5',

            18 => '$1<! $2text-decoration:underline;$4 !>$5',
            19 => '$1<! $2text-decoration:line-through;$4 !>$5',
            20 => '$1<! $2text-decoration:none;$4 !>$5',
            21 => '$1<! $2text-variant:small-caps;$4 !>$5',
            22 => '$1<! $2font-stretch:condensed;$4 !>$5',
            23 => '$1<! $2font-stretch:expanded;$4 !>$5',

            24 => '$1$2$3',
            25 => "$1<br />$2\n$3"
        );
        return preg_replace($patterns, $replacements, trim($markup));
    }
}
?>