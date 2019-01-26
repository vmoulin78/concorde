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
     * Manage the SELECT and JOIN parts of the query for the current Table_abstract_model_trait
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

        $table_models                     = self::get_table_models();
        $abstract_model_concrete_aliases  = array();
        foreach ($table_models as $table_model) {
            $table_model_alias   = 'alias_' . $table_alias_number;
            $table_model_object  = $data_conv->schema[business_to_table(model_full_name($table_model))];
            $table_model_object->business_selector($query_manager, $table_model_alias);
            // This is a "LEFT JOIN" because there could be no matching row
            $query_manager->join($table_model_object->name . ' AS ' . $table_model_alias, $table_model_alias . '.id = ' . $table_alias . '.id', 'left');
            $abstract_model_concrete_aliases[] = $table_model_alias;
            $table_alias_number++;

            foreach ($business_compositions->compounds[$table_model]->components as $component) {
                $component_model_full_name = model_full_name($component->model);
                list($table_alias_number, $model_number) = $component_model_full_name::business_initializor($query_manager, $table_alias_number, $model_number, $table_model_alias, ['compound_name' => $model, 'compound_property' => $component]);
            }
        }

        /******************************************************************************/

        $query_manager->models[$model] = array(
            'name' => self::get_business_short_name(),
            'type' => 'abstract_model',
            'simple_model' => array(
                'alias'  => null,
            ),
            'concrete_model' => array(
                'concrete_alias'  => null,
                'abstract_alias'  => null,
            ),
            'abstract_model' => array(
                'abstract_alias'    => $table_alias,
                'concrete_aliases'  => $abstract_model_concrete_aliases,
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

        if ($qm_model_value['type'] === 'abstract_model') {
            if (is_null($row->{$qm_model_value['abstract_model']['abstract_alias'] . ':id'})) {
                return null;
            }

            $abstract_table_object  = $data_conv->schema[business_to_table(self::get_business_full_name())];
            $args                   = $abstract_table_object->business_creator_args($row, $qm_model_value['abstract_model']['abstract_alias']);

            foreach ($qm_model_value['abstract_model']['concrete_aliases'] as $abstract_model_concrete_alias) {
                if ( ! is_null($row->{$abstract_model_concrete_alias . ':id'})) {
                    $abstract_model_concrete_table = $qm_aliases[$abstract_model_concrete_alias];
                    foreach (self::get_table_models() as $table_model) {
                        $model_full_name  = model_full_name($table_model);
                        $model_table      = business_to_table($model_full_name);
                        if ($model_table === $abstract_model_concrete_table) {
                            $concrete_table_object  = $data_conv->schema[$model_table];
                            $args                   = array_merge($args, $concrete_table_object->business_creator_args($row, $abstract_model_concrete_alias));
                            break 2;
                        }
                    }
                }
            }
        } else {
            exit(1);
        }

        $retour = new $model_full_name(...$args);

        foreach (array_merge($business_compositions->compounds[self::get_business_short_name()]->components, $business_compositions->compounds[$table_model]->components) as $component) {
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
}
