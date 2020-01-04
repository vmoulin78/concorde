<?php
/**
 * Concorde
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2019 - 2020, Vincent MOULIN
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
 * @copyright   Copyright (c) 2019 - 2020, Vincent MOULIN
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link       
 * @since       Version 1.0.0
 * @filesource
 */
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

/**
 * CodeIgniter Reflection Helpers
 *
 * @package     Concorde
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Vincent MOULIN
 * @link        
 */

// ------------------------------------------------------------------------

if ( ! function_exists('get_class_short_name'))
{
    /**
     * Get the class short name of the parameter $arg1
     *
     * @param   mixed  $arg1  The full name of a class or an object
     * @return  string
     */
    function get_class_short_name($arg1) {
        return (new ReflectionClass($arg1))->getShortName();
    }
}

if ( ! function_exists('get_trait_names'))
{
    /**
     * Get the trait names of the parameter $arg1
     *
     * @param   mixed  $arg1  The full name of a class or an object
     * @return  array
     */
    function get_trait_names($arg1) {
        return (new ReflectionClass($arg1))->getTraitNames();
    }
}

if ( ! function_exists('has_trait_name'))
{
    /**
     * Check if the parameter $arg1 has the trait name $trait_name
     *
     * @param   mixed   $arg1        The full name of a class or an object
     * @param   string  $trait_name  The full name of a trait
     * @return  bool
     */
    function has_trait_name($arg1, $trait_name) {
        $arg1_trait_names = get_trait_names($arg1);
        if (in_array($trait_name, $arg1_trait_names)) {
            return true;
        } else {
            return false;
        }
    }
}
