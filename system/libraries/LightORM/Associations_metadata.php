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
    
    private function __construct() {
        $this->CI =& get_instance();

        $this->associations = array();

        foreach (scandir(APPPATH . 'business' . DIRECTORY_SEPARATOR . 'associations') as $item) {
            $item_array = explode('.', $item);

            if (count($item_array) != 2) {
                continue;
            }

            list($item_main, $item_ext) = $item_array;

            if ($item_ext != 'php') {
                continue;
            }

            $item_main_full_name = association_full_name($item_main);

            if (is_table_association($item_main_full_name)) {
                $item_main_is_table_association  = true;
                $item_main_table                 = business_to_table($item_main_full_name);
            } else {
                $item_main_is_table_association  = false;
                $item_main_table                 = null;
            }

            $this->associations[$item_main] = array(
                'association_full_name'  => $item_main_full_name,
                'is_table_association'   => $item_main_is_table_association,
                'table'                  => $item_main_table,
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
}
