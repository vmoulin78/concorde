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
 * Associations_metadata Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Associations_metadata
{
    private static $singleton = null;

    private $CI;
    public $associations;
    private $basic_properties;
    
    private function __construct() {
        $this->CI =& get_instance();

        $association_number = 1;
        foreach ($this->CI->config->item('lightORM_business_associations') as $config_association) {
            $association_array = array();

            $there_is_one   = false;
            $there_is_many  = false;
            foreach ($config_association['associates'] as $associate) {
                if ($associate['dimension'] === 'one') {
                    $there_is_one = true;
                }
                if ($associate['dimension'] === 'many') {
                    $there_is_many = true;
                }

                if ( ! isset($associate['field'])) {
                    $associate['field'] = 'id';
                }

                $association_array['associates'][] = $associate;
            }
            if ($there_is_one
                && $there_is_many
            ) {
                $association_array['type'] = 'one_to_many';
            } elseif ($there_is_one) {
                $association_array['type'] = 'one_to_one';
            } elseif ($there_is_many) {
                $association_array['type']             = 'many_to_many';
                $association_array['class']            = $config_association['class'];
                $association_array['class_full_name']  = $this->CI->config->item('application_namespace') . '\\business\\associations\\' . $association_array['class'];
                if (isset($config_association['table'])) {
                    $association_array['table'] = $config_association['table'];
                } else {
                    $association_array['table'] = strtolower($association_array['class']);
                }
            } else {
                trigger_error('LightORM error: Configuration error', E_USER_ERROR);
            }

            $this->associations['association_' . $association_number] = $association_array;
            $association_number++;
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
     * Get the numbered name of the association corresponding to the model $model and the property $property
     *
     * @param   string  $model
     * @param   string  $property
     * @return  string|false
     */
    public function get_association_numbered_name($model, $property) {
        foreach ($this->associations as $association_numbered_name => $association_array) {
            foreach ($association_array['associates'] as $associate_array) {
                if (($associate_array['model'] == $model)
                    && ($associate_array['property'] == $property)
                ) {
                    return $association_numbered_name;
                }
            }
        }

        return false;
    }

    /**
     * Get the associate array corresponding to the model $model and the property $property
     *
     * @param   string  $model
     * @param   string  $property
     * @return  string|false
     */
    public function get_associate_array($model, $property) {
        foreach ($this->associations as $association_numbered_name => $association_array) {
            foreach ($association_array['associates'] as $associate_array) {
                if (($associate_array['model'] == $model)
                    && ($associate_array['property'] == $property)
                ) {
                    return $associate_array;
                }
            }
        }

        return false;
    }

    /**
     * Get the association array corresponding to the association $association_short_name
     *
     * @param   string  $association_short_name
     * @return  array|false
     */
    public function get_association_array($association_short_name = null) {
        if (is_null($association_short_name)) {
            $retour = array();
        } else {
            $retour = false;
        }

        foreach ($this->associations as $association_array) {
            if (($association_array['type'] === 'many_to_many')
                && ($association_array['class'] === $association_short_name)
            ) {
                if (is_null($association_short_name)) {
                    $retour[] = $association_array;
                } else {
                    $retour = $association_array;
                    break;
                }
            }
        }

        return $retour;
    }

    /**
     * Get the basic properties of the association $association_short_name
     *
     * @param   string  $association_short_name
     * @return  array
     */
    public function get_basic_properties($association_short_name) {
        if (isset($this->basic_properties[$association_short_name])) {
            return $this->basic_properties[$association_short_name];
        }

        $association_array = $this->get_association_array($association_short_name);

        $this->basic_properties[$association_short_name] = array();

        $association_reflection  = new \ReflectionClass($association_array['class_full_name']);
        $association_parameters  = $association_reflection->getConstructor()->getParameters();

        foreach ($association_parameters as $association_parameter) {
            $this->basic_properties[$association_short_name][] = $association_parameter->getName();
        }

        return $this->basic_properties[$association_short_name];
    }
}
