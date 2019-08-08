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
    private $constructor_reflexive_part;
    private $databubble;
    private $dimension;
    private $associations;
    private $with;
    private $user_aliases;
    private $stack;
    private $offset;
    private $limit;

    public $qm_main;
    public $qm_offsetlimit_subquery;
    public $has_offsetlimit_subquery;
    public $models;

    //------------------------------------------------------//

    /**
     * The constructor
     *
     * @param  string      $model       The model
     * @param  Databubble  $databubble  The databubble
     * @param  int         $dimension   The dimension
     */
    public function __construct($model, $databubble = null, $dimension = self::DIMENSION_AUTO) {
        $model = trim($model);

        if (is_null($databubble)) {
            $databubble = new Databubble();
        }

        //------------------------------------------------------//

        $model_array = explode(' REC[', $model, 2);
        $model_first_part = rtrim(array_shift($model_array));
        if (count($model_array) === 1) {
            $model_reflexive_part = array_shift($model_array);
            if (substr($model_reflexive_part, -1) !== ']') {
                trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
            }
            $this->constructor_reflexive_part = trim(substr($model_reflexive_part, 0, -1));
        } else {
            $this->constructor_reflexive_part = null;
        }

        //------------------------------------------------------//

        $model_first_part_array = explode(' AS[', $model_first_part, 2);
        $model_short_name = rtrim(array_shift($model_first_part_array));
        $associate = new Business_associations_associate($model_short_name);

        $this->model_short_name  = $model_short_name;
        $this->databubble        = $databubble;
        $this->dimension         = $dimension;
        $this->associations      = $associate;
        $this->with              = array();
        $this->user_aliases      = array();
        $this->stack             = array();
        $this->offset            = null;
        $this->limit             = null;

        $this->soft_reset();

        //------------------------------------------------------//

        if (count($model_first_part_array) === 1) {
            $model_alias_part = array_shift($model_first_part_array);
            if (substr($model_alias_part, -1) !== ']') {
                trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
            }

            $model_alias = trim(substr($model_alias_part, 0, -1));

            $this->user_aliases[$model_alias] = array(
                'type'    => 'model',
                'object'  => $associate,
            );
        }
    }

    //------------------------------------------------------//

    public function get_databubble() {
        return $this->databubble;
    }
    public function set_databubble($databubble) {
        $this->databubble = $databubble;
    }

    public function get_dimension() {
        return $this->dimension;
    }
    public function set_dimension($dimension) {
        $this->dimension = $dimension;
    }

    //------------------------------------------------------//

    /**
     * Set the associations that must be retrieved
     *
     * @param   array|string|null  $associations
     * @return  Finder
     */
    public function with($associations) {
        $this->with = $associations;
        return $this;
    }

    /**
     * Set the offset
     *
     * @param   int  $offset
     * @return  Finder
     */
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Set the limit
     *
     * @param   int  $limit
     * @return  Finder
     */
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * The magic method __call()
     *
     * @param   string  $method
     * @param   array   $args
     * @return  Finder
     */
    public function __call($method, $args) {
        if (in_array(
            $method,
            array(
                'from',
                'table',
                'join',
                'get_where',
                'group_by',
                'having',
                'or_having',
            )
        )) {
            trigger_error("LightORM error: The method '" . $method . "()' cannot be used on a Finder instance", E_USER_ERROR);
        }

        $this->stack[] = array(
            'method'  => $method,
            'args'    => $args
        );
        return $this;
    }

    //------------------------------------------------------//

    /**
     * Process a soft reset
     *
     * @return  void
     */
    private function soft_reset() {
        $this->qm_main                   = new Query_manager();
        $this->qm_offsetlimit_subquery   = null;
        $this->has_offsetlimit_subquery  = null;
        $this->models                    = array();
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
            $associations = trim($associations);
            return array($associations => null);
        }

        $retour = array();

        foreach ($associations as $key1 => $item1) {
            if (is_string($key1)) {
                $trimmed_key1 = trim($key1);
                $retour[$trimmed_key1] = $this->format_with_rec($item1);
            } else {
                if (is_string($item1)) {
                    $trimmed_item1 = trim($item1);
                    $retour[$trimmed_item1] = null;
                } elseif (is_array($item1)) {
                    foreach ($item1 as $item2) {
                        if (is_string($item2)) {
                            $trimmed_item2 = trim($item2);
                            $retour[$trimmed_item2] = null;
                        } elseif (is_array($item2)) {
                            foreach ($item2 as $key3 => $item3) {
                                if ( ! is_string($key3)) {
                                    trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                                }

                                $trimmed_key3 = trim($key3);
                                $retour[$trimmed_key3] = $this->format_with_rec($item3);
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
     * Format the REC part of the reflexive associations in the associations $associations
     *
     * @param   array  $associations
     * @return  array
     */
    private function format_reflexive_associations_rec_part($associations) {
        if (is_null($associations)) {
            return null;
        }

        $retour = array();

        foreach ($associations as $key => $item) {
            $formatted_item = $this->format_reflexive_associations_rec_part($item);

            $key_array = explode(' REC[', $key, 2);
            $key_first_part = rtrim(array_shift($key_array));

            if (count($key_array) === 1) {
                $key_reflexive_part = array_shift($key_array);
                if (substr($key_reflexive_part, -1) !== ']') {
                    trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                }
                $key_reflexive_part = trim(substr($key_reflexive_part, 0, -1));

                if (is_null($formatted_item)) {
                    $formatted_item = array(
                        $key_reflexive_part => null,
                    );
                } else {
                    $formatted_item[$key_reflexive_part] = $formatted_item;
                }
            }

            $retour[$key_first_part] = $formatted_item;
        }

        return $retour;
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

            if ($key_array_part2 < 1) {
                trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
            }

            if ($key_array_part2 === 1) {
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
     * Convert the string of user aliases in array
     *
     * @param   string  $user_aliases_string
     * @return  array
     */
    private function convert_user_aliases($user_aliases_string) {
        if (empty($user_aliases_string)) {
            return array();
        }

        $retour = array();

        $first_label = strstr($user_aliases_string, ':', true);
        $after_first_label = strstr_after_needle($user_aliases_string, ':');
        switch ($first_label) {
            case 'model':
                if (in_string(',', $after_first_label)) {
                    $retour = $this->convert_user_aliases(strstr_after_needle($after_first_label, ','));
                    $retour['unique_model'] = strstr($after_first_label, ',', true);
                } else {
                    $retour['unique_model'] = $after_first_label;
                }
                break;
            case 'association':
                if (in_string(',', $after_first_label)) {
                    $retour = $this->convert_user_aliases(strstr_after_needle($after_first_label, ','));
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
                    $retour = $this->convert_user_aliases(substr($after_first_segment, 1));
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
     * This function is the recursive part of the function process_with()
     *
     * @param   array  $associations
     * @param   array  $associatounds  an array of Business_associations_associate
     * @return  void
     */
    private function process_with_rec(array $associations, array $associatounds) {
        $associations_metadata = Associations_metadata::get_singleton();

        if (count($associatounds) == 1) {
            $only_one_associatound = true;
        } else {
            $only_one_associatound = false;
        }

        foreach ($associatounds as $associatound) {
            foreach ($associations as $key => $item) {
                $key_array = explode(' AS[', $key, 2);

                $associatound_atom_full_path = str_replace(' ', '', array_shift($key_array));

                $user_aliases = array();
                if (count($key_array) === 1) {
                    $user_aliases_string = array_shift($key_array);
                    if (substr($user_aliases_string, -1) !== ']') {
                        trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                    }
                    $user_aliases_string = substr($user_aliases_string, 0, -1);
                    $user_aliases_string = str_replace(' ', '', $user_aliases_string);

                    if (in_string(':', $user_aliases_string)) {
                        $user_aliases = $this->convert_user_aliases($user_aliases_string);
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
                if (count($associatound_atom_full_path_array) !== 2) {
                    trigger_error('LightORM error: Error in the tree of associations', E_USER_ERROR);
                }
                list($associatound_atom_path, $associatound_atom_property) = $associatound_atom_full_path_array;

                if (in_string('>', $associatound_atom_path)) {
                    $associatound_atom_path_separator = '>';
                } elseif (in_string('<', $associatound_atom_path)) {
                    $associatound_atom_path_separator = '<';
                } else {
                    $associatound_atom_path_separator = null;
                }
                if (is_null($associatound_atom_path_separator)) {
                    $associatound_atom_path_first_model  = $associatound_atom_path;
                    $associatound_atom_path_last_model   = $associatound_atom_path;
                } else {
                    $associatound_atom_path_array = explode($associatound_atom_path_separator, $associatound_atom_path, 2);
                    list($associatound_atom_path_first_model, $associatound_atom_path_last_model) = $associatound_atom_path_array;
                }

                if ($associatound_atom_path_first_model != $associatound->model) {
                    continue;
                }

                $association_numbered_name = $associations_metadata->get_association_numbered_name(
                    array(
                        'model'     => $associatound_atom_path_last_model,
                        'property'  => $associatound_atom_property,
                    )
                );
                if ($association_numbered_name === false) {
                    trigger_error('LightORM error: No association for model "' . $associatound_atom_path_last_model . '" with property "' . $associatound_atom_property . '"', E_USER_ERROR);
                }
                $business_associations_associatonents_group = new Business_associations_associatonents_group($association_numbered_name);
                $associatound->add_associatonents_group($business_associations_associatonents_group, $associatound_atom_full_path, $associatound_atom_path, $associatound_atom_path_last_model, $associatound_atom_property);

                $opposite_associates_arrays = $associations_metadata->get_opposite_associates_arrays(
                    array(
                        'model'     => $associatound_atom_path_last_model,
                        'property'  => $associatound_atom_property,
                    )
                );
                foreach ($opposite_associates_arrays as $opposite_associate_array) {
                    $associatonent = new Business_associations_associate($opposite_associate_array['model'], $opposite_associate_array['property']);
                    $business_associations_associatonents_group->add_associatonent($associatonent);

                    if (isset($user_aliases['models'][$opposite_associate_array['model']])) {
                        $user_alias_candidate = $user_aliases['models'][$opposite_associate_array['model']];
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

                $association_array = $associations_metadata->associations[$association_numbered_name];
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
                    $this->process_with_rec($item, $business_associations_associatonents_group->associatonents);
                }
            }
        }
    }

    /**
     * Process the property $with
     *
     * @return  void
     */
    private function process_with() {
        $associations = $this->format_with($this->with);

        if ( ! is_null($this->constructor_reflexive_part)) {
            $associations[$this->constructor_reflexive_part]  = $associations;
            $this->constructor_reflexive_part                 = null;
        }

        $associations = $this->format_reflexive_associations_rec_part($associations);

        $associations = $this->format_reflexive_associations($associations);

        $this->process_with_rec($associations, [$this->associations]);
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

                if (count($arg1_ext_field_array) > 2) {
                    trigger_error('LightORM error: Syntax error', E_USER_ERROR);
                }

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
                        $arg1_ext_field_first_segment_array = explode($arg1_ext_field_first_segment_separator, $arg1_ext_field_first_segment, 2);
                        list($arg1_ext_field_user_alias, $arg1_ext_field_first_segment_end) = $arg1_ext_field_first_segment_array;
                        $arg1_ext_field_first_segment_end = $arg1_ext_field_first_segment_separator . $arg1_ext_field_first_segment_end;
                    }

                    if ( ! isset($this->user_aliases[$arg1_ext_field_user_alias])) {
                        trigger_error('LightORM error: Unknown alias', E_USER_ERROR);
                    }
                    $user_alias = $this->user_aliases[$arg1_ext_field_user_alias];
                    switch ($user_alias['type']) {
                        case 'model':
                            $formatted_arg1_ext_field = $user_alias['object']->atoms_aliases[$user_alias['object']->model . $arg1_ext_field_first_segment_end] . '.' . $formatted_arg1_ext_field;
                            break;
                        case 'association':
                            if ( ! is_null($arg1_ext_field_first_segment_separator)) {
                                trigger_error('LightORM error: Syntax error', E_USER_ERROR);
                            }
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
     * @param   Business_associations_associate  $associate
     * @param   int                              $table_alias_number
     * @param   int                              $model_number
     * @return  array
     */
    private function associations_business_initialization(Business_associations_associate $associate, $table_alias_number = LIGHTORM_START_TABLE_ALIAS_NUMBER, $model_number = LIGHTORM_START_MODEL_NUMBER) {
        $models_metadata        = Models_metadata::get_singleton();
        $associations_metadata  = Associations_metadata::get_singleton();

        $associate_full_name = $models_metadata->models[$associate->model]['model_full_name'];

        $associatonents_properties = array();
        foreach ($associate->associatonents_groups as $associatonents_group) {
            $associatonents_properties[$associatonents_group->associatound_atom_path][] = $associatonents_group->associatound_atom_property;
        }

        $associate_table_alias = 'alias_' . $table_alias_number;
        list($table_alias_number, $model_number) = $associate_full_name::business_initialization($this, $associate, $table_alias_number, $model_number, $associatonents_properties);

        foreach ($associate->associatonents_groups as $associatonents_group) {
            $associatonents_group->associatound_atom_numbered_name  = $associate->atoms_numbered_names[$associatonents_group->associatound_atom_path];
            $associatonents_group->associatound_atom_alias          = $associate->atoms_aliases[$associatonents_group->associatound_atom_path];

            $association_array = $associations_metadata->associations[$associatonents_group->association_numbered_name];

            if ($association_array['type'] === 'many_to_many') {
                $association_table_alias = 'alias_' . $table_alias_number;

                $association_full_name  = $association_array['class_full_name'];
                $table_alias_number     = $association_full_name::business_initialization($this, $table_alias_number, $associatonents_group);

                $associatonents_group->joining_alias = $association_table_alias;
            } else {
                $associatonents_group->joining_alias = $associatonents_group->associatound_atom_alias;
            }

            foreach ($associatonents_group->associatonents as $associatonent_instance) {
                $associatonent_array = $associations_metadata->get_associate_array(
                    array(
                        'model'     => $associatonent_instance->model,
                        'property'  => $associatonent_instance->property,
                    )
                );

                if ($association_array['type'] === 'many_to_many') {
                    $associatonent_instance->joining_field        = $associatonent_array['joining_field'];
                    $associatonent_instance->associatonent_field  = 'id';
                } else {
                    $associatound_atom_array = $associations_metadata->get_associate_array(
                        array(
                            'model'     => $associatonents_group->associatound_atom_model,
                            'property'  => $associatonents_group->associatound_atom_property,
                        )
                    );

                    $associatonent_instance->joining_field        = $associatound_atom_array['field'];
                    $associatonent_instance->associatonent_field  = $associatonent_array['field'];
                }

                list($table_alias_number, $model_number) = $this->associations_business_initialization($associatonent_instance, $table_alias_number, $model_number);
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
        $associations_metadata = Associations_metadata::get_singleton();

        if ( ! is_null($filter)) {
            if (is_array($filter)) {
                $this->where_in('alias_' . LIGHTORM_START_TABLE_ALIAS_NUMBER . '.id', $filter);
            } else {
                $this->where('alias_' . LIGHTORM_START_TABLE_ALIAS_NUMBER . '.id', $filter);
            }
        }

        $this->process_with();

        if (is_null($this->offset)
            && is_null($this->limit)
        ) {
            $this->has_offsetlimit_subquery = false;
        } else {
            $this->has_offsetlimit_subquery  = true;
            $this->qm_offsetlimit_subquery   = new Query_manager();
        }

        $this->associations_business_initialization($this->associations);

        $this->complete_query($this->qm_main);

        if ($this->has_offsetlimit_subquery) {
            $this->qm_offsetlimit_subquery->select('alias_' . LIGHTORM_START_TABLE_ALIAS_NUMBER . '.id');

            $this->complete_query($this->qm_offsetlimit_subquery);

            $this->qm_offsetlimit_subquery->group_by('alias_' . LIGHTORM_START_TABLE_ALIAS_NUMBER . '.id');

            if ( ! is_null($this->offset)) {
                $this->qm_offsetlimit_subquery->offset($this->offset);
            }

            if ( ! is_null($this->limit)) {
                $this->qm_offsetlimit_subquery->limit($this->limit);
            }

            $this->qm_main->simple_where_in('alias_' . LIGHTORM_START_TABLE_ALIAS_NUMBER . '.id', $this->qm_offsetlimit_subquery->get_compiled_select(), false);
        }

        //------------------------------------------------------//

        $query = $this->qm_main->get();

        $retour                            = array();
        $already_reinitialized_properties  = array();
        $already_managed_retour_ids        = array();
        $already_managed_associations      = array();
        $associations_instances            = array();

        foreach ($query->result() as $row) {
            $this->qm_main->convert_row($row);

            $models_pool        = array();
            $associations_pool  = array();

            foreach ($this->models as $finder_model_key => $finder_model_item) {
                $model_instance = $this->databubble->add_model_row($finder_model_item, $row, $this->qm_main->aliases);

                if ( ! is_null($model_instance)) {
                    $concrete_model_full_name  = get_class($model_instance);
                    $concrete_model            = $concrete_model_full_name::get_business_short_name();
                    $abstract_model            = $concrete_model_full_name::get_table_abstract_model();

                    // We set to null or array() the associatonents properties that should not be undefined anymore
                    $associatonents_properties = array();
                    switch ($finder_model_item['model_info']['type']) {
                        case 'simple_model':
                            $associatonents_properties['concrete_model']  = $finder_model_item['model_info']['associatonents_properties'];
                            break;
                        case 'concrete_model':
                            $associatonents_properties['concrete_model']  = $finder_model_item['model_info']['associatonents_properties']['concrete_model'];
                            $associatonents_properties['abstract_model']  = $finder_model_item['model_info']['associatonents_properties']['abstract_model'];
                            break;
                        case 'abstract_model':
                            $associatonents_properties['abstract_model']  = $finder_model_item['model_info']['associatonents_properties']['abstract_model'];
                            $associatonents_properties['concrete_model']  = $finder_model_item['model_info']['associatonents_properties']['concrete_models'][$concrete_model];
                            break;
                        default:
                            exit(1);
                            break;
                    }

                    foreach ($associatonents_properties as $model_type => $model_type_associatonents_properties) {
                        foreach ($model_type_associatonents_properties as $associatonent_property) {
                            if (( ! isset($already_reinitialized_properties[$concrete_model][$model_instance->get_id()]))
                                || ( ! in_array($associatonent_property, $already_reinitialized_properties[$concrete_model][$model_instance->get_id()]))
                            ) {
                                $associatonent_array = $associations_metadata->get_associate_array(
                                    array(
                                        'model'     => ${$model_type},
                                        'property'  => $associatonent_property,
                                    )
                                );
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

                                $already_reinitialized_properties[$concrete_model][$model_instance->get_id()][] = $associatonent_property;
                            }
                        }
                    }
                }

                $models_pool[$finder_model_key] = $model_instance;
            }

            foreach ($this->models as $finder_model_key => $finder_model_item) {
                $current_model = $models_pool[$finder_model_key];

                if (is_null($current_model)) {
                    continue;
                }

                if (is_null($finder_model_item['associate']->associatound_associatonents_group)) {
                    if (in_array($current_model->get_id(), $already_managed_retour_ids)) {
                        // To avoid having duplicate models in the variable $retour
                        continue;
                    }

                    $retour[]                      = $current_model;
                    $already_managed_retour_ids[]  = $current_model->get_id();
                } else {
                    $association_numbered_name        = $finder_model_item['associate']->associatound_associatonents_group->association_numbered_name;
                    $associatound_atom_numbered_name  = $finder_model_item['associate']->associatound_associatonents_group->associatound_atom_numbered_name;
                    $associatound_atom_model          = $finder_model_item['associate']->associatound_associatonents_group->associatound_atom_model;
                    $associatound_atom_property       = $finder_model_item['associate']->associatound_associatonents_group->associatound_atom_property;

                    if (isset($already_managed_associations[$finder_model_key][$current_model->get_id()][$associatound_atom_numbered_name][$models_pool[$associatound_atom_numbered_name]->get_id()])
                        && in_array($associatound_atom_property, $already_managed_associations[$finder_model_key][$current_model->get_id()][$associatound_atom_numbered_name][$models_pool[$associatound_atom_numbered_name]->get_id()])
                    ) {
                        // To avoid having duplicate models in the associations
                        continue;
                    }

                    $association_array = $associations_metadata->associations[$association_numbered_name];

                    switch ($association_array['type']) {
                        case 'one_to_one':
                            $models_pool[$associatound_atom_numbered_name]->{'set_' . $associatound_atom_property}($current_model);
                            break;
                        case 'one_to_many':
                            $associatound_atom_array = $associations_metadata->get_associate_array(
                                array(
                                    'model'     => $associatound_atom_model,
                                    'property'  => $associatound_atom_property,
                                )
                            );
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
                            $association_short_name   = $association_array['class'];
                            $association_full_name    = $association_array['class_full_name'];
                            $association_table_alias  = $finder_model_item['associate']->associatound_associatonents_group->joining_alias;

                            if ( ! isset($associations_pool[$associatound_atom_numbered_name][$associatound_atom_property])) {
                                $association_primary_key_ids = array();
                                foreach ($association_full_name::get_primary_key_fields() as $association_primary_key_field) {
                                    $association_primary_key_ids[] = $row->{$association_table_alias . ':' . $association_primary_key_field};
                                }
                                $association_primary_key_scalar = implode(':', $association_primary_key_ids);

                                if ( ! isset($associations_instances[$association_short_name][$association_primary_key_scalar])) {
                                    $associations_instances[$association_short_name][$association_primary_key_scalar] = $association_full_name::business_creation($finder_model_item, $row, $this->qm_main->aliases);
                                }

                                $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property] = $associations_instances[$association_short_name][$association_primary_key_scalar];

                                //------------------------------------------------------//

                                $associatound_associatonents = $models_pool[$associatound_atom_numbered_name]->{'get_' . $associatound_atom_property}();
                                $associatound_associatonents[] = $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property];
                                $models_pool[$associatound_atom_numbered_name]->{'set_' . $associatound_atom_property}($associatound_associatonents);

                                $associatound_atom_array = $associations_metadata->get_associate_array(
                                    array(
                                        'model'     => $associatound_atom_model,
                                        'property'  => $associatound_atom_property,
                                    )
                                );
                                $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property]->{'set_' . $associatound_atom_array['reverse_property']}($models_pool[$associatound_atom_numbered_name]);
                            }

                            $associate_array = $associations_metadata->get_associate_array(
                                array(
                                    'model'     => $finder_model_item['name'],
                                    'property'  => $finder_model_item['associate']->property,
                                )
                            );
                            $associations_pool[$associatound_atom_numbered_name][$associatound_atom_property]->{'set_' . $associate_array['reverse_property']}($current_model);
                            break;
                        default:
                            exit(1);
                            break;
                    }

                    $already_managed_associations[$finder_model_key][$current_model->get_id()][$associatound_atom_numbered_name][$models_pool[$associatound_atom_numbered_name]->get_id()][] = $associatound_atom_property;
                }
            }
        }

        foreach ($associations_instances as $associations_instances_bunch) {
            foreach ($associations_instances_bunch as $associations_instances_bunch_item) {
                $this->databubble->add_association_instance($associations_instances_bunch_item);
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

        $retour = $this->format_return($retour, $dimension);

        $this->soft_reset();

        return $retour;
    }
}
