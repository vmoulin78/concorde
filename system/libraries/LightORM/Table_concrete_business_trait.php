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
    use Table_business_trait;

    /**
     * Seek the field given the attribute $attribute and the value $value in the related abstract table
     *
     * @param   string  $attribute
     * @param   mixed   $value
     * @return  bool
     */
    abstract protected function seek_field_in_abstract_table($attribute, $value);

    /**
     * Set the attribute $attribute with the value $value
     *
     * @param   string  $attribute
     * @param   mixed   $value
     * @return  object
     */
    public function set($attribute, $value) {
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $this->{'set_' . $attribute}($value);

        //------------------------------------------------------//

        $field_is_found = $this->seek_field_in_abstract_table($attribute, $value);

        if ( ! $field_is_found) {
            if (is_table_concrete_model($this)) {
                $table_object = $data_conv->schema[$models_metadata->models[$this::get_business_short_name()]['table']];
            } elseif (is_table_association($this)) {
                $table_object = $data_conv->schema[$associations_metadata->associations[$this::get_business_short_name()]['table']];
            } else {
                trigger_error('LightORM error: Business type error', E_USER_ERROR);
            }

            if ($table_object->field_exists($attribute)) {
                $field_name      = $attribute;
                $field_value     = $value;
                $field_is_found  = true;
            } elseif ($table_object->field_exists($attribute . '_id')) {
                $field_name = $attribute . '_id';
                if (is_null($value)) {
                    $field_value = null;
                } else {
                    $field_value = $value->get_id();
                }
                $field_is_found = true;
            }

            if ($field_is_found) {
                $this->get_concrete_update_manager()->set($field_name, $field_value);
            }
        }

        if ( ! $field_is_found) {
            trigger_error('LightORM error: Unable to find table field', E_USER_ERROR);
        }

        //------------------------------------------------------//

        return $this;
    }
}
