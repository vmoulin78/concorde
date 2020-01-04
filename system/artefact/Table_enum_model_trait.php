<?php
namespace Concorde\artefact;

/**
 * Concorde
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2019 - 2020, Vincent MOULIN
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
 * @copyright   Copyright (c) 2019 - 2020, Vincent MOULIN
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link       
 * @since       Version 1.0.0
 * @filesource
 */
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

/**
 * Table_enum_model_trait Trait
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
trait Table_enum_model_trait
{
    /**
     * (See the Enum_model class)
     */
    public static function find($filter) {
        $enum_models_loader = Enum_models_loader::get_singleton();

        $model_short_name = self::get_business_short_name();

        return $enum_models_loader->get_by_id($model_short_name, $filter);
    }

    /**
     * (See the Enum_model class)
     */
    public static function all() {
        $enum_models_loader = Enum_models_loader::get_singleton();

        $model_short_name = self::get_business_short_name();

        return $enum_models_loader->get_all($model_short_name);
    }

    /**
     * (See the Enum_model class)
     */
    public static function find_by_name($filter) {
        $enum_models_loader = Enum_models_loader::get_singleton();

        $model_short_name = self::get_business_short_name();

        return $enum_models_loader->get_by_name($model_short_name, $filter);
    }
}
