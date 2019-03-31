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
 * Finder Class
 *
 * Manage the data conversion for the database
 * This class is a factory of singletons
 *
 * @package     Concorde
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Vincent MOULIN
 * @link        
 */
class Finder
{
    const DIMENSION_ONE   = 0;
    const DIMENSION_MANY  = 1;
    const DIMENSION_AUTO  = 2;

    private $model_short_name;
    private $dimension;
    private $associations;
    private $user_aliases;
    private $stack;

    //------------------------------------------------------//

    /**
     * The constructor
     *
     * @access public
     * @param $model      The model
     * @param $dimension  The dimension
     */
    public function __construct($model, $dimension = self::DIMENSION_AUTO) {
        $model_array = explode(' AS ', $model, 2);
        $model_short_name = array_shift($model_array);
        $associate = new Business_associations_associate($model_short_name);

        $this->model_short_name  = $model_short_name;
        $this->dimension         = $dimension;
        $this->associations      = $associate;
        $this->user_aliases      = array();
        $this->stack             = array();

        if (count($model_array) === 1) {
            $model_alias = array_shift($model_array);

            $this->user_aliases[$model_alias] = array(
                'type'    => 'model',
                'object'  => $associate,
            );
        }
    }

    //------------------------------------------------------//

    public function get_dimension() {
        return $this->dimension;
    }

    public function set_dimension($dimension) {
        $this->dimension = $dimension;
    }

    //------------------------------------------------------//

    public function __call($method, $args) {
        $this->stack[] = array(
            'method'  => $method,
            'args'    => $args
        );
        return $this;
    }

    /**
     * This function is the recursive part of the function format_with()
     *
     * @param   mixed  $associations
     * @return  array|null
     */
    private function format_with_rec($associations) {
        if (empty($associations)) {
            return null;
        }

        if ( ! is_array($associations)) {
            $associations = (string) $associations;
            return array($associations => null);
        }

        $retour = array();

        foreach ($associations as $key1 => $item1) {
            if (is_string($key1)) {
                $retour[$key1] = $this->format_with_rec($item1);
            } else {
                if (is_string($item1)) {
                    $retour[$item1] = null;
                } elseif (is_array($item1)) {
                    foreach ($item1 as $item2) {
                        if (is_string($item2)) {
                            $retour[$item2] = null;
                        } elseif (is_array($item2)) {
                            foreach ($item2 as $key3 => $item3) {
                                if ( ! is_string($key3)) {
                                    trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                                }

                                $retour[$key3] = $this->format_with_rec($item3);
                            }
                        } else {
                            trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                        }
                    }
                } else {
                    trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                }
            }
        }

        return $retour;
    }

    /**
     * Format the associations $associations
     *
     * @param   mixed  $associations
     * @return  array
     */
    private function format_with($associations) {
        if (empty($associations)) {
            return array();
        }

        return $this->format_with_rec($associations);
    }

    /**
     * Format the reflexive associations in the associations $associations
     *
     * @param   array  $associations
     * @return  array
     */
    private function format_reflexive_associations($associations) {
        if (is_null($associations)) {
            return null;
        }

        $retour = array();

        foreach ($associations as $key => $item) {
            $key_array = explode('|', $key, 2);

            if (count($key_array) === 1) {
                $retour[$key] = $this->format_reflexive_associations($item);
                continue;
            }

            list($key_array_part1, $key_array_part2) = $key_array;
            $key_array_part2 = (int) $key_array_part2;

            if ($key_array_part2 <= 1) {
                $retour[$key_array_part1] = $this->format_reflexive_associations($item);
                continue;
            }

            if (is_null($item)) {
                $item_plus = array();
            } else {
                $item_plus = $item;
            }
            $key_array_part2--;
            $item_plus[$key_array_part1 . '|' . $key_array_part2] = $item;
            $retour[$key_array_part1] = $this->format_reflexive_associations($item_plus);
        }

        return $retour;
    }

    /**
     * This function is the recursive part of the function convert_user_aliases_array()
     *
     * @param   string  $user_aliases_string
     * @return  array
     */
    private function convert_user_aliases_array_rec($user_aliases_string) {
        if (empty($user_aliases_string)) {
            return array();
        }

        $retour = array();

        $first_label = strstr($user_aliases_string, ':', true);
        $after_first_label = strstr_after_needle($user_aliases_string, ':');
        switch ($first_label) {
            case 'model':
                if (in_string(',', $after_first_label)) {
                    $retour = $this->convert_user_aliases_array_rec(strstr_after_needle($after_first_label, ','));
                    $retour['unique_model'] = strstr($after_first_label, ',', true);
                } else {
                    $retour['unique_model'] = $after_first_label;
                }
                break;
            case 'association':
                if (in_string(',', $after_first_label)) {
                    $retour = $this->convert_user_aliases_array_rec(strstr_after_needle($after_first_label, ','));
                    $retour['association'] = strstr($after_first_label, ',', true);
                } else {
                    $retour['association'] = $after_first_label;
                }
                break;
            case 'models':
                if ((substr($after_first_label, 0, 1) !== '[')
                    || ( ! in_string(']', $after_first_label))
                ) {
                    trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                }
                $models_aliases = substr(strstr($after_first_label, ']', true), 1);
                $after_first_segment = strstr_after_needle($after_first_label, ']');
                if (substr($after_first_segment, 0, 1) === ',') {
                    $retour = $this->convert_user_aliases_array_rec(substr($after_first_segment, 1));
                }

                $models_aliases_array = explode(',', $models_aliases);
                foreach ($models_aliases_array as $item) {
                    $item_array = explode(':', $item, 2);
                    if (count($item_array) !== 2) {
                        trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                    }
                    list($item_array_model, $item_array_alias) = $item_array;
                    $retour['models'][$item_array_model] = $item_array_alias;
                }
                break;
            default:
                exit(1);
                break;
        }

        return $retour;
    }

    /**
     * Convert the string of user aliases in array
     *
     * @param   string  $user_aliases_string
     * @return  array
     */
    private function convert_user_aliases_array($user_aliases_string) {
        return $this->convert_user_aliases_array_rec(substr($user_aliases_string, 1, -1));
    }

    /**
     * This function is the recursive part of the function with()
     *
     * @param   array  $associations
     * @param   array  $associatounds  an array of Business_associations_associate
     * @return  array
     */
    private function with_rec(array $associations, array $associatounds) {
        $business_associations = Business_associations::get_singleton();

        if (count($associatounds) == 1) {
            $only_one_associatound = true;
        } else {
            $only_one_associatound = false;
        }

        foreach ($associatounds as $associatound) {
            foreach ($associations as $key => $item) {
                $key_array = explode(' AS ', $key, 2);

                $associatound_atom_full_path = str_replace(' ', '', array_shift($key_array));

                $user_aliases = array();
                if (count($key_array) === 1) {
                    $user_aliases_string = str_replace(' ', '', array_shift($key_array));
                    if ((substr($user_aliases_string, 0, 1) === '[')
                        && (substr($user_aliases_string, -1) === ']')
                    ) {
                        $user_aliases = $this->convert_user_aliases_array($user_aliases_string);
                    } else {
                        $user_aliases['unique_model'] = $user_aliases_string;
                    }
                }

                if ( ! in_string(':', $associatound_atom_full_path)) {
                    $associatound_atom_full_path = '.:' . $associatound_atom_full_path;
                }

                if (substr($associatound_atom_full_path, 0, 1) === '.') {
                    if ( ! $only_one_associatound) {
                        trigger_error('LightORM error: Path "' . $key . '" is ambiguous', E_USER_ERROR);
                    }

                    $associatound_atom_full_path = $associatound->model . substr($associatound_atom_full_path, 1);
                }

                $associatound_atom_full_path_array = explode(':', $associatound_atom_full_path);

                $associatound_atom_property = array_pop($associatound_atom_full_path_array);

                $associatound_atom_path = implode(':', $associatound_atom_full_path_array);

                $associatound_atom_path_first_segment = array_shift($associatound_atom_full_path_array);
                if (in_string('>', $associatound_atom_path_first_segment)) {
                    $associatound_atom_path_first_segment_separator = '>';
                } elseif (in_string('<', $associatound_atom_path_first_segment)) {
                    $associatound_atom_path_first_segment_separator = '<';
                } else {
                    $associatound_atom_path_first_segment_separator = null;
                }
                if (is_null($associatound_atom_path_first_segment_separator)) {
                    $associatound_atom_path_first_model = $associatound_atom_path_first_segment;
                } else {
                    $associatound_atom_path_first_segment_array = explode($associatound_atom_path_first_segment_separator, $associatound_atom_path_first_segment, 2);
                    $associatound_atom_path_first_model = array_shift($associatound_atom_path_first_segment_array);
                }

                if ($associatound_atom_path_first_model != $associatound->model) {
                    continue;
                }

                if (count($associatound_atom_full_path_array) === 0) {
                    $associatound_atom_path_last_segment = $associatound_atom_path_first_segment;
                } else {
                    $associatound_atom_path_last_segment = array_pop($associatound_atom_full_path_array);
                }
                if (in_string('>', $associatound_atom_path_last_segment)) {
                    $associatound_atom_path_last_segment_separator = '>';
                } elseif (in_string('<', $associatound_atom_path_last_segment)) {
                    $associatound_atom_path_last_segment_separator = '<';
                } else {
                    $associatound_atom_path_last_segment_separator = null;
                }
                if (is_null($associatound_atom_path_last_segment_separator)) {
                    $associatound_atom_path_last_model = $associatound_atom_path_last_segment;
                } else {
                    $associatound_atom_path_last_segment_array = explode($associatound_atom_path_last_segment_separator, $associatound_atom_path_last_segment, 2);
                    $associatound_atom_path_last_model = array_pop($associatound_atom_path_last_segment_array);
                }

                $association_numbered_name = $business_associations->get_association_numbered_name($associatound_atom_path_last_model, $associatound_atom_property);
                if ($association_numbered_name === false) {
                    trigger_error('LightORM error: No association for model "' . $associatound_atom_path_last_model . '" with property "' . $associatound_atom_property . '"', E_USER_ERROR);
                }
                $business_associations_associatonents_group = new Business_associations_associatonents_group($association_numbered_name);
                $associatound->add_associatonents_group($business_associations_associatonents_group, $associatound_atom_full_path, $associatound_atom_path, $associatound_atom_path_last_model, $associatound_atom_property);

                $association_array = $business_associations->associations[$association_numbered_name];
                foreach ($association_array['associates'] as $associate_array) {
                    if (($associate_array['model'] != $associatound_atom_path_last_model)
                        || ($associate_array['property'] != $associatound_atom_property)
                    ) {
                        $associatonent = new Business_associations_associate($associate_array['model'], $associate_array['property']);
                        $business_associations_associatonents_group->add_associatonent($associatonent);

                        if (isset($user_aliases['models'][$associate_array['model']])) {
                            $user_alias_candidate = $user_aliases['models'][$associate_array['model']];
                        } elseif (isset($user_aliases['unique_model'])) {
                            $user_alias_candidate = $user_aliases['unique_model'];
                        } else {
                            $user_alias_candidate = null;
                        }
                        if ( ! is_null($user_alias_candidate)) {
                            if (isset($this->user_aliases[$user_alias_candidate])) {
                                trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                            }
                            $this->user_aliases[$user_alias_candidate] = array(
                                'type'    => 'model',
                                'object'  => $associatonent,
                            );
                        }
                    }
                }

                if (isset($user_aliases['association'])) {
                    if (isset($this->user_aliases[$user_aliases['association']])
                        || ($association_array['type'] !== 'many_to_many')
                    ) {
                        trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                    }

                    $this->user_aliases[$user_aliases['association']] = array(
                        'type'    => 'association',
                        'object'  => $business_associations_associatonents_group,
                    );
                }

                if ( ! is_null($item)) {
                    $this->with_rec($item, $business_associations_associatonents_group->associatonents);
                }
            }
        }
    }

    /**
     * Define the associations that must be retrieved
     *
     * @param   array|string|null  $associations
     * @return  Finder
     */
    public function with($associations) {
        $associations = $this->format_with($associations);
        $associations = $this->format_reflexive_associations($associations);
        $this->with_rec($associations, [$this->associations]);
        return $this;
    }

    /**
     * Complete the query of the Query_manager $query_manager
     *
     * @param   Query_manager  $query_manager
     * @return  void
     */
    private function complete_query(Query_manager $query_manager) {
        foreach ($this->stack as $item) {
            $formatted_args = $item['args'];
            if ( ! empty($formatted_args)) {
                $arg1 = array_shift($formatted_args);
                $arg1_array = explode(' ', $arg1, 2);
                $arg1_ext_field = array_shift($arg1_array);
                $arg1_ext_field_array = explode(':', $arg1_ext_field);

                $formatted_arg1_ext_field = array_pop($arg1_ext_field_array);
                if (count($arg1_ext_field_array) > 0) {
                    $arg1_ext_field_first_segment = array_shift($arg1_ext_field_array);
                    if (in_string('>', $arg1_ext_field_first_segment)) {
                        $arg1_ext_field_first_segment_separator = '>';
                    } elseif (in_string('<', $arg1_ext_field_first_segment)) {
                        $arg1_ext_field_first_segment_separator = '<';
                    } else {
                        $arg1_ext_field_first_segment_separator = null;
                    }
                    if (is_null($arg1_ext_field_first_segment_separator)) {
                        $arg1_ext_field_user_alias = $arg1_ext_field_first_segment;
                        $arg1_ext_field_first_segment_end = '';
                    } else {
                        $arg1_ext_field_user_alias = strstr($arg1_ext_field_first_segment, $arg1_ext_field_first_segment_separator, true);
                        $arg1_ext_field_first_segment_end = strstr($arg1_ext_field_first_segment, $arg1_ext_field_first_segment_separator);
                    }

                    if ( ! isset($this->user_aliases[$arg1_ext_field_user_alias])) {
                        trigger_error('LightORM error: Unknown alias', E_USER_ERROR);
                    }
                    $user_alias = $this->user_aliases[$arg1_ext_field_user_alias];
                    switch ($user_alias['type']) {
                        case 'model':
                            $atom_path_array = $arg1_ext_field_array;
                            array_unshift($atom_path_array, $user_alias['object']->model . $arg1_ext_field_first_segment_end);
                            $atom_path =  implode(':', $atom_path_array);
                            $formatted_arg1_ext_field = $user_alias['object']->atoms_aliases[$atom_path] . '.' . $formatted_arg1_ext_field;
                            break;
                        case 'association':
                            $formatted_arg1_ext_field = $user_alias['object']->joining_alias . '.' . $formatted_arg1_ext_field;
                            break;
                        default:
                            exit(1);
                            break;
                    }
                }

                array_unshift($arg1_array, $formatted_arg1_ext_field);
                $formatted_arg1 = implode(' ', $arg1_array);

                array_unshift($formatted_args, $formatted_arg1);
            }

            call_user_func_array(array($query_manager, $item['method']), $formatted_args);
        }
    }

    /**
     * Format the return $retour given the dimension $dimension
     *
     * @param   array                                       $retour
     * @param   self::DIMENSION_ONE | self::DIMENSION_MANY  $dimension
     * @return  mixed
     */
    private function format_return($retour, $dimension) {
        if ($dimension === self::DIMENSION_ONE) {
            switch (count($retour)) {
                case 0:
                    return null;
                case 1:
                    return array_shift($retour);
                default:
                    return false;
            }
        }

        return $retour;
    }

    /**
     * Manage the business initialization for the associations
     *
     * @param   Query_manager                    $query_manager
     * @param   Business_associations_associate  $associate
     * @param   int                              $table_alias_number
     * @param   int                              $model_number
     * @return  array
     */
    private function associations_business_initialization(Query_manager $query_manager, Business_associations_associate $associate, $table_alias_number = LIGHTORM_START_TABLE_ALIAS_NUMBER, $model_number = LIGHTORM_START_MODEL_NUMBER) {
        $models_metadata        = Models_metadata::get_singleton();
        $business_associations  = Business_associations::get_singleton();

        $associate_full_name = $models_metadata->models[$associate->model]['model_full_name'];
        if (is_null($associate->associatound_associatonents_group)) {
            $relation_info = array(
                'type' => 'none',
            );
        } else {
            $relation_info = array(
                'type'       => 'association',
                'associate'  => $associate,
            );
        }

        $associatonents_properties = array();
        foreach ($associate->associatonents_groups as $associatonents_group) {
            $associatonents_properties[$associatonents_group->associatound_atom_path][] = $associatonents_group->associatound_atom_property;
        }

        $associate_table_alias = 'alias_' . $table_alias_number;
        list($table_alias_number, $model_number) = $associate_full_name::business_initialization($query_manager, $table_alias_number, $model_number, $relation_info, $associate, $associate->model, $associatonents_properties);

        foreach ($associate->associatonents_groups as $associatonents_group) {
            $associatonents_group->associatound_atom_numbered_name  = $associate->atoms_numbered_names[$associatonents_group->associatound_atom_path];
            $associatonents_group->associatound_atom_alias          = $associate->atoms_aliases[$associatonents_group->associatound_atom_path];

            $association_array = $business_associations->associations[$associatonents_group->association_numbered_name];

            if ($association_array['type'] === 'many_to_many') {
                $association_table_alias = 'alias_' . $table_alias_number;

                $association_full_name  = $association_array['class_full_name'];
                $table_alias_number     = $association_full_name::business_initialization($query_manager, $table_alias_number, $associatonents_group);

                $associatonents_group->joining_alias = $association_table_alias;
            } else {
                $associatonents_group->joining_alias = $associatonents_group->associatound_atom_alias;
            }

            foreach ($associatonents_group->associatonents as $associatonent_instance) {
                $associatonent_array = $business_associations->get_associate_array($associatonent_instance->model, $associatonent_instance->property);

                if ($association_array['type'] === 'many_to_many') {
                    $associatonent_instance->joining_field        = $associatonent_array['joining_field'];
                    $associatonent_instance->associatonent_field  = 'id';
                } else {
                    $associatound_atom_array = $business_associations->get_associate_array($associatonents_group->associatound_atom_model, $associatonents_group->associatound_atom_property);

                    $associatonent_instance->joining_field        = $associatound_atom_array['field'];
                    $associatonent_instance->associatonent_field  = $associatonent_array['field'];
                }

                list($table_alias_number, $model_number) = $this->associations_business_initialization($query_manager, $associatonent_instance, $table_alias_number, $model_number);
            }
        }

        return [$table_alias_number, $model_number];
    }

    /**
     * Get the data
     *
     * @param   mixed  $filter  An id or an array of ids
     * @return  mixed
     */
    public function get($filter = null) {
        $business_compositions  = Business_compositions::get_singleton();
        $business_associations  = Business_associations::get_singleton();

        $databubble = new Databubble();

        $qm = new Query_manager();

        $this->associations_business_initialization($qm, $this->associations);

        if ( ! is_null($filter)) {
            if (is_array($filter)) {
                $qm->where_in('alias_' . LIGHTORM_START_TABLE_ALIAS_NUMBER . '.id', $filter);
            } else {
                $qm->where('alias_' . LIGHTORM_START_TABLE_ALIAS_NUMBER . '.id', $filter);
            }
        }
        $this->complete_query($qm);

        $query = $qm->get();

        $retour                 = array();
        $created_instances_ids  = array();
        foreach ($qm->models as $qm_model_key => $qm_model_item) {
            $created_instances_ids[$qm_model_key] = array();
        }

        foreach ($query->result() as $row) {
            $qm->convert_row($row);

            $models_pool        = array();
            $associations_pool  = array();

            foreach ($qm->models as $qm_model_key => $qm_model_item) {
                $model_instance = $databubble->add_model_row($qm_model_item, $row, $qm->aliases);

                if ( ! is_null($model_instance)) {
                    $concrete_model_full_name  = $model_instance->get_business_full_name();
                    $concrete_model            = $model_instance->get_business_short_name();
                    $abstract_model            = $concrete_model_full_name::get_table_abstract_model();

                    /******************************************************************************/

                    // We set to null or array() the components properties that should not be undefined anymore
                    $components_properties = array();
                    switch ($qm_model_item['model_info']['type']) {
                        case 'simple_model':
                            $concrete_model_compociate                = $business_compositions->compociates[$qm_model_item['name']];
                            $components_properties['concrete_model']  = $qm_model_item['model_info']['components_properties'];
                            break;
                        case 'concrete_model':
                            $concrete_model_compociate                = $business_compositions->compociates[$qm_model_item['name']];
                            $abstract_model_compociate                = $business_compositions->compociates[$abstract_model];
                            $components_properties['concrete_model']  = $qm_model_item['model_info']['components_properties']['concrete_model'];
                            $components_properties['abstract_model']  = $qm_model_item['model_info']['components_properties']['abstract_model'];
                            break;
                        case 'abstract_model':
                            $abstract_model_compociate                = $business_compositions->compociates[$qm_model_item['name']];
                            $concrete_model_compociate                = $business_compositions->compociates[$concrete_model];
                            $components_properties['abstract_model']  = $qm_model_item['model_info']['components_properties']['abstract_model'];
                            $components_properties['concrete_model']  = $qm_model_item['model_info']['components_properties']['concrete_models'][$concrete_model];
                            break;
                        default:
                            exit(1);
                            break;
                    }

                    foreach ($components_properties as $model_type => $model_type_components_properties) {
                        foreach ($model_type_components_properties as $component_property) {
                            foreach (${$model_type . '_compociate'}->components as $component_array) {
                                if ($component_array['compound_property'] === $component_property) {
                                    if (is_undefined($model_instance->{'get_' . $component_property}())) {
                                        switch ($component_array['component_dimension']) {
                                            case 'one':
                                                $model_instance->{'set_' . $component_property}(null);
                                                break;
                                            case 'many':
                                                $model_instance->{'set_' . $component_property}(array());
                                                break;
                                            default:
                                                exit(1);
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    /******************************************************************************/

                    // We set to null or array() the associatonents properties that should not be undefined anymore
                    $associatonents_properties = array();
                    switch ($qm_model_item['model_info']['type']) {
                        case 'simple_model':
                            $associatonents_properties['concrete_model']  = $qm_model_item['model_info']['associatonents_properties'];
                            break;
                        case 'concrete_model':
                            $associatonents_properties['concrete_model']  = $qm_model_item['model_info']['associatonents_properties']['concrete_model'];
                            $associatonents_properties['abstract_model']  = $qm_model_item['model_info']['associatonents_properties']['abstract_model'];
                            break;
                        case 'abstract_model':
                            $associatonents_properties['abstract_model']  = $qm_model_item['model_info']['associatonents_properties']['abstract_model'];
                            $associatonents_properties['concrete_model']  = $qm_model_item['model_info']['associatonents_properties']['concrete_models'][$concrete_model];
                            break;
                        default:
                            exit(1);
                            break;
                    }

                    foreach ($associatonents_properties as $model_type => $model_type_associatonents_properties) {
                        foreach ($model_type_associatonents_properties as $associatonent_property) {
                            if (is_undefined($model_instance->{'get_' . $associatonent_property}())) {
                                $associatonent_array = $business_associations->get_associate_array(${$model_type}, $associatonent_property);
                                switch ($associatonent_array['dimension']) {
                                    case 'one':
                                        $model_instance->{'set_' . $associatonent_property}(null);
                                        break;
                                    case 'many':
                                        $model_instance->{'set_' . $associatonent_property}(array());
                                        break;
                                    default:
                                        exit(1);
                                        break;
                                }
                            }
                        }
                    }
                }

                $models_pool[$qm_model_key] = $model_instance;
            }

            // We manage the relations between the models
            foreach ($qm->models as $qm_model_key => $qm_model_item) {
                $current_model = $models_pool[$qm_model_key];

                if (is_null($current_model)
                    || in_array($current_model->get_id(), $created_instances_ids[$qm_model_key])
                ) {
                    continue;
                }

                switch ($qm_model_item['relation_info']['type']) {
                    case 'none':
                        $retour[] = $current_model;
                        break;
                    case 'composition':
                        $compound_numbered_name  = $qm_model_item['relation_info']['compound_numbered_name'];
                        $compound_property       = $qm_model_item['relation_info']['component_array']['compound_property'];
                        $component_dimension     = $qm_model_item['relation_info']['component_array']['component_dimension'];

                        switch ($component_dimension) {
                            case 'one':
                                $models_pool[$compound_numbered_name]->{'set_' . $compound_property}($current_model);
                                break;
                            case 'many':
                                $compound_component = $models_pool[$compound_numbered_name]->{'get_' . $compound_property}();
                                $compound_component[] = $current_model;
                                $models_pool[$compound_numbered_name]->{'set_' . $compound_property}($compound_component);
                                break;
                            default:
                                exit(1);
                                break;
                        }
                        break;
                    case 'association':
                        $association_numbered_name        = $qm_model_item['relation_info']['associate']->associatound_associatonents_group->association_numbered_name;
                        $associatound_atom_numbered_name  = $qm_model_item['relation_info']['associate']->associatound_associatonents_group->associatound_atom_numbered_name;
                        $associatound_atom_model          = $qm_model_item['relation_info']['associate']->associatound_associatonents_group->associatound_atom_model;
                        $associatound_atom_property       = $qm_model_item['relation_info']['associate']->associatound_associatonents_group->associatound_atom_property;

                        $association_array = $business_associations->associations[$association_numbered_name];

                        switch ($association_array['type']) {
                            case 'one_to_one':
                                $models_pool[$associatound_atom_numbered_name]->{'set_' . $associatound_atom_property}($current_model);
                                break;
                            case 'one_to_many':
                                $associatound_atom_array = $business_associations->get_associate_array($associatound_atom_model, $associatound_atom_property);
                                switch ($associatound_atom_array['dimension']) {
                                    case 'one':
                                        $models_pool[$associatound_atom_numbered_name]->{'set_' . $associatound_atom_property}($current_model);
                                        break;
                                    case 'many':
                                        $associatound_associatonents = $models_pool[$associatound_atom_numbered_name]->{'get_' . $associatound_atom_property}();
                                        $associatound_associatonents[] = $current_model;
                                        $models_pool[$associatound_atom_numbered_name]->{'set_' . $associatound_atom_property}($associatound_associatonents);
                                        break;
                                    default:
                                        exit(1);
                                        break;
                                }
                                break;
                            case 'many_to_many':
                                if ( ! isset($associations_pool[$associatound_atom_numbered_name][$associatound_atom_property])) {
                                    $association_full_name = $association_array['class_full_name'];
                                    $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property] = $association_full_name::business_creation($qm_model_item, $row, $qm->aliases);

                                    $associatound_associatonents = $models_pool[$associatound_atom_numbered_name]->{'get_' . $associatound_atom_property}();
                                    $associatound_associatonents[] = $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property];
                                    $models_pool[$associatound_atom_numbered_name]->{'set_' . $associatound_atom_property}($associatound_associatonents);

                                    $associatound_atom_array = $business_associations->get_associate_array($associatound_atom_model, $associatound_atom_property);
                                    $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property]->{'set_' . $associatound_atom_array['reverse_property']}($models_pool[$associatound_atom_numbered_name]);
                                }

                                $associate_array = $business_associations->get_associate_array($qm_model_item['name'], $qm_model_item['relation_info']['associate']->property);
                                $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property]->{'set_' . $associate_array['reverse_property']}($current_model);
                                break;
                            default:
                                exit(1);
                                break;
                        }

                        // To avoid having duplicate models in the reflexive associations
                        $qm_models = $qm->models;
                        foreach ($qm_models as $key => $item) {
                            if (($key !== $qm_model_key)
                                && ($item['name'] === $qm_model_item['name'])
                                && ($item['relation_info']['type'] === 'association')
                                && ($item['relation_info']['associate']->property === $qm_model_item['relation_info']['associate']->property)
                            ) {
                                $created_instances_ids[$key][] = $current_model->get_id();
                            }
                        }
                        break;
                    default:
                        exit(1);
                        break;
                }

                $created_instances_ids[$qm_model_key][] = $current_model->get_id();
            }
        }

        if ($this->dimension === self::DIMENSION_AUTO) {
            if (is_null($filter)
                || is_array($filter)
            ) {
                $dimension = self::DIMENSION_MANY;
            } else {
                $dimension = self::DIMENSION_ONE;
            }
        } else {
            $dimension = $this->dimension;
        }

        return $this->format_return($retour, $dimension);
    }
}
