<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of ToNull
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class ToNull {
    const TYPE_BOOLEAN      = 1;
    const TYPE_INTEGER      = 2;
    const TYPE_EMPTY_ARRAY  = 4;
    const TYPE_STRING       = 8;
    const TYPE_ZERO_STRING  = 16;
    const TYPE_FLOAT        = 32;
    const TYPE_ALL          = 63;
    /**
     * @var array
     */
    protected $constants = [
        self::TYPE_BOOLEAN     => 'boolean',
        self::TYPE_INTEGER     => 'integer',
        self::TYPE_EMPTY_ARRAY => 'array',
        self::TYPE_STRING      => 'string',
        self::TYPE_ZERO_STRING => 'zero',
        self::TYPE_FLOAT       => 'float',
        self::TYPE_ALL         => 'all',
    ];
    
    /**
     * Returns null representation of $value, if value is empty and matches
     * types that should be considered null.
     *
     * @param  string $value
     * @return string
     * @see Zend\Filter\ToNull
     */
    public static function filter($value){
        $type = gettype($value);

        // FLOAT (0.0)
        if ($type & self::TYPE_FLOAT) {
            if (is_float($value) && ($value == 0.0)) {
                return;
            }
        }
        // STRING ZERO ('0')
        if ($type & self::TYPE_ZERO_STRING) {
            if (is_string($value) && ($value == '0')) {
                return;
            }
        }
        // STRING ('')
        if ($type & self::TYPE_STRING) {
            if (is_string($value) && ($value == '')) {
                return;
            }
        }
        // EMPTY_ARRAY (array())
        if ($type & self::TYPE_EMPTY_ARRAY) {
            if (is_array($value) && ($value == [])) {
                return;
            }
        }
        // INTEGER (0)
        if ($type & self::TYPE_INTEGER) {
            if (is_int($value) && ($value == 0)) {
                return;
            }
        }
        // BOOLEAN (false)
        if ($type & self::TYPE_BOOLEAN) {
            if (is_bool($value) && ($value == false)) {
                return;
            }
        }
        return $value;
    }

}
