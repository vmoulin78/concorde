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
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

// --------------------------------------------------------------------

use Concorde\artefact\Databubble;

// --------------------------------------------------------------------

/**
 * Databubbles warehouse Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class CI_Databubbles_warehouse {

    private $databubbles;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @return  void
     */
    public function __construct()
    {
        $this->databubbles = array();

        log_message('info', 'Databubbles Warehouse Class Initialized');
    }

    // --------------------------------------------------------------------

    /**
     * Get the array $this->databubbles
     *
     * @return  array
     */
    public function get_databubbles() {
        return $this->databubbles;
    }

    /**
     * Get the databubble named $name
     *
     * @param   string  $name
     * @return  object
     */
    public function get_databubble($name) {
        return $this->databubbles[$name];
    }

    /**
     * Create an instance of Databubble and add it to the array $this->databubbles with the name $name
     *
     * @param   string  $name
     * @return  object
     */
    public function create_databubble($name) {
        $this->databubbles[$name] = new Databubble();

        return $this->databubbles[$name];
    }

    /**
     * Add the databubble $databubble to the array $this->databubbles with the name $name
     *
     * @param   Databubble  $databubble
     * @param   string      $name
     * @return  void
     */
    public function add_databubble(Databubble $databubble, $name) {
        $this->databubbles[$name] = $databubble;
    }

    /**
     * Remove the databubble named $name from the array $this->databubbles
     *
     * @param   string  $name
     * @return  void
     */
    public function remove_databubble($name) {
        unset($this->databubbles[$name]);
    }
}
