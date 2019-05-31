<?php
namespace Concorde\utils\datetime;

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
 * Pgsql_timetz Class
 *
 * @package     Concorde
 * @subpackage  Utils
 * @category    Utils
 * @author      Vincent MOULIN
 * @link        
 */
class Pgsql_timetz extends Dbms_datetime_pgsql
{
    public static function create_from_format($format, $time, $timezone = null) {
        if (is_null($timezone)) {
            $datetime = \DateTime::createFromFormat($format, $time);
        } else {
            $datetime = \DateTime::createFromFormat($format, $time, $timezone);
        }

        return (new self($datetime->format(PGSQL_TIMETZ_FORMAT)));
    }

    /**
     * {@inheritDoc}
     */
    public function convert() {
        return new \DateTime('1970-01-01 ' . $this->value);
    }

    /**
     * {@inheritDoc}
     */
    public function db_format() {
        return "TIME WITH TIME ZONE '" . $this->value . "'";
    }
}
