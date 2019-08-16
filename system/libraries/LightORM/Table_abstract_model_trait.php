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
 * Table_abstract_model_trait Trait
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
trait Table_abstract_model_trait
{
    use Table_model_trait;

    /**
     * Get the names of the table concrete models
     *
     * @return  array
     */
    public static function get_table_concrete_models() {
        $models_metadata = Models_metadata::get_singleton();

        return $models_metadata->get_table_concrete_models(self::get_business_short_name());
    }

    /**
     * Get the name of the table concrete model whose id is $id
     *
     * @param   int  $id
     * @return  string
     */
    public static function get_table_concrete_model($id) {
        $models_metadata = Models_metadata::get_singleton();

        $table_concrete_models = self::get_table_concrete_models();
        foreach ($table_concrete_models as $table_concrete_model) {
            $concrete_model_table = $models_metadata->models[$table_concrete_model]['table'];
            $qm = new Query_manager();
            $qm->from($concrete_model_table)
               ->where('id', $id);
            if ($qm->count_all_results() === 1) {
                return $table_concrete_model;
            }
        }

        return false;
    }

    /**
     * Manage the SELECT, FROM and JOIN parts of the query for the current Table_abstract_model_trait
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

        $table_concrete_models                      = self::get_table_concrete_models();
        $abstract_model_concrete_aliases            = array();
        $concrete_models_associatonents_properties  = array();
        foreach ($table_concrete_models as $table_concrete_model) {
            $table_concrete_model_atom_path = $atom_path . '>' . $table_concrete_model;

            $table_concrete_model_alias   = $finder->get_next_table_alias_numbered_name();
            $table_concrete_model_object  = $data_conv->schema[$models_metadata->models[$table_concrete_model]['table']];

            $table_concrete_model_object->business_selection($finder->main_qm, $table_concrete_model_alias);

            // This is a "LEFT JOIN" because there could be no matching row
            $finder->main_qm->join($table_concrete_model_object->name . ' AS ' . $table_concrete_model_alias, $table_concrete_model_alias . '.id = ' . $table_alias . '.id', 'left');
            if ($finder->has_offsetlimit_subquery) {
                $finder->offsetlimit_subquery_qm->join($table_concrete_model_object->name . ' AS ' . $table_concrete_model_alias, $table_concrete_model_alias . '.id = ' . $table_alias . '.id', 'left');

                if (is_null($associate->associatound_associatonents_group)) {
                    $finder->offsetlimit_subquery_qm->group_by($table_concrete_model_alias . '.id');
                }
            }

            $abstract_model_concrete_aliases[] = $table_concrete_model_alias;

            $associate->atoms_numbered_names[$table_concrete_model_atom_path]  = $model_numbered_name;
            $associate->atoms_aliases[$table_concrete_model_atom_path]         = $table_concrete_model_alias;

            $concrete_models_associatonents_properties[$table_concrete_model] = isset($associatonents_properties[$table_concrete_model_atom_path]) ? $associatonents_properties[$table_concrete_model_atom_path] : array();
        }

        //----------------------------------------------------------------------------//

        $abstract_model_associatonents_properties = isset($associatonents_properties[$atom_path]) ? $associatonents_properties[$atom_path] : array();

        $finder->models[$model_numbered_name] = array(
            'name'        => $model_short_name,
            'associate'   => $associate,
            'model_info'  => array(
                'type'                       => 'abstract_model',
                'abstract_alias'             => $table_alias,
                'concrete_aliases'           => $abstract_model_concrete_aliases,
                'associatonents_properties'  => array(
                    'abstract_model'   => $abstract_model_associatonents_properties,
                    'concrete_models'  => $concrete_models_associatonents_properties,
                ),
            ),
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
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        if ($finder_model_item['model_info']['type'] === 'abstract_model') {
            if (is_null($row->{$finder_model_item['model_info']['abstract_alias'] . ':id'})) {
                return null;
            }

            $abstract_table_object  = $data_conv->schema[$models_metadata->models[$finder_model_item['name']]['table']];
            $args                   = $abstract_table_object->business_creation_args($row, $finder_model_item['model_info']['abstract_alias']);

            foreach ($finder_model_item['model_info']['concrete_aliases'] as $abstract_model_concrete_alias) {
                if ( ! is_null($row->{$abstract_model_concrete_alias . ':id'})) {
                    $abstract_model_concrete_table = $qm_aliases[$abstract_model_concrete_alias];
                    foreach (self::get_table_concrete_models() as $table_concrete_model) {
                        $concrete_model_full_name  = $models_metadata->models[$table_concrete_model]['model_full_name'];
                        $concrete_model_table      = $models_metadata->models[$table_concrete_model]['table'];
                        if ($concrete_model_table === $abstract_model_concrete_table) {
                            $concrete_table_object  = $data_conv->schema[$concrete_model_table];
                            $args                   = array_merge($args, $concrete_table_object->business_creation_args($row, $abstract_model_concrete_alias));
                            break 2;
                        }
                    }
                }
            }
        } else {
            exit(1);
        }

        $retour = new $concrete_model_full_name(...$args);

        return $retour;
    }

    /**
     * Update in the database the table model whose id is $id with the data $data
     *
     * @param   int    $id    The id of the table model
     * @param   array  $data  An associative array whose keys are the fields and values are the values
     * @return  bool
     */
    public static function update($id, $data) {
        $models_metadata = Models_metadata::get_singleton();

        $table_concrete_model = self::get_table_concrete_model($id);

        if ($table_concrete_model === false) {
            return true;
        }

        $table_concrete_model_full_name = $models_metadata->models[$table_concrete_model]['model_full_name'];

        return $table_concrete_model_full_name::update($id, $data);
    }

    /**
     * Delete from the database the table model whose id is $id
     *
     * @param   int  $id
     * @return  bool
     */
    public static function delete($id) {
        $models_metadata = Models_metadata::get_singleton();

        $table_concrete_model = self::get_table_concrete_model($id);

        if ($table_concrete_model === false) {
            return true;
        }

        $table_concrete_model_full_name = $models_metadata->models[$table_concrete_model]['model_full_name'];

        return $table_concrete_model_full_name::delete($id);
    }
}
