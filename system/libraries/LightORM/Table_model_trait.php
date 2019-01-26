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
 * If a class is using the trait Table_model_trait, it means that this class is related to a database table (except for abstract classes).
 */
trait Table_model_trait
{
    use Table_concrete_business_trait;

    /**
     * Get the name of the Table_abstract_model
     *
     * @return  string
     */
    public static function get_table_abstract_model() {
        $table_model_loader = Table_model_loader::get_singleton();
        return $table_model_loader->get_table_abstract_model(self::get_business_short_name());
    }

    /**
     * Get the name of the abstract table
     *
     * @return  string
     */
    public static function get_abstract_table() {
        $table_abstract_model = self::get_table_abstract_model();
        if (is_null($table_abstract_model)) {
            return null;
        }
        return business_to_table(model_full_name($table_abstract_model));
    }

    /**
     * Manage the SELECT, FROM and JOIN parts of the query for the current Table_model_trait
     *
     * @param   Query_manager  $query_manager
     * @param   int            $table_alias_number
     * @param   int            $model_number
     * @param   string         $ref_alias
     * @param   array          $compound
     * @return  array
     */
    public static function business_initializor(Query_manager $query_manager, $table_alias_number = 1, $model_number = 1, $ref_alias = null, $compound = null) {
        $data_conv              = Data_conv::factory();
        $business_compositions  = Business_compositions::get_singleton();

        /******************************************************************************/

        $table_alias   = 'alias_' . $table_alias_number;
        $model         = 'model_' . $model_number;
        $table_object  = $data_conv->schema[business_to_table(self::get_business_full_name())];
        $table_object->business_selector($query_manager, $table_alias);
        if (is_null($ref_alias)) {
            $query_manager->from($table_object->name . ' AS ' . $table_alias);
        } else {
            // This is a "LEFT JOIN" because there could be no matching row
            $query_manager->join($table_object->name . ' AS ' . $table_alias, $table_alias . '.' . $query_manager->aliases[$ref_alias] . '_id = ' . $ref_alias . '.id', 'left');
        }
        $table_alias_number++;
        $model_number++;

        foreach ($business_compositions->compounds[self::get_business_short_name()]->components as $component) {
            $component_model_full_name = model_full_name($component->model);
            list($table_alias_number, $model_number) = $component_model_full_name::business_initializor($query_manager, $table_alias_number, $model_number, $table_alias, ['compound_name' => $model, 'compound_property' => $component]);
        }

        /******************************************************************************/

        $table_abstract_model = self::get_table_abstract_model();
        if (isset($table_abstract_model)) {
            $abstract_table        = business_to_table(model_full_name($table_abstract_model));
            $abstract_table_alias  = 'alias_' . $table_alias_number;

            $abstract_table_object = $data_conv->schema[$abstract_table];
            $abstract_table_object->business_selector($query_manager, $abstract_table_alias);
            // This is an "INNER JOIN" because we are sure that there is a matching row
            $query_manager->join($abstract_table_object->name . ' AS ' . $abstract_table_alias, $abstract_table_alias . '.id = ' . $table_alias . '.id');

            $model_type                     = 'concrete_model';
            $simple_model_alias             = null;
            $concrete_model_concrete_alias  = $table_alias;
            $concrete_model_abstract_alias  = $abstract_table_alias;
            $table_alias_number++;

            foreach ($business_compositions->compounds[$table_abstract_model]->components as $component) {
                $component_model_full_name = model_full_name($component->model);
                list($table_alias_number, $model_number) = $component_model_full_name::business_initializor($query_manager, $table_alias_number, $model_number, $abstract_table_alias, ['compound_name' => $model, 'compound_property' => $component]);
            }
        } else {
            $model_type                     = 'simple_model';
            $simple_model_alias             = $table_alias;
            $concrete_model_concrete_alias  = null;
            $concrete_model_abstract_alias  = null;
        }

        /******************************************************************************/

        $query_manager->models[$model] = array(
            'name' => self::get_business_short_name(),
            'type' => $model_type,
            'simple_model' => array(
                'alias'  => $simple_model_alias,
            ),
            'concrete_model' => array(
                'concrete_alias'  => $concrete_model_concrete_alias,
                'abstract_alias'  => $concrete_model_abstract_alias,
            ),
            'abstract_model' => array(
                'abstract_alias'    => null,
                'concrete_aliases'  => null,
            ),
            'compound' => $compound,
        );

        /******************************************************************************/

        return [$table_alias_number, $model_number];
    }

    /**
     * Create the Business object
     *
     * @param   array   $qm_model_value
     * @param   object  $row
     * @param   array   $qm_aliases
     * @return  object
     */
    public static function business_creator($qm_model_value, $row, $qm_aliases) {
        $data_conv              = Data_conv::factory();
        $business_compositions  = Business_compositions::get_singleton();

        $model_full_name = self::get_business_full_name();

        switch ($qm_model_value['type']) {
            case 'concrete_model':
                if (is_null($row->{$qm_model_value['concrete_model']['concrete_alias'] . ':id'})) {
                    return null;
                }

                $abstract_table_object            = $data_conv->schema[$qm_aliases[$qm_model_value['concrete_model']['abstract_alias']]];
                $args                             = $abstract_table_object->business_creator_args($row, $qm_model_value['concrete_model']['abstract_alias']);
                $concrete_table_object            = $data_conv->schema[$qm_aliases[$qm_model_value['concrete_model']['concrete_alias']]];
                $args                             = array_merge($args, $concrete_table_object->business_creator_args($row, $qm_model_value['concrete_model']['concrete_alias']));
                $table_abstract_model_components  = $business_compositions->compounds[self::get_table_abstract_model()]->components;
                break;
            case 'simple_model':
                if (is_null($row->{$qm_model_value['simple_model']['alias'] . ':id'})) {
                    return null;
                }

                $table_object                     = $data_conv->schema[$qm_aliases[$qm_model_value['simple_model']['alias']]];
                $args                             = $table_object->business_creator_args($row, $qm_model_value['simple_model']['alias']);
                $table_abstract_model_components  = array();
                break;
            default:
                exit(1);
                break;
        }

        $retour = new $model_full_name(...$args);

        foreach (array_merge($table_abstract_model_components, $business_compositions->compounds[self::get_business_short_name()]->components) as $component) {
            if ($component->is_array) {
                $retour->{'set_' . $component->property}(array());
            } else {
                $retour->{'set_' . $component->property}(null);
            }
        }

        return $retour;
    }

    /**
     * Find one or many Table_model given the filter $filter
     *
     * @param   mixed  $filter  An id or an array of ids
     * @return  mixed
     */
    public static function find($filter = null) {
        if (is_null($filter)
            || is_array($filter)
        ) {
            $finder_type = Finder::FIND_MANY;
        } else {
            $finder_type = Finder::FIND_ONE;
        }
        $finder = new Finder(self::get_business_short_name(), $finder_type);

        return $finder->get($filter);
    }

    /**
     * Return true if the concrete update manager exists and false otherwise
     *
     * @return  bool
     */
    public function concrete_update_manager_exists() {
        $lightORM = LightORM::get_singleton();

        $class_short_name = get_class_short_name($this);

        if (isset($lightORM->model_update_managers[$class_short_name][$this->get_id()]['concrete'])) {
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

        $class_short_name = get_class_short_name($this);

        if (isset($lightORM->model_update_managers[$class_short_name][$this->get_id()]['abstract'])) {
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

        $class_short_name = get_class_short_name($this);

        if ( ! isset($lightORM->model_update_managers[$class_short_name])) {
            $lightORM->model_update_managers[$class_short_name] = array();
        }

        if ( ! isset($lightORM->model_update_managers[$class_short_name][$this->get_id()])) {
            $lightORM->model_update_managers[$class_short_name][$this->get_id()] = array(
                'abstract'  => null,
                'concrete'  => null,
            );
        }

        return $lightORM->model_update_managers[$class_short_name][$this->get_id()];
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
        $lightORM = LightORM::get_singleton();

        $class_short_name                = get_class_short_name($this);
        $concrete_update_manager_result  = true;
        $abstract_update_manager_result  = true;

        if ($this->concrete_update_manager_exists()) {
            $concrete_update_manager = $this->get_concrete_update_manager();
            $concrete_update_manager->table(business_to_table($this))
                                    ->where('id', $this->get_id());
            $concrete_update_manager_result = $concrete_update_manager->update();
        }

        if ($this->abstract_update_manager_exists()) {
            $abstract_update_manager = $this->get_abstract_update_manager();
            $abstract_update_manager->table(self::get_abstract_table())
                                    ->where('id', $this->get_id());
            $abstract_update_manager_result = $abstract_update_manager->update();
        }

        unset($lightORM->model_update_managers[$class_short_name][$this->get_id()]);

        return $concrete_update_manager_result && $abstract_update_manager_result;
    }

    /**
     * Delete from the database the Table_model whose id is $id
     *
     * @param   int  $id
     * @return  bool
     */
    public static function delete($id) {
        $qm = new Query_manager();
        $qm->table(business_to_table(self::get_business_full_name()))
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
