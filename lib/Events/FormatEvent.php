<?php

class Lumine_Events_FormatEvent extends Lumine_Event {
	
	/**
	 * Objeto de base
	 * @var Lumine_Base
	 */
	public $obj;
	/**
	 * Valor antigo
	 * @var mixed
	 */
	public $oldValue;
	/**
	 * Valor novo
	 * @var mixed
	 */
	public $newValue;
	/**
	 * nome do campo
	 * @var string
	 */
	public $field;
	
	/**
	 * Evento disparado ao formatar um valor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $type nome do evento
	 * @param Lumine_Base $obj Objeto alvo
	 * @param mixed $oldValue 
	 * @param mixed $newValue
	 * @return Lumine_Events_FormatEvent
	 */
	function __construct($type, Lumine_Base $obj, $field, $oldValue = null, $newValue = null){
		$this->type = $type;
		$this->obj = $obj;
		$this->field = $field;
		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
		
	}
	
}

