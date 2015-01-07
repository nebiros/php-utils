<?php

namespace Nebiros\PhpUtils\Form\Element;

class Select extends Nebiros\PhpUtils\Form\Element\FormElementAbstract
{
    public function __construct($name, Array $options = null) {
        parent::__construct($name, $options);
    }

    public function build() {
        $this->_xhtml = <<<XHTML
            <div id="div_{$this->_name}" class="form-group">
            <label>{$this->_options["element_label"]}</label>
            <br/>
            <select class="form-control" name="{$this->_name}">
                <option>––</option>
XHTML;

        $o = $this->_options['options'];

        foreach($o as $option)
        {
            $this->_xhtml .= <<<XHTML
                    <option value="{$option["element_option_id"]}">
                        {$option["element_option_label"]}
                    </option>
XHTML;
        }

        $this->_xhtml .= " </select></div>";

        return $this;
    }

    public function isValid(Array $data) {
        //validate if name exists in array
        if(array_key_exists($this->_name, $data)){
            $optionsUser = $data[$this->_name];

            foreach ($optionsUser as $ou) {
                $key = array_key_exists($ou, $this->_options['options']);
                if($key === false) {
                    $this->_message = $this->_options["element_label"] . ", option no valid";
                    return false;
                }
            }

             $this->_value=array($this->_options['element_id']=> array($optionsUser));
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