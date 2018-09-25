<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of ToInteger
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class ToInteger {
    
    /**
     *
     * Returns (int) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  string $value
     * @return int|mixed
     * @see Zend\Filter\ToInt
     */
    public static function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }
        
        $value = (string) $value;
        
        return (int) $value;
    }
    
}
