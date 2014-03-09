<?php
class CfpSampleIndex implements Iterator, ArrayAccess {
    protected $conf;
    protected $iterPos = 0;
    protected $iterKeys = array();
    protected $iterSize = 0;

    public $index = array();

    public function __construct() {
        //Shortcut reference to CFP_CONF global
        if(is_a($GLOBALS['CFP_CONF'], 'CfpPreferences')) {
            $this->conf = $GLOBALS['CFP_CONF'];
        } else {
            $this->conf = new CfpPreferences();
        }
    }

    public function offsetExists($offset) {
        return isset($this->index[$offset]);
    }

    public function offsetGet($offset) {
        return $this->getItem($offset);
    }

    public function offsetSet($offset, $value) {
        if( is_a($value, 'CfpSampleIndexItem') ) {
            return $this->addItem($offset, $value);
        } else {
            throw new BadMethodCallException(
                 'Assigned value must be of type CfpSampleIndexItem, '
                .gettype($value).' given.'
            );
        }
    }

    public function offsetUnset($offset) {
        return $this->removeItem($offset);
    }

    protected function mapIter() {
        $this->iterKeys = array_keys($this->index);
        $this->iterSize = count($this->iterKeys);
    }

    protected function checkIterSize() {
        if($this->iterSize <> $this->getItemCount()) {
            $this->mapIter();
        }
    }

    public function current() {
        $this->checkIterSize();
        return $this->index[$this->iterKeys[$this->iterPos]];
    }
    public function key() {
        $this->checkIterSize();
        return $this->iterKeys[$this->iterPos];
    }
    public function next() {
        $this->checkIterSize();
        ++$this->iterPos;
    }
    public function rewind() {
        $this->checkIterSize();
        $this->iterPos = 0;
    }
    public function valid() {
        $this->checkIterSize();
        return isset($this->iterKeys[$this->iterPos]);
    }

    public function getNthItem($n) {
        $this->checkIterSize();
        if($n < 0 || $n >= $this->iterSize) {
            return false;
        }
        return $this->index[$this->iterKeys[$n]];
    }

    public function getItemPos($ref) {
        $this->checkIterSize();
        return array_search($ref, $this->iterKeys);
    }

    public function getItemCount() {
        return count($this->index);
    }

    public function addItem($ref, CfpSampleIndexItem $item) {
        $this->index[$ref] = $item;
    }

    public function removeItem($ref) {
        if(isset($this->index[$ref])) {
            unset($this->index[$ref]);
        }
    }

    public function getItem($ref) {
        if(isset($this->index[$ref])) {
            return $this->index[$ref];
        } else {
            return null;
        }
    }

    public function parseSampleIndex() {
        //Define file paths
        $file = $this->conf->sample_index;
        if(!file_exists($file)) {
            throw new CfpIoException("Failed to load file: \"".$file."\"");
        }
        $xsd = $this->conf->res_path.'/sampleIndex.xsd';
        if(!file_exists($xsd)) {
            throw new CfpIoException("Failed to load file: \"".$xsd."\"");
        }

        //Open sampleIndex and validate
        $dom = new DOMDocument();
        $dom->loadXML(file_get_contents($file));
        $old_libxml_errors = libxml_use_internal_errors(true);
        if(!$dom->schemaValidateSource(file_get_contents($xsd))) {
            $error = libxml_get_last_error();
            $error->file = $file; //Override $error->file
            throw CfpXmlValidationException::newFromLibXMLError($error);
        }
        libxml_use_internal_errors($old_libxml_errors);

        //Read node list
        $nodes = $dom->getElementsByTagName("sample");

        //Extract nodes into sampleIndex
        foreach($nodes as $node) {
            if(!$node->hasAttribute("name")) {
                next;
            }
            $name = $node->getAttribute("name");
            if($node->hasAttribute("title")) {
                $title = $node->getAttribute("title");
            } else {
                $title = $name;
            }
            if($node->hasAttribute("file")) {
                $file = $node->getAttribute("file");
            } else {
                $file = $name.".php";
            }
            $desc = '';
            if($node->hasChildNodes()) {
                //This buffers the xml representation of the node's contents into $desc
                foreach($node->childNodes as $cnode) {
                    $desc .= $cnode->ownerDocument->saveXML($cnode);
                }
                $desc = $this->collapseWhitespace($desc);
            }
            $this->addItem(
                $name,
                new CfpSampleIndexItem(
                    $name,
                    $title,
                    $desc,
                    $file
                )
            );
        }
    }

    protected function collapseWhitespace($code) {
        $result = '';
        foreach(explode("\n", $code) as $line) {
            $line = trim($line);
            $line = str_ireplace(array("<br/>", "<br />", "<br></br>"), "\n", $line);
            $line = trim($line, " \t\r\0\x0B");
            if( $line != '' )
                $result .= " ".$line;
        }
        $result = explode("\n", $result);
        foreach($result as &$line) {
            $line = trim($line);
        }
        unset($line);
        $result = implode("\n", $result);
        return $result;
    }
}

class CfpSampleIndexItem implements ArrayAccess {
    public $name;
    public $title;
    public $desc;
    public $file;

    public function __construct($name, $title, $desc, $file) {
        $this->name = $name;
        $this->title = $title;
        $this->desc = $desc;
        $this->file = $file;
    }

    public function getName() {
        return $this->name;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDesc() {
        return $this->desc;
    }

    public function getFile() {
        return $this->file;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function setTitle($value) {
        $this->title = $value;
    }

    public function setDesc($value) {
        $this->desc = $value;
    }

    public function setFile($value) {
        $this->file = $value;
    }

    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetGet($offset) {
        if(isset($this->$offset)) {
            return $this->$offset;
        } else {
            return null;
        }
    }

    public function offsetSet($offset, $value) {
        $vars = array('name', 'title', 'desc', 'file');
        if(isset($this->$offset) || in_array($offset, $vars)) {
            $this->$offset = (string) $value;
        }
    }

    public function offsetUnset($offset) {
        $vars = array('name', 'title', 'desc', 'file');
        if(isset($this->$offset) || in_array($offset, $vars)) {
            $this->$offset = '';
        }
    }
}
?>