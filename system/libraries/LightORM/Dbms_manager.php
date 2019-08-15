<?php
namespace LightORM;

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
 * Dbms_manager Class
 *
 * Contain the specific code for the DBMS
 *
 * This class is a factory of singletons.
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
abstract class Dbms_manager
{
    /**
     * The singleton instance
     *
     * @var object
     */
    protected static $singleton = null;

    protected function __construct() {}

    /**
     * The factory of singletons
     *
     * @param   string ['postgresql'|'mysql']  $dbms  The name of the dbms
     * @return  object
     */
    public static function factory($dbms = null) {
        $CI =& get_instance();

        if (is_null($dbms)) {
            $dbms = $CI->db->dbms;
        }

        return call_user_func('\\LightORM\\dbms\\' . $dbms . '\\Dbms_manager_' . $dbms . '::get_singleton');
    }

    /**
     * Get the singleton
     *
     * @return  object
     */
    protected static function get_singleton() {
        if (is_null(static::$singleton)) {
            static::$singleton = new static();
        }

        return static::$singleton;
    }

    /**
     * Manage the value $value for the method add() given the business full name $business_full_name,
     * the property name $property and the property value $property_value
     *
     * @param   string  $business_full_name  The business full name
     * @param   string  $property            The name of the property
     * @param   array   $property_value      The value of the property
     * @param   mixed   $value               The value
     * @return  mixed
     */
    abstract public function manage_value_for_add($business_full_name, $property, $property_value, $value);

    /**
     * Manage the value $value for the method remove() given the business full name $business_full_name,
     * the property name $property and the property value $property_value
     *
     * @param   string  $business_full_name  The business full name
     * @param   string  $property            The name of the property
     * @param   array   $property_value      The value of the property
     * @param   mixed   $value               The value
     * @return  mixed
     */
    abstract public function manage_value_for_remove($business_full_name, $property, $property_value, $value);
}
