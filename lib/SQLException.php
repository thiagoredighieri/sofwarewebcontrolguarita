<?php
/**
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Classe de excecao para erros de consulta (SQL)
 * 
 * @package Lumine 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_SQLException extends Lumine_Exception {
	
	/**
	 * SQL executada
	 * @var string
	 */
	public $sql;
	
	/**
	 * Conexao utilizada
	 * @var Lumine_Connection
	 */
	public $connection;
	
	
	/**
	 * Excecao para erros de SQL
	 *
	 * @param Lumine_Connection $conn Conexao usada
	 * @param string $sql Comando SQL executada
	 * @param string $msg Mensagem de erro retornada
	 */
	function __construct($conn, $sql, $msg) {
		
		$this->sql = $sql;
		$this->connection = $conn;
		$this->message = $msg;
		
		parent::__construct($this->message, Lumine_Exception::QUERY_ERROR);
	}
	
}

?>