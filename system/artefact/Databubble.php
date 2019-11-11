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
 * Databubble Class
 *
 * This class represents a set of data
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Databubble
{
    public $models;
    public $associations;
    public $update_managers;

    public function __construct() {
        $this->models           = array();
        $this->associations     = array();
        $this->update_managers  = array(
            'models'        => array(),
            'associations'  => array(),
        );
    }

    /**
     * Return true if the current databubble has the instance of the model $model_short_name whose id is $model_instance_id and false otherwise
     *
     * @param   string  $model_short_name
     * @param   int     $model_instance_id
     * @return  bool
     */
    public function has_model_instance($model_short_name, $model_instance_id) {
        if (isset($this->models[$model_short_name][$model_instance_id])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the instance of the model $model_short_name whose id is $model_instance_id
     *
     * @param   string  $model_short_name
     * @param   int     $model_instance_id
     * @return  object
     */
    public function get_model_instance($model_short_name, $model_instance_id) {
        if ($this->has_model_instance($model_short_name, $model_instance_id)) {
            return $this->models[$model_short_name][$model_instance_id];
        } else {
            return null;
        }
    }

    /**
     * Add the instance $model_instance in the current databubble
     *
     * @param   object  $model_instance
     * @return  void
     */
    public function add_model_instance($model_instance) {
        $model_full_name    = get_class($model_instance);
        $model_short_name   = $model_full_name::get_business_short_name();
        $model_instance_id  = $model_instance->get_id();

        $this->models[$model_short_name][$model_instance_id] = $model_instance;

        $table_abstract_model = $model_full_name::get_table_abstract_model();
        if ( ! is_null($table_abstract_model)) {
            $this->models[$table_abstract_model][$model_instance_id] = $model_instance;
        }

        $model_instance->databubble = $this;
    }

    /**
     * Remove the instance $model_instance from the current databubble
     *
     * @param   object  $model_instance
     * @return  void
     */
    public function remove_model_instance($model_instance) {
        $model_full_name    = get_class($model_instance);
        $model_short_name   = $model_full_name::get_business_short_name();
        $model_instance_id  = $model_instance->get_id();

        unset($this->update_managers['models'][$model_short_name][$model_instance_id]);

        unset($this->models[$model_short_name][$model_instance_id]);

        $table_abstract_model = $model_full_name::get_table_abstract_model();
        if ( ! is_null($table_abstract_model)) {
            unset($this->models[$table_abstract_model][$model_instance_id]);
        }

        unset($model_instance->databubble);
    }

    /**
     * Add the instance $association_instance in the current databubble
     *
     * @param   object  $association_instance
     * @return  void
     */
    public function add_association_instance($association_instance) {
        $association_full_name           = get_class($association_instance);
        $association_short_name          = $association_full_name::get_business_short_name();
        $association_primary_key_scalar  = $association_instance->get_primary_key_scalar();

        $this->associations[$association_short_name][$association_primary_key_scalar] = $association_instance;

        $association_instance->databubble = $this;
    }

    /**
     * Remove the instance $association_instance from the current databubble
     *
     * @param   object  $association_instance
     * @return  void
     */
    public function remove_association_instance($association_instance) {
        $association_full_name           = get_class($association_instance);
        $association_short_name          = $association_full_name::get_business_short_name();
        $association_primary_key_scalar  = $association_instance->get_primary_key_scalar();

        unset($this->update_managers['associations'][$association_short_name][$association_primary_key_scalar]);

        unset($this->associations[$association_short_name][$association_primary_key_scalar]);

        unset($association_instance->databubble);
    }

    /**
     * Add the model instance given the data $finder_model_item, $row and $qm_aliases
     *
     * @param   array   $finder_model_item
     * @param   object  $row
     * @param   array   $qm_aliases
     * @return  object
     */
    public function add_model_row($finder_model_item, $row, $qm_aliases) {
        $models_metadata = Models_metadata::get_singleton();

        $model_full_name = $models_metadata->models[$finder_model_item['name']]['model_full_name'];

        switch ($finder_model_item['model_info']['type']) {
            case 'simple_model':
                $alias = $finder_model_item['model_info']['alias'];
                break;
            case 'concrete_model':
                $alias = $finder_model_item['model_info']['abstract_alias'];
                break;
            case 'abstract_model':
                $alias = $finder_model_item['model_info']['abstract_alias'];
                break;
            default:
                exit(1);
                break;
        }

        $created_model_instance = $model_full_name::business_creation($finder_model_item, $row, $qm_aliases);

        if ($this->has_model_instance($finder_model_item['name'], $row->{$alias . ':id'})) {
            $model_instance = $this->get_model_instance($finder_model_item['name'], $row->{$alias . ':id'});

            foreach ($models_metadata->get_basic_properties($finder_model_item['name']) as $basic_property) {
                $model_instance->{'set_' . $basic_property}($created_model_instance->{'get_' . $basic_property}());
            }

            return $model_instance;
        } else {
            if ( ! is_null($created_model_instance)) {
                $this->add_model_instance($created_model_instance);
            }

            return $created_model_instance;
        }
    }

    /**
     * Insert in the database the model whose name is $model_short_name and data are $data
     * Add the created instance in the current databubble
     *
     * @param   string  $model_short_name
     * @param   array   $data
     * @return  object|false  The created instance or false if an error occurred
     */
    public function insert_model($model_short_name, $data) {
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();

        $model_full_name = $models_metadata->models[$model_short_name]['model_full_name'];

        $basic_properties        = $models_metadata->get_basic_properties($model_short_name);
        $association_properties  = $models_metadata->get_association_properties($model_short_name);

        $data_for_insert_query  = array();
        $associations_data      = array();
        foreach ($data as $property => $value) {
            if (in_array($property, $basic_properties)) {
                $data_for_insert_query[$property] = $value;
            } elseif (in_array($property, $association_properties)) {
                if (( ! is_null($value))
                    && ( ! is_object($value))
                    && ( ! is_array($value))
                ) {
                    trigger_error("Artefact error: Invalid value for the property '" . $property . "'", E_USER_ERROR);
                }

                if (is_object($value)
                    || (is_array($value) && ( ! empty($value)))
                ) {
                    $associations_data[$property] = $value;
                }
            } else {
                trigger_error("Artefact error: Unknown property name '" . $property . "' for the model '" . $model_short_name . "'", E_USER_ERROR);
            }
        }

        $association_properties_values  = array();
        $opposite_instances_to_manage   = array();
        $remaining_associations_data    = array();
        foreach ($associations_metadata->associations as $association_array) {
            foreach ($association_array['associates'] as $associate) {
                if ($associate['model'] === $model_short_name) {
                    switch ($associate['dimension']) {
                        case 'one':
                            $association_properties_values[$associate['property']] = null;
                            break;

                        case 'many':
                            $association_properties_values[$associate['property']] = array();
                            break;

                        default:
                            exit(1);
                            break;
                    }

                    if ($associate['field'] === 'id') {
                        if (isset($associations_data[$associate['property']])) {
                            $remaining_associations_data[$association_array['type']][$associate['property']] = $associations_data[$associate['property']];
                        }
                    } else {
                        if (isset($associations_data[$associate['property']])) {
                            if ($associations_data[$associate['property']]->databubble !== $this) {
                                trigger_error('Artefact error: The databubble of the associate must be the current one', E_USER_ERROR);
                            }

                            $association_properties_values[$associate['property']]  = $associations_data[$associate['property']];
                            $data_for_insert_query[$associate['field']]             = $associations_data[$associate['property']]->get_id();

                            list($opposite_associate_array) = $associations_metadata->get_opposite_associates_arrays(
                                array(
                                    'model'     => $associate['model'],
                                    'property'  => $associate['property'],
                                )
                            );
                            $opposite_instances_to_manage[] = array(
                                'instance'   => $associations_data[$associate['property']],
                                'property'   => $opposite_associate_array['property'],
                                'dimension'  => $opposite_associate_array['dimension'],
                            );
                        } else {
                            $data_for_insert_query[$associate['field']] = null;
                        }
                    }
                }
            }
        }

        $insert_id = $model_full_name::insert($data_for_insert_query, true);

        if ($insert_id === false) {
            return false;
        }

        $inserted_instance = $model_full_name::find($insert_id, $this);

        foreach ($association_properties_values as $property => $value) {
            $inserted_instance->{'set_' . $property}($value);
        }

        foreach ($opposite_instances_to_manage as $opposite_instance_array) {
            switch ($opposite_instance_array['dimension']) {
                case 'one':
                    $opposite_instance_array['instance']->{'set_' . $opposite_instance_array['property']}($inserted_instance);
                    break;

                case 'many':
                    $opposite_associate_property_value = $opposite_instance_array['instance']->{'get_' . $opposite_instance_array['property']}();
                    if ( ! is_undefined($opposite_associate_property_value)) {
                        $opposite_associate_property_value[] = $inserted_instance;
                        $opposite_instance_array['instance']->{'set_' . $opposite_instance_array['property']}($opposite_associate_property_value);
                    }
                    break;

                default:
                    exit(1);
                    break;
            }
        }

        foreach ($remaining_associations_data as $dimension => $remaining_associations_data_bunch) {
            foreach ($remaining_associations_data_bunch as $property => $value) {
                switch ($dimension) {
                    case 'one_to_one':
                        $inserted_instance->set_assoc($property, $value);
                        break;

                    case 'one_to_many':
                    case 'many_to_many':
                        foreach ($value as $item) {
                            $inserted_instance->add_assoc($property, $item);
                        }
                        break;

                    default:
                        exit(1);
                        break;
                }
            }
        }

        return $inserted_instance;
    }

    /**
     * Delete the model instance $model_instance from the database and from the current databubble
     *
     * @param   object  $model_instance
     * @return  bool
     */
    public function delete_model($model_instance) {
        $associations_metadata = Associations_metadata::get_singleton();

        if ($model_instance->databubble !== $this) {
            trigger_error('Artefact error: The databubble of the model instance must be the current one', E_USER_ERROR);
        }

        $model_full_name   = get_class($model_instance);
        $model_short_name  = $model_full_name::get_business_short_name();

        //----------------------------------------------------------------------------//

        $delete_result = $model_full_name::delete($model_instance->get_id());

        if ( ! $delete_result) {
            return false;
        }

        //----------------------------------------------------------------------------//

        foreach ($associations_metadata->associations as $association_array) {
            foreach ($association_array['associates'] as $associate) {
                if ($associate['model'] === $model_short_name) {
                    $opposite_associates_arrays = $associations_metadata->get_opposite_associates_arrays(
                        array(
                            'model'     => $associate['model'],
                            'property'  => $associate['property'],
                        )
                    );

                    foreach ($opposite_associates_arrays as $opposite_associate_array) {
                        if ($opposite_associate_array['dimension'] === 'one') {
                            if (isset($this->models[$opposite_associate_array['model']])) {
                                foreach ($this->models[$opposite_associate_array['model']] as $item) {
                                    $opposite_associate_property_value = $item->{'get_' . $opposite_associate_array['property']}();
                                    if (( ! is_undefined($opposite_associate_property_value))
                                        && ($opposite_associate_property_value === $model_instance)
                                    ) {
                                        $item->{'set_' . $opposite_associate_array['property']}(null);
                                    }
                                }
                            }
                        } elseif ($association_array['type'] === 'one_to_many') {
                            if (isset($this->models[$opposite_associate_array['model']])) {
                                foreach ($this->models[$opposite_associate_array['model']] as $item1) {
                                    $opposite_associate_property_value = $item1->{'get_' . $opposite_associate_array['property']}();
                                    if ( ! is_undefined($opposite_associate_property_value)) {
                                        $new_opposite_associate_property_value = array();
                                        foreach ($opposite_associate_property_value as $item2) {
                                            if ($item2 !== $model_instance) {
                                                $new_opposite_associate_property_value[] = $item2;
                                            }
                                        }
                                        $item1->{'set_' . $opposite_associate_array['property']}($new_opposite_associate_property_value);
                                    }
                                }
                            }
                        } elseif ($association_array['type'] === 'many_to_many') {
                            if (isset($this->associations[$association_array['class']])) {
                                $association_primary_key_scalars_to_remove = array();

                                foreach ($this->associations[$association_array['class']] as $key1 => $item1) {
                                    if ($item1->{'get_' . $associate['reverse_property']}() === $model_instance) {
                                        foreach ($association_array['associates'] as $item2) {
                                            $item2_instance = $item1->{'get_' . $item2['reverse_property']}();
                                            $item2_instance_property_value = $item2_instance->{'get_' . $item2['property']}();
                                            if ( ! is_undefined($item2_instance_property_value)) {
                                                $new_item2_instance_property_value = array();
                                                foreach ($item2_instance_property_value as $item3) {
                                                    if ($item3 !== $item1) {
                                                        $new_item2_instance_property_value[] = $item3;
                                                    }
                                                }
                                                $item2_instance->{'set_' . $item2['property']}($new_item2_instance_property_value);
                                            }

                                            $item1->{'set_' . $item2['reverse_property']}(null);
                                        }

                                        $association_primary_key_scalars_to_remove[] = $key1;
                                    }
                                }

                                foreach ($association_primary_key_scalars_to_remove as $item) {
                                    unset($this->associations[$association_array['class']][$item]->databubble);

                                    unset($this->update_managers['associations'][$association_array['class']][$item]);

                                    unset($this->associations[$association_array['class']][$item]);
                                }
                            }
                        } else {
                            exit(1);
                        }
                    }

                    //----------------------------------------------------------------------------//

                    switch ($associate['dimension']) {
                        case 'one':
                            $model_instance->{'set_' . $associate['property']}(null);
                            break;

                        case 'many':
                            $model_instance->{'set_' . $associate['property']}(array());
                            break;

                        default:
                            exit(1);
                            break;
                    }
                }
            }
        }

        //----------------------------------------------------------------------------//

        $this->remove_model_instance($model_instance);

        //----------------------------------------------------------------------------//

        return true;
    }
}
