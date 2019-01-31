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
 * Table_model_loader Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Table_model_loader
{
    private static $singleton = null;

    private $table_models;
    
    private function __construct() {
        $this->table_models = array();
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
     * Get the table abstract model of the table model $table_model
     *
     * @param   string  $table_model
     * @return  string
     */
    public function get_table_abstract_model($table_model) {
        if (isset($this->table_models[$table_model])) {
            return $this->table_models[$table_model];
        }

        $CI =& get_instance();

        $table_model_full_name = model_full_name($table_model);

        $parent_class_full_name   = get_parent_class($table_model_full_name);
        $parent_class_short_name  = $parent_class_full_name::get_business_short_name();

        if ($parent_class_full_name === false) {
            return ($this->table_models[$table_model] = null);
        }

        $parent_class_reflection = new \ReflectionClass($parent_class_full_name);
        $parent_class_namespace_name = $parent_class_reflection->getNamespaceName();

        if ($parent_class_namespace_name != ($CI->config->item('application_namespace') . '\\business\\models')) {
            return ($this->table_models[$table_model] = null);
        }

        $parent_class_trait_names = $parent_class_reflection->getTraitNames();
        if (in_array('LightORM\\Table_abstract_model_trait', $parent_class_trait_names)) {
            return ($this->table_models[$table_model] = $parent_class_short_name);
        }

        return ($this->table_models[$table_model] = $this->get_table_abstract_model($parent_class_short_name));
    }
}
