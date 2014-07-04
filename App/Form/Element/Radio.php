<?php

class App_Form_Element_Radio extends App_Form_Element_FormElementAbstract
{
    public function __construct($name, Array $options = null) {
        parent::__construct($name, $options);
    }

    public function build() {
        $this->_xhtml = <<<XHTML
            <div id="div_{$this->_name}" class="form-group">
            <label>{$this->_options["element_label"]}</label>
XHTML;

        $o = $this->_options['options'];

        foreach ($o as $option) {
            $this->_xhtml .= <<<XHTML
                <div class="radio">
                    <input type="radio" name="{$this->_name}" value="{$option["element_option_id"]}">
                        {$option["element_option_label"]}
                    </input>
                </div>
XHTML;
        }

        $this->_xhtml .= " </div>";

        return $this;
    }

    public function isValid(Array $data) {
        //validate if name exists in array
        if(array_key_exists($this->_name, $data)){
            $optionsUser = $data[$this->_name];
            $key = array_key_exists($optionsUser, $this->_options['options']);
            if($key === false) {
                $this->_message = $this->_options["element_label"] . ", option no valid";
                return false;
            }

            $this->_value=array($this->_options['element_id'] => array($optionsUser));
        } 
        else 
        {
            //check if it is required
            if($this->_options['element_required']) {
                $this->_message = $this->_options["element_label"] . ", is required";
                return false;    
            }
        }
        return true;
    }
}