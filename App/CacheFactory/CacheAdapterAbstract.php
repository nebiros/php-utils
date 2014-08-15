<?php

/**
 *
 * @author nebiros
 */
class App_CacheFactory_CacheAdapterAbstract implements App_CacheFactory_CacheAdapterInterface 
{
/**
     *
     * @var string
     */
    protected $_key = null;

    protected $_options = null;

    public function __construct(Array $options = null) {
        if ($options != null) {
            $this->_options = $options;
        }
    }

    /**
     *
     * @return string
     */
    public function getKey() {
        return $this->_key;
    }

    public function getOptions() {
        return $this->_options;
    }

    /**
     *
     * @param string $key
     * @param mixed $data
     * @param int $ttl Cache TTL in seconds.
     * @return boolean
     */
    public function save($key, $data = null, $ttl = 3600) {
        throw new LogicException(__METHOD__ . " method not implemented");
    }

    /**
     *
     * @param string $key
     * @return mixed|false
     */
    public function load($key){
        throw new LogicException(__METHOD__ . " method not implemented");
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function exist($key) {
        throw new LogicException(__METHOD__ . " method not implemented");
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public function delete($key) {
        throw new LogicException(__METHOD__ . " method not implemented");
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
}