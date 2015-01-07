<?php

namespace Nebiros\PhpUtils\CacheFactory;

class Memcache extends Nebiros\PhpUtils\CacheFactory\CacheAdapterAbstract 
{
    protected $_memcache = null;

    public function __construct(Array $options = null) {
        parent::__construct($options);

        $this->_memcache = new \Memcache();
        $this->_init();
    }

    protected function _init() {
        $server = $this->_options["server"];
        $port = $this->_options["port"];

        if (!$this->_memcache->connect($server, $port)) {
            throw new \Exception("Can't connect to memcache server with options (" . implode(", ", $this->_options) . ")");
        }
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
            throw new \InvalidArgumentException("Invalid key");
        }

        $this->_buildKey($key);
        $this->_memcache->set($this->_key,$data,$ttl);
    }

    /**
     *
     * @param string $key
     * @return mixed|false
     */
    public function load($key) {
        $this->_buildKey($key);
        $data = $this->_memcache->get($this->_key);

        return $data;
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function exist($key) {
        if ($this->load($key)) {
            return true;
        }

       return false;
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function delete($key) {
        $this->_buildKey($key);
        $this->_memcache->delete($this->_key);
    }

    public function flush() {
        $this->_memcache->flush();
    }
}