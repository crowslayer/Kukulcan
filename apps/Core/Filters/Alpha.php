<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of Alpha
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Alpha {

    protected static $_allowWhiteSpace = false;

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @var bool
     * */
    protected static $_hasPcreUnicodeSupport = null;

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns the string $value, removing all but alphabetic characters
     *
     * @param  string|array $value
     * @return string|array
     */
    public static function filter($value) {
        if (!is_scalar($value) && !is_array($value)) {
            return $value;
        }

        $whiteSpace = static::$_allowWhiteSpace ? '\s' : '';

        if (!static::hasPcreUnicodeSupport()) {
            // POSIX named classes are not supported, use alternative [a-zA-Z] match
            $pattern = '/[^a-zA-Z' . $whiteSpace . ']/';
        } else {
            // Use native language alphabet
            $pattern = '/[^\p{L}' . $whiteSpace . ']/u';
        }
        return preg_replace($pattern, '', $value);
    }

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @return bool
     */
    public static function hasPcreUnicodeSupport() {
        if (static::$_hasPcreUnicodeSupport === null) {
            
            static::$_hasPcreUnicodeSupport = defined('PREG_BAD_UTF8_OFFSET_ERROR') && preg_match('/\pL/u', 'a') == 1;
        }
        return static::$_hasPcreUnicodeSupport;
    }

}
