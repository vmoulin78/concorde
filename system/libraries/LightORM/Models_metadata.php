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
    
    private function __construct() {
        $this->CI =& get_instance();

        $this->models = array();

        foreach ($this->CI->config->item('lightORM_business_models') as $model => $config_model) {
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
}
