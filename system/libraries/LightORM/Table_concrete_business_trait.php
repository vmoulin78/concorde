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
 * @since       Version 0.0.0
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
        $CI =& get_instance();

        $dbms          = $CI->db->dbms;
        $field_object  = self::get_table_field_object($property);

        $property_value = $this->{'get_' . $property}();

        if (($dbms === 'postgresql')
            && ($field_object->is_array())
        ) {
            // nothing to do
        } elseif (($dbms === 'mysql')
            && ($field_object->full_type === 'set')
        ) {
            if (in_array($value, $property_value)) {
                return $this;
            }
        } else {
            trigger_error('LightORM error: Property error', E_USER_ERROR);
        }

        $property_value[] = $value;
        return $this->set($property, $property_value);
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
        $CI =& get_instance();

        $dbms          = $CI->db->dbms;
        $field_object  = self::get_table_field_object($property);

        if ((($dbms === 'postgresql') && ($field_object->is_array()))
            || (($dbms === 'mysql') && ($field_object->full_type === 'set'))
        ) {
            $property_value = $this->{'get_' . $property}();

            if ( ! in_array($value, $property_value)) {
                return $this;
            }

            $new_property_value  = array();
            $found               = false;
            foreach ($property_value as $item) {
                if (($item != $value)
                    || $found
                ) {
                    $new_property_value[] = $item;
                }

                if ($item == $value) {
                    $found = true;
                }
            }

            return $this->set($property, $new_property_value);
        } else {
            trigger_error('LightORM error: Property error', E_USER_ERROR);
        }
    }
}
