<?php

class App_Hmac 
{
    protected $_hmacFile = null;
    protected $_message = null;
    protected $_options = array(
        "algo" => "sha256",
        "auth_version_param_name" => "auth_version",
        "auth_key_param_name" => "auth_key",
        "auth_timestamp_param_name" => "auth_timestamp",
        "auth_signature_param_name" => "auth_signature"
    );

    public function __construct($hmacFile, $message, Array $options = null) {
        $this->setHmacFile($hmacFile)
            ->setMessage($message);

        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * 
     * @param string $hmacFile
     * @return App_Hmac
     */
    public function setHmacFile($hmacFile) {
        $tmp = realpath($hmacFile);

        if (empty($tmp) || !is_readable($tmp)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("HMAC file not readable, '{$hmacFile}'");
        }

        $this->_hmacFile = $tmp;

        return $this;
    }

    public function getHmacFile() {
        return $this->_hmacFile;
    }

    /**
     * 
     * @param string $message
     * @return App_Hmac
     */
    public function setMessage($message) {
        if (empty($message)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("HMAC message is required");
        }

        $this->_message = $message;

        return $this;
    }

    public function getMessage() {
        return $this->_message;
    }

    /**
     *
     * @param array $options
     * @return App_Hmac
     */
    public function setOptions(Array $options) {
        $this->_options = array_merge($this->_options, $options);
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
     * @return boolean
     */
    public function isValid() {
        $message = base64_decode($this->_message);
        $message = json_decode(stripslashes($message), TRUE);
        if (!($privateKey = $this->getPrivateKey($message[$this->_options["auth_key_param_name"]]))) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new Exception("Private key not found");
        }

        return $this->resolve($privateKey, $message);
    }

    /**
     * 
     * @param string $publicKey
     * @return string|bool
     */
    public function getPrivateKey($publicKey) {
        if (empty($publicKey)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("Public key is required");
        }

        $fp = @fopen($this->_hmacFile, "r");
        if (!$fp) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new Exception("Unable to open password file: {$this->_hmacFile}");
        }

        while (($line = @fgetcsv($fp, 0, ":")) !== false) {
            if ($line[0] == $publicKey) {
                fclose($fp);
                return $line[1];
            }
        }

        @fclose($fp);
        return false;
    }

    /**
     * 
     * @param string $privateKey
     * @param Array $message
     * @return bool
     */
    public function resolve($privateKey, Array $message) {
        if (empty($privateKey)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("Private key is required");
        }

        if (!array_key_exists("params", $message)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("Params key is required");
        }

        if (!array_key_exists("sig", $message)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("Signature key is required");
        }

        $params = $message["params"];
        $hash = hash_hmac($this->_options["algo"], $params, $privateKey, true);
        $base64Hash = base64_encode($hash);

        return ($base64Hash == $message[$this->_options["auth_signature_param_name"]]);
    }
}