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
 * If a class is using the trait Table_concrete_model_trait, it means that this class is related to a database table.
 */
trait Table_concrete_model_trait
{
    use Table_concrete_business_trait;
    use Table_model_trait;

    /**
     * Get the name of the table abstract model
     *
     * @return  string
     */
    public static function get_table_abstract_model() {
        $table_concrete_model_loader = Table_concrete_model_loader::get_singleton();
        return $table_concrete_model_loader->get_table_abstract_model(self::get_business_short_name());
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
     * Manage the SELECT, FROM and JOIN parts of the query for the current table concrete model
     *
     * @param   Query_manager                    $query_manager
     * @param   Business_associations_associate  $associate
     * @param   int                              $table_alias_number
     * @param   int                              $model_number
     * @param   array                            $associatonents_properties
     * @return  array
     */
    public static function business_initialization(Query_manager $query_manager, Business_associations_associate $associate, $table_alias_number = LIGHTORM_START_TABLE_ALIAS_NUMBER, $model_number = LIGHTORM_START_MODEL_NUMBER, array $associatonents_properties = []) {
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        $model_short_name  = self::get_business_short_name();
        $atom_path         = $model_short_name;

        //----------------------------------------------------------------------------//

        $table_alias          = 'alias_' . $table_alias_number;
        $model_numbered_name  = 'model_' . $model_number;
        $table_object         = $data_conv->schema[$models_metadata->models[$model_short_name]['table']];
        $table_object->business_selection($query_manager, $table_alias);

        if (is_null($associate->associatound_associatonents_group)) {
            $query_manager->from($table_object->name . ' AS ' . $table_alias);
        } else {
            // This is a "LEFT JOIN" because there could be no matching row
            $query_manager->join($table_object->name . ' AS ' . $table_alias, $table_alias . '.' . $associate->associatonent_field . ' = ' . $associate->associatound_associatonents_group->joining_alias . '.' . $associate->joining_field, 'left');
        }

        $table_alias_number++;
        $model_number++;

        //----------------------------------------------------------------------------//

        $table_abstract_model                      = self::get_table_abstract_model();
        $concrete_model_associatonents_properties  = isset($associatonents_properties[$atom_path]) ? $associatonents_properties[$atom_path] : array();
        if (isset($table_abstract_model)) {
            $table_abstract_model_atom_path = $atom_path . '<' . $table_abstract_model;

            $abstract_table        = $models_metadata->models[$table_abstract_model]['table'];
            $abstract_table_alias  = 'alias_' . $table_alias_number;

            $abstract_table_object = $data_conv->schema[$abstract_table];
            $abstract_table_object->business_selection($query_manager, $abstract_table_alias);
            // This is a "LEFT JOIN" because there could be no matching row
            $query_manager->join($abstract_table_object->name . ' AS ' . $abstract_table_alias, $abstract_table_alias . '.id = ' . $table_alias . '.id', 'left');

            $table_alias_number++;

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

        $query_manager->models[$model_numbered_name] = array(
            'name'        => $model_short_name,
            'associate'   => $associate,
            'model_info'  => $model_info,
        );

        $associate->atoms_numbered_names[$atom_path]  = $model_numbered_name;
        $associate->atoms_aliases[$atom_path]         = $table_alias;

        //----------------------------------------------------------------------------//

        return [$table_alias_number, $model_number];
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

        switch ($qm_model_item['model_info']['type']) {
            case 'simple_model':
                if (is_null($row->{$qm_model_item['model_info']['alias'] . ':id'})) {
                    return null;
                }

                $table_object  = $data_conv->schema[$qm_aliases[$qm_model_item['model_info']['alias']]];
                $args          = $table_object->business_creation_args($row, $qm_model_item['model_info']['alias']);
                break;
            case 'concrete_model':
                if (is_null($row->{$qm_model_item['model_info']['concrete_alias'] . ':id'})) {
                    return null;
                }

                $abstract_table_object  = $data_conv->schema[$qm_aliases[$qm_model_item['model_info']['abstract_alias']]];
                $args                   = $abstract_table_object->business_creation_args($row, $qm_model_item['model_info']['abstract_alias']);
                $concrete_table_object  = $data_conv->schema[$qm_aliases[$qm_model_item['model_info']['concrete_alias']]];
                $args                   = array_merge($args, $concrete_table_object->business_creation_args($row, $qm_model_item['model_info']['concrete_alias']));
                break;
            default:
                exit(1);
                break;
        }

        $model_full_name = self::get_business_full_name();
        $retour = new $model_full_name(...$args);

        return $retour;
    }

    /**
     * Return true if the concrete update manager exists and false otherwise
     *
     * @return  bool
     */
    public function concrete_update_manager_exists() {
        $lightORM = LightORM::get_singleton();

        $model_short_name = $this::get_business_short_name();

        if (isset($lightORM->model_update_managers[$model_short_name][$this->get_id()]['concrete'])) {
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
        $lightORM = LightORM::get_singleton();

        $model_short_name = $this::get_business_short_name();

        if (isset($lightORM->model_update_managers[$model_short_name][$this->get_id()]['abstract'])) {
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
        $lightORM = LightORM::get_singleton();

        $model_short_name = $this::get_business_short_name();

        if ( ! isset($lightORM->model_update_managers[$model_short_name][$this->get_id()])) {
            $lightORM->model_update_managers[$model_short_name][$this->get_id()] = array(
                'abstract'  => null,
                'concrete'  => null,
            );
        }

        return $lightORM->model_update_managers[$model_short_name][$this->get_id()];
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
     * {@inheritDoc}
     */
    protected function seek_field_in_abstract_table($attribute, $value) {
        $data_conv = Data_conv::factory();

        $class_full_name  = get_class($this);
        $abstract_table   = $class_full_name::get_abstract_table();
        if (is_null($abstract_table)) {
            return false;
        }

        $abstract_table_object  = $data_conv->schema[$abstract_table];
        $field_is_found         = false;
        if ($abstract_table_object->field_exists($attribute)) {
            $field_name      = $attribute;
            $field_value     = $value;
            $field_is_found  = true;
        } elseif ($abstract_table_object->field_exists($attribute . '_id')) {
            $field_name = $attribute . '_id';
            if (is_null($value)) {
                $field_value = null;
            } else {
                $field_value = $value->get_id();
            }
            $field_is_found = true;
        }

        if ($field_is_found) {
            $this->get_abstract_update_manager()->set($field_name, $field_value);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Trigger the UPDATE query
     *
     * @return  bool
     */
    public function update() {
        $models_metadata  = Models_metadata::get_singleton();
        $lightORM         = LightORM::get_singleton();

        $model_short_name                = $this::get_business_short_name();
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

        unset($lightORM->model_update_managers[$model_short_name][$this->get_id()]);

        return $concrete_update_manager_result && $abstract_update_manager_result;
    }

    /**
     * Insert in the database the table model whose data are $data
     *
     * @param   array  $data            An associative array whose keys are the fields and values are the values
     * @param   bool   $with_insert_id  If true, the method will return the insert id
     * @return  bool|int
     */
    public static function insert($data, $with_insert_id = false) {
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        $concrete_table_object = $data_conv->schema[$models_metadata->models[self::get_business_short_name()]['table']];
        $qm_concrete_table = new Query_manager();
        $qm_concrete_table->table($concrete_table_object->name);

        $abstract_table = self::get_abstract_table();
        if (is_null($abstract_table)) {
            $abstract_table_object = null;
        } else {
            $abstract_table_object = $data_conv->schema[$abstract_table];
            $qm_abstract_table = new Query_manager();
            $qm_abstract_table->table($abstract_table_object->name);
        }

        foreach ($data as $field => $value) {
            if (isset($concrete_table_object->fields[$field])) {
                $field_object        = $concrete_table_object->fields[$field];
                $current_table_type  = 'concrete';
            } elseif (( ! is_null($abstract_table_object))
                && isset($abstract_table_object->fields[$field])
            ) {
                $field_object        = $abstract_table_object->fields[$field];
                $current_table_type  = 'abstract';
            } else {
                trigger_error('LightORM error: Error while inserting data', E_USER_ERROR);
            }

            if ($field_object->is_enum_model_id
                && ( ! is_null($value))
            ) {
                $db_value = (string) $value;
            } else {
                $db_value = $data_conv->convert_value_for_db($value, $field_object);
            }

            ${'qm_' . $current_table_type . '_table'}->simple_set($field, $db_value, false);
        }

        if (is_null($abstract_table_object)) {
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
