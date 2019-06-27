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
    use Table_business_trait;

    /**
     * Get the primary key fields
     *
     * @return  array
     */
    public static function get_primary_key_fields() {
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $association_array  = $associations_metadata->get_association_array(['association' => self::get_business_short_name()]);
        $table_object       = $data_conv->schema[$association_array['table']];

        return $table_object->get_primary_key_fields();
    }

    /**
     * Get the model id given the field name $field_name
     *
     * @param   string  $field_name
     * @return  int
     */
    public function get_model_id_by_joining_field($joining_field) {
        $associations_metadata = Associations_metadata::get_singleton();

        $association_array = $associations_metadata->get_association_array(['association' => self::get_business_short_name()]);

        foreach ($association_array['associates'] as $associate) {
            if ($associate['joining_field'] == $joining_field) {
                return call_user_func([call_user_func([$this, 'get_' . $associate['reverse_property']]), 'get_id']);
            }
        }

        return false;
    }

    /**
     * Get the primary key
     *
     * @return  array
     */
    public function get_primary_key() {
        $retour = array();
        foreach (self::get_primary_key_fields() as $primary_key_field) {
            $retour[$primary_key_field] = $this->get_model_id_by_joining_field($primary_key_field);
        }

        return $retour;
    }

    /**
     * Get the primary key scalar
     *
     * @return  string
     */
    public function get_primary_key_scalar() {
        $primary_key = $this->get_primary_key();
        return implode(':', $primary_key);
    }

    /**
     * Manage the SELECT and JOIN parts of the query for the current Table_association_trait
     *
     * @param   Query_manager                               $query_manager
     * @param   int                                         $table_alias_number
     * @param   Business_associations_associatonents_group  $associatonents_group
     * @return  int
     */
    public static function business_initialization(Query_manager $query_manager, $table_alias_number, Business_associations_associatonents_group $associatonents_group) {
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $association_table_alias = 'alias_' . $table_alias_number;

        $association_array         = $associations_metadata->get_association_array(['association' => self::get_business_short_name()]);
        $association_table_object  = $data_conv->schema[$association_array['table']];

        $association_table_object->business_selection($query_manager, $association_table_alias);

        // This is a "LEFT JOIN" because there could be no matching row
        $query_manager->join($association_table_object->name . ' AS ' . $association_table_alias, $association_table_alias . '.' . $associatonents_group->associatound_atom_joining_field . ' = ' . $associatonents_group->associatound_atom_alias . '.' . $associatonents_group->associatound_atom_field, 'left');

        $table_alias_number++;

        return $table_alias_number;
    }

    /**
     * Create the Business object
     *
     * @param   array   $qm_model_item
     * @param   object  $row
     * @param   array   $qm_aliases
     * @return  object
     */
    public static function business_creation($qm_model_item, $row, $qm_aliases) {
        $data_conv = Data_conv::factory();

        $association_table_alias = $qm_model_item['associate']->associatound_associatonents_group->joining_alias;

        if (is_null($row->{$association_table_alias . ':' . $qm_model_item['associate']->joining_field})) {
            return null;
        }

        $table_object  = $data_conv->schema[$qm_aliases[$association_table_alias]];
        $args          = $table_object->business_creation_args($row, $association_table_alias);

        $association_full_name = self::get_business_full_name();
        $retour = new $association_full_name(...$args);

        return $retour;
    }

    /**
     * Get the concrete update manager
     *
     * @return  array
     */
    public function get_concrete_update_manager() {
        $lightORM = LightORM::get_singleton();

        $association_short_name = self::get_business_short_name();

        $primary_key_scalar = $this->get_primary_key_scalar();

        if ( ! isset($lightORM->association_update_managers[$association_short_name][$primary_key_scalar])) {
            $lightORM->association_update_managers[$association_short_name][$primary_key_scalar] = new Query_manager();
        }

        return $lightORM->association_update_managers[$association_short_name][$primary_key_scalar];
    }

    /**
     * Set the property $property with the value $value
     *
     * @param   string  $property
     * @param   mixed   $value
     * @return  object
     */
    public function set($property, $value) {
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $this->{'set_' . $property}($value);

        //------------------------------------------------------//

        $association_short_name  = self::get_business_short_name();
        $association_array       = $associations_metadata->get_association_array(['association' => $association_short_name]);
        $table_object            = $data_conv->schema[$association_array['table']];

        if ($table_object->field_exists($property)) {
            $field_name   = $property;
            $field_value  = $value;
        } elseif ($table_object->field_exists($property . '_id')
            && $table_object->field_is_enum_model_id($property . '_id')
        ) {
            $field_name = $property . '_id';
            if (is_null($value)) {
                $field_value = null;
            } else {
                $field_value = $value->get_id();
            }
        } else {
            trigger_error('LightORM error: Unable to find table field', E_USER_ERROR);
        }

        $this->get_concrete_update_manager()->set($field_name, $field_value);

        //------------------------------------------------------//

        return $this;
    }

    /**
     * Save the modifications in the database
     *
     * @return  bool
     */
    public function save() {
        $associations_metadata  = Associations_metadata::get_singleton();
        $lightORM               = LightORM::get_singleton();

        $association_short_name  = self::get_business_short_name();
        $association_array       = $associations_metadata->get_association_array(['association' => $association_short_name]);
        $table                   = $association_array['table'];

        $update_manager = $this->get_concrete_update_manager();
        $update_manager->table($table);

        foreach ($this->get_primary_key() as $field_name => $field_value) {
            $update_manager->where($field_name, $field_value);
        }

        $retour = $update_manager->update();

        $primary_key_scalar = $this->get_primary_key_scalar();
        unset($lightORM->association_update_managers[$association_short_name][$primary_key_scalar]);

        return $retour;
    }

    /**
     * Set the data $data for the database
     *
     * @param   array  $data  An associative array whose keys are the fields and values are the values
     * @return  object
     */
    private static function set_data_for_database($data) {
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $association_array  = $associations_metadata->get_association_array(['association' => self::get_business_short_name()]);
        $table_object       = $data_conv->schema[$association_array['table']];

        $qm = new Query_manager();
        $qm->table($table_object->name);
        foreach ($data as $field => $value) {
            if ( ! isset($table_object->fields[$field])) {
                trigger_error('LightORM error: Error while inserting data', E_USER_ERROR);
            }

            $field_object = $table_object->fields[$field];
            if ($field_object->is_enum_model_id
                && ( ! is_null($value))
            ) {
                $db_value = (string) $value;
            } else {
                $db_value = $data_conv->convert_value_for_db($value, $field_object);
            }

            $qm->simple_set($field, $db_value, false);
        }

        return $qm;
    }

    /**
     * Insert in the database the table association whose data are $data
     *
     * @param   array  $data  An associative array whose keys are the fields and values are the values
     * @return  bool
     */
    public static function insert($data) {
        $qm = self::set_data_for_database($data);

        return $qm->insert();
    }

    /**
     * Update in the database the table association whose ids of the primary key are $primary_key_ids with the data $data
     *
     * @param   array  $primary_key_ids  An associative array whose keys are the fields of the primary key and values are the values of the primary key
     * @param   array  $data             An associative array whose keys are the fields and values are the values
     * @return  bool
     */
    public static function update($primary_key_ids, $data) {
        $qm = self::set_data_for_database($data);

        foreach ($primary_key_ids as $field => $value) {
            $qm->where($field, $value);
        }

        return $qm->update();
    }

    /**
     * Delete from the database the table association whose ids of the primary key are $primary_key_ids
     *
     * @param   array  $primary_key_ids  An associative array whose keys are the fields of the primary key and values are the values of the primary key
     * @return  bool
     */
    public static function delete($primary_key_ids) {
        $associations_metadata = Associations_metadata::get_singleton();

        $association_array = $associations_metadata->get_association_array(['association' => self::get_business_short_name()]);

        $qm = new Query_manager();
        $qm->table($association_array['table']);
        foreach ($primary_key_ids as $field => $value) {
            $qm->where($field, $value);
        }
        if ($qm->delete() === false) {
            return false;
        }

        return true;
    }
}
