<?php

/**
 *
 * @author nebiros
 */
interface App_CacheFactory_CacheAdapterInterface 
{
    public function __construct(Array $options = null);
    public function save($key, $data = null, $ttl = 3600);
    public function load($key);
    public function exist($key);
    public function delete($key);
    public function flush();
}