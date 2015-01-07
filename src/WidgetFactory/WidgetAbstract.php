<?php

namespace Nebiros\PhpUtils\WidgetFactory;

/**
 *
 * @author nebiros
 */
abstract class WidgetAbstract implements Nebiros\PhpUtils\WidgetFactory\WidgetInterface {    
    /**
     *
     * @var array
     */
    protected $_options = array();

    /**
     *
     * @var array
     */
    protected $_config = null;
    
    /**
     *
     * @var App_Cache
     */
    protected $_cache = null;

    /**
     *
     * @param array $options 
     */
    public function  __construct(Array $options) {
        if (!isset($options["cache"]) || empty($options["cache"])) {
            throw new \InvalidArgumentException("Cache object must be set");            
        }

        $this->setOptions($options);

        $this->_cache = $options["cache"];
        $this->_config = $this->getConfig();
    }
    
    /**
     * Set option.
     *
     * @param mixed $key
     * @param mixed $value
     * @return App_WidgetFactory_WidgetAbstract
     */
    public function setOption($key, $value = null) {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * Get option.
     *
     * @param mixed $key
     * @param null|mixed $default
     * @return mixed
     */
    public function getOption($key, $default = null) {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }

        return $default;
    }

    /**
     * Reset to default options.
     *
     * @return App_WidgetFactory_WidgetAbstract
     */
    public function clearOptions() {
        $this->_options = array();
        return $this;
    }

    /**
     * Set options.
     *
     * @param array $options
     * @return App_WidgetFactory_WidgetAbstract
     */
    public function setOptions(Array $options) {
        $this->_options = array_merge($this->_options, $options);
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getOptions() {
        return $this->_options;
    }

    /**
     * Ejecuta el widget y retorna un html. Esta es la implementacion
     * por defecto.
     *
     * @return string
     */
    public function run() {
        throw new \Exception(__METHOD__ . " not implemented");
    }

    /**
     * Trae el la configuracion del widget.
     *
     * @return stdClass
     */
    public function getConfig() {
        if (null === $this->getOption("config")) {
            return null;
        }
        
        return json_decode($this->getOption("config"), true);
    }
}
