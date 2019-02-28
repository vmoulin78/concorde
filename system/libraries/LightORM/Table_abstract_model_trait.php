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
    use Table_abstract_business_trait;

    /**
     * Get the table models
     *
     * @return  array
     */
    public static function get_table_models() {
        $table_abstract_model_loader = Table_abstract_model_loader::get_singleton();
        return $table_abstract_model_loader->get_table_models(self::get_business_short_name());
    }

    /**
     * Manage the SELECT, FROM and JOIN parts of the query for the current Table_abstract_model_trait
     *
     * @param   Query_manager  $query_manager
     * @param   int            $table_alias_number
     * @param   int            $model_number
     * @param   array          $relation_info
     * @param   array          $associatonents_properties
     * @return  array
     */
    public static function business_initialization(Query_manager $query_manager, $table_alias_number = LIGHTORM_START_TABLE_ALIAS_NUMBER, $model_number = LIGHTORM_START_MODEL_NUMBER, array $relation_info = ['type' => 'none'], $main_associate = null, $atom_path = null, array $associatonents_properties = []) {
        $data_conv              = Data_conv::factory();
        $business_compositions  = Business_compositions::get_singleton();
        $business_associations  = Business_associations::get_singleton();

        $model_full_name   = self::get_business_full_name();
        $model_short_name  = self::get_business_short_name();

        if (is_null($atom_path)) {
            $atom_path = $model_short_name;
        }

        /******************************************************************************/

        $table_alias          = 'alias_' . $table_alias_number;
        $model_numbered_name  = 'model_' . $model_number;
        $table_object  = $data_conv->schema[business_to_table($model_full_name)];
        $table_object->business_selection($query_manager, $table_alias);
        switch ($relation_info['type']) {
            case 'none':
                $query_manager->from($table_object->name . ' AS ' . $table_alias);
                break;
            case 'composition':
                // This is a "LEFT JOIN" because there could be no matching row
                $query_manager->join($table_object->name . ' AS ' . $table_alias, $table_alias . '.' . $relation_info['component_array']['component_field'] . ' = ' . $relation_info['joining_alias'] . '.id', 'left');
                break;
            case 'association':
                // This is a "LEFT JOIN" because there could be no matching row
                $query_manager->join($table_object->name . ' AS ' . $table_alias, $table_alias . '.' . $relation_info['associate']->associatonent_field . ' = ' . $relation_info['associate']->associatound_associatonents_group->joining_alias . '.' . $relation_info['associate']->joining_field, 'left');
                break;
            default:
                exit(1);
                break;
        }
        $table_alias_number++;
        $model_number++;

        $abstract_model_components_properties = array();
        foreach ($business_compositions->compociates[$model_short_name]->components as $component_array) {
            $component_atom_path = $atom_path . ':' . $component_array['component']->model;

            $component_model_full_name = model_full_name($component_array['component']->model);
            list($table_alias_number, $model_number) = $component_model_full_name::business_initialization($query_manager, $table_alias_number, $model_number, ['type' => 'composition', 'component_array' => $component_array, 'joining_alias' => $table_alias, 'compound_numbered_name' => $model_numbered_name], $main_associate, $component_atom_path, $associatonents_properties);

            $abstract_model_components_properties[] = $component_array['compound_property'];
        }

        /******************************************************************************/

        $table_models                               = self::get_table_models();
        $abstract_model_concrete_aliases            = array();
        $concrete_models_components_properties      = array();
        $concrete_models_associatonents_properties  = array();
        foreach ($table_models as $table_model) {
            $table_model_atom_path = $atom_path . '>' . $table_model;

            $table_model_alias   = 'alias_' . $table_alias_number;
            $table_model_object  = $data_conv->schema[business_to_table(model_full_name($table_model))];
            $table_model_object->business_selection($query_manager, $table_model_alias);
            // This is a "LEFT JOIN" because there could be no matching row
            $query_manager->join($table_model_object->name . ' AS ' . $table_model_alias, $table_model_alias . '.id = ' . $table_alias . '.id', 'left');
            $abstract_model_concrete_aliases[] = $table_model_alias;
            $table_alias_number++;

            if ( ! is_null($main_associate)) {
                $main_associate->atoms_numbered_names[$table_model_atom_path]  = $model_numbered_name;
                $main_associate->atoms_aliases[$table_model_atom_path]         = $table_model_alias;
            }

            $concrete_models_components_properties[$table_model] = array();
            foreach ($business_compositions->compociates[$table_model]->components as $component_array) {
                $component_atom_path = $table_model_atom_path . ':' . $component_array['component']->model;

                $component_model_full_name = model_full_name($component_array['component']->model);
                list($table_alias_number, $model_number) = $component_model_full_name::business_initialization($query_manager, $table_alias_number, $model_number, ['type' => 'composition', 'component_array' => $component_array, 'joining_alias' => $table_model_alias, 'compound_numbered_name' => $model_numbered_name], $main_associate, $component_atom_path, $associatonents_properties);

                $concrete_models_components_properties[$table_model][] = $component_array['compound_property'];
            }

            $concrete_models_associatonents_properties[$table_model] = isset($associatonents_properties[$table_model_atom_path]) ? $associatonents_properties[$table_model_atom_path] : array();
        }

        /******************************************************************************/

        $abstract_model_associatonents_properties = isset($associatonents_properties[$atom_path]) ? $associatonents_properties[$atom_path] : array();

        $query_manager->models[$model_numbered_name] = array(
            'name'           => $model_short_name,
            'model_info'     => array(
                'type'                      => 'abstract_model',
                'abstract_alias'            => $table_alias,
                'concrete_aliases'          => $abstract_model_concrete_aliases,
                'components_properties'     => array(
                    'abstract_model'   => $abstract_model_components_properties,
                    'concrete_models'  => $concrete_models_components_properties,
                ),
                'associatonents_properties'     => array(
                    'abstract_model'   => $abstract_model_associatonents_properties,
                    'concrete_models'  => $concrete_models_associatonents_properties,
                ),
            ),
            'relation_info'  => $relation_info,
        );

        if ( ! is_null($main_associate)) {
            $main_associate->atoms_numbered_names[$atom_path]  = $model_numbered_name;
            $main_associate->atoms_aliases[$atom_path]         = $table_alias;
        }

        /******************************************************************************/

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

        if ($qm_model_item['model_info']['type'] === 'abstract_model') {
            if (is_null($row->{$qm_model_item['model_info']['abstract_alias'] . ':id'})) {
                return null;
            }

            $abstract_table_object  = $data_conv->schema[business_to_table(self::get_business_full_name())];
            $args                   = $abstract_table_object->business_creation_args($row, $qm_model_item['model_info']['abstract_alias']);

            foreach ($qm_model_item['model_info']['concrete_aliases'] as $abstract_model_concrete_alias) {
                if ( ! is_null($row->{$abstract_model_concrete_alias . ':id'})) {
                    $abstract_model_concrete_table = $qm_aliases[$abstract_model_concrete_alias];
                    foreach (self::get_table_models() as $table_model) {
                        $model_full_name  = model_full_name($table_model);
                        $model_table      = business_to_table($model_full_name);
                        if ($model_table === $abstract_model_concrete_table) {
                            $concrete_table_object  = $data_conv->schema[$model_table];
                            $args                   = array_merge($args, $concrete_table_object->business_creation_args($row, $abstract_model_concrete_alias));
                            break 2;
                        }
                    }
                }
            }
        } else {
            exit(1);
        }

        $retour = new $model_full_name(...$args);

        return $retour;
    }

    /**
     * Find one or many Table_model given the filter $filter
     *
     * @param   mixed  $filter  An id or an array of ids
     * @return  mixed
     */
    public static function find($filter = null) {
        $finder = new Finder(self::get_business_short_name());
        return $finder->get($filter);
    }
}
