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

    protected $_elements = array();
    protected $_options = array();
    protected $_xhtml = null;

    public function __construct(Array $elements = null, Array $options = null) {
        if ($elements != null) {
            $this->_elements = $elements;
        }
        
        if ($options != null) {
            $this->_options = $options;
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
        $this->_options = $options;
        return $this;
    }

    public function getOptions() {
        return $this->_options;
    }

    public function getXhtml() {
        return $this->_xhtml;
    }

    public function build() {
        if (empty($this->_elements)) {
            throw new InvalidArgumentException("Elements cannot be empty");
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

        return $this;
    }

    public function draw() {
        return $this->build()->getXhtml();
    }
}