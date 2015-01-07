<?php

namespace Nebiros\PhpUtils\Util;

/**
 *
 * @author nebiros
 */
class Arrays { 
    /**
     * 
     * @param Array $array
     * @return boolean
     * @see http://stackoverflow.com/a/4254008/255463
     */
    public static function isAssoc(Array $array) {
        return (bool) count(array_filter(array_keys($array), "is_string"));
    }
}
