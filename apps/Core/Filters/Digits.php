<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of Digits
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Digits {
    
    /**
     * Returns the string $value, removing all but digit characters
     *
     * If the value provided is not integer, float or string, the value will remain unfiltered
     *
     * @param  string $value
     * @return string|mixed
     * @see Zend\Filter\Digits
     */
    public static function filter($value)
    {
        if (is_int($value)) {
            return (string) $value;
        }
        
        if (! (is_float($value) || is_string($value))) {
            return $value;
        }
        
        $value = (string) $value;
        
        if (extension_loaded('mbstring')) {
            // Filter for the value with mbstring
            $pattern = '/[^[:digit:]]/';
        } else {
            // Filter for the value without mbstring
            $pattern = '/[\p{^N}]/';
        }
        return preg_replace($pattern, '', $value);
    }
    
}
