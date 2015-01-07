<?php

namespace Nebiros\PhpUtils\WidgetFactory;

/**
 *
 * @author nebiros
 */
interface WidgetInterface {
    /**
     * Ejecuta el widget y retorna un html.
     *
     * @return string
     */
    public function run();

    /**
     * Trae el la configuracion del widget.
     *
     * @return stdClass
     */
    public function getConfig();
}
