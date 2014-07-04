<?php

class App_Form_Element_Text extends App_Form_Element_FormElementAbstract
{
    public function __construct($name, Array $options = null) {
        parent::__construct($name, $options);
    }

    public function build() {

        $value = "";
        if (is_string($this->_options["value"])) {
            $value = $this->_options["value"];
        }
        
        $type = "text";
        if (is_string($this->_options["type"])) {
            $type = $this->_options["type"];
        }


        $this->_xhtml = <<<XHTML
            <div class="form-group">
                    <label class="control-label">{$this->_options[element_label]}</label>
                    <input name="{$this->_name}" type="{$type}" class="form-control" value="{$value}" />
            </div>
XHTML;

        return $this;
    }
}