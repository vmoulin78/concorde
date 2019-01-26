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
 * CodeIgniter MySQL Helpers
 *
 * @package     Concorde
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Vincent MOULIN
 * @link        
 */

// ------------------------------------------------------------------------

use Concorde\utils\datetime\Mysql_date;
use Concorde\utils\datetime\Mysql_datetime;
use Concorde\utils\datetime\Mysql_time;
use Concorde\utils\datetime\Mysql_timestamp;
use Concorde\utils\datetime\Mysql_year;

// ------------------------------------------------------------------------

if ( ! function_exists('php_data_to_mysql_data'))
{
    /**
     * Return the MySQL data string corresponding to the PHP data $php_data
     *
     * @param   mixed   $php_data   A PHP data
     * @param   string  $data_type  The type of the data
     * @return  string
     */
    function php_data_to_mysql_data($php_data, $data_type) {
        $CI =& get_instance();

        if (is_null($php_data)) {
            return 'NULL';
        }

        switch ($data_type) {
            case 'int':
            case 'float':
            case 'pk':
                $retour = (string) $php_data;
                break;
            case 'string':
            case 'json':
                //TODO
                $retour = "'" . mysqli_real_escape_string($CI->db->conn_id, $php_data) . "'";
                break;
            case 'bool':
                $retour = $php_data ? '1' : '0';
                break;
            case 'set':
                $retour = '(' . implode(',', $php_data) . ')';
                break;
            case 'date':
            case 'datetime':
            case 'time':
            case 'timestamp':
            case 'year':
                $retour = $php_data->db_format();
                break;
            default:
                $data_type_array = explode(':', $data_type, 2);
                if (count($data_type_array) == 2) {
                    list($data_type_part1) = $data_type_array;
                    switch ($data_type_part1) {
                        case 'fk':
                        case 'pk_fk':
                            $retour = (string) $php_data;
                            break;
                        case 'enum_model_id':
                            $retour = (string) $php_element->get_id();
                            break;
                        default:
                            trigger_error("LightORM error: Unknown data type '" . $data_type . "'");
                            break;
                    }
                } else {
                    trigger_error("LightORM error: Unknown data type '" . $data_type . "'");
                }
                break;
        }

        return $retour;
    }
}

if ( ! function_exists('mysql_data_to_php_data'))
{
    /**
     * Return the PHP data corresponding to the MySQL data $mysql_data
     *
     * @param   mixed   $mysql_data  The MySQL data
     * @param   string  $data_type   The type of the data
     * @return  string
     */
    function mysql_data_to_php_data($mysql_data, $data_type) {
        if (is_null($mysql_data)) {
            return null;
        }

        switch ($data_type) {
            case 'int':
            case 'pk':
                $retour = (int) $mysql_data;
                break;
            case 'float':
                $retour = (float) $mysql_data;
                break;
            case 'string':
            case 'json':
                //TODO
                $retour = $mysql_data;
                break;
            case 'bool':
                if ($mysql_data === '0') {
                    $retour = false;
                } elseif ($mysql_data === '1') {
                    $retour = true;
                } else {
                    trigger_error("LightORM error: Unknown MySQL boolean");
                }
                break;
            case 'set':
                $retour = explode(',', $mysql_data);
                break;
            case 'date':
                $retour = new Mysql_date($mysql_data);
                break;
            case 'datetime':
                $retour = new Mysql_datetime($mysql_data);
                break;
            case 'time':
                $retour = new Mysql_time($mysql_data);
                break;
            case 'timestamp':
                $retour = new Mysql_timestamp($mysql_data);
                break;
            case 'year':
                $retour = new Mysql_year($mysql_data);
                break;
            default:
                $data_type_array = explode(':', $data_type, 2);
                if (count($data_type_array) == 2) {
                    list($data_type_part1, $data_type_part2) = $data_type_array;
                    switch ($data_type_part1) {
                        case 'fk':
                        case 'pk_fk':
                            $retour = (int) $mysql_data;
                            break;
                        case 'enum_model_id':
                            $model_full_name = model_full_name($data_type_part2);
                            $retour = $model_full_name::find($mysql_data);
                            break;
                        default:
                            trigger_error("LightORM error: Unknown data type '" . $data_type . "'");
                            break;
                    }
                } else {
                    trigger_error("LightORM error: Unknown data type '" . $data_type . "'");
                }
                break;
        }

        return $retour;
    }
}
