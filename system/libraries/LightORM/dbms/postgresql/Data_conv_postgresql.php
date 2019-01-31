<?php
namespace LightORM\dbms\postgresql;

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

use LightORM\Data_conv;
use Concorde\utils\datetime\Pgsql_timestamp;
use Concorde\utils\datetime\Pgsql_timestamptz;
use Concorde\utils\datetime\Pgsql_date;
use Concorde\utils\datetime\Pgsql_time;
use Concorde\utils\datetime\Pgsql_timetz;
use Concorde\utils\datetime\Pgsql_interval;

/**
 * Data_conv_postgresql Class
 *
 * Manage the data conversion for the PostgreSQL database
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Data_conv_postgresql extends Data_conv
{
    /**
     * {@inheritDoc}
     */
    public function convert_value_for_db($value, $field_object) {
        return php_data_to_pgsql_data($value, $field_object->full_type);
    }

    /**
     * {@inheritDoc}
     */
    public function convert_value_for_php($value, $field_object) {
        return pgsql_data_to_php_data($value, $field_object->full_type);
    }
}
