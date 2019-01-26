<?php
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
 * CodeIgniter LightORM Helpers
 *
 * @package     Concorde
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Vincent MOULIN
 * @link        
 */

// ------------------------------------------------------------------------

use LightORM\Data_conv;
use LightORM\Databubble;

// ------------------------------------------------------------------------

if ( ! function_exists('model_full_name'))
{
    /**
     * Return the model full name of the given model short name
     *
     * @param   string
     * @return  string
     */
    function model_full_name($model_short_name) {
        $CI =& get_instance();

        return $CI->config->item('application_namespace') . '\\business\\models\\' . $model_short_name;
    }
}

if ( ! function_exists('association_full_name'))
{
    /**
     * Return the association full name of the given association short name
     *
     * @param   string
     * @return  string
     */
    function association_full_name($association_short_name) {
        $CI =& get_instance();

        return $CI->config->item('application_namespace') . '\\business\\associations\\' . $association_short_name;
    }
}

if ( ! function_exists('business_to_table'))
{
    /**
     * Return the name of the table related to the given business full name or business object
     *
     * @param   string
     * @return  string
     */
    function business_to_table($business) {
        if (is_object($business)) {
            $business = get_class($business);
        }
        return isset($business::$table) ? $business::$table : strtolower(get_class_short_name($business));
    }
}

if ( ! function_exists('get_business_private_properties_names_rec'))
{
    /**
     * Return an array of the private properties of the business class $class and of its business ancestors classes
     *
     * @param   ReflectionClass
     * @return  array
     */
    function get_business_private_properties_names_rec($class) {
        $business_class_full_name = 'LightORM\\Business';
        if (($class->getName() != $business_class_full_name)
            && ( ! $class->isSubclassOf($business_class_full_name))
        ) {
            return array();
        }

        $retour = array();

        foreach ($class->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $retour[] = $property->getName();
        }

        $parent_class = $class->getParentClass();
        if ($parent_class !== false) {
            $retour = array_unique(array_merge($retour, get_business_private_properties_names_rec($parent_class)));
        }

        return $retour;
    }
}

if ( ! function_exists('sanitize_business'))
{
    /**
     * Return a sanitized copy of the given business (model or association)
     *
     * @param   object
     * @return  object
     */
    function sanitize_business($business) {
        $clone = clone $business;
        $reflection_clone = new ReflectionObject($clone);

        $properties_names_to_unset = array();
        foreach ($reflection_clone->getProperties() as $property) {
            if ( ! $property->isPublic()) {
                continue;
            }

            if ($clone->{$property->getName()} instanceof Databubble) {
                $properties_names_to_unset[] = $property->getName();
            }
        }

        foreach ($properties_names_to_unset as $property_name_to_unset) {
            unset($clone->{$property_name_to_unset});
        }

        $reflection_clone = new ReflectionObject($clone);

        $parent_class = $reflection_clone->getParentClass();
        if ($parent_class !== false) {
            $properties_names = get_business_private_properties_names_rec($reflection_clone->getParentClass());
        } else {
            $properties_names = array();
        }
        foreach ($reflection_clone->getProperties() as $property) {
            $properties_names[] = $property->getName();
        }
        $properties_names = array_unique($properties_names);

        foreach ($properties_names as $property_name) {
            if ($reflection_clone->hasMethod('get_' . $property_name)) {
                $property_value = $clone->{'get_' . $property_name}();
            } else {
                continue;
            }

            if (is_object($property_value)) {
                $clone->{'set_' . $property_name}(sanitize_business($property_value));
            } elseif (is_array($property_value)) {
                foreach ($property_value as &$item) {
                    if (is_object($item)) {
                        $item = sanitize_business($item);
                    }
                }
                $clone->{'set_' . $property_name}($property_value);
            }
        }

        return $clone;
    }
}

if ( ! function_exists('print_model'))
{
    /**
     * Display the given model using the print_r function
     *
     * @param   object
     * @return  void
     */
    function print_model($model) {
        echo '<pre>';
        print_r(sanitize_business($model));
        echo '</pre>';
    }
}

if ( ! function_exists('var_dump_model'))
{
    /**
     * Display the given model using the var_dump function
     *
     * @param   object
     * @return  void
     */
    function var_dump_model($model) {
        echo '<pre>';
        var_dump(sanitize_business($model));
        echo '</pre>';
    }
}

if ( ! function_exists('print_association'))
{
    /**
     * Display the given association using the print_r function
     *
     * @param   object
     * @return  void
     */
    function print_association($association) {
        echo '<pre>';
        print_r(sanitize_business($association));
        echo '</pre>';
    }
}

if ( ! function_exists('var_dump_association'))
{
    /**
     * Display the given association using the var_dump function
     *
     * @param   object
     * @return  void
     */
    function var_dump_association($association) {
        echo '<pre>';
        var_dump(sanitize_business($association));
        echo '</pre>';
    }
}
