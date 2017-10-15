<?php
#################################
#       GydruS's Engine 3       #
#       "DataKeeper" class      #
#             v. 1.0            #
#           2012 10 29          #
#################################

# 2Do: Using Redis, Memcached and etc via providers if provider protperty is not empty

class DataKeeper
{
    public $items = null;
    public $data = null;
 
    public function __construct(){
        $this->items = array();
        $this->data = &$this->items;
    }
    
    public function __destruct(){
    }

    public function add($item, $key = NULL){
        $this->set($item, $key);
    }
    
    public function set($item, $key = NULL){
        if (empty($key)) $this->items[] = $item;
        else $this->items[$key] = $item;
    }
    
    public function get($key){
        return isset($this->items[$key]) ? $this->items[$key] : NULL;
    }
    
    public function getAll(){
        return $this->items;
    }
    
    public function key_exists($key){
        return array_key_exists($key, $this->items);
    }
    
}
