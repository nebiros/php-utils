<?php

namespace Nebiros\PhpUtils;

use Nebiros\PhpUtils\ConfigFactory\ConfigAdapterInterface;

defined("APPLICATION_ENV")
    || define("APPLICATION_ENV", (getenv("APPLICATION_ENV") ? getenv("APPLICATION_ENV") : "production"));

defined("APPLICATION_PATH")
    || define("APPLICATION_PATH", realpath(getcwd()));

/**
 * Nebiros\PhpUtils\ConfigFactory
 *
 * @author nebiros
 */
class ConfigFactory {
    /**
     *
     * @var Nebiros\PhpUtils\ConfigFactory
     */
    protected static $_instance = null;

    /**
     *
     * @var Nebiros\PhpUtils\ConfigFactory\ConfigAdapterInterface 
     */
    protected static $_configAdapter = null;

    /**
     *
     * @var array
     */
    protected static $_configAdapterOptions = null;
    
    /**
     *
     * @var array
     */
    protected static $_data = null;
    
    /**
     *
     * @return Nebiros\PhpUtils\ConfigFactory
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
     * @param string $klass
     * @param array $adapterOptions
     * @return Nebiros\PhpUtils\ConfigFactory
     */
    public static function config($klass, Array $adapterOptions = null) {
        if (empty($klass)) {
            throw new \InvalidArgumentException("Configuration class type must be set");
        }

        if (empty($adapterOptions["file"])) {
            throw new \InvalidArgumentException("Configuration file path must be set");
        }

        self::getInstance();

        $klass = "Nebiros\\PhpUtils\\ConfigFactory\\" . ucfirst(strtolower($klass));

        if (false === class_exists($klass)) {
            throw new \Exception("Configuration class '{$klass}' was not found");
        }

        $adapter = new $klass($adapterOptions);

        if (false === ($adapter instanceof ConfigAdapterInterface)) { 
            throw new \Exception("Configuration class '{$klass}' does not implement Nebiros\PhpUtils\ConfigFactory\ConfigAdapterInterface");
        }

        self::$_configAdapter = $adapter;
        self::$_configAdapterOptions = $adapterOptions;

        self::_read();

        return self::$_instance;
    }

    /**
     *
     * @return Nebiros\PhpUtils\ConfigFactory\ConfigAdapterInterface
     */
    public static function getAdapter() {
        return self::$_configAdapter;
    }

    /**
     *
     * @return array
     */
    public static function getAdapterOptions() {
        return self::$_configAdapterOptions;
    }
    
    /**
     *
     * @return array
     */
    public static function getData() {
        return self::$_data;
    }
    
    /**
     *
     * @return void
     */
    protected static function _read() {
        if (null === self::$_configAdapter) {
            throw new \Exception("Configuration adapter was not initialized");
        }

        if (empty(self::$_data)) {
            self::$_data = self::$_configAdapter->read();
        }

        if (isset(self::$_configAdapterOptions["refresh_data"]) && true === self::$_configAdapterOptions["refresh_data"]) {
            self::$_data = self::$_configAdapter->read();
        }
    }
}