<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;
/**
 * Description of DateTimeFormatter
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class DateTimeFormatter {
   
    /**
     * Filter a datetime string by normalizing it to the filters specified format
     *
     * @param  DateTime|string|integer $value
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public static function filter($value, $format){
        
        if ($value === '' || $value === null) {
            return $value;
        }
        
        if (!is_string($value) && !is_int($value) && !$value instanceof DateTime) {
            return $value;
        }
        
        if (is_int($value)) {
            //timestamp
            $value = new \DateTime('@' . $value);
        } elseif (!$value instanceof DateTime) {
            $value = new \DateTime($value);
        }
        return $value->format($format);
    }
}
