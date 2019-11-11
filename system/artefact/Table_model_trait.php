<?php
namespace Concorde\artefact;

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
 * Table_model_trait Trait
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
trait Table_model_trait
{
    use Table_business_trait;

    /**
     * Find one or many table models given the filter $filter
     *
     * @param   mixed       $filter      An id or an array of ids
     * @param   Databubble  $databubble  The databubble where the data will be put or updated (if it is null, a new Databubble will be created)
     * @return  mixed
     */
    public static function find($filter, Databubble $databubble = null) {
        $model_short_name = self::get_business_short_name();

        $alias_name = 'alias_model';

        $finder = new Finder($model_short_name . ' AS[' . $alias_name . ']');

        if (is_array($filter)) {
            $finder->where_in($alias_name . ':id', $filter);
            return $finder->get($databubble);
        } else {
            $finder->where($alias_name . ':id', $filter);
            return $finder->first($databubble);
        }
    }

    /**
     * Find all the table models
     *
     * @param   Databubble  $databubble  The databubble where the data will be put or updated (if it is null, a new Databubble will be created)
     * @return  array
     */
    public static function all(Databubble $databubble = null) {
        $model_short_name = self::get_business_short_name();

        $finder = new Finder($model_short_name);
        return $finder->get($databubble);
    }

    /**
     * Get the depth of the table given the property $property and the filter $filter
     *
     * @param   mixed  $property  The reflexive property
     * @param   mixed  $filter    An id or an array of ids
     * @return  int
     */
    public static function get_table_depth($property, $filter = null) {
        $CI =& get_instance();
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();

        $model_short_name = self::get_business_short_name();

        $associate_array = $associations_metadata->get_associate_array(
            array(
                'model'     => $model_short_name,
                'property'  => $property,
            )
        );

        return $CI->db->get_table_depth($models_metadata->models[$model_short_name]['table'], $associate_array['field'], $filter);
    }
}
