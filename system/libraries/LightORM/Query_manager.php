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
 * Query_manager Class
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
class Query_manager
{
    const QUERY_METHODS = array(
        'query',
        'simple_query',
        'get',
        'count_all',
        'count_all_results',
        'get_where',
        'insert_id',
        'insert',
        'update',
        'delete',
        'platform',
        'version',
    );

    const CONV_METHODS = array(
        'set',
        'where',
        'where_in',
        'where_not_in',
        'or_where',
        'or_where_in',
        'or_where_not_in',
        'like',
        'or_like',
        'not_like',
        'or_not_like',
        'having',
        'or_having',
    );

    private $CI;
    private $stack;
    public $table; //only for insert, update and delete operations
    public $aliases; //only for read operation
    public $models; //only for read operation

    public function __construct() {
        $this->CI =& get_instance();
        $this->reset_query();
    }

    /**
     * Execute the method $method with the arguments $args
     *
     * @param   string  $method
     * @param   array   $args
     * @return  void
     */
    private function execute($method, $args) {
        $data_conv = Data_conv::factory();

        if (in_array($method, self::CONV_METHODS)) {
            if (count($args) < 2) {
                trigger_error('LightORM error: The call of the method "' . $method . '" requires at least two parameters.', E_USER_ERROR);
            }

            $ext_field  = array_shift($args);
            $value      = array_shift($args);

            $trimmed_ext_field  = trim($ext_field);
            list($field)        = explode(' ', $trimmed_ext_field, 2);
            $field_array        = explode('.', $field, 2);
            $simple_field       = array_pop($field_array);
            if (count($field_array) === 1) {
                $alias = array_shift($field_array);
                if ( ! isset($this->aliases[$alias])) {
                    trigger_error('LightORM error: Unknown alias', E_USER_ERROR);
                }
                $table = $this->aliases[$alias];
            } else {
                $table = $this->table;
            }

            $field_object = $data_conv->get_table_field_object($table, $simple_field);

            if (in_array($method, ['where_in', 'where_not_in', 'or_where_in', 'or_where_not_in'])) {
                $converted_value_for_db = array();
                foreach ($value as $item) {
                    $converted_value_for_db[] = $data_conv->convert_value_for_db($item, $field_object);
                }
            } else {
                $converted_value_for_db = $data_conv->convert_value_for_db($value, $field_object);
            }

            $formatted_args = array($ext_field);

            if (in_array($method, ['like', 'or_like', 'not_like', 'or_not_like'])) {
                $converted_value_for_db = str_replace(['_', '%'], ['\\_', '\\%'], $converted_value_for_db);
                $formatted_args[] = $converted_value_for_db;

                if (count($args) >= 1) {
                    $side = array_shift($args);
                } else {
                    $side = 'both';
                }
                $formatted_args[] = $side;
            } else {
                $formatted_args[] = $converted_value_for_db;
            }

            $formatted_args[] = false;

            call_user_func_array(array($this->CI->db, $method), $formatted_args);
        } elseif ((substr($method, 0, 7) == 'simple_')
            && (in_array(substr($method, 7), self::CONV_METHODS))
        ) {
            $this->CI->db->{substr($method, 7)}(...$args);
        } else {
            $this->CI->db->{$method}(...$args);
        }
    }

    /**
     * Reset the query
     *
     * @return  void
     */
    public function reset_query() {
        $this->table    = null;
        $this->aliases  = array();
        $this->models   = array();
        $this->stack    = array();
    }

    /**
     * Trigger the insert query
     *
     * @param   bool  $with_insert_id  If true, the method will return the insert id in case of success and false otherwise
     * @return  bool|int
     */
    public function insert($with_insert_id = false) {
        $insert_result = $this->__call('insert');

        if ($insert_result === false) {
            return false;
        }

        if ($with_insert_id) {
            return $this->__call('insert_id');
        } else {
            return true;
        }
    }

    /**
     * Manage the method $method that must be executed with the arguments $args
     *
     * @param   string  $method
     * @param   array   $args
     * @return  mixed
     */
    public function __call($method, $args = []) {
        if (in_array($method, self::QUERY_METHODS)) {
            foreach ($this->stack as $item) {
                $this->execute($item['method'], $item['args']);
            }

            $result = $this->CI->db->{$method}(...$args);

            $this->CI->db->reset_query();

            return $result;
        } else {
            if (in_array($method, ['table', 'from', 'join'])) {
                if (count($args) == 0) {
                    trigger_error('LightORM error: Missing parameter', E_USER_ERROR);
                }

                if (is_array($args[0])) {
                    if (($method == 'table')
                        || ($method == 'join')
                    ) {
                        trigger_error('LightORM error: Parameter error', E_USER_ERROR);
                    }

                    list($first_snippet) = $args[0];
                } else {
                    $first_snippet = $args[0];
                }

                if (in_string(',', $first_snippet)) {
                    if (($method == 'table')
                        || ($method == 'join')
                    ) {
                        trigger_error('LightORM error: Parameter error', E_USER_ERROR);
                    }

                    list($ext_table) = explode(',', $first_snippet, 2);
                } else {
                    $ext_table = $first_snippet;
                }

                $ext_table_array = explode(' AS ', $ext_table, 2);
                if (count($ext_table_array) == 2) {
                    list($table, $alias) = $ext_table_array;
                    $table = trim($table);
                    $alias = trim($alias);
                } else {
                    $table = trim($ext_table);
                    $alias = $table;
                }

                $this->table = $table;
                $this->aliases[$alias] = $table;
            }

            if ($method == 'table') {
                $method = 'from';
            }

            $this->stack[] = array(
                'method'  => $method,
                'args'    => $args
            );

            return $this;
        }
    }

    /**
     * Convert the row $row
     *
     * @param   object  $row
     * @return  void
     */
    public function convert_row(&$row) {
        $data_conv = Data_conv::factory();

        $vars = array_keys(get_object_vars($row));

        foreach ($vars as $var) {
            $exploded_var = explode(':', $var);

            if (count($exploded_var) !== 2) {
                trigger_error('LightORM error: Invalid format for ' . $var, E_USER_ERROR);
            }

            if ( ! is_null($row->{$var})) {
                list($alias, $field) = $exploded_var;

                $field_object = $data_conv->get_table_field_object($this->aliases[$alias], $field);

                if (isset($field_object)) {
                    $row->{$var} = $data_conv->convert_value_for_php($row->{$var}, $field_object);
                } else {
                    trigger_error('LightORM error: Undefined type for ' . $var, E_USER_ERROR);
                }
            }
        }
    }
}
