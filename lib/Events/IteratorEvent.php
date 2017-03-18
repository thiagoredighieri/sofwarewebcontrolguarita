<?php

class Lumine_Events_IteratorEvent extends Lumine_Event {
	
	/**
	 * Objeto alvo
	 * @var Lumine_Base
	 */
	public $obj;
	
	/**
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $type
	 * @param Lumine_Base $obj
	 * @return Lumine_Events_IteratorEvent
	 */
	function __construct($type, Lumine_Base $obj){
		$this->type = $type;
		$this->obj = $obj;
	}
	
}

