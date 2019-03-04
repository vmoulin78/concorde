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
 * Business_tables_metadata Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Business_tables_metadata
{
    private static $singleton = null;

    private $CI;
    public $tables;
    
    private function __construct() {
        $this->CI =& get_instance();

        $this->tables = array();

        $scandir = array(
            'models'        => scandir(APPPATH . 'business' . DIRECTORY_SEPARATOR . 'models'),
            'associations'  => scandir(APPPATH . 'business' . DIRECTORY_SEPARATOR . 'associations'),
        );

        foreach ($scandir as $scandir_key => $scandir_item) {
            foreach ($scandir_item as $item) {
                $item_array = explode('.', $item);

                if (count($item_array) != 2) {
                    continue;
                }

                list($item_main, $item_ext) = $item_array;

                if ($item_ext != 'php') {
                    continue;
                }

                switch ($scandir_key) {
                    case 'models':
                        $item_main_full_name = model_full_name($item_main);
                        if ( ! is_table_model($item_main_full_name)) {
                            continue 2;
                        }
                        break;
                    case 'associations':
                        $item_main_full_name = association_full_name($item_main);
                        if ( ! is_table_association($item_main_full_name)) {
                            continue 2;
                        }
                        break;
                    default:
                        exit(1);
                        break;
                }

                $table = business_to_table($item_main_full_name);

                $this->tables[$table] = array(
                    'business_short_name'  => $item_main,
                    'business_full_name'   => $item_main_full_name,
                );
            }
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
}
