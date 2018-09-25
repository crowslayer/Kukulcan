<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of UpperCaseWords
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class UpperCaseWords {
   /**
     * {@inheritDoc}
     *
     * Returns the string $value, converting words to have an uppercase first character as necessary
     *
     * If the value provided is not a string, the value will remain unfiltered
     *
     * @param  string|mixed $value
     * @return string|mixed
     */
    public static function filter($value , $encoding = null)
    {
        if (! is_string($value)) {
            return $value;
        }
        $value = (string) $value;
        if ($encoding !== null) {
            return mb_convert_case($value, MB_CASE_TITLE, $encoding);
        }
        return \ucwords(strtolower($value));
    }
}
