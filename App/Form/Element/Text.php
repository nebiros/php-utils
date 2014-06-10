<?php

class App_Form_Element_Text extends App_Form_Element_FormElementAbstract
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
            $this->_xhtml .= $this->implodeOptionsForHtml();
        }

        $this->_xhtml .= " />";

        return $this;
    }
}