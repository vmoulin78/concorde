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
 * Databubble Class
 *
 * This class represents a set of data
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Databubble
{
    public $models;
    public $associations;
    public $update_managers;

    public function __construct() {
        $this->models           = array();
        $this->associations     = array();
        $this->update_managers  = array(
            'models'        => array(),
            'associations'  => array(),
        );
    }

    /**
     * Return true if the current databubble has the instance of the model $model_short_name whose id is $model_instance_id and false otherwise
     *
     * @param   string  $model_short_name
     * @param   int     $model_instance_id
     * @return  bool
     */
    public function has_model_instance($model_short_name, $model_instance_id) {
        if (isset($this->models[$model_short_name][$model_instance_id])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the instance of the model $model_short_name whose id is $model_instance_id
     *
     * @param   string  $model_short_name
     * @param   int     $model_instance_id
     * @return  object
     */
    public function get_model_instance($model_short_name, $model_instance_id) {
        if ($this->has_model_instance($model_short_name, $model_instance_id)) {
            return $this->models[$model_short_name][$model_instance_id];
        } else {
            return null;
        }
    }

    /**
     * Add the instance $model_instance in the current databubble
     *
     * @param   object  $model_instance
     * @return  void
     */
    public function add_model_instance($model_instance) {
        $this->models[$model_instance::get_business_short_name()][$model_instance->get_id()] = $model_instance;

        $table_abstract_model = $model_instance::get_table_abstract_model();
        if ( ! is_null($table_abstract_model)) {
            $this->models[$table_abstract_model][$model_instance->get_id()] = $model_instance;
        }

        $model_instance->databubble = $this;
    }

    /**
     * Add the instance $association_instance in the current databubble
     *
     * @param   object  $association_instance
     * @return  void
     */
    public function add_association_instance($association_instance) {
        $this->associations[$association_instance::get_business_short_name()][$association_instance->get_primary_key_scalar()] = $association_instance;

        $association_instance->databubble = $this;
    }

    /**
     * Remove the instance of the model $model_short_name whose id is $model_instance_id
     *
     * @param   string  $model_short_name
     * @param   int     $model_instance_id
     * @return  void
     */
    public function remove_model_instance($model_short_name, $model_instance_id) {
        unset($this->models[$model_short_name][$model_instance_id]->databubble);
        unset($this->models[$model_short_name][$model_instance_id]);
    }

    /**
     * Add the model instance given the data $qm_model_item, $row and $qm_aliases
     *
     * @param   array   $qm_model_item
     * @param   object  $row
     * @param   array   $qm_aliases
     * @return  object
     */
    public function add_model_row($qm_model_item, $row, $qm_aliases) {
        $models_metadata = Models_metadata::get_singleton();

        $model_full_name = $models_metadata->models[$qm_model_item['name']]['model_full_name'];

        switch ($qm_model_item['model_info']['type']) {
            case 'simple_model':
                $alias = $qm_model_item['model_info']['alias'];
                break;
            case 'concrete_model':
                $alias = $qm_model_item['model_info']['abstract_alias'];
                break;
            case 'abstract_model':
                $alias = $qm_model_item['model_info']['abstract_alias'];
                break;
            default:
                exit(1);
                break;
        }

        $created_model_instance = $model_full_name::business_creation($qm_model_item, $row, $qm_aliases);

        if ($this->has_model_instance($qm_model_item['name'], $row->{$alias . ':id'})) {
            $model_instance = $this->get_model_instance($qm_model_item['name'], $row->{$alias . ':id'});

            foreach ($models_metadata->get_basic_properties($qm_model_item['name']) as $basic_property) {
                $model_instance->{'set_' . $basic_property}($created_model_instance->{'get_' . $basic_property}());
            }

            return $model_instance;
        } else {
            if ( ! is_null($created_model_instance)) {
                $this->add_model_instance($created_model_instance);
            }

            return $created_model_instance;
        }
    }

    /**
     * Insert in the database the model whose name is $model_short_name and data are $data
     * Add the created instance in the current databubble
     *
     * @param   string  $model_short_name
     * @param   array   $data
     * @return  object
     */
    public function insert_model($model_short_name, $data) {
        $data_conv              = Data_conv::factory();
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();

        $model_full_name  = $models_metadata->models[$model_short_name]['model_full_name'];
        $table            = $models_metadata->models[$model_short_name]['table'];

        $insert_id = $model_full_name::insert($data, true);

        $finder = new Finder($model_short_name, $this);
        $inserted_instance = $finder->get($insert_id);

        foreach ($data as $field => $value) {
            $field_object = $data_conv->get_table_field_object($table, $field);

            if ($field_object->is_foreign_key
                && ( ! is_null($value))
            ) {
                $association_numbered_name = $associations_metadata->get_association_numbered_name(
                    array(
                        'model'  => $model_short_name,
                        'field'  => $field,
                    )
                );
                $association_array = $associations_metadata->associations[$association_numbered_name];

                foreach ($association_array['associates'] as $associate) {
                    if (($associate['model'] != $model_short_name)
                        && $this->has_model_instance($associate['model'], $value)
                    ) {
                        $associate_instance = $this->get_model_instance($associate['model'], $value);

                        if ( ! is_undefined($associate_instance->{'get_' . $associate['property']}())) {
                            switch ($associate['dimension']) {
                                case 'one':
                                    trigger_error('LightORM error: Error in one-to-one association', E_USER_ERROR);
                                    break;
                                case 'many':
                                    $associate_property_value = $associate_instance->{'get_' . $associate['property']}();
                                    $associate_property_value[] = $inserted_instance;
                                    $associate_instance->{'set_' . $associate['property']}($associate_property_value);
                                    break;
                                default:
                                    exit(1);
                                    break;
                            }
                        }
                    }
                }
            }
        }

        return $inserted_instance;
    }
}
