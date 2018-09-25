<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of Boolean
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class Boolean {
   
    const TYPE_BOOLEAN        = 1;
    const TYPE_INTEGER        = 2;
    const TYPE_FLOAT          = 4;
    const TYPE_STRING         = 8;
    const TYPE_ZERO_STRING    = 16;
    const TYPE_EMPTY_ARRAY    = 32;
    const TYPE_NULL           = 64;
    const TYPE_PHP            = 127;
    const TYPE_FALSE_STRING   = 128;
    const TYPE_LOCALIZED      = 256;
    const TYPE_ALL            = 511;
    /**
     * @var array
     */
    protected static $constants = [
        self::TYPE_BOOLEAN       => 'boolean',
        self::TYPE_INTEGER       => 'integer',
        self::TYPE_FLOAT         => 'float',
        self::TYPE_STRING        => 'string',
        self::TYPE_ZERO_STRING   => 'zero',
        self::TYPE_EMPTY_ARRAY   => 'array',
        self::TYPE_NULL          => 'null',
        self::TYPE_PHP           => 'php',
        self::TYPE_FALSE_STRING  => 'false',
        self::TYPE_LOCALIZED     => 'localized',
        self::TYPE_ALL           => 'all',
    ];
    /**
     * Determina el tipo de dato empleado y arroja el valor
     * intero referente ala constante.
     * 
     * @param string $type
     * @return integer
     * @throws \InvalidArgumentException
     */
    public static function getType($value) {
        $type = gettype($value);
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected |= $value;
                } elseif (in_array($value, self::$constants)) {
                    $detected |= array_search($value, self::$constants);
                }
            }
            $type = $detected;
        } elseif (is_string($type) && in_array($type, self::$constants)) {
            $type = array_search($type, self::$constants);
        }
        
        if (!is_int($type) || ($type < 0) || ($type > self::TYPE_ALL)) {
            throw new \InvalidArgumentException(sprintf(
                    'Unknown type value "%s" (%s)', $type, gettype($type)
            ));
        }
        
        return $type;
    }
    
    /**
     * Returns a boolean representation of $value
     *
     * @param  string $value
     * @return string
     * @see Zend\Filter\Boolean
     */
    public static function filter($value){
        $type    = self::getType($value);
        $casting = true;
        
        // FALSE_STRING ('false')
        if ($type & self::TYPE_FALSE_STRING) {
            if (is_string($value) && (strtolower($value) == 'false')) {
                return false;
            }
            if (! $casting && is_string($value) && (strtolower($value) == 'true')) {
                return true;
            }
        }
        
        // NULL (null)
        if ($type & self::TYPE_NULL) {
            if ($value === null) {
                return false;
            }
        }
        // EMPTY_ARRAY (array())
        if ($type & self::TYPE_EMPTY_ARRAY) {
            if (is_array($value) && ($value == [])) {
                return false;
            }
        }
        // ZERO_STRING ('0')
        if ($type & self::TYPE_ZERO_STRING) {
            if (is_string($value) && ($value == '0')) {
                return false;
            }
            if (! $casting && (is_string($value)) && ($value == '1')) {
                return true;
            }
        }
        // STRING ('')
        if ($type & self::TYPE_STRING) {
            if (is_string($value) && ($value == '')) {
                return false;
            }
            if (is_string($value) && (strtolower($value) == 'false')) {
                return false;
            }
            if (is_string($value) && (strtolower($value) == 'true')) {
                return true;
            }
        }
        // FLOAT (0.0)
        if ($type & self::TYPE_FLOAT) {
            if (is_float($value) && ($value == 0.0)) {
                return false;
            }
            if (! $casting && is_float($value) && ($value == 1.0)) {
                return true;
            }
        }
        // INTEGER (0)
        if ($type & self::TYPE_INTEGER) {
            if (is_int($value) && ($value == 0)) {
                return false;
            }
            if (! $casting && is_int($value) && ($value == 1)) {
                return true;
            }
        }
        // BOOLEAN (false)
        if ($type & self::TYPE_BOOLEAN) {
            if (is_bool($value)) {
                return $value;
            }
        }
        
        return $value;
    }
    
}
