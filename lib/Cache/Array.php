<?php

/**
 * 
 *
 * @author Hugo Ferreira da Silva
 */
class Lumine_Cache_Array extends Lumine_Cache_Abstract {
	
	private static $db = array();
	
	/**
	 * @see Lumine_Cache_Abstract::store()
	 */
    public function store($key, $value){
    	self::$db[$key] = $value;
    }

    /**
     * @see Lumine_Cache_Abstract::fetch()
     */
    public function fetch($key){
        if(!array_key_exists($key, self::$db)){
            throw new Lumine_Exception('Valor nao encontrado no cache: '.$key);
        }
        
        return self::$db[$key];
    }

    /**
     * @see Lumine_Cache_Abstract::exists()
     */
    public function exists($key){
        return array_key_exists($key, self::$db);
    }

    /**
     * @see Lumine_Cache_Abstract::delete()
     */
    public function delete($key){
       unset(self::$db[$key]);
    }

    /**
     * @see Lumine_Cache_Abstract::clear()
     */
    public function clear(){
        self::$db = array();
    }
}

