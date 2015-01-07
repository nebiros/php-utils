<?php

namespace Nebiros\PhpUtils\ConfigFactory;

interface ConfigAdapterInterface {
    public function getOptions();
    public function read($file = null);
}