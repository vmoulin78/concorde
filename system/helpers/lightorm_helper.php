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

use LightORM\Associations_metadata;
use LightORM\Business;
use LightORM\Databubble;
use LightORM\Models_metadata;

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
        $models_metadata = Models_metadata::get_singleton();

        if ( ! isset($models_metadata->models[$model_short_name])) {
            return false;
        }

        return $models_metadata->models[$model_short_name]['model_full_name'];
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
        $associations_metadata = Associations_metadata::get_singleton();

        $association_array = $associations_metadata->get_association_array(['association' => $association_short_name]);

        if ($association_array === false) {
            return false;
        }

        return $association_array['class_full_name'];
    }
}

if ( ! function_exists('model_to_table'))
{
    /**
     * Return the name of the table related to the given model short name or model object
     *
     * @param   string|object
     * @return  string
     */
    function model_to_table($model) {
        $models_metadata = Models_metadata::get_singleton();

        if (is_object($model)) {
            $model = get_class_short_name($model);
        }

        if ( ! isset($models_metadata->models[$model])) {
            return false;
        }

        return $models_metadata->models[$model]['table'];
    }
}

if ( ! function_exists('association_to_table'))
{
    /**
     * Return the name of the table related to the given association short name or association object
     *
     * @param   string|object
     * @return  string
     */
    function association_to_table($association) {
        $associations_metadata = Associations_metadata::get_singleton();

        if (is_object($association)) {
            $association = get_class_short_name($association);
        }

        $association_array = $associations_metadata->get_association_array(['association' => $association]);

        if ($association_array === false) {
            return false;
        }

        return $association_array['table'];
    }
}

if ( ! function_exists('is_table_abstract_model'))
{
    /**
     * Check if the given business full name or business object is a table abstract model
     *
     * @param   string|object
     * @return  bool
     */
    function is_table_abstract_model($business) {
        if (has_trait_name($business, 'LightORM\\Table_abstract_model_trait')) {
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists('is_table_concrete_model'))
{
    /**
     * Check if the given business full name or business object is a table concrete model
     *
     * @param   string|object
     * @return  bool
     */
    function is_table_concrete_model($business) {
        if (has_trait_name($business, 'LightORM\\Table_concrete_model_trait')) {
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists('is_table_enum_model'))
{
    /**
     * Check if the given business full name or business object is a table enum model
     *
     * @param   string|object
     * @return  bool
     */
    function is_table_enum_model($business) {
        if (has_trait_name($business, 'LightORM\\Table_enum_model_trait')) {
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists('is_table_model'))
{
    /**
     * Check if the given business full name or business object is a table model
     *
     * @param   string|object
     * @return  bool
     */
    function is_table_model($business) {
        if (is_table_abstract_model($business)
            || is_table_concrete_model($business)
            || is_table_enum_model($business)
        ) {
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists('is_table_association'))
{
    /**
     * Check if the given business full name or business object is a table association
     *
     * @param   string|object
     * @return  bool
     */
    function is_table_association($business) {
        if (has_trait_name($business, 'LightORM\\Table_association_trait')) {
            return true;
        } else {
            return false;
        }
    }
}

if ( ! function_exists('get_business_private_properties_names'))
{
    /**
     * Return an array of the private properties of the business class $class and of its business ancestors classes
     *
     * @param   ReflectionClass
     * @return  array
     */
    function get_business_private_properties_names($class) {
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
            $retour = array_unique(array_merge($retour, get_business_private_properties_names($parent_class)));
        }

        return $retour;
    }
}

if ( ! function_exists('sanitize_business_rec'))
{
    /**
     * This function is the recursive part of the function sanitize_business()
     *
     * @internal
     * @param   object  $business
     * @param   array   $processed_instances
     * @return  object
     */
    function sanitize_business_rec($business, &$processed_instances = array()) {
        if (is_table_concrete_model($business)) {
            $business_type       = 'Model';
            $primary_key_scalar  = $business->get_id();
        } elseif (is_table_association($business)) {
            $business_type       = 'Association';
            $primary_key_scalar  = $business->get_primary_key_scalar();
        } elseif (is_object($business)) {
            return (clone $business);
        } else {
            exit(1);
        }

        $business_short_name = $business->get_business_short_name();

        if (isset($processed_instances[$business_type][$business_short_name][$primary_key_scalar])) {
            return $processed_instances[$business_type][$business_short_name][$primary_key_scalar];
        }

        $retour = clone $business;

        //------------------------------------------------------//

        $reflection_retour = new ReflectionObject($retour);

        $properties_names_to_unset = array();
        foreach ($reflection_retour->getProperties() as $property) {
            if ( ! $property->isPublic()) {
                continue;
            }

            if ($retour->{$property->getName()} instanceof Databubble) {
                $properties_names_to_unset[] = $property->getName();
            }
        }

        foreach ($properties_names_to_unset as $property_name_to_unset) {
            unset($retour->{$property_name_to_unset});
        }

        //------------------------------------------------------//

        $processed_instances[$business_type][$business_short_name][$primary_key_scalar] = $retour;

        $reflection_retour = new ReflectionObject($retour);

        $parent_class = $reflection_retour->getParentClass();
        if ($parent_class !== false) {
            $properties_names = get_business_private_properties_names($reflection_retour->getParentClass());
        } else {
            $properties_names = array();
        }
        foreach ($reflection_retour->getProperties() as $property) {
            $properties_names[] = $property->getName();
        }
        $properties_names = array_unique($properties_names);

        foreach ($properties_names as $property_name) {
            if ( ! $reflection_retour->hasMethod('get_' . $property_name)) {
                continue;
            }

            $property_value = $retour->{'get_' . $property_name}();

            if (is_object($property_value)) {
                $retour->{'set_' . $property_name}(sanitize_business_rec($property_value, $processed_instances));
            } elseif (is_array($property_value)) {
                $property_value_copy = array();
                foreach ($property_value as $key => $item) {
                    $property_value_copy[$key] = $item;

                    if (is_object($item)) {
                        $property_value_copy[$key] = sanitize_business_rec($item, $processed_instances);
                    }
                }
                $retour->{'set_' . $property_name}($property_value_copy);
            }
        }

        return $retour;
    }
}

if ( ! function_exists('sanitize_business'))
{
    /**
     * Return a sanitized copy of the given business (model or association)
     *
     * @param   Business  $business
     * @return  Business
     */
    function sanitize_business(Business $business) {
        return sanitize_business_rec($business);
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

if ( ! function_exists('print_models'))
{
    /**
     * Display the given array of models using the print_r function
     *
     * @param   array
     * @return  void
     */
    function print_models($models) {
        foreach ($models as $model) {
            print_model($model);
        }
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

if ( ! function_exists('var_dump_models'))
{
    /**
     * Display the given array of models using the var_dump function
     *
     * @param   array
     * @return  void
     */
    function var_dump_models($models) {
        foreach ($models as $model) {
            var_dump_model($model);
        }
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

if ( ! function_exists('print_associations'))
{
    /**
     * Display the given array of associations using the print_r function
     *
     * @param   array
     * @return  void
     */
    function print_associations($associations) {
        foreach ($associations as $association) {
            print_association($association);
        }
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

if ( ! function_exists('var_dump_associations'))
{
    /**
     * Display the given array of associations using the var_dump function
     *
     * @param   array
     * @return  void
     */
    function var_dump_associations($associations) {
        foreach ($associations as $association) {
            var_dump_association($association);
        }
    }
}
