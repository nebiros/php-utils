<?php

namespace Nebiros\PhpUtils\Form\Element;

use Nebiros\PhpUtils\Form\Element\FormElementAbstract;
use Nebiros\PhpUtils\Form;

class Text extends FormElementAbstract
{
    public function __construct($name, Array $options = null) {
        parent::__construct($name, $options);
    }

    public function build() {
        $this->_xhtml = <<<XHTML
            <input type="text" name="{$this->_name}"
XHTML;

        $o = $this->_options;

        if (!empty($o)) {
            $this->_xhtml .= Form::implodeOptionsForHtml($this->_options);
        }

        $this->_xhtml .= " />";

        return $this;
    }
}