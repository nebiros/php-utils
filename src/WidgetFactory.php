<?php

namespace Nebiros\PhpUtils;

/**
 *
 * @author nebiros
 */
class WidgetFactory {
    const TYPE_BANNERS_ID = 7;
    const TYPE_GOOGLE_ANALYTICS_ID = 8;
    const TYPE_GOOGLE_ANALYTICS_INTERNAL_ID = 10;
    const TYPE_REMOTE_URL = 9;
    
    const POSITION_UP = "up";
    const POSITION_DOWN = "down";    
    
    /**
     *
     * @param string $class
     * @param array $options
     * @return Nebiros\PhpUtils\WidgetFactory\WidgetAbstract|Nebiros\PhpUtils\WidgetFactory\WidgetInterface
     */
    public static function factory($class, Array $options = array()) {
        $klass = ucfirst($class);

        if (!class_exists($klass)) {
            throw new \InvalidArgumentException("Widget class '{$class}' not found");
        }

        $widget = new $klass($options);
        if (!$widget instanceof Nebiros\PhpUtils\WidgetFactory\WidgetInterface) {
            throw new \Exception("Widget class '{$class}' does not implements Nebiros\\PhpUtils\\WidgetFactory\\WidgetInterface");
        }

        return $widget;
    }    
}