<?php

class Lumine_Events_ConnectionEvent extends Lumine_Event {
	
	/**
	 * Objeto de conexao
	 * @var Lumine_Connection_IConnection
	 */
	public $connection;
	/**
	 * mensagem de erro
	 * @var string
	 */
	public $msg;
	/**
	 * sql executada
	 * @var string
	 */
	public $sql;
	
	/**
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $type
	 * @param Lumine_Connection_IConnection $connection
	 * @param string $msg Mensagem
	 * @param string $sql Comando SQL
	 * @return Lumine_Events_ConnectionEvent
	 */
	function __construct($type, Lumine_Connection_IConnection $connection, $msg = null, $sql = ''){
		$this->type = $type;
		$this->connection = $connection;
		$this->msg = $msg;
		$this->sql = $sql;
		
	}
	
}

