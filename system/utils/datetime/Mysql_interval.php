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
 * @since       Version 1.0.0
 * @filesource
 */
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

/**
 * Mysql_interval Class
 *
 * This class represents a time interval.
 * The corresponding MySQL type is TIME.
 *
 * @package     Concorde
 * @subpackage  Utils
 * @category    Utils
 * @author      Vincent MOULIN
 * @link        
 */
class Mysql_interval extends Dbms_datetime_mysql
{
    /**
     * The constructor
     */
    public function __construct($value) {
        parent::__construct();

        if ($value instanceof \DateInterval) {
            $this->value = $value->format(MYSQL_INTERVAL_FORMAT);
        } else {
            $this->value = (string) $value;
        }
    }

    /**
     * Create a Mysql_interval object
     *
     * @param   DateTime  $datetime_1
     * @param   DateTime  $datetime_2
     * @param   bool      $absolute
     * @return  object
     */
    public static function create(\DateTime $datetime_1, \DateTime $datetime_2, $absolute = false) {
        $diff_in_seconds = $datetime_1->getTimestamp() - $datetime_2->getTimestamp();
        if ($absolute) {
            $diff_in_seconds = abs($diff_in_seconds);
        }

        if ($diff_in_seconds < 0) {
            $diff_is_negative  = true;
            $diff_in_seconds   = abs($diff_in_seconds);
        } else {
            $diff_is_negative = false;
        }

        $seconds = $diff_in_seconds % 60;
        $diff_in_minutes = intdiv($diff_in_seconds, 60);

        $minutes = $diff_in_minutes % 60;
        $diff_in_hours = intdiv($diff_in_minutes, 60);

        $diff_string = $diff_in_hours . ':' . $minutes . ':' . $seconds;
        if ($diff_is_negative) {
            $diff_string = '-' . $diff_string;
        }

        return (new self($diff_string));
    }

    /**
     * Create a Mysql_interval object
     *
     * @param   string  $time
     * @return  object
     */
    public static function create_from_date_string($time) {
        $dateinterval = \DateInterval::createFromDateString($time);

        return (new self($dateinterval->format(MYSQL_INTERVAL_FORMAT)));
    }

    /**
     * {@inheritDoc}
     */
    public function convert() {
        list($hours, $minutes, $seconds) = explode(':', $this->value, 3);

        return \DateInterval::createFromDateString($hours . ' hours ' . $minutes . ' minutes ' . $seconds . ' seconds');
    }
}
