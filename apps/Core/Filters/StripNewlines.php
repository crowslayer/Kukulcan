<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of StripNewlines
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class StripNewlines {
    
    
    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns $value without newline control characters
     *
     * @param  string|array $value
     * @return string|array
     * @see Zend\Filter\StripNewlines
     */
    public static function filter($value){
        
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }
        
        return str_replace(["\n", "\r"], '', $value);
    }

}
