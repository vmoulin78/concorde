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
defined('APP_ENTRY_PASS') OR exit('No direct script access allowed');

/**
 * Data_conv_table Class
 *
 * This class represents a table of the database and is used in the context of the Data_conv Class
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Data_conv_table
{
    public $name;
    public $fields;

    public function __construct($name, $fields) {
        $this->name    = $name;
        $this->fields  = $fields;
    }

    /**
     * Return true if the field $field exists and false otherwise
     *
     * @param   string  $field
     * @return  bool
     */
    public function field_exists($field) {
        if (isset($this->fields[$field])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the fields of the primary key
     *
     * @return  array
     */
    public function get_primary_key_fields() {
        $retour = array();

        foreach ($this->fields as $field) {
            if ($field->is_primary_key) {
                $retour[] = $field->name;
            }
        }

        return $retour;
    }

    /**
     * Get the basic fields
     *
     * @param   bool  $with_pk_fk  If true, the fields that are both primary key and foreign key are also returned
     * @return  array
     */
    public function get_basic_fields($with_pk_fk = true) {
        $retour = array();

        foreach ($this->fields as $field) {
            if (( ! $field->is_foreign_key)
                || ($with_pk_fk && $field->is_primary_key)
            ) {
                $retour[] = $field->name;
            }
        }

        return $retour;
    }

    /**
     * Manage the SELECT part of the query for the current Data_conv_table
     *
     * @param   Query_manager  $query_manager  The current Query_manager
     * @param   string         $prefix         The prefix
     * @return  void
     */
    public function business_selection(Query_manager $query_manager, $prefix = null) {
        if (is_null($prefix)) {
            $prefix = $this->name;
        }

        foreach ($this->get_basic_fields() as $basic_field) {
            $query_manager->select($prefix . '.' . $basic_field . ' AS ' . $prefix . ':' . $basic_field);
        }
    }

    /**
     * Return a subset of the values of the row $row
     * This subset contains the values used to create the object related to the current Data_conv_table
     *
     * @param   object  $row     The row
     * @param   string  $prefix  The prefix
     * @return  array
     */
    public function business_creation_args($row, $prefix = null) {
        if (is_null($prefix)) {
            $prefix = $this->name;
        }

        $retour = array();

        foreach ($this->get_basic_fields(false) as $basic_field) {
            $retour[$basic_field] = $row->{$prefix . ':' . $basic_field};
        }

        return $retour;
    }
}
