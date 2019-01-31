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
    const FIND_ONE   = 0;
    const FIND_MANY  = 1;

    private $model_short_name;
    private $type;
    private $stack;

    //------------------------------------------------------//

    /**
     * The constructor
     *
     * @access public
     * @param $model_short_name The model short name
     * @param $type The type of Finder
     */
    public function __construct($model_short_name, $type = self::FIND_MANY) {
        $this->model_short_name  = $model_short_name;
        $this->type              = $type;
        $this->stack             = array();
    }

    //------------------------------------------------------//

    public function get_type() {
        return $this->type;
    }

    public function set_type($type) {
        $this->type = $type;
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
     * Complete the query of the Query_manager $query_manager
     *
     * @param   Query_manager  $query_manager
     * @return  void
     */
    public function complete_query(Query_manager $query_manager) {
        foreach ($this->stack as $item) {
            call_user_func_array(array($query_manager, $item['method']), $item['args']);
        }
    }

    /**
     * Format the return $retour
     *
     * @param   array  $retour
     * @return  mixed
     */
    public function format_return($retour) {
        if ($this->type == self::FIND_ONE) {
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
     * Get the data
     *
     * @param   mixed  $filter  An id or an array of ids
     * @return  mixed
     */
    public function get($filter = null) {
        $databubble       = new Databubble();
        $model_full_name  = model_full_name($this->model_short_name);

        $qm = new Query_manager();

        $model_full_name::business_initializor($qm);

        if ( ! is_null($filter)) {
            if (is_array($filter)) {
                $qm->where_in('alias_1.id', $filter);
            } else {
                $qm->where('alias_1.id', $filter);
            }
        }
        $this->complete_query($qm);

        $query = $qm->get();

        $retour      = array();
        $retour_ids  = array();
        foreach ($query->result() as $row) {
            $qm->convert_row($row);

            $models_pool = array();
            foreach ($qm->models as $qm_model_key => $qm_model_value) {
                $models_pool[$qm_model_key] = $databubble->add_model_row($qm_model_value, $row, $qm->aliases);
            }

            foreach ($qm->models as $qm_model_key => $qm_model_value) {
                $current_model = $models_pool[$qm_model_key];

                if (is_null($current_model)) {
                    continue;
                }

                if (is_null($qm_model_value['compound'])) {
                    if ( ! in_array($current_model->get_id(), $retour_ids)) {
                        $retour[] = $current_model;
                        $retour_ids[] = $current_model->get_id();
                    }
                    continue;
                }

                $compound_name       = $qm_model_value['compound']['compound_name'];
                $compound_property   = $qm_model_value['compound']['compound_property'];
                $compound_component  = $models_pool[$compound_name]->{'get_' . $compound_property->property}();

                if ($compound_property->is_array) {
                    if ( ! isset($compound_component[$current_model->get_id()])) {
                        $compound_component[$current_model->get_id()] = $current_model;
                        $models_pool[$compound_name]->{'set_' . $compound_property->property}($compound_component);
                    }
                } else {
                    if ( ! isset($compound_component)) {
                        $models_pool[$compound_name]->{'set_' . $compound_property->property}($current_model);
                    }
                }
            }
        }

        return $this->format_return($retour);
    }
}
