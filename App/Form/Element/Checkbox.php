<?php

class App_Form_Element_Checkbox extends App_Form_Element_FormElementAbstract
{
    protected $_checkboxOptions = null;

    public function __construct($name, Array $options = null) {
        parent::__construct($name, $options);

        if (!empty($options["options"])) {
            $this->_checkboxOptions = $options["options"];
        }
    }

    public function build() {
        $this->_xhtml .= <<<XHTML
            <div class="form-group">
                <label>{$this->_options["element_label"]}</label>
XHTML;
        if (empty($this->_checkboxOptions)) {
            return $this;
        }

        foreach ($this->_checkboxOptions as $co) {
            $this->_xhtml .= <<<XHTML
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="{$this->_name}" value="{$co["element_option_id"]}">
                        {$co["element_option_label"]}
                    </label>
                </div>
XHTML;
        }

        $this->_xhtml .= "</div>";

        return $this;
    }
}