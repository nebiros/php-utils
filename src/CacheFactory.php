<?php

namespace Nebiros\PhpUtils;

use Nebiros\PhpUtils\CacheFactory\CacheAdapterInterface;

/**
 *
 * @author nebiros
 */
class CacheFactory {
    /**
     *
     * @var Nebiros\PhpUtils\CacheFactory
     */
    protected static $_instance = null; 

    /**
     *
     * @var Nebiros\PhpUtils\CacheFactory\CacheAdapterInterface 
     */
    protected $_adapter = null;

    /**
     *
     * @var array
     */
    protected $_adapterOptions = array();

    /**
     *
     * @return Nebiros\PhpUtils\CacheFactory
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function __construct() {}
    protected function __clone() {}

    /**
     *
     * @return Nebiros\PhpUtils\CacheFactory\CacheAdapterInterface
     */
    public static function getAdapter() {
        return self::$_instance->_adapter;
    }

    /**
     *
     * @param string $klass
     * @param array $adapterOptions
     * @return Nebiros\PhpUtils\CacheFactory\CacheAdapterInterface
     */
    public static function cache($klass, Array $adapterOptions = null) {
        if (empty($klass)) {
            throw new \InvalidArgumentException("Cache class type must be set");
        }

        self::getInstance();

        $klass = "Nebiros\\PhpUtils\\CacheFactory\\" . ucfirst(strtolower($klass));

        if (false === class_exists($klass)) {
            throw new \Exception("Cache class '{$klass}' was not found");
        }

        $adapter = new $klass($adapterOptions);

        if (false === ($adapter instanceof CacheAdapterInterface)) { 
            throw new \Exception("Cache class '{$klass}' does not implement Nebiros\PhpUtils\CacheFactory\CacheAdapterInterface");
        }

        self::$_instance->_adapter = $adapter;
        self::$_instance->_adapterOptions = $adapterOptions;

        return self::$_instance;
    }
}