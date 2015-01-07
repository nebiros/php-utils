<?php

namespace Nebiros\PhpUtils\Form\Element;

abstract class FormElementAbstract implements Nebiros\PhpUtils\Form\Element\FormElementInterface
{
    protected $_name = null;
    protected $_options = null;
    protected $_xhtml = null;
    protected $_message = null;
    protected $_value = null;

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

    public function build() {
        throw new \LogicException(__METHOD__ . " method not implemented");
    }

    public function draw() {
        return $this->build()->getXhtml();
    }

    public function __toString() {
        return $this->draw();
    }

    public function getError() {
        return $this->_message;
    }

    public function isValid(Array $data) {
        throw new \LogicException(__METHOD__ . " method not implemented");
    }

    public function getValue() {
        return $this->_value;
    }

}