<?php

interface App_ConfigFactory_ConfigAdapterInterface {
    public function getOptions();
    public function read($file = null);
}