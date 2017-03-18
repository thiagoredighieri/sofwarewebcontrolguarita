<?php

class Lumine_Events_ConfigurationEvent extends Lumine_Event {
	
	/**
	 * Objeto alvo
	 * @var Lumine_Base
	 */
	public $obj;
	/**
	 * Objeto de configuracao
	 * @var Lumine_Configuration
	 */
	public $conf;
	
	/**
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $type Tipo de comando
	 * @param Lumine_Configuration $conf Configuracao usada
	 * @param Lumine_Base $obj Objeto alvo
	 * @return Lumine_Events_ConfigurationEvent
	 */
	function __construct($type, Lumine_Configuration $conf, Lumine_Base $obj = null){
		$this->type = $type;
		$this->conf = $conf;
		$this->obj = $obj;
		
	}
	
}

