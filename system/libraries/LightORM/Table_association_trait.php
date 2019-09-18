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
    use Table_concrete_business_trait;

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
     * Get the table field object corresponding to the field $field in the related table
     *
     * @param   string  $field  The name of the field
     * @return  object
     */
    public static function get_table_field_object($field) {
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $table = $associations_metadata->associations[self::get_business_short_name()]['table'];

        $field_object = $data_conv->get_table_field_object($table, $field);
        if ($field_object !== false) {
            return $field_object;
        }

        return false;
    }

    /**
     * Get the model id given the joining field $joining_field
     *
     * @param   string  $joining_field
     * @return  int
     */
    public function get_model_id_by_joining_field($joining_field) {
        $associations_metadata = Associations_metadata::get_singleton();

        $association_array = $associations_metadata->get_association_array(['association' => self::get_business_short_name()]);

        foreach ($association_array['associates'] as $associate) {
            if ($associate['joining_field'] == $joining_field) {
                return $this->{'get_' . $associate['reverse_property']}()->get_id();
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
     * Return true if the current Association equals the Association $association and false otherwise
     *
     * @param   Association  $association
     * @return  bool
     */
    public function equals(Association $association) {
        if ((get_class($this) === get_class($association))
            && empty(array_diff_assoc($this->get_primary_key(), $association->get_primary_key()))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Manage the SELECT and JOIN parts of the query for the current Table_association_trait
     *
     * @param   Finder                                      $finder
     * @param   Business_associations_associatonents_group  $associatonents_group
     * @return  void
     */
    public static function business_initialization(Finder $finder, Business_associations_associatonents_group $associatonents_group) {
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $association_short_name = self::get_business_short_name();

        $association_table_alias = $finder->get_next_table_alias_numbered_name();

        $association_array         = $associations_metadata->get_association_array(['association' => $association_short_name]);
        $association_table_object  = $data_conv->schema[$association_array['table']];

        $association_table_object->business_selection($finder->main_qm, $association_table_alias);

        // This is a "LEFT JOIN" because there could be no matching row
        $finder->main_qm->join($association_table_object->name . ' AS ' . $association_table_alias, $association_table_alias . '.' . $associatonents_group->associatound_atom_joining_field . ' = ' . $associatonents_group->associatound_atom_alias . '.' . $associatonents_group->associatound_atom_field, 'left');
        if ($finder->has_offsetlimit_subquery) {
            $finder->offsetlimit_subquery_qm->join($association_table_object->name . ' AS ' . $association_table_alias, $association_table_alias . '.' . $associatonents_group->associatound_atom_joining_field . ' = ' . $associatonents_group->associatound_atom_alias . '.' . $associatonents_group->associatound_atom_field, 'left');
        }

        $associatonents_group->joining_alias                           = $association_table_alias;
        $associatonents_group->atoms_aliases[$association_short_name]  = $association_table_alias;
    }

    /**
     * Create the Business object
     *
     * @param   array   $finder_model_item
     * @param   object  $row
     * @param   array   $qm_aliases
     * @return  object
     */
    public static function business_creation($finder_model_item, $row, $qm_aliases) {
        $data_conv = Data_conv::factory();

        $association_table_alias = $finder_model_item['associate']->associatound_associatonents_group->joining_alias;

        if (is_null($row->{$association_table_alias . ':' . $finder_model_item['associate']->joining_field})) {
            return null;
        }

        $association_full_name = self::get_business_full_name();

        $table_object  = $data_conv->schema[$qm_aliases[$association_table_alias]];
        $args          = $table_object->business_creation_args($row, $association_table_alias);

        $retour = call_user_constructor_array_assoc($association_full_name, $args);

        return $retour;
    }

    /**
     * Return true if the concrete update manager exists and false otherwise
     *
     * @return  bool
     */
    public function concrete_update_manager_exists() {
        $association_short_name = self::get_business_short_name();

        $primary_key_scalar = $this->get_primary_key_scalar();

        if (isset($this->databubble->update_managers['associations'][$association_short_name][$primary_key_scalar])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the concrete update manager
     *
     * @return  array
     */
    public function get_concrete_update_manager() {
        $association_short_name = self::get_business_short_name();

        $primary_key_scalar = $this->get_primary_key_scalar();

        if ( ! isset($this->databubble->update_managers['associations'][$association_short_name][$primary_key_scalar])) {
            $this->databubble->update_managers['associations'][$association_short_name][$primary_key_scalar] = new Query_manager();
        }

        return $this->databubble->update_managers['associations'][$association_short_name][$primary_key_scalar];
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

        $association_short_name  = self::get_business_short_name();
        $association_array       = $associations_metadata->get_association_array(['association' => $association_short_name]);
        $table_object            = $data_conv->schema[$association_array['table']];

        if ( ! $table_object->field_exists($property)) {
            trigger_error('LightORM error: Unable to find the field', E_USER_ERROR);
        }

        if ( ! in_array($property, $associations_metadata->get_basic_properties($association_short_name))) {
            trigger_error('LightORM error: The property \'' . $property . '\' is not a basic property', E_USER_ERROR);
        }

        $this->{'set_' . $property}($value);

        $this->get_concrete_update_manager()->set($property, $value);

        return $this;
    }

    /**
     * Save the modifications in the database
     *
     * @return  bool
     */
    public function save() {
        $associations_metadata = Associations_metadata::get_singleton();

        $association_short_name  = self::get_business_short_name();
        $association_array       = $associations_metadata->get_association_array(['association' => $association_short_name]);
        $table                   = $association_array['table'];

        $retour = true;

        if ($this->concrete_update_manager_exists()) {
            $update_manager = $this->get_concrete_update_manager();
            $update_manager->table($table);

            foreach ($this->get_primary_key() as $field_name => $field_value) {
                $update_manager->where($field_name, $field_value);
            }

            $retour = $update_manager->update();
        }

        $primary_key_scalar = $this->get_primary_key_scalar();
        unset($this->databubble->update_managers['associations'][$association_short_name][$primary_key_scalar]);

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
            if ( ! $table_object->field_exists($field)) {
                trigger_error('LightORM error: Error while inserting data', E_USER_ERROR);
            }

            $qm->set($field, $value);
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
