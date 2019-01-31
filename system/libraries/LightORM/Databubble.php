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
    public function __construct() {}

    /**
     * Return true if the current Databubble has the instance of the model $model_short_name whose id is $model_instance_id and false otherwise
     *
     * @param   string  $model_short_name
     * @param   int     $model_instance_id
     * @return  bool
     */
    public function has_model_instance($model_short_name, $model_instance_id) {
        if (isset($this->{$model_short_name}[$model_instance_id])) {
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
            return $this->{$model_short_name}[$model_instance_id];
        } else {
            return null;
        }
    }

    /**
     * Add the instance $model_instance of the model $model_short_name
     *
     * @param   string  $model_short_name
     * @param   object  $model_instance
     * @return  void
     */
    public function add_model_instance($model_short_name, $model_instance) {
        $this->{$model_short_name}[$model_instance->get_id()] = $model_instance;
        $model_instance->databubble = $this;
    }

    /**
     * Remove the instance of the model $model_short_name whose id is $model_instance_id
     *
     * @param   string  $model_short_name
     * @param   int     $model_instance_id
     * @return  void
     */
    public function remove_model_instance($model_short_name, $model_instance_id) {
        unset($this->{$model_short_name}[$model_instance_id]->databubble);
        unset($this->{$model_short_name}[$model_instance_id]);
    }

    /**
     * Add the model instance given the data $qm_model_value, $row and $qm_aliases
     *
     * @param   array   $qm_model_value
     * @param   object  $row
     * @param   array   $qm_aliases
     * @return  object
     */
    public function add_model_row($qm_model_value, $row, $qm_aliases) {
        $model_full_name  = model_full_name($qm_model_value['name']);

        switch ($qm_model_value['type']) {
            case 'simple_model':
                $alias = $qm_model_value['simple_model']['alias'];
                break;
            case 'concrete_model':
                $alias = $qm_model_value['concrete_model']['abstract_alias'];
                break;
            case 'abstract_model':
                $alias = $qm_model_value['abstract_model']['abstract_alias'];
                break;
            default:
                exit(1);
                break;
        }

        if ($this->has_model_instance($qm_model_value['name'], $row->{$alias . ':id'})) {
            $model_instance = $this->get_model_instance($qm_model_value['name'], $row->{$alias . ':id'});
        } else {
            $model_instance = $model_full_name::business_creator($qm_model_value, $row, $qm_aliases);
            if ( ! is_null($model_instance)) {
                $this->add_model_instance($qm_model_value['name'], $model_instance);

                if ($qm_model_value['type'] == 'concrete_model') {
                    $this->add_model_instance($model_full_name::get_table_abstract_model(), $model_instance);
                } elseif ($qm_model_value['type'] == 'abstract_model') {
                    $model_instance_table = business_to_table($model_instance::get_business_full_name());
                    foreach ($qm_model_value['abstract_model']['concrete_aliases'] as $abstract_model_concrete_alias) {
                        $abstract_model_concrete_table = $qm_aliases[$abstract_model_concrete_alias];
                        if ($model_instance_table === $abstract_model_concrete_table) {
                            $this->add_model_instance($model_instance::get_business_short_name(), $model_instance);
                            break;
                        }
                    }
                }
            }
        }

        return $model_instance;
    }
}
