<?php

abstract class App_Form_Element_FormElementAbstract implements App_Form_Element_FormElementInterface
{
    protected $_name = null;
    protected $_options = null;
    protected $_xhtml = null;

    public function __construct($name, Array $options = null) {
        $this->_name = $name;

        if ($options != null) {
            $this->_options = $options;
        }
    }

    public function getName() {
        return $this->_name;
    }

    public function getOptions() {
        return $this->_options;
    }

    public function getXhtml() {
        return $this->_xhtml;
    }

    protected function implodeOptionsForHtml() {
        return $this->convertArrayOptionsToHtml(array_keys($this->_options), array_values($this->_options));
    }

    protected function convertArrayOptionsToHtml($keys, $values) {
        $merge = array_map(array($this, "mergeHtmlOptions"), $keys, $values);

        return implode(" ", $merge);
    }

    protected function mergeHtmlOptions($key, $value) {
        return "{$key}=\"{$value}\"";
    }

    public function build() {
        throw new LogicException(__METHOD__ . " method not implemented");
    }

    public function draw() {
        return $this->build()->getXhtml();
    }
}