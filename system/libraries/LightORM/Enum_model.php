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
 * Enum_model Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
abstract class Enum_model extends Model
{
    private $id;
    private $name;

    //------------------------------------------------------//

    public function __construct($id, $name) {
        parent::__construct();

        $this->id    = $id;
        $this->name  = $name;
    }

    //------------------------------------------------------//
    //                     The getters                      //
    //------------------------------------------------------//
    public function get_id() {
        return $this->id;
    }

    public function get_name() {
        return $this->name;
    }
    //------------------------------------------------------//

    /**
     * Find one or many Enum_model given the filter $filter
     *
     * @param   mixed  $filter  An id or an array of ids
     * @return  mixed
     */
    abstract public static function find($filter = null);

    /**
     * Find one or many Enum_model given the filter $filter
     *
     * @param   mixed  $filter  A name or an array of names
     * @return  mixed
     */
    abstract public static function find_by_name($filter);

    /**
     * Find the id of the Enum_model whose name is $name
     *
     * @param   string  $name  The name of the Enum_model
     * @return  int
     */
    public static function find_id($name) {
        $enum_model_full_name = self::get_business_full_name();

        return ($enum_model_full_name::find_by_name($name))->get_id();
    }

    /**
     * Find the name of the Enum_model whose id is $id
     *
     * @param   int  $id  The id of the Enum_model
     * @return  string
     */
    public static function find_name($id) {
        $enum_model_full_name = self::get_business_full_name();

        return ($enum_model_full_name::find($id))->get_name();
    }

    /**
     * Return true if the current Enum_model equals the Enum_model $arg1 and false otherwise
     *
     * @param   Enum_model  $arg1
     * @return  bool
     */
    public function equals(Enum_model $arg1) {
        if ($this->get_id() === $arg1->get_id()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return true if the name of the current Enum_model is in the array of names $enum_models_names and false otherwise
     * Trigger an error if a name in the array of names $enum_models_names doesn't exist
     *
     * @param   array  $enum_models_names
     * @return  bool
     */
    public function is_in(array $enum_models_names) {
        $enum_model_full_name = self::get_business_full_name();

        foreach ($enum_model_full_name::find_by_name($enum_models_names) as $item) {
            if ($this->equals($item)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if the name of the current Enum_model is the name $enum_model_name and false otherwise
     * Trigger an error if the name $enum_model_name doesn't exist
     *
     * @param   string  $enum_model_name
     * @return  bool
     */
    public function is($enum_model_name) {
        return $this->is_in([$enum_model_name]);
    }
}
