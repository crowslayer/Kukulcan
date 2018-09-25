<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;


/**
 * Description of StringToUpper
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class StringToUpper {
    protected $_encoding;
    
    public function __construct($encoding = null) {
        $this->_encoding = $encoding;
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Returns the string $value, converting characters to uppercase as necessary
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  string $value
     * @return string|mixed
     * @see Zend\Filter\StringToUpper
     */
    public static function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }
        
        $value = (string) $value;
        
//        if (null !== $this->getEncoding()) {
//            return mb_strtoupper($value, $this->options['encoding']);
//        }
        $valueFilter = filter_var($value, FILTER_SANITIZE_STRING);
        $valueFilter = StringTrim::filter($valueFilter);
        
        return strtoupper($valueFilter);
    }
}
