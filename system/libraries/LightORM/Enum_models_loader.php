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
 * Enum_models_loader Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Enum_models_loader
{
    private static $singleton = null;

    private $enum_models;
    
    private function __construct() {
        $this->enum_models = array();
    }

    /**
     * Get the singleton
     *
     * @return  object
     */
    public static function get_singleton() {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    /**
     * Load the instances of the Enum_model $model_short_name
     *
     * @param   string  $model_short_name  The Enum_model short name
     * @return  void
     */
    private function load_enum_model($model_short_name) {
        $models_metadata  = Models_metadata::get_singleton();
        $data_conv        = Data_conv::factory();

        if (isset($this->enum_models[$model_short_name])) {
            return;
        }

        $model_full_name = $models_metadata->models[$model_short_name]['model_full_name'];

        $qm = new Query_manager();

        $table_object = $data_conv->schema[$models_metadata->models[$model_short_name]['table']];
        $table_object->business_selection($qm);

        $qm->from($table_object->name);

        $query = $qm->get();

        $enum_model_items = array();
        foreach ($query->result() as $row) {
            $qm->convert_row($row);

            $args = $table_object->business_creation_args($row);

            $instance = new $model_full_name(...$args);

            $enum_model_items[$instance->get_id()] = $instance;
        }

        $this->enum_models[$model_short_name] = $enum_model_items;
    }

    /**
     * Get an array of Enum_model given the model short name $model_short_name and the filter $filter
     * This function is the recursive part of the function get_by_id()
     *
     * @param   string  $model_short_name  The Enum_model short name
     * @param   array   $filter            An array of ids
     * @return  array
     */
    private function get_by_id_rec($model_short_name, $filter) {
        if (empty($filter)) {
            return array();
        }

        $last_item = array_pop($filter);

        $retour = $this->get_by_id_rec($model_short_name, $filter);

        if ( ! isset($this->enum_models[$model_short_name][$last_item])) {
            trigger_error("LightORM error: Unknown id '" . $last_item . "' for the Enum_model '" . $model_short_name . "'", E_USER_ERROR);
        }

        array_push($retour, $this->enum_models[$model_short_name][$last_item]);

        return $retour;
    }

    /**
     * Get one or many Enum_model given the model short name $model_short_name and the filter $filter
     *
     * @param   string  $model_short_name  The Enum_model short name
     * @param   mixed   $filter            An id or an array of ids
     * @return  mixed
     */
    public function get_by_id($model_short_name, $filter) {
        $this->load_enum_model($model_short_name);

        if (is_array($filter)) {
            $filter_is_array = true;
        } else {
            $filter = array($filter);
            $filter_is_array = false;
        }

        $retour = $this->get_by_id_rec($model_short_name, $filter);

        if ( ! $filter_is_array) {
            $retour = array_shift($retour);
        }

        return $retour;
    }

    /**
     * Get an array of Enum_model given the model short name $model_short_name and the filter $filter
     * This function is the recursive part of the function get_by_name()
     *
     * @param   string  $model_short_name  The Enum_model short name
     * @param   array   $filter            An array of names
     * @return  array
     */
    private function get_by_name_rec($model_short_name, $filter) {
        if (empty($filter)) {
            return array();
        }

        $last_item = array_pop($filter);

        $retour = $this->get_by_name_rec($model_short_name, $filter);

        $last_item_is_found = false;
        foreach ($this->enum_models[$model_short_name] as $enum_model_item) {
            if ($enum_model_item->get_name() === $last_item) {
                array_push($retour, $enum_model_item);
                $last_item_is_found = true;
                break;
            }
        }

        if ( ! $last_item_is_found) {
            trigger_error("LightORM error: Unknown name '" . $last_item . "' for the Enum_model '" . $model_short_name . "'", E_USER_ERROR);
        }

        return $retour;
    }

    /**
     * Get one or many Enum_model given the model short name $model_short_name and the filter $filter
     *
     * @param   string  $model_short_name  The Enum_model short name
     * @param   mixed   $filter            A name or an array of names
     * @return  mixed
     */
    public function get_by_name($model_short_name, $filter) {
        $this->load_enum_model($model_short_name);

        if (is_array($filter)) {
            $filter_is_array = true;
        } else {
            $filter = array($filter);
            $filter_is_array = false;
        }

        $retour = $this->get_by_name_rec($model_short_name, $filter);

        if ( ! $filter_is_array) {
            $retour = array_shift($retour);
        }

        return $retour;
    }

    /**
     * Get all the instances of the Enum_model $model_short_name
     *
     * @param   string  $model_short_name  The Enum_model short name
     * @return  array
     */
    public function get_all($model_short_name) {
        $this->load_enum_model($model_short_name);

        return $this->enum_models[$model_short_name];
    }
}
