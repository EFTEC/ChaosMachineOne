<?php

namespace mapache_commons;

/**
 * Class Text
 *
 * @package   mapache_commons
 * @version   1.14 2020-feb-12
 * @copyright Jorge Castro Castillo
 * @license   Apache-2.0
 * @see       https://github.com/EFTEC/mapache-commons
 */
class Text {

    /**
     * Returns true if the str is (completelly) uppercase
     *
     * @param string $str Input text
     *
     * @return bool true if the text is uppercase, otherwise false
     * @see https://stackoverflow.com/questions/4211875/check-if-a-string-is-all-caps-in-php
     */
    public static function isUpper($str) {
        return strtoupper($str) == $str;
    }

    /**
     * Returns true if the str is (completelly) lowercase
     *
     * @param $str
     *
     * @return bool
     * @see https://stackoverflow.com/questions/25340288/php-function-to-check-string-if-is-all-lowercase
     */
    public static function isLower($str) {
        return strtolower($str) == $str;
    }

    /**
     * Obtain a string between one text and other.
     * Example: between('mary has a lamb','has','lamb') // returns ' a '
     *
     * @param string   $haystack
     * @param string   $startNeedle The initial text to search<br />
     *                              if empty then it starts at the start of the haystack.
     * @param string   $endNeedle   The end tag to search<br />
     *                              if empty then it ends at the end of the haystack
     * @param null|int $offset
     * @param bool     $ignoreCase
     *
     * @return bool|string
     */
    public static function between($haystack, $startNeedle, $endNeedle, &$offset = 0, $ignoreCase = false) {
        if ($startNeedle === '') {
            $ini = $offset;
        } else {
            $ini = ($ignoreCase) ? @stripos($haystack, $startNeedle, $offset)
                : @strpos($haystack, $startNeedle, $offset);
        }
        if ($ini === false) {
            return false;
        }
        $ini += strlen($startNeedle);
        if ($endNeedle === '') {
            $len = strlen($haystack);
        } else {
            $len = (($ignoreCase) ? stripos($haystack, $endNeedle, $ini) : strpos($haystack, $endNeedle, $ini));
            if ($len === false) {
                return false;
            }
            $len -= $ini;
        }
        $offset = $ini + $len;
        return substr($haystack, $ini, $len);
    }

    /**
     * Strip quotes of a text (" or ')
     *
     * @param $text
     *
     * @return bool|string
     */
    public static function stripQuotes($text) {
        return self::removeParenthesis($text, ['"', "'"], ['"', "'"]);
    }

    /**
     * Remove the initial and final parenthesis but only if both matches.<br/>
     * If the $start and $end are arrays then both must have the same count and arrays are compared by pair of index
     *
     * @param string       $txt   Input value. Example "hello", "(hello)"
     * @param string|array $start the open parenthesis, by default it's '('.
     * @param string|array $end   the close parenthesis, by default it's ')'.
     *
     * @return bool|string The string processed of false if error.
     */
    public static function removeParenthesis($txt, $start = '(', $end = ')') {
        if ($txt == "") {
            return $txt;
        }
        if (is_array($start)) {
            if (count($start) != @count($end)) {
                return false;
            }
            foreach ($start as $k => $v) {
                if (substr($txt, 0, 1) === $v && substr($txt, -1) === $end[$k]) {
                    return substr($txt, 1, strlen($txt) - 2);
                }
            }
        } else {
            if (substr($txt, 0, 1) === $start && substr($txt, -1) === $end) {
                return substr($txt, 1, strlen($txt) - 2);
            }
        }
        return $txt;
    }

    /**
     * Replace the text between two needles
     *
     * @param string   $haystack    the input value
     * @param string   $startNeedle The initial text to search<br />
     *                              if empty then it starts at the start of the haystack.
     * @param string   $endNeedle   The end tag to search<br />
     *                              if empty then it ends at the end of the haystack
     * @param string   $replaceText Text to replace
     * @param null|int $offset      the offset position to start the search.
     * @param bool     $replaceTag  If true then it also replaces the tags
     *
     * @return bool|mixed
     */
    public static function replaceBetween(
        $haystack,
        $startNeedle,
        $endNeedle,
        $replaceText,
        &$offset = 0,
        $replaceTag = false
    ) {
        $ini = ($startNeedle === '') ? 0 : strpos($haystack, $startNeedle, $offset);
        if ($ini === false) {
            return false;
        }
        $p1 = ($endNeedle === '') ? strlen($haystack) : strpos($haystack, $endNeedle, $ini);
        if ($p1 === false) {
            return false;
        }
        if ($replaceTag) {
            $len = $p1 + strlen($endNeedle) - $ini;
            $offset = $ini + $len;
            return substr_replace($haystack, $replaceText, $ini, $len);

        } else {
            $ini += strlen($startNeedle);
            $len = $p1 - $ini;
            $offset = $ini + $len;
            return substr_replace($haystack, $replaceText, $ini, $len);
        }

    }

    /**
     * Remove the first character(s) for a string
     *
     * @param string $str The input text
     * @param int    $length The amount of characters to remove
     *
     * @return bool|string
     */
    public static function removeFirstChars($str, $length = 1) {
        return substr($str, $length);
    }

    /**
     * Remove the last character(s) for a string
     *
     * @param string $str The input text
     * @param int    $length The amount of characters to remove
     *
     * @return bool|string
     */
    public static function removeLastChars($str, $length = 1) {
        return substr($str, 0, -$length);
    }

    /**
     * It separates an argument from the value to the set value.
     * Example self::getArgument("arg=200") returns ["arg","200"]
     * Example self::getArgument("arg:200",':') returns ["arg","200"]
     *
     * @param string $str The input text
     * @param string $set The separator of operator
     * @param bool   $trimValue
     *
     * @return array it always returns a two dimensional array. It could returns [null,null] or ['arg',null]
     */
    public static function getArgument($str, $set = '=', $trimValue = true) {
        if (empty($str)) {
            return [null, null];
        }
        $parts = explode($set, $str, 2);
        if (count($parts) < 2) {
            $parts[] = null;
        }
        $parts[0] = trim($parts[0]);
        if ($trimValue && $parts[1]) {
            $parts[1] = trim($parts[1]);
        }

        return $parts;
    }

    /**
     * It returns the first non-space position inside a string.
     *
     * @param string $str    input string
     * @param int    $offset offset position
     * @param string $charlist list of characters considered as space
     *
     * @return int the position of the first non-space
     */
    public static function strPosNotSpace($str, $offset = 0, $charlist=" \t\n\r\0\x0B") {
        $txtTmp = substr($str, 0, $offset) . ltrim(substr($str, $offset),$charlist);
        return strlen($str) - strlen($txtTmp) + $offset;
    }

    /**
     * It find the first (or last) ocurrence of a text.<br>
     * Unlikely strpos(), this method allows to find more than one neddle.
     *
     * @param string       $haystack the input value
     * @param string|array $needles  the value (or values) to find
     * @param int          $offset   the offset position (initially it's 0)
     * @param bool         $last     if false (default) it returns the first ocurrence. If true returns the last one
     *
     * @return bool|int if not found then it returns false
     */
    public static function strposArray($haystack, $needles, $offset = 0, $last = false) {
        $min = strlen($haystack);
        if (is_array($needles)) {
            foreach ($needles as $str) {
                if (is_array($str)) {
                    $pos = self::strposArray($haystack, $str, $offset, $last);
                } else {
                    $pos = ($last) ? strrpos($haystack, $str, $offset) : strpos($haystack, $str, $offset);
                }
                if ($pos !== false) {
                    $min = ($pos < $min) ? $pos : $min;
                }
            }
        } else {
            $min = ($last) ? strrpos($haystack, $needles, $offset) : strpos($haystack, $needles, $offset);
        }
        return $min;
    }

    /**
     * It transforms a text = 'a1=1,a2=2' into an associative array<br/>
     * It uses the method parse_str() to do the conversion<br/>
     *
     * @param string $text      The input string with the initial values
     * @param string $separator The separator
     *
     * @return array An associative array
     */
    public static function parseArg($text, $separator = ',') {
        $tmpToken = '¶|¶';
        $output = [];
        if ($separator == '&') {
            parse_str($text, $output);
            return $output;
        }
        $parsR = str_replace(['&', $separator], [$tmpToken, '&'], $text);
        parse_str($parsR, $output);
        foreach ($output as &$k) {
            $k = str_replace($tmpToken, '&', $k);
        }
        return $output;
    }

    /**
     * It parses a natural string and returns a declarative array<br>
     * Example: natural("v1 obj obj1 type type1"<br>
     *               ,['item'=>'first','obj'=>'opt','type'=>'req'])
     * returns ['item'=>v1,'obj'=>'obj1','type'=>'type1']<br>
     * example natural("select * from table where condition"
     *          ,['select'=>'req','from'=>'req','where'=>'opt']);
     *
     * @param string $txt        the input value. Example "somevalue TYPE int LENGHT 30"
     * @param array  $separators the indicator for each field.<br>
     *                           first = indicates the first element (optional)<br>
     *                           opt = indicates the field is optional<br>
     *                           req = indicates the field is required <br>
     *
     * @return array|null It returns an associative array or null if the operation fails.
     */
    public static function naturalArg($txt, $separators) {
        $keySeparator = array_keys($separators);
        $result = array_flip($keySeparator);
        $firstKey = array_search('first', $separators);
        foreach ($result as &$v) {
            $v = null;
        }
        if (!$txt) {
            return $result;
        }
        $txtArr = explode(' ', trim($txt));
        $c = count($txtArr);
        $initial = 1;
        if ($firstKey) {
            $result[$firstKey] = $txtArr[0]; // the first value is the first index (always!)
        } else {
            $initial = 0; // it does not have a initial value
        }
        for ($i = $initial; $i < $c; $i += 2) {
            if (in_array($txtArr[$i], $keySeparator)) {
                $result[$txtArr[$i]] = @$txtArr[$i + 1];
            }
        }
        // validation
        foreach ($result as $k => $item) {
            if ($separators[$k] == 'req' && $result[$k] === null) {
                // it misses one required value.
                return null;
            }
        }
        return $result;
    }

    /**
     * Replaces all variables defined between {{ }} by a variable inside the dictionary of values.<br>
     * Example:<br>
     *      replaceCurlyVariable('hello={{var}}',['var'=>'world']) // hello=world<br>
     *      replaceCurlyVariable('hello={{var}}',['varx'=>'world']) // hello=<br>
     *      replaceCurlyVariable('hello={{var}}',['varx'=>'world'],true) // hello={{var}}<br>
     *
     * @param string $string The input value. It could contains variables defined as {{namevar}}
     * @param array  $values The dictionary of values.
     * @param bool $notFoundThenKeep [false] If true and the value is not found, then it keeps the value.
     *                               Otherwise, it is replaced by an empty value
     *
     * @return string|string[]|null
     */
    public static function replaceCurlyVariable($string,$values,$notFoundThenKeep=false) {
        if(strpos($string,'{{')===false) return $string; // nothing to replace.
        return preg_replace_callback('/{{\s?(\w+)\s?}}/u', function ($matches) use ($values,$notFoundThenKeep) {
            if (is_array($matches)) {
                $item = substr($matches[0], 2, strlen($matches[0]) - 4); // removes {{ and }}
                return isset($values[$item])? $values[$item] : ($notFoundThenKeep?$matches[0]:'') ;
            } else {
                $item = substr($matches, 2, strlen($matches) - 4); // removes {{ and }}
                return isset($values[$item])? $values[$item] : ($notFoundThenKeep?$matches:'') ;
            }
        }, $string);
    }

    /**
     * It adds an parenthesis (or other symbol) at the start and end of the text. If it already has it, then it is not added.
     *
     * @param string       $txt   Input value. Example "hello", "(hello)"
     * @param string|array $start the open parenthesis, by default it's '('.
     * @param string|array $end   the close parenthesis, by default it's ')'.
     *
     * @return string
     */
    public static function addParenthesis($txt, $start = '(', $end = ')') {
        if (self::hasParenthesis($txt, $start, $end) === false) {
            return $start . $txt . $end;
        }
        return $txt;
    }

    /**
     * It returns true if the text has an open and ending parenthesis (or other symbol).
     *
     * @param string       $txt   Input value. Example "hello", "(hello)"
     * @param string|array $start the open parenthesis, by default it's '('.
     * @param string|array $end   the close parenthesis, by default it's ')'.
     *
     * @return bool
     */
    public static function hasParenthesis($txt, $start = '(', $end = ')') {
        if ($txt == "") {
            return false;
        }
        if (is_array($start)) {
            if (count($start) != @count($end)) {
                return false;
            }
            foreach ($start as $k => $v) {
                if (substr($txt, 0, 1) === $v && substr($txt, -1) === $end[$k]) {
                    return true;
                }
            }
        } else {
            if (substr($txt, 0, 1) === $start && substr($txt, -1) === $end) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retains the case minus the first letter that it's converted in lowercase<br>
     * If the text contains the characters "_" or " ", then it the next character is uppercase<br>
     * If the text does not contain any character "_" or " ", then only the first character is replaced.
     *
     * @param string $txt input value
     *
     * @return string
     */
    public static function camelCase($txt) {
        if ($txt === null || $txt === '') {
            return $txt;
        }

        if (strpos($txt, '_') != false || strpos($txt, ' ') != false) {
            $txt = strtolower($txt);
            $result = '';
            $l = strlen($txt);
            for ($i = 0; $i < $l; $i++) {
                $c = $txt[$i];
                if ($c == '_' || $c == ' ') {
                    if($i!=$l-1) {
                        $result .= strtoupper($txt[$i + 1]);
                        $i++;
                    } else {
                        $result .= $c;
                    }
                } else {
                    $result .= $c;
                }
            }
            return $result;
        } else {
            // the text is simple.
            return strtolower(substr($txt, 0, 1)) . substr($txt, 1);
        }

    }
}