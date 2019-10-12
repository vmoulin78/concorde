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
 * Business_associations_associate Class
 *
 * This class represents an associate in the Business associations
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Business_associations_associatonents_group
{
    public $association_numbered_name;
    public $associatound;
    public $associatound_atom_full_path;
    public $associatound_atom_path;
    public $associatound_atom_model;
    public $associatound_atom_property;
    public $associatound_atom_numbered_name;
    public $associatound_atom_alias;
    public $associatound_atom_field; //used only for the many_to_many associations
    public $associatound_atom_joining_field; //used only for the many_to_many associations
    public $joining_alias;
    public $atoms_aliases; //used only for the many_to_many associations
    public $associatonents;

    public function __construct($association_numbered_name) {
        $this->association_numbered_name        = $association_numbered_name;

        $this->associatound                     = null;
        $this->associatound_atom_full_path      = null;
        $this->associatound_atom_path           = null;
        $this->associatound_atom_model          = null;
        $this->associatound_atom_property       = null;
        $this->associatound_atom_numbered_name  = null;
        $this->associatound_atom_alias          = null;
        $this->associatound_atom_field          = null;
        $this->associatound_atom_joining_field  = null;
        $this->joining_alias                    = null;
        $this->atoms_aliases                    = array();
        $this->associatonents                   = array();
    }

    /**
     * Add the associatonent $associatonent
     *
     * @param   Business_associations_associate  $associatonent
     * @return  void
     */
    public function add_associatonent(Business_associations_associate $associatonent) {
        $this->associatonents[] = $associatonent;
        $associatonent->associatound_associatonents_group = $this;
    }
}
