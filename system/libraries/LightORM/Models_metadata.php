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
 * Models_metadata Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Models_metadata
{
    private static $singleton = null;

    private $CI;
    public $models;
    private $basic_properties;
    private $association_properties;
    private $table_concrete_models;
    private $table_abstract_models;
    
    private function __construct() {
        $this->CI =& get_instance();

        $this->models = array();
        foreach ($this->CI->config->item('lightORM_mapping_models') as $model => $config_model) {
            if (isset($config_model['table'])) {
                $table = $config_model['table'];
            } else {
                $table = strtolower($model);
            }

            $this->models[$model] = array(
                'model_full_name'  => $this->CI->config->item('application_namespace') . '\\business\\models\\' . $model,
                'table'            => $table,
            );
        }

        $this->basic_properties        = array();
        $this->association_properties  = array();
        $this->table_concrete_models   = array();
        $this->table_abstract_models   = array();
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
     * Get the basic properties of the model $model
     *
     * @param   string  $model
     * @return  array
     */
    public function get_basic_properties($model) {
        if (isset($this->basic_properties[$model])) {
            return $this->basic_properties[$model];
        }

        $this->basic_properties[$model] = array();

        $model_reflection  = new \ReflectionClass($this->models[$model]['model_full_name']);
        $model_parameters  = $model_reflection->getConstructor()->getParameters();

        foreach ($model_parameters as $model_parameter) {
            $this->basic_properties[$model][] = $model_parameter->getName();
        }

        return $this->basic_properties[$model];
    }

    /**
     * Get the association properties of the model $model
     *
     * @param   string  $model
     * @return  array
     */
    public function get_association_properties($model) {
        $associations_metadata = Associations_metadata::get_singleton();

        if (isset($this->association_properties[$model])) {
            return $this->association_properties[$model];
        }

        $this->association_properties[$model] = array();

        foreach ($associations_metadata->associations as $association_array) {
            foreach ($association_array['associates'] as $associate) {
                if ($associate['model'] === $model) {
                    $this->association_properties[$model][] = $associate['property'];
                }
            }
        }

        return $this->association_properties[$model];
    }

    /**
     * Get the table abstract model of the table concrete model $table_concrete_model
     *
     * @param   string  $table_concrete_model
     * @return  string
     */
    public function get_table_abstract_model($table_concrete_model) {
        $models_metadata = Models_metadata::get_singleton();

        if (isset($this->table_concrete_models[$table_concrete_model])) {
            return $this->table_concrete_models[$table_concrete_model];
        }

        $table_concrete_model_full_name = $models_metadata->models[$table_concrete_model]['model_full_name'];

        $retour = null;

        foreach ($models_metadata->models as $key => $item) {
            $item_model_full_name = $item['model_full_name'];

            if (is_subclass_of($table_concrete_model_full_name, $item_model_full_name)
                && is_table_abstract_model($item_model_full_name)
            ) {
                $retour = $key;
                break;
            }
        }

        return ($this->table_concrete_models[$table_concrete_model] = $retour);
    }

    /**
     * Get the table concrete models of the table abstract model $table_abstract_model
     *
     * @param   string  $table_abstract_model
     * @return  array
     */
    public function get_table_concrete_models($table_abstract_model) {
        $models_metadata = Models_metadata::get_singleton();

        if (isset($this->table_abstract_models[$table_abstract_model])) {
            return $this->table_abstract_models[$table_abstract_model];
        }

        $table_abstract_model_full_name = $models_metadata->models[$table_abstract_model]['model_full_name'];

        $retour = array();

        foreach ($models_metadata->models as $key => $item) {
            $item_model_full_name = $item['model_full_name'];

            if (is_subclass_of($item_model_full_name, $table_abstract_model_full_name)
                && is_table_concrete_model($item_model_full_name)
            ) {
                $retour[] = $key;
            }
        }

        return ($this->table_abstract_models[$table_abstract_model] = $retour);
    }
}
