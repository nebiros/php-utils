<?php

namespace Nebiros\PhpUtils\Form\Element;

use Nebiros\PhpUtils\Form\Element\FormElementAbstract;

class Checkbox extends FormElementAbstract
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
                        <input type="checkbox" name="{$this->_name}[]" value="{$co["element_option_id"]}">
                        {$co["element_option_label"]}
                    </label>
                </div>
XHTML;
        }

        $this->_xhtml .= "</div>";

        return $this;
    }

    public function isValid(Array $data) {
        //validate if name exists in array
        if(array_key_exists($this->_name, $data)){
            $optionsUser = $data[$this->_name];
            foreach ($optionsUser as $ou) {
                $key = array_key_exists($ou, $this->_checkboxOptions);
                if($key === false) {
                    $this->_message = $this->_options["element_label"] . ", option no valid";
                    return false;
                }
            }

             $this->_value=array($this->_options['element_id']=> $optionsUser);
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