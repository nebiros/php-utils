<?php

/**
 * HMAC implementation using a file to validate credentials.
 * 
 * 'api_keys' example file:
 * <code>
 * my_auth_key:my_auth_secret
 * </code>
 * 
 * PHP validation:
 * <code>
 * <?php
 * 
 * $hmacAuth = new App_Hmac(dirname(__FILE__) . "/../../data/api_keys", $_GET);
 *
 * if (!$hmacAuth->isValid()) {
 *     header($_SERVER["SERVER_PROTOCOL"] . " 401 Unauthorized", true, 401);
 * }
 * </code>
 *
 * ObjC implementation:
 * <code>
 * NSString *authKey = @"my_auth_key";
 * NSString *authSecret = @"my_auth_secret";
 * NSString *params = [NSString URLQueryWithParameters:@{@"auth_version": @1.0, @"auth_key": authKey, @"auth_timestamp": [NSString stringWithFormat:@"%0.0f", ([NSDate date]).timeIntervalSince1970]}];
 * 
 * const char *cAuthSecret = [authSecret cStringUsingEncoding:NSASCIIStringEncoding];
 * const char *cParams = [params cStringUsingEncoding:NSASCIIStringEncoding];
 * 
 * unsigned char cHMAC[CC_SHA256_DIGEST_LENGTH];
 * CCHmac(kCCHmacAlgSHA256, cAuthSecret, strlen(cAuthSecret), cParams, strlen(cParams), cHMAC);
 * 
 * NSData *HMAC = [[NSData alloc] initWithBytes:cHMAC length:sizeof(cHMAC)];
 * NSString *hash = [HMAC base64EncodedStringWithOptions:0];
 * 
 * NSString *urlAsString = [NSString stringWithFormat:@"http://example.com/api/v1/get_vertical_menu?%@&auth_signature=%@", params, hash];
 * NSURL *url = [NSURL URLWithString:urlAsString];
 * 
 * NSURLRequest *req = [NSURLRequest requestWithURL:url];
 * NSURLResponse *resp;
 * NSError *error;
 * NSData *data = [NSURLConnection sendSynchronousRequest:req returningResponse:&resp error:&error];
 * 
 * if (error) {
 *     NSString *errorMessage = [NSString stringWithFormat:@"\n%@\n%@", [error localizedDescription], error.userInfo];
 *     NSLog(@"[ERROR] - %s: %@",
 *         __PRETTY_FUNCTION__,
 *         errorMessage);
 *         return 1;
 * }
 * 
 * NSLog(@"resp: %@", resp);
 * </code>
 */
class App_Hmac 
{
    protected $_hmacFile = null;

    protected $_message = null;

    protected $_authOptions = array(
        "algo" => "sha256",
        "auth_version_param_name" => "auth_version",
        "auth_key_param_name" => "auth_key",
        "auth_timestamp_param_name" => "auth_timestamp",
        "auth_signature_param_name" => "auth_signature"
    );

    public function __construct($hmacFile, $message, Array $authOptions = null) {
        $this->setHmacFile($hmacFile)
            ->setMessage($message)
            ->setDefaultAuthOptions();

        if ($authOptions !== null) {
            $this->setAuthOptions($authOptions);
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

        $tmp = array();

        if (is_string($message)) {            
            parse_str($message, $tmp);

            if (empty($tmp)) {
                if (false === ($tmp = base64_decode($message))) {
                    header("HTTP/1.1 403 Forbidden", true, 403);
                    throw new InvalidArgumentException("Cannot parse HMAC message");
                }
            }
        } else if (is_array($message)) {
            $tmp = $message;
        } else {
            throw new InvalidArgumentException("HMAC message is not valid");
        }

        $this->_message = $tmp;

        return $this;
    }

    public function getMessage() {
        return $this->_message;
    }

    /**
     *
     * @return array
     */
    public function setDefaultAuthOptions() {
        $this->_authOptions = array_merge($this->_authOptions, array(
            $this->_authOptions["auth_version_param_name"] => 1,
            $this->_authOptions["auth_timestamp_param_name"] => time(),
        ));

        return $this;
    }

    /**
     *
     * @param array $options
     * @return App_Hmac
     */
    public function setAuthOptions(Array $authOptions) {
        $this->_authOptions = array_merge($this->_authOptions, $authOptions);
        return $this;
    }

    public function getAuthOptions() {
        return $this->_authOptions;
    }

    /**
     *
     * @param int $timestampGrace
     * @return boolean
     */
    public function isValid($timestampGrace = 600) {
        $message = $this->_message;

        $this->_validateVersion();
        $this->_validateTimestamp($timestampGrace);

        if (!($authSecret = $this->_getAuthSecret($message[$this->_authOptions["auth_key_param_name"]]))) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new Exception("Auth secret not found");
        }

        return $this->_resolve($authSecret, $message);
    }

    /**
     * 
     * @param string $authKey
     * @return string|bool
     */
    protected function _getAuthSecret($authKey) {
        if (empty($authKey)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("Auth key is required");
        }

        $fp = @fopen($this->_hmacFile, "r");
        if (!$fp) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new Exception("Unable to open password file: {$this->_hmacFile}");
        }

        while (($line = @fgetcsv($fp, 0, ":")) !== false) {
            if ($line[0] == $authKey) {
                fclose($fp);
                return $line[1];
            }
        }

        @fclose($fp);
        return false;
    }

    protected function _validateVersion() {
        if ((int) $this->_message[$this->_authOptions["auth_version_param_name"]] !== $this->_authOptions[$this->_authOptions["auth_version_param_name"]]) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new Exception("Auth version is incorrect");
        }

        return true;
    }

    protected function _validateTimestamp($timestampGrace) {
        if ($timestampGrace == 0) {
            return true;
        }

        $difference = time() - (int) $this->_message[$this->_authOptions["auth_timestamp_param_name"]];
        if ($difference >= $timestampGrace) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new Exception("Auth timestamp is invalid");
        }

        return true;
    }

    /**
     * 
     * @param string $authSecret
     * @param Array $message
     * @return bool
     */
    protected function _resolve($authSecret, Array $message) {
        if (empty($authSecret)) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("Auth secret is required");
        }

        if (!isset($message[$this->_authOptions["auth_signature_param_name"]])) {
            header("HTTP/1.1 403 Forbidden", true, 403);
            throw new InvalidArgumentException("Signature key is required");
        }

        $params = $message;
        unset($params[$this->_authOptions["auth_signature_param_name"]]);        
        $params = http_build_query($message);

        $hash = hash_hmac($this->_authOptions["algo"], $params, $authSecret, true);
        $base64Hash = base64_encode($hash);

        return ($base64Hash == $message[$this->_authOptions["auth_signature_param_name"]]);
    }
}