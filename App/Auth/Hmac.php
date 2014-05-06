<?php

class App_Auth_Hmac {
    protected $_hmacFile = null;
    protected $_message = null;

    public function __construct($hmacFile, $message) {
        $this->setHmacFile($hmacFile)
            ->setMessage($message);
    }

    public function setHmacFile($hmacFile) {
        $temp = realpath($hmacFile);

        if (empty($temp) || !is_readable($temp)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("HMAC file not readable, '{$hmacFile}'");
        }

        $this->_hmacFile = $temp;

        return $this;
    }

    public function getHmacFile() {
        return $this->_hmacFile;
    }

    public function setMessage($message) {
        if (empty($message)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("HMAC message is required");
        }

        $this->message = $message;

        return $this;
    }

    public function getMessage() {
        return $this->message;
    }

    public function authenticate() {
        // TODO: implement headers authentication (jfas).

        // jsonp.
        if (array_key_exists("callback", $_GET) && !empty($_GET["callback"])) {
            $message = base64_decode($this->message);
            $message = json_decode(stripslashes($message), TRUE);
            if (!($privateKey = $this->getPrivateKey($message["apiKey"]))) {
                header("HTTP/1.1 403 Forbidden", true, 403);
                throw new Exception("Private key not found");
            }

            return $this->resolve($privateKey, $message);
        }

        header("HTTP/1.1 403 Forbidden", true, 403);
        throw new Exception("Private key not found");
    }

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

        while (($line = fgetcsv($fp, 0, ":")) !== false) {
            if ($line[0] == $publicKey) {
                fclose($fp);
                return $line[1];
            }
        }

        fclose($fp);
        return false;
    }

    public function resolve($privateKey, $message) {
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
        $hash = hash_hmac("sha256", $params, $privateKey, true);
        $base64Hash = base64_encode($hash);

        return ($base64Hash == $message["sig"]);
    }
}