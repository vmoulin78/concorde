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
 * Pgsql_timestamptz Class
 *
 * This class represents a datetime with time zone.
 * The corresponding PostgreSQL type is TIMESTAMP WITH TIME ZONE.
 *
 * @package     Concorde
 * @subpackage  Utils
 * @category    Utils
 * @author      Vincent MOULIN
 * @link        
 */
class Pgsql_timestamptz extends Dbms_datetime_pgsql
{
    public function __construct($value = 'now') {
        if ($value === 'now') {
            $datetime     = new \DateTime($value);
            $this->value  = $datetime->format(PGSQL_TIMESTAMPTZ_FORMAT);
        } else {
            $this->value = $value;
        }
    }

    /**
     * Create a Pgsql_timestamptz object
     *
     * @param   string        $format
     * @param   string        $time
     * @param   DateTimeZone  $format
     * @return  object
     */
    public static function create_from_format($format, $time, $timezone = null) {
        if (is_null($timezone)) {
            $datetime = \DateTime::createFromFormat($format, $time);
        } else {
            $datetime = \DateTime::createFromFormat($format, $time, $timezone);
        }

        return (new self($datetime->format(PGSQL_TIMESTAMPTZ_FORMAT)));
    }

    /**
     * {@inheritDoc}
     */
    public function convert() {
        return new \DateTime($this->value);
    }

    /**
     * {@inheritDoc}
     */
    public function db_format() {
        return "TIMESTAMP WITH TIME ZONE '" . $this->value . "'";
    }

    //------------------------------------------------------//

    public function diff(Pgsql_timestamptz $pgsql_timestamptz, $absolute = false) {
        return (new Pgsql_interval($this->convert()->diff($pgsql_timestamptz->convert(), $absolute)->format(PGSQL_INTERVAL_FORMAT)));
    }

    public function add(Pgsql_interval $pgsql_interval) {
        $this->value = $this->convert()->add($pgsql_interval->convert())->format(PGSQL_TIMESTAMPTZ_FORMAT);
        return $this;
    }

    public function sub(Pgsql_interval $pgsql_interval) {
        $this->value = $this->convert()->sub($pgsql_interval->convert())->format(PGSQL_TIMESTAMPTZ_FORMAT);
        return $this;
    }

    public function modify(string $modify) {
        $this->value = $this->convert()->modify($modify)->format(PGSQL_TIMESTAMPTZ_FORMAT);
        return $this;
    }

    public function get_offset() {
        return $this->convert()->getOffset();
    }

    public function get_timestamp() {
        return $this->convert()->getTimestamp();
    }

    public function get_timezone() {
        return $this->convert()->getTimezone();
    }
}
