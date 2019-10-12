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
class Business_associations_associate
{
    public $model;
    public $property;
    public $associatound_associatonents_group;
    public $associatonent_field;
    public $joining_field;
    public $associatonents_groups;
    public $atoms_numbered_names;
    public $atoms_aliases;

    public function __construct($model, $property = null) {
        $this->model                              = $model;
        $this->property                           = $property;

        $this->associatound_associatonents_group  = null;
        $this->associatonent_field                = null;
        $this->joining_field                      = null;
        $this->associatonents_groups              = array();
        $this->atoms_numbered_names               = array();
        $this->atoms_aliases                      = array();
    }

    /**
     * Add the associatonents group $associatonents_group with the related data $associatound_atom_full_path, $associatound_atom_path, $associatound_atom_model and $associatound_atom_property
     *
     * @param   Business_associations_associatonents_group  $associatonents_group
     * @param   string                                      $associatound_atom_full_path
     * @param   string                                      $associatound_atom_path
     * @param   string                                      $associatound_atom_model
     * @param   string                                      $associatound_atom_property
     * @return  void
     */
    public function add_associatonents_group(Business_associations_associatonents_group $associatonents_group, $associatound_atom_full_path, $associatound_atom_path, $associatound_atom_model, $associatound_atom_property) {
        $associations_metadata = Associations_metadata::get_singleton();

        $this->associatonents_groups[] = $associatonents_group;

        $associatonents_group->associatound                 = $this;
        $associatonents_group->associatound_atom_full_path  = $associatound_atom_full_path;
        $associatonents_group->associatound_atom_path       = $associatound_atom_path;
        $associatonents_group->associatound_atom_model      = $associatound_atom_model;
        $associatonents_group->associatound_atom_property   = $associatound_atom_property;

        $association_array = $associations_metadata->associations[$associatonents_group->association_numbered_name];
        if ($association_array['type'] === 'many_to_many') {
            $associate_array = $associations_metadata->get_associate_array(
                array(
                    'model'     => $associatound_atom_model,
                    'property'  => $associatound_atom_property,
                )
            );

            $associatonents_group->associatound_atom_field          = $associate_array['field'];
            $associatonents_group->associatound_atom_joining_field  = $associate_array['joining_field'];
        }
    }
}
