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
 * Business_associations Class
 *
 * This class represents the Business associations defined in the file config_lightORM.php -> $config['lightORM_business_associations']
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Business_associations
{
    private static $singleton = null;

    private $CI;
    public $associations;
    
    private function __construct() {
        $this->CI =& get_instance();
        $associations_metadata = Associations_metadata::get_singleton();

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

                $formatted_associate = array();
                foreach ($associate as $key => $item) {
                    if ($key !== 'model') {
                        $formatted_associate[$key] = $item;
                    }
                }

                if ( ! isset($formatted_associate['field'])) {
                    $formatted_associate['field'] = 'id';
                }

                $association_array['associates'][$associate['model']] = $formatted_associate;
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
                $association_array['class_full_name']  = $associations_metadata->associations[$association_array['class']]['association_full_name'];
                $association_array['table']            = $associations_metadata->associations[$association_array['class']]['table'];
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
            foreach ($association_array['associates'] as $associate_model => $associate_array) {
                if (($associate_model == $model)
                    && ($associate_array['property'] == $property)
                ) {
                    return $association_numbered_name;
                }
            }
        }

        return false;
    }
}
