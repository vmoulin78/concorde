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
 * Table_association_trait Trait
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
trait Table_association_trait
{
    use Table_concrete_business_trait;

    /**
     * Manage the SELECT part of the query for the current Table_association_trait
     *
     * @param   Query_manager  $query_manager
     * @param   string         $table_alias
     * @return  void
     */
    public static function business_selector(Query_manager $query_manager, $table_alias = null) {
        $data_conv = Data_conv::factory();

        $table_object = $data_conv->schema[business_to_table(self::get_business_full_name())];
        $table_object->business_selector($query_manager, $table_alias);
    }

    /**
     * Get the primary key fields
     *
     * @return  array
     */
    public static function get_primary_key_fields() {
        $data_conv = Data_conv::factory();

        $table_object = $data_conv->schema[business_to_table(self::get_business_full_name())];
        return $table_object->get_primary_key_fields();
    }

    /**
     * Get the model id given the field name $field_name
     *
     * @param   string  $field_name
     * @return  int
     */
    public function get_model_id_by_field_name($field_name) {
        if (substr($field_name, -3) != '_id') {
            trigger_error('LightORM error: Error in field name', E_USER_ERROR);
        }

        return call_user_func([call_user_func([$this, 'get_' . substr($field_name, 0, -3)]), 'get_id']);
    }

    /**
     * Get the primary key
     *
     * @return  array
     */
    public function get_primary_key() {
        $retour = array();
        foreach (self::get_primary_key_fields() as $primary_key_field) {
            $retour[$primary_key_field] = $this->get_model_id_by_field_name($primary_key_field);
        }

        return $retour;
    }

    /**
     * Get the concrete update manager
     *
     * @return  array
     */
    public function get_concrete_update_manager() {
        $lightORM = LightORM::get_singleton();

        $class_short_name = get_class_short_name($this);
        if ( ! isset($lightORM->association_update_managers[$class_short_name])) {
            $lightORM->association_update_managers[$class_short_name] = array();
        }

        $primary_key = $this->get_primary_key();
        $nb_primary_key_items = count($primary_key);

        $explorer =& $lightORM->association_update_managers[$class_short_name];
        $primary_key_counter = 1;
        foreach ($primary_key as $primary_key_field => $primary_key_value) {
            if ( ! isset($explorer[$primary_key_value])) {
                if ($primary_key_counter == $nb_primary_key_items) {
                    $explorer[$primary_key_value] = new Query_manager();
                } else {
                    $explorer[$primary_key_value] = array();
                }
            }

            $explorer =& $explorer[$primary_key_value];

            $primary_key_counter++;
        }

        return $explorer;
    }

    /**
     * {@inheritDoc}
     */
    protected function seek_field_in_abstract_table($attribute, $value) {
        return false;
    }

    /**
     * Trigger the UPDATE query
     *
     * @return  bool
     */
    public function update() {
        $lightORM = LightORM::get_singleton();

        $class_short_name  = get_class_short_name($this);
        $table             = business_to_table($this);

        $update_manager = $this->get_concrete_update_manager();
        $update_manager->table($table);

        foreach ($this->get_primary_key() as $field_name => $field_value) {
            $update_manager->where($field_name, $field_value);
        }

        $retour = $update_manager->update();

        $primary_key = $this->get_primary_key();
        $explorer =& $lightORM->association_update_managers[$class_short_name];
        while (count($primary_key) > 1) {
            $primary_key_value = array_shift($primary_key);
            $explorer =& $explorer[$primary_key_value];
        }
        unset($explorer[array_shift($primary_key)]);

        return $retour;
    }
}
