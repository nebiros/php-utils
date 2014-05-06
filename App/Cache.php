<?php

/**
 * Based on FileCache class by Erik Giberti,
 * http://af-design.com/blog/2010/07/30/simple-file-based-caching-in-php/
 *
 * @author nebiros
 */
class App_Cache {
    /**
     *
     * @var string
     */
    protected $_dir = null;

    /**
     *
     * @var string
     */
    protected $_key = null;

    /**
     *
     * @param string $dir
     */
    public function __construct($dir = null) {
        $this->setDir($dir);
    }

    /**
     *
     * @param string $dir
     * @return App_Cache
     */
    public function setDir($dir = null) {
        $dir = realpath($dir);

        if (!is_dir($dir)) {
            $dir = sys_get_temp_dir();
        }

        $this->_dir = $dir;
        return $this;
    }

    /**
     *
     * @param string $key
     * @param mixed $data
     * @param int $ttl Cache TTL in seconds.
     * @return boolean
     */
    public function save($key, $data = null, $ttl = 3600) {
        if (true === empty($key)) {
            throw new Exception("invalid key");
        }

        $dataPath = $this->_buildPath($key);
        $metaPath = $this->_buildPath($key . "_meta");

        $metadata = array("ttl" => time() + $ttl);

        if (false === $this->_put($metaPath, $metadata)) {
            return false;
        }
       
        return $this->_put($dataPath, $data);
    }
    
    /**
     *
     * @param string $path
     * @param mixed $data
     * @return boolean 
     */
    protected function _put($path, $data) {
        $status = false;

        $fh = @fopen($path, "ab+");

        if (@flock($fh, LOCK_EX)) {             
            fseek($fh, 0);
            ftruncate($fh, 0);
            $tmp = @fwrite($fh, serialize($data));
            if (false !== $tmp) {
                $status = true;
            }
            @flock($fh, LOCK_UN);            
        }

        @fclose($fh);

        return $status;        
    }

    /**
     *
     * @param string $key
     * @return mixed|false
     */
    public function load($key){
        if (true === empty($key)) {
            throw new Exception("invalid key");
        }

        $dataPath = $this->_buildPath($key);

        if (false === is_file($dataPath)) {
            return false;
        }
        
        $data = $this->_get($dataPath);
        
        $metaPath = $this->_buildPath($key . "_meta");
        $metadata = $this->_get($metaPath);
        
        if (false === empty($data)) {
            if ($metadata["ttl"] < time()) {
                @unlink($path);
                return false;
            }
        }        
        
        return $data;
    }
    
    /**
     *
     * @param string $path
     * @return mixed 
     */
    protected function _get($path) {
        $data = null;

        $fh = @fopen($path, "rb");

        if (@flock($fh, LOCK_SH)) {
            $data = stream_get_contents($fh);
        }

        @fclose($fh);

        return @unserialize($data);
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function exist($key) {
        if (true === empty($key)) {
            throw new Exception("invalid key");
        }

        $path = $this->_buildPath($key);

        return is_file($path);
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function delete($key) {
        if (true === empty($key)) {
            throw new Exception("invalid key");
        }

        $dataPath = $this->_buildPath($key);
        $metaPath = $this->_buildPath($key . "_meta");

        if (true === is_file($dataPath)) {
            @unlink($dataPath);
            @unlink($metaPath);
        }

        return true;
    }

    /**
     *
     * @param string $key
     * @param bool $lowercase
     * @param string $glue
     * @return void
     */
    protected function _buildKey($key, $lowercase = true, $glue = "-") {
        if (true === $lowercase) {
            $key = strtolower($key);
        }

        $this->_key = preg_replace("/[^a-zA-Z0-9_-]/", "", preg_replace("/\s+/", $glue, $key));
    }

    /**
     *
     * @return string
     */
    public function getKey() {
        return $this->_key;
    }

    /**
     *
     * @param string $key
     * @return string
     */
    protected function _buildPath($key) {
        $this->_buildKey($key);
        return $this->_dir . "/" . $this->_key;
    }
}