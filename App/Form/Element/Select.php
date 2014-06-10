<?php

class App_Form_Element_Select extends App_Form_Element_FormElementAbstract
{
    public function __construct($name, Array $options = null) {
        parent::__construct($name, $options);
    }

    public function build() {
        $this->_xhtml = <<<XHTML
            <div id="div_{$this->_name}" class="form-group">
            <label>{$this->_options[element_label]}</label>
            <br/>
            <select class="form-control" name="{$this->_name}">
                <option>––</option>
XHTML;

        $o = $this->_options['options'];

        foreach($o as $option)
        {
            $this->_xhtml .= <<<XHTML
                    <option value="{$option[element_option_id]}">
                        {$option[element_option_label]}
                    </option>
XHTML;
        }

        $this->_xhtml .= " </select></div>";

        return $this;
    }
}