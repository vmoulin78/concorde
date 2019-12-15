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
 * Mysql_year Class
 *
 * This class represents a year.
 * The corresponding MySQL type is YEAR.
 *
 * @package     Concorde
 * @subpackage  Utils
 * @category    Utils
 * @author      Vincent MOULIN
 * @link        
 */
class Mysql_year extends Dbms_datetime_mysql
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
            $this->value = $value->format(MYSQL_YEAR_FORMAT);
        } else {
            $this->value = (string) $value;
        }
    }

    /**
     * Create a Mysql_year object
     *
     * @param   string  $format
     * @param   string  $time
     * @return  object
     */
    public static function create_from_format($format, $time) {
        $datetime = \DateTime::createFromFormat($format, $time);

        return (new self($datetime->format(MYSQL_YEAR_FORMAT)));
    }

    /**
     * {@inheritDoc}
     */
    public function convert() {
        return new \DateTime($this->value . '-01-01 00:00:00');
    }

    //------------------------------------------------------//

    public function diff(Mysql_year $mysql_year, $absolute = false) {
        return Mysql_interval::create($this->convert(), $mysql_year->convert(), $absolute);
    }

    public function add(Mysql_interval $mysql_interval) {
        $this->value = $this->convert()->add($mysql_interval->convert())->format(MYSQL_YEAR_FORMAT);
        return $this;
    }

    public function sub(Mysql_interval $mysql_interval) {
        $this->value = $this->convert()->sub($mysql_interval->convert())->format(MYSQL_YEAR_FORMAT);
        return $this;
    }

    public function modify(string $modify) {
        $this->value = $this->convert()->modify($modify)->format(MYSQL_YEAR_FORMAT);
        return $this;
    }
}
