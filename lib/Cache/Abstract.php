<?php

abstract class Lumine_Cache_Abstract {
    /**
     * Armazena um valor no cache
     * 
     * @param string $key Nome da chave
     * @param mixed $value Valor a ser armazenado
     * @return void
     */
    abstract public function store($key, $value);
    /**
     * Recupera um valor do cache
     * 
     * @param string $key Nome da chave
     * @return mixed
     */
    abstract public function fetch($key);
    /**
     * Verifica se um valor esta no cache
     * @param string $key nome da chave
     * @return boolean
     */
    abstract public function exists($key);
    /**
     * Remove um valor do cache pela chave
     * @param string $key nome da chave
     * @return void
     */
    abstract public function delete($key);
    /**
     * Limpa o cache
     * @return void
     */
    abstract public function clear();
    
}