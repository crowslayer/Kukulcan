<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Kukulcan\Core\Filters;

/**
 * Description of StripTags
 *
 * @author Alexander Herrera <crowslayer@gmail.com>
 */
class StripTags {
    
    
    /**
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @todo   improve docblock descriptions
     * @param  string $value
     * @return string|mixed
     * @see Zend\Filter\StripTags
     */
    public static function filter($value) {

        if (!is_scalar($value)) {
            return $value;
        }

        $value = (string) $value;
        
        // Strip HTML comments first
        while (strpos($value, '<!--') !== false) {
            $pos = strrpos($value, '<!--');
            $start = substr($value, 0, $pos);
            $value = substr($value, $pos);
            // If there is no comment closing tag, strip whole text
            if (!preg_match('/--\s*>/s', $value)) {
                $value = '';
            } else {
                $value = preg_replace('/<(?:!(?:--[\s\S]*?--\s*)?(>))/s', '', $value);
            }
            $value = $start . $value;
        }
        // Initialize accumulator for filtered data
        $dataFiltered = '';
        // Parse the input data iteratively as regular pre-tag text followed by a
        // tag; either may be empty strings
        preg_match_all('/([^<]*)(<?[^>]*>?)/', (string) $value, $matches);
        // Iterate over each set of matches
        foreach ($matches[1] as $index => $preTag) {
            // If the pre-tag text is non-empty, strip any ">" characters from it
            if (strlen($preTag)) {
                $preTag = str_replace('>', '', $preTag);
            }
            // If a tag exists in this match, then filter the tag
            $tag = $matches[2][$index];
            if (strlen($tag)) {
                $tagFiltered = static::_filterTag($tag);
            } else {
                $tagFiltered = '';
            }
            // Add the filtered pre-tag text and filtered tag to the data buffer
            $dataFiltered .= $preTag . $tagFiltered;
        }
        // Return the filtered data
        return $dataFiltered;
    }

    /**
     * Filters a single tag against the current option settings
     *
     * @param  string $tag
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected static function _filterTag($tag) {
        // @codingStandardsIgnoreEnd
        // Parse the tag into:
        // 1. a starting delimiter (mandatory)
        // 2. a tag name (if available)
        // 3. a string of attributes (if available)
        // 4. an ending delimiter (if available)
        $isMatch = preg_match('~(</?)(\w*)((/(?!>)|[^/>])*)(/?>)~', $tag, $matches);
        // If the tag does not match, then strip the tag entirely
        if (!$isMatch) {
            return '';
        }
        // Save the matches to more meaningfully named variables
        $tagStart = $matches[1];
        $tagName = strtolower($matches[2]);
        $tagAttributes = $matches[3];
        $tagEnd = $matches[5];
        // If the tag is not an allowed tag, then remove the tag entirely
        if (!isset($this->tagsAllowed[$tagName])) {
            return '';
        }
        // Trim the attribute string of whitespace at the ends
        $tagAttributes = trim($tagAttributes);
        // If there are non-whitespace characters in the attribute string
        if (strlen($tagAttributes)) {
            // Parse iteratively for well-formed attributes
            preg_match_all('/([\w-]+)\s*=\s*(?:(")(.*?)"|(\')(.*?)\')/s', $tagAttributes, $matches);
            // Initialize valid attribute accumulator
            $tagAttributes = '';
            // Iterate over each matched attribute
            foreach ($matches[1] as $index => $attributeName) {
                $attributeName = strtolower($attributeName);
                $attributeDelimiter = empty($matches[2][$index]) ? $matches[4][$index] : $matches[2][$index];
                $attributeValue = (strlen($matches[3][$index]) == 0) ? $matches[5][$index] : $matches[3][$index];
                // If the attribute is not allowed, then remove it entirely
                if (!array_key_exists($attributeName, $this->tagsAllowed[$tagName]) && !array_key_exists($attributeName, $this->attributesAllowed)) {
                    continue;
                }
                // Add the attribute to the accumulator
                $tagAttributes .= " $attributeName=" . $attributeDelimiter
                        . $attributeValue . $attributeDelimiter;
            }
        }
        // Reconstruct tags ending with "/>" as backwards-compatible XHTML tag
        if (strpos($tagEnd, '/') !== false) {
            $tagEnd = " $tagEnd";
        }
        // Return the filtered tag
        return $tagStart . $tagName . $tagAttributes . $tagEnd;
    }

}
