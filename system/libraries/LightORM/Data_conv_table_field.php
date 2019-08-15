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
 * @since       Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data_conv_table_field Class
 *
 * This class represents a field of a table of the database and is used in the context of the Data_conv Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Data_conv_table_field
{
    public $name;
    public $full_type;
    public $element_type;
    public $is_primary_key;
    public $is_foreign_key;
    public $array_depth;

    public function __construct($name, $full_type) {
        $this->name       = $name;
        $this->full_type  = $full_type;

        $element_type  = $full_type;
        $array_depth   = 0;
        while (substr($element_type, -2) === '[]') {
            $element_type = substr($element_type, 0, -2);
            $array_depth++;
        }
        $this->element_type  = $element_type;
        $this->array_depth   = $array_depth;

        if ($full_type === 'pk') {
            $this->is_primary_key  = true;
            $this->is_foreign_key  = false;
        } elseif (substr($full_type, 0, 3) === 'fk:') {
            $this->is_primary_key  = false;
            $this->is_foreign_key  = true;
        } elseif (substr($full_type, 0, 6) === 'pk_fk:') {
            $this->is_primary_key  = true;
            $this->is_foreign_key  = true;
        } else {
            $this->is_primary_key  = false;
            $this->is_foreign_key  = false;
        }
    }

    /**
     * Return true if the current Data_conv_table_field is an array and false otherwise
     *
     * @return  bool
     */
    public function is_array() {
        if ($this->array_depth === 0) {
            return false;
        } else {
            return true;
        }
    }
}
