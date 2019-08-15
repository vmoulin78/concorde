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
 * Data_conv Class
 *
 * Manage the data conversion for the database
 *
 * This class is a factory of singletons.
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
abstract class Data_conv
{
    /**
     * The singleton instance
     *
     * @var object
     */
    protected static $singleton = null;

    /**
     * The CI instance
     *
     * @var object
     */
    protected $CI;

    /**
     * The schema of the database
     *
     * @var array
     */
    public $schema;

    protected function __construct() {
        $this->CI =& get_instance();

        $this->schema = array();

        foreach ($this->CI->config->item('lightORM_data_conv') as $table_name => $table_fields) {
            $fields = array();

            foreach ($table_fields as $table_field_name => $table_field_full_type) {
                $fields[$table_field_name] = new Data_conv_table_field($table_field_name, $table_field_full_type);
            }

            $this->schema[$table_name] = new Data_conv_table($table_name, $fields);
        }
    }

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

        return call_user_func('\\LightORM\\dbms\\' . $dbms . '\\Data_conv_' . $dbms . '::get_singleton');
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
     * Get the table field object corresponding to the field $field in the table $table
     *
     * @param   string  $table  The name of the table
     * @param   string  $field  The name of the field
     * @return  object
     */
    public function get_table_field_object($table, $field) {
        if ( ! isset($this->schema[$table])) {
            return false;
        }

        $table_object = $this->schema[$table];

        if ( ! isset($table_object->fields[$field])) {
            return false;
        }

        return $table_object->fields[$field];
    }

    /**
     * Convert the value $value related to the database field $field_object for the database
     *
     * @param   mixed                  $value         The value
     * @param   Data_conv_table_field  $field_object  The related database field
     * @return  mixed
     */
    abstract public function convert_value_for_db($value, $field_object);

    /**
     * Convert the value $value related to the database field $field_object for PHP
     *
     * @param   mixed                  $value         The value
     * @param   Data_conv_table_field  $field_object  The related database field
     * @return  mixed
     */
    abstract public function convert_value_for_php($value, $field_object);
}
