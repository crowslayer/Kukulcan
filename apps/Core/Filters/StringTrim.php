<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of StringTrim
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class StringTrim {
    protected $_options;
    
    /**
     * Returns the string $value with characters stripped from the beginning and end
     *
     * @param  string $value
     * @return string
     * @see Zend\Filter\StringTrim
     */
    public static function filter($value){
        
        if (! is_string($value)) {
            return $value;
        }
        
        $value = (string) $value;
        
        
        return static::_unicodeTrim($value);
    }
    /**
     * Unicode aware trim method
     * Fixes a PHP problem
     *
     * @param string $value
     * @param string $charlist
     * @return string
     */
    protected static function _unicodeTrim($value, $charlist = '\\\\s'){
        $chars = preg_replace(
            ['/[\^\-\]\\\]/S', '/\\\{4}/S', '/\//'],
            ['\\\\\\0', '\\', '\/'],
            $charlist
        );
        
        $pattern = '/^[' . $chars . ']+|[' . $chars . ']+$/usSD';
        
        return preg_replace($pattern, '', $value);
    }
    
}
