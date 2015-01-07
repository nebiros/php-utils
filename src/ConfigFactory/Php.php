<?php

namespace Nebiros\PhpUtils\ConfigFactory;

use Nebiros\PhpUtils\ConfigFactory\ConfigAdapterInterface;

/**
 * Nebiros\PhpUtils\ConfigFactory\Php
 *
 * @author nebiros
 */
class Php implements ConfigAdapterInterface
{
    /**
     *
     * @var array
     */
    protected $_options = null;

    /**
     *
     * @param array $options
     */
    public function __construct($options = null) {
        $this->_options = $options;
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
     * @param string $file
     * @return array
     * @throws \Exception 
     */
    public function read($file = null) {
        if (empty($file)) {
            $file = $this->_options["file"];
        }

        $tmp = $file;
        $file = realpath($tmp);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (!is_file($file)) {
            throw new \InvalidArgumentException("File '{$tmp}' not found");
        }

        if ($ext != "php") {
            throw new \Exception("File '{$tmp}' must be a PHP file");
        }

        $php = require $file;
        if (empty($php)) {
            throw new \Exception("There's not an explicit return in '{$tmp}'");
        }

        $section = APPLICATION_ENV;
        if (isset($this->_options["section"]) && !empty($this->_options["section"])) {
            $section = $this->_options["section"];
        }

        $env = $php[$section];
        if (empty($env)) {
            throw new \Exception("Section '" . $section . "' not found in '{$tmp}'");
        }
        
        return $env;
    }
}
