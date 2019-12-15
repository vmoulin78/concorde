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
 * Dbms_datetime Class
 *
 * @package     Concorde
 * @subpackage  Utils
 * @category    Utils
 * @author      Vincent MOULIN
 * @link        
 */
abstract class Dbms_datetime
{
    protected $value;

    //------------------------------------------------------//

    /**
     * The constructor
     */
    public function __construct() {}

    /**
     * Get the value
     *
     * @return  string
     */
    public function get_value() {
        return $this->value;
    }

    /**
     * Convert the value $this->value into a DateTime or DateInterval object
     *
     * @return  DateTime|DateInterval
     */
    abstract public function convert();

    /**
     * Get the value formatted for the database
     *
     * @return  string
     */
    abstract public function db_format();

    /**
     * Format $this given the format $format
     *
     * @param   string  $format
     * @return  string
     */
    public function format($format) {
        return $this->convert()->format($format);
    }

    /**
     * Compare the current Dbms_datetime to the Dbms_datetime $arg1
     * Return :
     *     0   if the current Dbms_datetime is equal to the Dbms_datetime $arg1
     *     1   if the current Dbms_datetime is greater than the Dbms_datetime $arg1
     *     -1  if the current Dbms_datetime is less than the Dbms_datetime $arg1
     *
     * @param   Dbms_datetime  $arg1
     * @return  int
     */
    public function compare_to(Dbms_datetime $arg1) {
        $current_datetime  = $this->convert();
        $arg1_datetime     = $arg1->convert();

        if ($current_datetime == $arg1_datetime) {
            return 0;
        } elseif ($current_datetime > $arg1_datetime) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * Return true if the current Dbms_datetime equals the Dbms_datetime $arg1 and false otherwise
     *
     * @param   Dbms_datetime  $arg1
     * @return  bool
     */
    public function equals(Dbms_datetime $arg1) {
        if ($this->compare_to($arg1) === 0) {
            return true;
        } else {
            return false;
        }
    }
}
