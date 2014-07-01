<?php

class App_CacheFactory_Memcached extends App_CacheFactory_CacheAdapterAbstract 
{
    protected $_memcached = null;

    public function __construct(Array $options = null) {
        parent::__construct($options);

        $this->_memcached = new Memcached();
        $this->_init();
    }

    protected function _init() {
        if (isset($this->_options["server"])) {
            return $this->_memcached->addServer($this->_options["server"], 
                $this->_options["port"], 
                (!empty($this->_options["weight"])) ? $this->_options["weight"] : 0);
        }

        if (isset($this->_options["servers"])) {
            return $this->_memcached->addServers($this->_options["servers"]);
        }

        throw new Exception("Can't connect to memcache server with options (" . implode(", ", $this->_options) . ")");
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
            throw new InvalidArgumentException("Invalid key");
        }

        $this->_buildKey($key);
        return $this->_memcached->set($this->_key, $data, $ttl);
    }

    /**
     *
     * @param string $key
     * @return mixed|false
     */
    public function load($key) {
        $this->_buildKey($key);
        $data = $this->_memcached->get($this->_key);

        return $data;
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function exist($key) {
        if (($this->load($key))) {
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
        return $this->_memcached->delete($this->_key);
    }
}