<?php
namespace Concorde\artefact;

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
 * Table_concrete_model_trait Trait
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
trait Table_concrete_model_trait
{
    use Table_model_trait;
    use Table_concrete_business_trait;
    use Elemental_concrete_model_trait;

    /**
     * Get the name of the table abstract model
     *
     * @return  string
     */
    public static function get_table_abstract_model() {
        $models_metadata = Models_metadata::get_singleton();

        return $models_metadata->get_table_abstract_model(self::get_business_short_name());
    }

    /**
     * Get the name of the abstract table
     *
     * @return  string
     */
    public static function get_abstract_table() {
        $models_metadata = Models_metadata::get_singleton();

        $table_abstract_model = self::get_table_abstract_model();
        if (is_null($table_abstract_model)) {
            return null;
        }
        return $models_metadata->models[$table_abstract_model]['table'];
    }

    /**
     * Get the table field object corresponding to the field $field in the related table or in the related abstract table
     *
     * @param   string  $field  The name of the field
     * @return  object
     */
    public static function get_table_field_object($field) {
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        $table = $models_metadata->models[self::get_business_short_name()]['table'];

        $field_object = $data_conv->get_table_field_object($table, $field);
        if ($field_object !== false) {
            return $field_object;
        }

        $abstract_table = self::get_abstract_table();
        if ( ! is_null($abstract_table)) {
            $field_object = $data_conv->get_table_field_object($abstract_table, $field);
            if ($field_object !== false) {
                return $field_object;
            }
        }

        return false;
    }

    /**
     * Manage the SELECT, FROM and JOIN parts of the query for the current table concrete model
     *
     * @param   Finder                           $finder
     * @param   Business_associations_associate  $associate
     * @param   array                            $associatonents_properties
     * @return  void
     */
    public static function business_initialization(Finder $finder, Business_associations_associate $associate, array $associatonents_properties = []) {
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        $model_short_name  = self::get_business_short_name();
        $atom_path         = $model_short_name;

        //----------------------------------------------------------------------------//

        $table_alias          = $finder->get_next_table_alias_numbered_name();
        $model_numbered_name  = $finder->get_next_model_numbered_name();
        $table_object         = $data_conv->schema[$models_metadata->models[$model_short_name]['table']];

        $table_object->business_selection($finder->main_qm, $table_alias);

        if (is_null($associate->associatound_associatonents_group)) {
            $finder->main_qm->from($table_object->name . ' AS ' . $table_alias);
            if ($finder->has_offsetlimit_subquery) {
                $finder->offsetlimit_subquery_qm->from($table_object->name . ' AS ' . $table_alias);
                $finder->offsetlimit_subquery_qm->group_by($table_alias . '.id');
            }
        } else {
            // This is a "LEFT JOIN" because there could be no matching row
            $finder->main_qm->join($table_object->name . ' AS ' . $table_alias, $table_alias . '.' . $associate->associatonent_field . ' = ' . $associate->associatound_associatonents_group->joining_alias . '.' . $associate->joining_field, 'left');
            if ($finder->has_offsetlimit_subquery) {
                $finder->offsetlimit_subquery_qm->join($table_object->name . ' AS ' . $table_alias, $table_alias . '.' . $associate->associatonent_field . ' = ' . $associate->associatound_associatonents_group->joining_alias . '.' . $associate->joining_field, 'left');
            }
        }

        //----------------------------------------------------------------------------//

        $table_abstract_model                      = self::get_table_abstract_model();
        $concrete_model_associatonents_properties  = isset($associatonents_properties[$atom_path]) ? $associatonents_properties[$atom_path] : array();
        if (isset($table_abstract_model)) {
            $table_abstract_model_atom_path = $atom_path . '<' . $table_abstract_model;

            $abstract_table        = $models_metadata->models[$table_abstract_model]['table'];
            $abstract_table_alias  = $finder->get_next_table_alias_numbered_name();

            $abstract_table_object = $data_conv->schema[$abstract_table];

            $abstract_table_object->business_selection($finder->main_qm, $abstract_table_alias);

            // This is a "LEFT JOIN" because there could be no matching row
            $finder->main_qm->join($abstract_table_object->name . ' AS ' . $abstract_table_alias, $abstract_table_alias . '.id = ' . $table_alias . '.id', 'left');
            if ($finder->has_offsetlimit_subquery) {
                $finder->offsetlimit_subquery_qm->join($abstract_table_object->name . ' AS ' . $abstract_table_alias, $abstract_table_alias . '.id = ' . $table_alias . '.id', 'left');

                if (is_null($associate->associatound_associatonents_group)) {
                    $finder->offsetlimit_subquery_qm->group_by($abstract_table_alias . '.id');
                }
            }

            $associate->atoms_numbered_names[$table_abstract_model_atom_path]  = $model_numbered_name;
            $associate->atoms_aliases[$table_abstract_model_atom_path]         = $abstract_table_alias;

            $abstract_model_associatonents_properties = isset($associatonents_properties[$table_abstract_model_atom_path]) ? $associatonents_properties[$table_abstract_model_atom_path] : array();

            $model_info = array(
                'type'                       => 'concrete_model',
                'concrete_alias'             => $table_alias,
                'abstract_alias'             => $abstract_table_alias,
                'associatonents_properties'  => array(
                    'concrete_model'  => $concrete_model_associatonents_properties,
                    'abstract_model'  => $abstract_model_associatonents_properties,
                ),
            );
        } else {
            $model_info = array(
                'type'                       => 'simple_model',
                'alias'                      => $table_alias,
                'associatonents_properties'  => $concrete_model_associatonents_properties,
            );
        }

        //----------------------------------------------------------------------------//

        $finder->models[$model_numbered_name] = array(
            'name'        => $model_short_name,
            'associate'   => $associate,
            'model_info'  => $model_info,
        );

        $associate->atoms_numbered_names[$atom_path]  = $model_numbered_name;
        $associate->atoms_aliases[$atom_path]         = $table_alias;
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

        $model_full_name = self::get_business_full_name();

        switch ($finder_model_item['model_info']['type']) {
            case 'simple_model':
                if (is_null($row->{$finder_model_item['model_info']['alias'] . ':id'})) {
                    return null;
                }

                $table_object  = $data_conv->schema[$qm_aliases[$finder_model_item['model_info']['alias']]];
                $args          = $table_object->business_creation_args($row, $finder_model_item['model_info']['alias']);
                break;
            case 'concrete_model':
                if (is_null($row->{$finder_model_item['model_info']['concrete_alias'] . ':id'})) {
                    return null;
                }

                $abstract_table_object  = $data_conv->schema[$qm_aliases[$finder_model_item['model_info']['abstract_alias']]];
                $args                   = $abstract_table_object->business_creation_args($row, $finder_model_item['model_info']['abstract_alias']);
                $concrete_table_object  = $data_conv->schema[$qm_aliases[$finder_model_item['model_info']['concrete_alias']]];
                $args                   = array_merge($args, $concrete_table_object->business_creation_args($row, $finder_model_item['model_info']['concrete_alias']));
                break;
            default:
                exit(1);
                break;
        }

        $retour = call_user_constructor_array_assoc($model_full_name, $args);

        return $retour;
    }

    /**
     * Return true if the concrete update manager exists and false otherwise
     *
     * @return  bool
     */
    public function concrete_update_manager_exists() {
        $model_short_name = self::get_business_short_name();

        if (isset($this->databubble->update_managers['models'][$model_short_name][$this->get_id()]['concrete'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return true if the abstract update manager exists and false otherwise
     *
     * @return  bool
     */
    public function abstract_update_manager_exists() {
        $model_short_name = self::get_business_short_name();

        if (isset($this->databubble->update_managers['models'][$model_short_name][$this->get_id()]['abstract'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the update managers
     *
     * @return  array
     */
    private function &get_update_managers() {
        $model_short_name = self::get_business_short_name();

        if ( ! isset($this->databubble->update_managers['models'][$model_short_name][$this->get_id()])) {
            $this->databubble->update_managers['models'][$model_short_name][$this->get_id()] = array(
                'abstract'  => null,
                'concrete'  => null,
            );
        }

        return $this->databubble->update_managers['models'][$model_short_name][$this->get_id()];
    }

    /**
     * Get the concrete update manager
     *
     * @return  array
     */
    public function get_concrete_update_manager() {
        $update_managers =& $this->get_update_managers();

        if (is_null($update_managers['concrete'])) {
            $update_managers['concrete'] = new Query_manager();
        }

        return $update_managers['concrete'];
    }

    /**
     * Get the abstract update manager
     *
     * @return  array
     */
    public function get_abstract_update_manager() {
        $update_managers =& $this->get_update_managers();

        if (is_null($update_managers['abstract'])) {
            $update_managers['abstract'] = new Query_manager();
        }

        return $update_managers['abstract'];
    }

    /**
     * Manage the 'set' for the objects and for the database
     *
     * @param   string                 $property
     * @param   mixed                  $value
     * @param   'abstract'|'concrete'  $ref_model_type
     * @return  bool
     */
    private function set_for_ref_model_type($property, $value, $ref_model_type) {
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        $model_short_name = self::get_business_short_name();

        switch ($ref_model_type) {
            case 'abstract':
                $ref_model_short_name = self::get_table_abstract_model();
                if (is_null($ref_model_short_name)) {
                    return false;
                }
                $table = self::get_abstract_table();
                break;
            case 'concrete':
                $ref_model_short_name  = $model_short_name;
                $table                 = $models_metadata->models[$model_short_name]['table'];
                break;
            default:
                exit(1);
                break;
        }

        $table_object = $data_conv->schema[$table];
        if ( ! $table_object->field_exists($property)) {
            return false;
        }

        if ( ! in_array($property, $models_metadata->get_basic_properties($model_short_name))) {
            trigger_error('Artefact error: The property \'' . $property . '\' is not a basic property', E_USER_ERROR);
        }

        $this->{'set_' . $property}($value);

        $this->{'get_' . $ref_model_type . '_update_manager'}()->set($property, $value);

        return true;
    }

    /**
     * Set the basic property $property with the value $value
     *
     * @param   string  $property
     * @param   mixed   $value
     * @return  object
     */
    public function set($property, $value) {
        if ($this->set_for_ref_model_type($property, $value, 'abstract') === false) {
            if ($this->set_for_ref_model_type($property, $value, 'concrete') === false) {
                trigger_error('Artefact error: Unable to find the field', E_USER_ERROR);
            }
        }

        //------------------------------------------------------//

        return $this;
    }

    /**
     * Manage the 'set_assoc' for the objects and for the database
     *
     * @param   string                 $property
     * @param   mixed                  $value
     * @param   bool                   $auto_commit
     * @param   'abstract'|'concrete'  $ref_model_type
     * @return  int
     */
    private function set_assoc_for_ref_model_type($property, $value, $auto_commit, $ref_model_type) {
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();

        $model_short_name = self::get_business_short_name();

        switch ($ref_model_type) {
            case 'abstract':
                $ref_model_short_name = self::get_table_abstract_model();
                if (is_null($ref_model_short_name)) {
                    return 0;
                }
                break;
            case 'concrete':
                $ref_model_short_name = $model_short_name;
                break;
            default:
                exit(1);
                break;
        }

        
        $associate_array = $associations_metadata->get_associate_array(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        if ($associate_array === false) {
            return 0;
        }

        if ($associate_array['dimension'] !== 'one') {
            trigger_error('Artefact error: Property error', E_USER_ERROR);
        }

        if (( ! is_null($value))
            && ($this->databubble !== $value->databubble)
        ) {
            trigger_error('Artefact error: The databubble of the associate must be the one of the current object', E_USER_ERROR);
        }

        $this->{'set_' . $property}($value);

        //------------------------------------------------------//

        list($opposite_associate_array) = $associations_metadata->get_opposite_associates_arrays(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        if (isset($this->databubble->models[$opposite_associate_array['model']])) {
            $associate_is_found = false;
            foreach ($this->databubble->models[$opposite_associate_array['model']] as $item1) {
                $opposite_associate_property_value = $item1->{'get_' . $opposite_associate_array['property']}();

                if (is_undefined($opposite_associate_property_value)) {
                    continue;
                }

                switch ($opposite_associate_array['dimension']) {
                    case 'one':
                        if (( ! is_null($opposite_associate_property_value))
                            && ($opposite_associate_property_value === $this)
                        ) {
                            $item1->{'set_' . $opposite_associate_array['property']}(null);
                            $associate_is_found = true;
                        }
                        break;
                    case 'many':
                        $new_opposite_associate_property_value = array();
                        foreach ($opposite_associate_property_value as $item2) {
                            if ($item2 === $this) {
                                $associate_is_found = true;
                            } else {
                                $new_opposite_associate_property_value[] = $item2;
                            }
                        }
                        $item1->{'set_' . $opposite_associate_array['property']}($new_opposite_associate_property_value);
                        break;
                    default:
                        exit(1);
                        break;
                }

                if ($associate_is_found) {
                    break;
                }
            }

            if ( ! is_null($value)) {
                foreach ($this->databubble->models[$opposite_associate_array['model']] as $item1) {
                    if ($item1 !== $value) {
                        continue;
                    }

                    $opposite_associate_property_value = $item1->{'get_' . $opposite_associate_array['property']}();

                    switch ($opposite_associate_array['dimension']) {
                        case 'one':
                            $item1->{'set_' . $opposite_associate_array['property']}($this);
                            break;
                        case 'many':
                            if ( ! is_undefined($opposite_associate_property_value)) {
                                $opposite_associate_property_value[] = $this;
                                $item1->{'set_' . $opposite_associate_array['property']}($opposite_associate_property_value);
                            }
                            break;
                        default:
                            exit(1);
                            break;
                    }

                    break;
                }
            }
        }

        //------------------------------------------------------//

        if ($auto_commit) {
            $qm = new Query_manager();

            if ($associate_array['field'] !== 'id') {
                switch ($ref_model_type) {
                    case 'abstract':
                        $table = self::get_abstract_table();
                        break;
                    case 'concrete':
                        $table = $models_metadata->models[$model_short_name]['table'];
                        break;
                    default:
                        exit(1);
                        break;
                }

                if (is_null($value)) {
                    $field_value = null;
                } else {
                    $field_value = $value->get_id();
                }

                $qm->table($table)
                   ->set($associate_array['field'], $field_value)
                   ->where('id', $this->get_id());
            } elseif ($opposite_associate_array['field'] !== 'id') {
                $table = $models_metadata->models[$opposite_associate_array['model']]['table'];

                $qm->table($table);

                if (is_null($value)) {
                    $qm->set($opposite_associate_array['field'], null)
                       ->where($opposite_associate_array['field'], $this->get_id());
                } else {
                    $qm->set($opposite_associate_array['field'], $this->get_id())
                       ->where('id', $value->get_id());
                }
            } else {
                exit(1);
            }

            $qm_result = $qm->update();

            if ($qm_result) {
                return 1;
            } else {
                return -1;
            }
        } else {
            if ($associate_array['field'] === 'id') {
                trigger_error('Artefact error: The updated field must be in the table corresponding to the current object', E_USER_ERROR);
            }

            if (is_null($value)) {
                $field_value = null;
            } else {
                $field_value = $value->get_id();
            }

            $this->{'get_' . $ref_model_type . '_update_manager'}()->set($associate_array['field'], $field_value);

            return 1;
        }
    }

    /**
     * Set the association property $property with the value $value
     *
     * If $auto_commit is true then the database will be automatically updated
     * Otherwise a call to the function save() will be required
     * The parameter $auto_commit may be set to false only if the updated field is in the table corresponding to the current object
     *
     * @param   string  $property
     * @param   mixed   $value
     * @param   bool    $auto_commit
     * @return  bool
     */
    public function set_assoc($property, $value, $auto_commit = true) {
        if (( ! is_null($value))
            && ( ! is_object($value))
        ) {
            trigger_error('Artefact error: Property error', E_USER_ERROR);
        }

        $abstract_set_assoc_result = $this->set_assoc_for_ref_model_type($property, $value, $auto_commit, 'abstract');
        if ($abstract_set_assoc_result === 1) {
            return true;
        } elseif ($abstract_set_assoc_result === -1) {
            return false;
        }

        $concrete_set_assoc_result = $this->set_assoc_for_ref_model_type($property, $value, $auto_commit, 'concrete');
        if ($concrete_set_assoc_result === 1) {
            return true;
        } elseif ($concrete_set_assoc_result === -1) {
            return false;
        }

        trigger_error('Artefact error: Unable to find the association', E_USER_ERROR);
    }

    /**
     * Manage the 'add_assoc' for the objects and for the database
     *
     * @param   string                 $property
     * @param   mixed                  $data
     * @param   'abstract'|'concrete'  $ref_model_type
     * @return  int
     */
    private function add_assoc_for_ref_model_type($property, $data, $ref_model_type) {
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $model_short_name = self::get_business_short_name();

        switch ($ref_model_type) {
            case 'abstract':
                $ref_model_short_name = self::get_table_abstract_model();
                if (is_null($ref_model_short_name)) {
                    return 0;
                }
                break;
            case 'concrete':
                $ref_model_short_name = $model_short_name;
                break;
            default:
                exit(1);
                break;
        }

        $association_array = $associations_metadata->get_association_array(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        if ($association_array === false) {
            return 0;
        }

        $associate_array = $associations_metadata->get_associate_array(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        if ($associate_array['dimension'] !== 'many') {
            trigger_error('Artefact error: Property error', E_USER_ERROR);
        }

        $opposite_associates_arrays = $associations_metadata->get_opposite_associates_arrays(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        switch ($association_array['type']) {
            case 'one_to_many':
                list($opposite_associate_array) = $opposite_associates_arrays;

                $set_assoc_result = $data->set_assoc($opposite_associate_array['property'], $this);
                if ($set_assoc_result === false) {
                    return -1;
                }

                break;
            case 'many_to_many':
                if ( ! is_array($data)) {
                    trigger_error('Artefact error: Property error', E_USER_ERROR);
                }

                foreach ($opposite_associates_arrays as $item) {
                    if ($this->databubble !== $data[$item['reverse_property']]->databubble) {
                        trigger_error('Artefact error: The databubble of the associate must be the one of the current object', E_USER_ERROR);
                    }
                }

                $association_short_name        = $association_array['class'];
                $association_full_name         = $association_array['class_full_name'];
                $association_basic_properties  = $associations_metadata->get_basic_properties($association_short_name);

                $data_for_association_insert = array();
                $data_for_association_insert[$associate_array['joining_field']] = $this->get_id();
                foreach ($opposite_associates_arrays as $item) {
                    $data_for_association_insert[$item['joining_field']] = $data[$item['reverse_property']]->get_id();
                }
                foreach ($data as $key => $item) {
                    if (in_array($key, $association_basic_properties)) {
                        $data_for_association_insert[$key] = $item;
                    }
                }
                $association_insert_result = $association_full_name::insert($data_for_association_insert);

                if ($association_insert_result === false) {
                    return -1;
                }

                $all_association_properties_are_undefined = is_undefined($this->{'get_' . $property}());
                foreach ($opposite_associates_arrays as $opposite_associate_array) {
                    $opposite_associate_instance = $data[$opposite_associate_array['reverse_property']];
                    $all_association_properties_are_undefined = $all_association_properties_are_undefined && is_undefined($opposite_associate_instance->{'get_' . $opposite_associate_array['property']}());
                }
                if ( ! $all_association_properties_are_undefined) {
                    $association_table_object = $data_conv->schema[$association_array['table']];
                    $qm = new Query_manager();
                    $association_table_object->business_selection($qm);
                    $qm->from($association_array['table'])
                       ->where($associate_array['joining_field'], $this->get_id());
                    foreach ($opposite_associates_arrays as $opposite_associate_array) {
                        $qm->where($opposite_associate_array['joining_field'], $data[$opposite_associate_array['reverse_property']]->get_id());
                    }
                    $query = $qm->get();
                    $row = $query->row();
                    $args = $association_table_object->business_creation_args($row);
                    $association_instance = call_user_constructor_array_assoc($association_full_name, $args);

                    $association_instance->{'set_' . $associate_array['reverse_property']}($this);

                    $associate_property_value = $this->{'get_' . $property}();
                    if ( ! is_undefined($associate_property_value)) {
                        $associate_property_value[] = $association_instance;
                        $this->{'set_' . $property}($associate_property_value);
                    }

                    foreach ($opposite_associates_arrays as $opposite_associate_array) {
                        $opposite_associate_instance = $data[$opposite_associate_array['reverse_property']];

                        $association_instance->{'set_' . $opposite_associate_array['reverse_property']}($opposite_associate_instance);

                        $opposite_associate_property_value = $opposite_associate_instance->{'get_' . $opposite_associate_array['property']}();
                        if ( ! is_undefined($opposite_associate_property_value)) {
                            $opposite_associate_property_value[] = $association_instance;
                            $opposite_associate_instance->{'set_' . $opposite_associate_array['property']}($opposite_associate_property_value);
                        }
                    }

                    $this->databubble->add_association_instance($association_instance);
                }

                break;
            default:
                exit(1);
                break;
        }

        return 1;
    }

    /**
     * Add the data $data to the association property $property
     *
     * @param   string  $property
     * @param   mixed   $data
     * @return  bool
     */
    public function add_assoc($property, $data) {
        $abstract_add_assoc_result = $this->add_assoc_for_ref_model_type($property, $data, 'abstract');
        if ($abstract_add_assoc_result === 1) {
            return true;
        } elseif ($abstract_add_assoc_result === -1) {
            return false;
        }

        $concrete_add_assoc_result = $this->add_assoc_for_ref_model_type($property, $data, 'concrete');
        if ($concrete_add_assoc_result === 1) {
            return true;
        } elseif ($concrete_add_assoc_result === -1) {
            return false;
        }

        trigger_error('Artefact error: Unable to find the association', E_USER_ERROR);
    }

    /**
     * Manage the 'remove_assoc' for the objects and for the database
     *
     * @param   string                 $property
     * @param   mixed                  $data
     * @param   'abstract'|'concrete'  $ref_model_type
     * @return  int
     */
    private function remove_assoc_for_ref_model_type($property, $data, $ref_model_type) {
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();
        $data_conv              = Data_conv::factory();

        $model_short_name = self::get_business_short_name();

        switch ($ref_model_type) {
            case 'abstract':
                $ref_model_short_name = self::get_table_abstract_model();
                if (is_null($ref_model_short_name)) {
                    return 0;
                }
                break;
            case 'concrete':
                $ref_model_short_name = $model_short_name;
                break;
            default:
                exit(1);
                break;
        }

        $association_array = $associations_metadata->get_association_array(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        if ($association_array === false) {
            return 0;
        }

        $associate_array = $associations_metadata->get_associate_array(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        if ($associate_array['dimension'] !== 'many') {
            trigger_error('Artefact error: Property error', E_USER_ERROR);
        }

        $opposite_associates_arrays = $associations_metadata->get_opposite_associates_arrays(
            array(
                'model'     => $ref_model_short_name,
                'property'  => $property,
            )
        );

        switch ($association_array['type']) {
            case 'one_to_many':
                list($opposite_associate_array) = $opposite_associates_arrays;

                $set_assoc_result = $data->set_assoc($opposite_associate_array['property'], null);
                if ($set_assoc_result === false) {
                    return -1;
                }

                break;
            case 'many_to_many':
                if ( ! is_array($data)) {
                    trigger_error('Artefact error: Property error', E_USER_ERROR);
                }

                foreach ($opposite_associates_arrays as $item) {
                    if ($this->databubble !== $data[$item['reverse_property']]->databubble) {
                        trigger_error('Artefact error: The databubble of the associate must be the one of the current object', E_USER_ERROR);
                    }
                }

                $association_short_name  = $association_array['class'];
                $association_full_name   = $association_array['class_full_name'];

                $data_for_association_delete = array();
                $data_for_association_delete[$associate_array['joining_field']] = $this->get_id();
                foreach ($opposite_associates_arrays as $item) {
                    $data_for_association_delete[$item['joining_field']] = $data[$item['reverse_property']]->get_id();
                }
                $association_delete_result = $association_full_name::delete($data_for_association_delete);

                if ($association_delete_result === false) {
                    return -1;
                }

                $association_instance = null;
                foreach ($this->databubble->associations[$association_short_name] as $item1) {
                    $item1_associate_instance = $item1->{'get_' . $associate_array['reverse_property']}();
                    if ($item1_associate_instance->get_id() !== $this->get_id()) {
                        continue;
                    }

                    foreach ($opposite_associates_arrays as $item2) {
                        $item1_opposite_associate_instance = $item1->{'get_' . $item2['reverse_property']}();
                        if ($item1_opposite_associate_instance->get_id() !== $data[$item2['reverse_property']]->get_id()) {
                            continue 2;
                        }
                    }

                    $association_instance = $item1;
                    break;
                }

                if ( ! is_null($association_instance)) {
                    foreach ($association_array['associates'] as $item1) {
                        $item1_associate_instance = $association_instance->{'get_' . $item1['reverse_property']}();

                        $item1_associate_instance_property_value = $item1_associate_instance->{'get_' . $item1['property']}();
                        if ( ! is_undefined($item1_associate_instance_property_value)) {
                            $new_item1_associate_instance_property_value = array();
                            foreach ($item1_associate_instance_property_value as $item2) {
                                if ($item2 !== $association_instance) {
                                    $new_item1_associate_instance_property_value[] = $item2;
                                }
                            }
                            $item1_associate_instance->{'set_' . $item1['property']}($new_item1_associate_instance_property_value);
                        }
                    }

                    $this->databubble->remove_association_instance($association_instance);
                }

                break;
            default:
                exit(1);
                break;
        }

        return 1;
    }

    /**
     * Remove the data $data from the association property $property
     *
     * @param   string  $property
     * @param   mixed   $data
     * @return  bool
     */
    public function remove_assoc($property, $data) {
        $abstract_remove_assoc_result = $this->remove_assoc_for_ref_model_type($property, $data, 'abstract');
        if ($abstract_remove_assoc_result === 1) {
            return true;
        } elseif ($abstract_remove_assoc_result === -1) {
            return false;
        }

        $concrete_remove_assoc_result = $this->remove_assoc_for_ref_model_type($property, $data, 'concrete');
        if ($concrete_remove_assoc_result === 1) {
            return true;
        } elseif ($concrete_remove_assoc_result === -1) {
            return false;
        }

        trigger_error('Artefact error: Unable to find the association', E_USER_ERROR);
    }

    /**
     * Save the modifications in the database
     *
     * @return  bool
     */
    public function save() {
        $models_metadata = Models_metadata::get_singleton();

        $model_short_name                = self::get_business_short_name();
        $concrete_update_manager_result  = true;
        $abstract_update_manager_result  = true;

        if ($this->concrete_update_manager_exists()) {
            $concrete_update_manager = $this->get_concrete_update_manager();
            $concrete_update_manager->table($models_metadata->models[$model_short_name]['table'])
                                    ->where('id', $this->get_id());
            $concrete_update_manager_result = $concrete_update_manager->update();
        }

        if ($this->abstract_update_manager_exists()) {
            $abstract_update_manager = $this->get_abstract_update_manager();
            $abstract_update_manager->table(self::get_abstract_table())
                                    ->where('id', $this->get_id());
            $abstract_update_manager_result = $abstract_update_manager->update();
        }

        unset($this->databubble->update_managers['models'][$model_short_name][$this->get_id()]);

        return $concrete_update_manager_result && $abstract_update_manager_result;
    }

    /**
     * Set the data $data for the database
     *
     * @param   array  $data  An associative array whose keys are the fields and values are the values
     * @return  array
     */
    private static function set_data_for_database($data) {
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        $concrete_table_object = $data_conv->schema[$models_metadata->models[self::get_business_short_name()]['table']];
        $qm_concrete_table = new Query_manager();
        $qm_concrete_table->table($concrete_table_object->name);

        $abstract_table = self::get_abstract_table();
        if (is_null($abstract_table)) {
            $abstract_table_object  = null;
            $qm_abstract_table      = null;
        } else {
            $abstract_table_object  = $data_conv->schema[$abstract_table];
            $qm_abstract_table      = new Query_manager();
            $qm_abstract_table->table($abstract_table_object->name);
        }

        foreach ($data as $field => $value) {
            if (isset($concrete_table_object->fields[$field])) {
                $current_table_type = 'concrete';
            } elseif (( ! is_null($abstract_table_object))
                && isset($abstract_table_object->fields[$field])
            ) {
                $current_table_type = 'abstract';
            } else {
                trigger_error('Artefact error: Error while inserting data', E_USER_ERROR);
            }

            ${'qm_' . $current_table_type . '_table'}->set($field, $value);
        }

        return array(
            'qm_abstract_table'  => $qm_abstract_table,
            'qm_concrete_table'  => $qm_concrete_table,
        );
    }

    /**
     * Insert in the database the table model whose data are $data
     *
     * @param   array  $data            An associative array whose keys are the fields and values are the values
     * @param   bool   $with_insert_id  If true, the method will return the insert id
     * @return  bool|int
     */
    public static function insert($data, $with_insert_id = false) {
        $query_managers     = self::set_data_for_database($data);
        $qm_abstract_table  = $query_managers['qm_abstract_table'];
        $qm_concrete_table  = $query_managers['qm_concrete_table'];

        if (is_null($qm_abstract_table)) {
            return $qm_concrete_table->insert($with_insert_id);
        }

        $insert_id = $qm_abstract_table->insert(true);
        if ($insert_id === false) {
            return false;
        }

        $qm_concrete_table->set('id', $insert_id);

        $concrete_table_insert_result = $qm_concrete_table->insert();
        if ($concrete_table_insert_result === false) {
            return false;
        }

        if ($with_insert_id) {
            return $insert_id;
        } else {
            return true;
        }
    }

    /**
     * Update in the database the table model whose id is $id with the data $data
     *
     * @param   int    $id    The id of the table model
     * @param   array  $data  An associative array whose keys are the fields and values are the values
     * @return  bool
     */
    public static function update($id, $data) {
        $query_managers = self::set_data_for_database($data);

        foreach ($query_managers as $query_manager) {
            if ( ! is_null($query_manager)) {
                $query_manager->where('id', $id);

                $update_result = $query_manager->update();
                if ($update_result === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Delete from the database the table model whose id is $id
     *
     * @param   int  $id
     * @return  bool
     */
    public static function delete($id) {
        $models_metadata = Models_metadata::get_singleton();

        $qm = new Query_manager();
        $qm->table($models_metadata->models[self::get_business_short_name()]['table'])
           ->where('id', $id);
        if ($qm->delete() === false) {
            return false;
        }

        $abstract_table = self::get_abstract_table();
        if (isset($abstract_table)) {
            $qm = new Query_manager();
            $qm->table($abstract_table)
               ->where('id', $id);
            if ($qm->delete() === false) {
                return false;
            }
        }

        return true;
    }
}
