<?php
/**
 * Concorde
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2019, Vincent MOULIN
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Concorde
 * @author      Vincent MOULIN
 * @copyright   Copyright (c) 2019, Vincent MOULIN
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link       
 * @since       Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Multibyte String Helpers
 *
 * @package     Concorde
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Vincent MOULIN
 * @link        
 */

// ------------------------------------------------------------------------

if ( ! function_exists('cc_strlen'))
{
    /**
     * Get string length considering the configuration item "charset"
     *
     * @param   string
     * @return  string
     */
    function cc_strlen($str) {
        $CI =& get_instance();

        return mb_strlen($str, $CI->config->item('charset'));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('db_strlen'))
{
    /**
     * Get string length considering the database configuration item "char_set"
     *
     * @param   string
     * @return  string
     */
    function db_strlen($str) {
        $CI =& get_instance();

        return mb_strlen($str, $CI->db->char_set);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('cc_strpos'))
{
    /**
     * Find position of first occurence of string in a string considering the configuration item "charset"
     *
     * @param   string
     * @param   string
     * @param   int
     * @return  int
     */
    function cc_strpos($haystack, $needle, $offset = 0) {
        $CI =& get_instance();

        return mb_strpos($haystack, $needle, $offset, $CI->config->item('charset'));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('db_strpos'))
{
    /**
     * Find position of first occurence of string in a string considering the database configuration item "char_set"
     *
     * @param   string
     * @param   string
     * @param   int
     * @return  int
     */
    function db_strpos($haystack, $needle, $offset = 0) {
        $CI =& get_instance();

        return mb_strpos($haystack, $needle, $offset, $CI->db->char_set);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('cc_substr'))
{
    /**
     * Get part of string considering the configuration item "charset"
     *
     * @param   string
     * @param   int
     * @param   int
     * @return  string
     */
    function cc_substr($str, $start, $length = null) {
        $CI =& get_instance();

        return mb_substr($str, $start, $length, $CI->config->item('charset'));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('db_substr'))
{
    /**
     * Get part of string considering the database configuration item "char_set"
     *
     * @param   string
     * @param   int
     * @param   int
     * @return  string
     */
    function db_substr($str, $start, $length = null) {
        $CI =& get_instance();

        return mb_substr($str, $start, $length, $CI->db->char_set);
    }
}
