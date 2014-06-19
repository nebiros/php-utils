<?php

defined("APPLICATION_ENV")
    || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));

defined("APPLICATION_PATH")
    || define("APPLICATION_PATH", realpath(getcwd()));

/**
 * App_ConfigFactory
 *
 * @author nebiros
 */
class App_ConfigFactory {
    /**
     *
     * @var App_ConfigFactory
     */
    protected static $_instance = null;   
    
    /**
     *
     * @var App_Cache
     */
    protected static $_cache = null;
    
    /**
     *
     * @var string
     */
    protected static $_cacheKey = null;    
    
    /**
     *
     * @var array
     */
    protected static $_data = null;

    /**
     *
     * @var App_ConfigFactory_ConfigAdapterInterface 
     */
    protected static $_adapter = null;

    /**
     *
     * @var array
     */
    protected static $_adapterOptions = array();

    /**
     *
     * @var array
     */
    protected static $_cacheOptions = array();
    
    /**
     *
     * @return App_ConfigFactory
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->_initialize();
        }

        return self::$_instance;
    }

    protected function __construct() {}
    protected function __clone() {}
    
    /**
     * 
     * @return void
     */
    protected function _initialize() {
        self::$_instance->setCache();
    }

    /**
     *
     * @param string $class
     * @param array $adapterOptions
     * @param array $cacheOptions
     * @return App_ConfigFactory
     */
    public static function cache($class, Array $adapterOptions = array(), Array $cacheOptions = array()) {
        if (empty($class)) {
            throw new InvalidArgumentException("Configuration class type must be set");
        }

        self::getInstance();

        $class = "App_ConfigFactory_" . ucfirst($class);

        if (false === class_exists($class)) {
            throw new Exception("Configuration class '{$class}' was not found");
        }

        $adapter = new $class($adapterOptions);

        if (false === ($adapter instanceof App_ConfigFactory_ConfigAdapterInterface)) { 
            throw new Exception("Configuration class '{$class}' does not implement App_ConfigFactory_ConfigAdapterInterface");
        }

        self::$_adapter = $adapter;
        self::$_adapterOptions = $adapterOptions;
        self::$_cacheOptions = $cacheOptions;

        self::_cache($cacheOptions);

        return self::$_instance;
    }
    
    /**
     *
     * @return App_ConfigFactory
     */
    public static function setCache() {
        self::$_cache = new App_Cache();
        return self::$_instance;
    }
    
    /**
     *
     * @return App_Cache 
     */
    public static function getCache() {
        return self::$_cache;
    }
    
    /**
     *
     * @return array
     */
    public static function getConfig() {
        return self::_cache(self::$_cacheOptions);
    }

    /**
     *
     * @return App_ConfigFactory_ConfigAdapterInterface
     */
    public static function getAdapter() {
        return self::$_adapter;
    }
    
    /**
     *
     * @param array $cacheOptions
     * @return array 
     */
    protected static function _cache(Array $cacheOptions = array()) {
        if (null === self::$_adapter) {
            throw new InvalidArgumentException("Configuration adapter was not initialized");
        }

        if (empty($cacheOptions["ttl"])) {
            $cacheOptions["ttl"] = 604800;
        }

        if (!empty($cacheOptions["dir"])) {
            self::$_cache->setDir($cacheOptions["dir"]);
        }

        self::$_cacheKey = "config_" . APPLICATION_ENV;

        if (false === ($data = self::$_cache->load(self::$_cacheKey))) {
            $data = self::$_adapter->read($file);
            self::$_cache->save(self::$_cacheKey, $data, $cacheOptions["ttl"]); // 1 week.
        }
        
        return $data;
    }
}