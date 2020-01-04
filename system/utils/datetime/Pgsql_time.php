<?php
namespace Concorde\utils\datetime;

/**
 * Concorde
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2019 - 2020, Vincent MOULIN
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
 * @copyright   Copyright (c) 2019 - 2020, Vincent MOULIN
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link       
 * @since       Version 1.0.0
 * @filesource
 */
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

/**
 * Pgsql_time Class
 *
 * This class represents a time of the day.
 * The corresponding PostgreSQL type is TIME.
 *
 * @package     Concorde
 * @subpackage  Utils
 * @category    Utils
 * @author      Vincent MOULIN
 * @link        
 */
class Pgsql_time extends Dbms_datetime_pgsql
{
    /**
     * The constructor
     */
    public function __construct($value = 'now') {
        parent::__construct();

        if ($value === 'now') {
            $value = new \DateTime();
        }

        if ($value instanceof \DateTime) {
            $this->value = $value->format(PGSQL_TIME_FORMAT);
        } else {
            $this->value = (string) $value;
        }
    }

    /**
     * Create a Pgsql_time object
     *
     * @param   string  $format
     * @param   string  $time
     * @return  object
     */
    public static function create_from_format($format, $time) {
        $datetime = \DateTime::createFromFormat($format, $time);

        return (new self($datetime->format(PGSQL_TIME_FORMAT)));
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
        return "TIME '" . $this->value . "'";
    }

    //------------------------------------------------------//

    public function diff(Pgsql_time $pgsql_time, $absolute = false) {
        return (new Pgsql_interval($this->convert()->diff($pgsql_time->convert(), $absolute)->format(PGSQL_INTERVAL_FORMAT)));
    }

    public function add(Pgsql_interval $pgsql_interval) {
        $this->value = $this->convert()->add($pgsql_interval->convert())->format(PGSQL_TIME_FORMAT);
        return $this;
    }

    public function sub(Pgsql_interval $pgsql_interval) {
        $this->value = $this->convert()->sub($pgsql_interval->convert())->format(PGSQL_TIME_FORMAT);
        return $this;
    }

    public function modify(string $modify) {
        $this->value = $this->convert()->modify($modify)->format(PGSQL_TIME_FORMAT);
        return $this;
    }
}
