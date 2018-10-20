<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Database_Query_Builder_Select extends Kohana_Database_Query_Builder_Select
{
    /**
     * Reset select array
     *
     * @return \Database_Query_Builder_Select
     */
    public function reset_select_columns()
    {
        $this->_select = array();
        return $this;
    }
    
    /**
     * Reset order by array
     *
     * @return \Database_Query_Builder_Select
     */
    public function reset_order_by()
    {
        $this->_order_by = array();
        return $this;
    }
}