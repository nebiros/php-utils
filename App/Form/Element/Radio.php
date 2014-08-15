<?php

class App_Form_Element_Radio extends App_Form_Element_FormElementAbstract
{
    public function __construct($name, Array $options = null) {
        parent::__construct($name, $options);
    }

    public function build() {
        $value = "";
        if (is_string($this->_options["value"])) {
            $value = $this->_options["value"];
        }

        $this->_xhtml = <<<XHTML
            <div id="div_{$this->_name}" class="form-group">
            <label class="control-label">{$this->_options["element_label"]}</label>
XHTML;

        $o = $this->_options['options'];

        $class = "";
        if (isset($this->_options['container-cls'])) {
            $class = $this->_options['container-cls'];
        }
        $is_inline = false;

        if (isset($this->_options['is_inline'])) {
            $is_inline = $this->_options['is_inline'];
        } 

        if ($is_inline) {
                $this->_xhtml .= '<div class="'. $class .'">';    
        }
        foreach ($o as $option) {
            if (!$is_inline) {
                $this->_xhtml .= '<div class="'. $class .'">';    
            }
            $checked = $value == $option["element_option_id"]? "checked" : "";
            $this->_xhtml .= <<<XHTML
                    <input type="radio" name="{$this->_name}" value="{$option["element_option_id"]}" {$checked}>
                        {$option["element_option_label"]}
                    </input>
XHTML;
            if (!$is_inline) {
                $this->_xhtml .= '</div>';
            }
            
        }
        if ($is_inline) {
                $this->_xhtml .= '</div>';
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