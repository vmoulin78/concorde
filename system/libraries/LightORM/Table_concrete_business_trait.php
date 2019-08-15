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
 * Table_concrete_business_trait Trait
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
trait Table_concrete_business_trait
{
    /**
     * Add the value $value to the property $property
     *
     * @param   string  $property
     * @param   mixed   $value
     * @return  object
     */
    public function add($property, $value) {
        $dbms_manager = Dbms_manager::factory();

        $business_full_name  = self::get_business_full_name();
        $property_value      = $this->{'get_' . $property}();

        $new_property_value = $dbms_manager->manage_value_for_add($business_full_name, $property, $property_value, $value);

        return $this->set($property, $new_property_value);
    }

    /**
     * Remove the value $value from the property $property
     *
     * In case of duplicate values, only one value is removed.
     *
     * @param   string  $property
     * @param   mixed   $value
     * @return  object
     */
    public function remove($property, $value) {
        $dbms_manager = Dbms_manager::factory();

        $business_full_name  = self::get_business_full_name();
        $property_value      = $this->{'get_' . $property}();

        $new_property_value = $dbms_manager->manage_value_for_remove($business_full_name, $property, $property_value, $value);

        return $this->set($property, $new_property_value);
    }
}
