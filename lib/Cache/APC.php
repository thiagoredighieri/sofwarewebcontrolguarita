<?php

/**
 * 
 *
 * @author Hugo Ferreira da Silva
 */
class Lumine_Cache_APC extends Lumine_Cache_Abstract {
	/**
	 * @see Lumine_Cache_Abstract::store()
	 */
    public function store($key, $value){
        apc_store($key, $value);
    }

    /**
     * @see Lumine_Cache_Abstract::fetch()
     */
    public function fetch($key){
        $value = apc_fetch($key, $success);
        if(!$success){
            throw new Lumine_Exception('Valor nao encontrado no cache: '.$key);
        }
        
        return $value;
    }

    /**
     * @see Lumine_Cache_Abstract::exists()
     */
    public function exists($key){
        return apc_exists($key);
    }

    /**
     * @see Lumine_Cache_Abstract::delete()
     */
    public function delete($key){
        apc_delete($key);
    }

    /**
     * @see Lumine_Cache_Abstract::clear()
     */
    public function clear(){
        apc_clear_cache('user');
    }
}

