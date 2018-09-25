<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of StringTruncate
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class StringTruncate {
    /**
     * Reduce el string al numero de caracteres especificado.
     * 
     * @param string $string
     * @param integer $maxLength
     * @param strint $suffix
     * @return string
     */
    public static function filter($string , $maxLength = 50, $suffix = '...'){
        
        $length = \strlen(\trim($string));
        
        $string = StripTags::filter($string);
        $string = StringTrim::filter($string);
        
        if($length > $maxLength){
            $newMax = $maxLength - strlen($suffix);
            
            return substr($string, 0,$newMax).$suffix;
        }
        
        return $string;
    }
}
