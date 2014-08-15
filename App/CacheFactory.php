<?php

/**
 *
 * @author nebiros
 */
class App_CacheFactory {
    /**
     *
     * @var App_CacheFactory
     */
    protected static $_instance = null; 

    /**
     *
     * @var App_CacheFactory_CacheAdapterInterface 
     */
    protected $_adapter = null;

    /**
     *
     * @var array
     */
    protected $_adapterOptions = array();

    /**
     *
     * @return App_CacheFactory
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
     * @return App_CacheFactory_CacheAdapterInterface
     */
    public static function getAdapter() {
        return self::$_instance->_adapter;
    }

    /**
     *
     * @param string $klass
     * @param array $adapterOptions
     * @return App_CacheFactory_CacheAdapterInterface
     */
    public static function cache($klass, Array $adapterOptions = null) {
        if (empty($klass)) {
            throw new InvalidArgumentException("Cache class type must be set");
        }

        self::getInstance();

        $klass = "App_CacheFactory_" . ucfirst(strtolower($klass));

        if (false === class_exists($klass)) {
            throw new Exception("Cache class '{$klass}' was not found");
        }

        $adapter = new $klass($adapterOptions);

        if (false === ($adapter instanceof App_CacheFactory_CacheAdapterInterface)) { 
            throw new Exception("Cache class '{$klass}' does not implement App_CacheFactory_CacheAdapterInterface");
        }

        self::$_instance->_adapter = $adapter;
        self::$_instance->_adapterOptions = $adapterOptions;

        return self::$_instance;
    }
}