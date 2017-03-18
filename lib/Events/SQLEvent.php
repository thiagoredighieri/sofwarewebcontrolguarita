<?php

class Lumine_Events_SQLEvent extends Lumine_Event {
	
	/**
	 * Objeto alvo
	 * @var Lumine_Base
	 */
	public $obj;
	
	/**
	 * SQL usada no evento
	 * @var string
	 */
	public $sql;
	
	/**
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $type
	 * @param Lumine_Base $obj
	 * @param string $sql
	 * @return Lumine_Events_SQLEvent
	 */
	function __construct($type, Lumine_Base $obj, $sql = null){
		$this->type = $type;
		$this->obj = $obj;
		$this->sql = $sql;
		
	}
	
}

