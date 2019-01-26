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
 * CodeIgniter Function Handling Helpers
 *
 * @package     Concorde
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Vincent MOULIN
 * @link        
 */

// ------------------------------------------------------------------------

if ( ! function_exists('get_callback_type'))
{
    /**
     * Return the type of the callback $callback
     *
     * @param   callable  $callback
     * @return  string ['function'|'method']
     */
    function get_callback_type($callback) {
        if ( ! is_callable($callback)) {
            trigger_error('The given callback is not callable.', E_USER_ERROR);
        }

        if ((is_string($callback) && function_exists($callback))
            || (is_object($callback) && (get_class($callback) === 'Closure'))
        ) {
            return 'function';
        }

        return 'method';
    }
}

if ( ! function_exists('sort_callback_parameters'))
{
    /**
     * Return a copy of the array $parameters sorted according to the parameters of the callable $callback
     *
     * @param   callable  $callback
     * @param   array     $parameters  An associative array whose each key matches a parameter of the callable $callback
     * @return  array
     */
    function sort_callback_parameters($callback, $parameters) {
        $callback_type = get_callback_type($callback);
        switch ($callback_type) {
            case 'function':
                $reflection_callback = new ReflectionFunction($callback);
                break;
            case 'method':
                if (is_object($callback)) {
                    $reflection_callback = new ReflectionMethod($callback, '__invoke');
                } elseif (is_array($callback)) {
                    $class   = array_shift($callback);
                    $method  = array_shift($callback);
                    $reflection_callback = new ReflectionMethod($class, $method);
                } elseif (is_string($callback)) {
                    $reflection_callback = new ReflectionMethod($callback);
                } else {
                    exit(1);
                }
                break;
            default:
                exit(1);
                break;
        }

        $reflection_parameters = $reflection_callback->getParameters();

        $retour = array();
        foreach ($reflection_parameters as $reflection_parameter) {
            $reflection_parameter_name = $reflection_parameter->getName();
            if (isset($parameters[$reflection_parameter_name])) {
                $retour[] = $parameters[$reflection_parameter_name];
            } else {
                if ($reflection_parameter->isDefaultValueAvailable) {
                    $retour[] = $reflection_parameter->getDefaultValue();
                } else {
                    return false;
                }
            }
        }

        return $retour;
    }
}

if ( ! function_exists('call_user_func_array_assoc'))
{
    /**
     * Call a callback with an associative array of parameters
     *
     * @param   callable  $callback
     * @param   array     $parameters  An associative array whose each key matches a parameter of the callable $callback
     * @return  mixed
     */
    function call_user_func_array_assoc($callback, $parameters) {
        $sorted_parameters = sort_callback_parameters($callback, $parameters);

        if ($sorted_parameters === false) {
            trigger_error('Missing parameter', E_USER_ERROR);
        }

        return call_user_func_array($callback, $sorted_parameters);
    }
}
