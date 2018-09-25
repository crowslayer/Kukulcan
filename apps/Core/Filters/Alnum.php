<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of Alnum
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Alnum extends Alpha {

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns $value as string with all non-alphanumeric characters removed
     *
     * @param  string|array $value
     * @return string|array
     */
    public static function filter($value) {
        if (!is_scalar($value) && !is_array($value)) {
            return $value;
        }
        /*
         * Comprobando si se aceptan spacios
         */
        $whiteSpace = static::$_allowWhiteSpace ? '\s' : '';

        if (!static::hasPcreUnicodeSupport()) {
            // POSIX named classes are not supported, use alternative a-zA-Z0-9 match
            $pattern = '/[^a-zA-Z0-9' . $whiteSpace . ']/';
        }else{
            // Use native language alphabet
            $pattern = '/[^\p{L}\p{N}' . $whiteSpace . ']/u';
        }

        return preg_replace($pattern, '', $value);
    }

}
