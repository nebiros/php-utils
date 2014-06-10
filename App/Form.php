<?php

class App_Form 
{
    const ELEMENT_TYPE_TEXT = 1;
    const ELEMENT_TYPE_SELECT = 2;
    const ELEMENT_TYPE_CHECKBOX = 3;    
    const ELEMENT_TYPE_RADIO = 4;

    public static $ELEMENT_TYPE_CLASS_NAMES = array(
        self::ELEMENT_TYPE_TEXT => "Text", 
        self::ELEMENT_TYPE_SELECT => "Select", 
        self::ELEMENT_TYPE_CHECKBOX => "Checkbox", 
        self::ELEMENT_TYPE_RADIO => "Radio");

    protected $_elements = null;
    protected $_options = null;
    protected $_xhtml = null;
    protected $_defaultOptions = null;

    public static function implodeOptionsForHtml(Array $options) {
        return self::convertArrayOptionsToHtml(array_keys($options), array_values($options));
    }

    public static function convertArrayOptionsToHtml($keys, $values) {
        $merge = array_map(array(self, "mergeHtmlOptions"), $keys, $values);

        return implode(" ", $merge);
    }

    public static function mergeHtmlOptions($key, $value) {
        return "{$key}=\"{$value}\"";
    }

    public function __construct(Array $elements = null, Array $options = null) {
        $this->_defaultOptions = array("draw_form_tag" => true, 
            "tag" => array("action" => $_SERVER["PHP_SELF"], "method" => "post"));

        if ($elements != null) {
            $this->setElements($elements);
        }
        
        if ($options != null) {
            $this->setOptions($options);
        }
    }

    public function setElements(Array $elements) {
        $this->_elements = $elements;
        return $this;
    }

    public function getElements() {
        return $this->_elements;
    }

    public function setOptions(Array $options) {
        $this->_options = array_merge($this->_defaultOptions, $options);
        return $this;
    }

    public function getOptions() {
        if (empty($this->_options)) {
            $this->setOptions(array());
        }

        return $this->_options;
    }

    public function getXhtml() {
        return $this->_xhtml;
    }

    public function build() {
        if (empty($this->_elements)) {
            throw new InvalidArgumentException("Elements cannot be empty");
        }

        $opt = $this->getOptions();

        if (!empty($opt["draw_form_tag"])) {
            $o = self::implodeOptionsForHtml($opt["tag"]);
            $this->_xhtml .= <<<XHTML
                <form {$o}>
XHTML;
        }

        foreach ($this->_elements as $element) {
            $type = ucfirst(strtolower($element["element_type"]));            
            $klass = "App_Form_Element_" . $type;

            if (!class_exists($klass)) {
                throw new InvalidArgumentException("Form element type '{$type}' not found");
            }

            $name = $element["element_name"];

            unset($element["element_type"], $element["element_name"]);
            $options = $element;

            $el = new $klass($name, $options);
            $this->_xhtml .= $el->draw();
        }

        if (!empty($this->_options["draw_form_tag"])) {
            $this->_xhtml .= "</form>";
        }

        return $this;
    }

    public function draw() {
        return $this->build()->getXhtml();
    }

    public function __toString() {
        return $this->draw();
    }
}