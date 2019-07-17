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

use LightORM\Dbms_manager;
use Concorde\utils\datetime\Dbms_datetime_pgsql;

/**
 * Dbms_manager_postgresql Class
 *
 * Contain the specific code for the PostgreSQL DBMS
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Dbms_manager_postgresql extends Dbms_manager
{
    /**
     * {@inheritDoc}
     */
    public function manage_value_for_add($business_full_name, $property, $property_value, $value) {
        $field_object = $business_full_name::get_table_field_object($property);

        if ($field_object->is_array()) {
            $retour = $property_value;

            $retour[] = $value;

            return $retour;
        } else {
            trigger_error('LightORM error: Property error', E_USER_ERROR);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manage_value_for_remove($business_full_name, $property, $property_value, $value) {
        $field_object = $business_full_name::get_table_field_object($property);

        if ($field_object->is_array()) {
            $retour = array();

            $found = false;
            foreach ($property_value as $item) {
                if ($found) {
                    $retour[] = $item;
                    continue;
                }

                if ($value instanceof Dbms_datetime_pgsql) {
                    if ($value.equals($item)) {
                        $found = true;
                    }
                } else {
                    if ($item == $value) {
                        $found = true;
                    }
                }

                if ( ! $found) {
                    $retour[] = $item;
                }
            }

            return $retour;
        } else {
            trigger_error('LightORM error: Property error', E_USER_ERROR);
        }
    }
}
