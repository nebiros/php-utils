<?php

namespace Nebiros\PhpUtils\Filter;

/**
 *
 * @author nebiros
 */
abstract class Input {
    const APPEND = "append";
    const PREPEND = "prepend";
    const SEPARATOR = ", ";
    
    /**
     *
     * @var array
     */
    protected $_data = null;
    
    /**
     *
     * @var array
     */
    protected $_options = null;
    
    /**
     *
     * @var array
     */
    protected $_messages = null;
    
    /**
     *
     * @param array $data
     * @param array $options 
     */
    public function __construct(Array $data = null, Array $options = null) {
        $this->_data = $data;
        
        if (null !== $options) {
            $this->_options = $options;
        }
    }
    
    /**
     *
     * @return void
     */
    public function process() {}
    
    /**
     *
     * @return array
     */
    public function getData() {
        return $this->_data;
    }

    /**
     *
     * @param array $data
     * @return Nebiros\PhpUtils\Filter\Input 
     */
    public function setData(Array &$data) {
        $this->_data = $data;
        return $this;
    }
    
    /**
     *
     * @param array $data
     * @return Nebiros\PhpUtils\Filter\Input 
     */
    public function addData(Array &$data) {
        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }

    /**
     *
     * @param array $options
     * @return Nebiros\PhpUtils\Filter\Input 
     */
    public function setOptions(Array $options) {
        $this->_options = $options;
        return $this;
    }
    
    /**
     *
     * @param array $options
     * @return Nebiros\PhpUtils\Filter\Input 
     */
    public function addOptions(Array $options) {
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }
    
    /**
     *
     * @param string $key
     * @param mixed $default
     * @return mixed 
     */
    public function getOption($key, $default = null) {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }
        
        return $default;
    }
    
    /**
     *
     * @param string $key
     * @param mixed $value
     * @return Nebiros\PhpUtils\Filter\Input 
     */
    public function setOption($key, $value = null) {
        $this->_options[$key] = $value;
        return $this;
    }
    
    /**
     *
     * @return array
     */
    public function getMessages() {
        return $this->_messages;
    }
    
    /**
     *
     * @param array $options
     * @return string
     */
    public function getLineMessages(Array $options = array()) {
        $messages = $this->getMessages();

        if (empty($messages)) {
            return null;
        }

        if (empty($options["text"]) ) {
            $options["text"] = null;
        }

        if (empty($options["text_position"])) {
            $options["text_position"] = self::PREPEND;
        }

        if (empty($options["message_separator"])) {
            $options["message_separator"] = self::SEPARATOR;
        }

        $fieldMessages = array();

        foreach ($messages as $field => $rules) {
            $fieldMessages[] = implode($options["message_separator"], array_values($rules));
        }

        $message = implode($options["message_separator"], $fieldMessages);

        if (null !== $options["text"]) {
            switch ($options["text_position"]) {
                case self::APPEND:
                    $message = $message . " " . $options["text"];
                    break;

                case self::PREPEND:
                default:
                    $message = $options["text"] . " " . $message;
                    break;
            }
        }

        return $message;
    }    
    
    /**
     *
     * @param string $msg
     * @return Nebiros\PhpUtils\Filter\Input 
     */
    public function addMessage($msg) {
        $this->_messages[] = $msg;
        return $this;
    }
    
    /**
     *
     * @return bool
     */
    public function isValid() {
        $this->process();
        
        if (empty($this->_messages)) {
            return true;
        }
        
        return false;
    }
}