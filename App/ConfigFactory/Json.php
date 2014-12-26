<?php

/**
 * App_ConfigFactory_Json
 *
 * @author nebiros
 */
class App_ConfigFactory_Json implements App_ConfigFactory_ConfigAdapterInterface {
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
     * @throws Exception 
     */
    public function read($file = null) {
        if (empty($file)) {
            $file = $this->_options["file"];
        }

        $tmp = $file;
        $file = realpath($tmp);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (!is_file($file)) {
            throw new InvalidArgumentException("File '{$tmp}' not found");
        }

        if ($ext != "json") {
            throw new Exception("File '{$tmp}' must be a JSON file");
        }

        $json = json_decode(file_get_contents($file), true);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = "";
                break;
            case JSON_ERROR_DEPTH:
                $error = "Maximum stack depth exceeded";
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = "Underflow or the modes mismatch";
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = "Unexpected control character found";
                break;
            case JSON_ERROR_SYNTAX:
                $error = "Syntax error, malformed JSON";
                break;
            case JSON_ERROR_UTF8:
                $error = "Malformed UTF-8 characters, possibly incorrectly encoded";
                break;
            default:
                $error = "Unknown error";
                break;
        }

        if (!empty($error)) {
            throw new Exception("JSON Error: {$error}");
        }

        $section = $this->_options["section"];
        if (empty($section)) {
            $section = APPLICATION_ENV;
        }

        $env = $php[$section];
        if (empty($env)) {
            throw new Exception("Section '" . $section . "' not found in '{$tmp}'");
        }
        
        return $env;
    }
}