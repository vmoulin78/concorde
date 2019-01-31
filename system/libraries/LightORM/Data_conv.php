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
 * @since       Version 0.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data_conv Class
 *
 * Manage the data conversion for the database
 * This class is a factory of singletons
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
            return null;
        }

        $table_object = $this->schema[$table];

        if ( ! isset($table_object->fields[$field])) {
            return null;
        }

        return $table_object->fields[$field];
    }

    /**
     * Convert the value $value related to the database field $field_object for the database
     *
     * @param   mixed                  $value         A value
     * @param   Data_conv_table_field  $field_object  The related database field
     * @return  mixed
     */
    abstract public function convert_value_for_db($value, $field_object);

    /**
     * Execute the function $func of the CodeIgniter database extension given the table $table, the field $ext_field and the value $value
     *
     * @param   string  $func       The name of the function
     * @param   string  $table      The name of the table
     * @param   string  $ext_field  The extended name of the field
     * @param   mixed   $value      The value
     * @return  mixed
     */
    public function data_conv_func($func, $table, $ext_field, $value) {
        $trimmed_ext_field  = trim($ext_field);
        list($field)        = explode(' ', $trimmed_ext_field, 2);
        $field_array        = explode('.', $field, 2);
        if (count($field_array) == 1) {
            $simple_field = $field_array[0];
        } else {
            $simple_field = $field_array[1];
        }
        $field_object = $this->get_table_field_object($table, $simple_field);

        if (in_array($func, ['where_in', 'where_not_in', 'or_where_in', 'or_where_not_in'])) {
            $converted_value_for_db = array();
            foreach ($value as $item) {
                $converted_value_for_db[] = $this->convert_value_for_db($item, $field_object);
            }
        } else {
            $converted_value_for_db = $this->convert_value_for_db($value, $field_object);
        }

        $this->CI->db->{$func}($ext_field, $converted_value_for_db, false);
    }

    /**
     * Convert the value $value related to the database field $field_object for PHP
     *
     * @param   mixed                  $value         A value
     * @param   Data_conv_table_field  $field_object  The related database field
     * @return  mixed
     */
    abstract public function convert_value_for_php($value, $field_object);
}
