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
 * CodeIgniter PostgreSQL Helpers
 *
 * @package     Concorde
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Vincent MOULIN
 * @link        
 */

// ------------------------------------------------------------------------

use Concorde\utils\datetime\Pgsql_date;
use Concorde\utils\datetime\Pgsql_interval;
use Concorde\utils\datetime\Pgsql_time;
use Concorde\utils\datetime\Pgsql_timestamp;
use Concorde\utils\datetime\Pgsql_timestamptz;
use Concorde\utils\datetime\Pgsql_timetz;

// ------------------------------------------------------------------------

if ( ! function_exists('php_element_to_pgsql_element'))
{
    /**
     * Return the PostgreSQL element corresponding to the PHP element $php_element
     * The PHP element $php_element is supposed to be not null
     *
     * @internal
     * @param   string  $php_element   A PHP element
     * @param   string  $element_type  The type of the element
     * @return  mixed
     */
    function php_element_to_pgsql_element($php_element, $element_type) {
        $CI =& get_instance();

        switch ($element_type) {
            case 'int':
            case 'float':
            case 'pk':
                $retour = (string) $php_element;
                break;
            case 'string':
            case 'json':
                $retour = pg_escape_literal($CI->db->conn_id, $php_element);
                break;
            case 'bool':
                $retour = $php_element ? 'TRUE' : 'FALSE';
                break;
            case 'date':
            case 'interval':
            case 'time':
            case 'timestamp':
            case 'timestamptz':
            case 'timetz':
                $retour = $php_element->db_format();
                break;
            default:
                $element_type_array = explode(':', $element_type, 2);
                if (count($element_type_array) == 2) {
                    list($element_type_part1) = $element_type_array;
                    switch ($element_type_part1) {
                        case 'fk':
                        case 'pk_fk':
                            $retour = (string) $php_element;
                            break;
                        case 'enum_model_id':
                            $retour = (string) $php_element->get_id();
                            break;
                        default:
                            trigger_error("LightORM error: Unknown element type '" . $element_type . "'");
                            break;
                    }
                } else {
                    trigger_error("LightORM error: Unknown element type '" . $element_type . "'");
                }
                break;
        }

        return $retour;
    }
}

if ( ! function_exists('php_data_to_pgsql_data_rec'))
{
    /**
     * Return the PostgreSQL data string corresponding to the PHP data $php_data
     * This function is the recursive part of the function php_data_to_pgsql_data()
     *
     * @internal
     * @param   string  $php_data      A PHP data
     * @param   string  $element_type  The type of the element
     * @return  mixed
     */
    function php_data_to_pgsql_data_rec($php_data, $element_type) {
        if (is_null($php_data)) {
            return 'NULL';
        }

        if (is_array($php_data)) {
            $retour = array();
            foreach ($php_data as $item) {
                $retour[] = php_data_to_pgsql_data_rec($item, $element_type);
            }

            $retour = 'ARRAY[' . implode(',', $retour) . ']';
        } else {
            $retour = php_element_to_pgsql_element($php_data, $element_type);
        }

        return $retour;
    }
}

if ( ! function_exists('php_data_to_pgsql_data'))
{
    /**
     * Return the PostgreSQL data string corresponding to the PHP data $php_data
     *
     * @param   mixed   $php_data   A PHP data
     * @param   string  $data_type  The type of the data
     * @return  string
     */
    function php_data_to_pgsql_data($php_data, $data_type) {
        if (substr($data_type, -2) == '[]') {
            $element_type = strstr($data_type, '[', true);
        } else {
            $element_type = $data_type;
        }

        return php_data_to_pgsql_data_rec($php_data, $element_type);
    }
}

if ( ! function_exists('pgsql_element_to_php_element'))
{
    /**
     * Return the PHP element corresponding to the PostgreSQL element $pgsql_element
     *
     * @internal
     * @param   string  $pgsql_element  A string containing a PostgreSQL element
     * @param   string  $element_type   The type of the element
     * @return  mixed
     */
    function pgsql_element_to_php_element($pgsql_element, $element_type) {
        if ($pgsql_element === 'NULL') {
            return null;
        }

        switch ($element_type) {
            case 'int':
            case 'pk':
                $retour = (int) $pgsql_element;
                break;
            case 'float':
                $retour = (float) $pgsql_element;
                break;
            case 'string':
                if (db_substr($pgsql_element, 0, 1) == '"') {
                    $retour = str_replace('\\"', '"', db_substr($pgsql_element, 1, -1));
                } else {
                    $retour = $pgsql_element;
                }
                break;
            case 'json':
                $retour = $pgsql_element;
                break;
            case 'bool':
                $retour = ($pgsql_element === 't');
                break;
            case 'date':
                $retour = new Pgsql_date($pgsql_element);
                break;
            case 'interval':
                $retour = new Pgsql_interval($pgsql_element);
                break;
            case 'time':
                $retour = new Pgsql_time($pgsql_element);
                break;
            case 'timestamp':
                $retour = new Pgsql_timestamp($pgsql_element);
                break;
            case 'timestamptz':
                $retour = new Pgsql_timestamptz($pgsql_element);
                break;
            case 'timetz':
                $retour = new Pgsql_timetz($pgsql_element);
                break;
            default:
                $element_type_array = explode(':', $element_type, 2);
                if (count($element_type_array) == 2) {
                    list($element_type_part1, $element_type_part2) = $element_type_array;
                    switch ($element_type_part1) {
                        case 'fk':
                        case 'pk_fk':
                            $retour = (int) $pgsql_element;
                            break;
                        case 'enum_model_id':
                            $model_full_name = model_full_name($element_type_part2);
                            $retour = $model_full_name::find($pgsql_element);
                            break;
                        default:
                            trigger_error("LightORM error: Unknown element type '" . $element_type . "'");
                            break;
                    }
                } else {
                    trigger_error("LightORM error: Unknown element type '" . $element_type . "'");
                }
                break;
        }

        return $retour;
    }
}

if ( ! function_exists('pgsql_array_to_php_array_rec'))
{
    /**
     * Return the PHP array corresponding to the PostgreSQL array $pgsql_array at the position $position
     * This function is the recursive part of the function pgsql_data_to_php_data()
     *
     * @internal
     * @param   string  $pgsql_array   A string containing a PostgreSQL array
     * @param   string  $element_type  The type of the elements
     * @param   int     $position      The current position in the string $pgsql_array
     * @return  array
     */
    function pgsql_array_to_php_array_rec($pgsql_array, $element_type, &$position = 0) {
        $position++;
        $retour = array();

        while (true) {
            $current_char = db_substr($pgsql_array, $position, 1);

            if ($current_char == '{') {
                $element = pgsql_array_to_php_array_rec($pgsql_array, $element_type, $position);
            } else {
                if ($current_char == '"') {
                    $element = '"';
                    $position++;

                    $current_char = db_substr($pgsql_array, $position, 1);
                    while ($current_char != '"') {
                        $element .= $current_char;
                        $position++;

                        if ($current_char == '\\') {
                            $element .= db_substr($pgsql_array, $position, 1);
                            $position++;
                        }

                        $current_char = db_substr($pgsql_array, $position, 1);
                    }

                    $element .= '"';
                    $position++;
                } else {
                    $element = '';
                    while ( ! in_array($current_char, [',', '}'])) {
                        $element .= $current_char;
                        $position++;
                        $current_char = db_substr($pgsql_array, $position, 1);
                    }
                }

                $element = pgsql_element_to_php_element($element, $element_type);
            }

            $retour[] = $element;

            if (db_substr($pgsql_array, $position, 1) == ',') {
                $position++;
            } else {
                $position++;
                break;
            }
        }

        return $retour;
    }
}

if ( ! function_exists('pgsql_data_to_php_data'))
{
    /**
     * Return the PHP data corresponding to the PostgreSQL data $pgsql_data
     *
     * @param   mixed   $pgsql_data  The PostgreSQL data
     * @param   string  $data_type   The type of the data
     * @return  string
     */
    function pgsql_data_to_php_data($pgsql_data, $data_type) {
        if (is_null($pgsql_data)) {
            return null;
        }

        if (substr($data_type, -2) == '[]') {
            return pgsql_array_to_php_array_rec($pgsql_data, strstr($data_type, '[', true));
        } else {
            return pgsql_element_to_php_element($pgsql_data, $data_type);
        }
    }
}
