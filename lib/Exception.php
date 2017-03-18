<?php
/**
 * Classe de Excecao
 *  
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Classe de Excecao
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Exception extends Exception
{
	/**
	 * constante para nivel de log
	 * @var int
	 */
	const LOG                   = 0;
	/**
	 * constante para nivel de erro
	 * @var int
	 */
	const ERROR                 = 1;
	/**
	 * constante para nivel de aviso
	 * @var int
	 */
	const WARNING               = 2;

	/**
	 * sem dialeto
	 * @var int
	 */
	const CONFIG_NO_DIALECT     = 10;
	/**
	 * sem banco de dados
	 * @var int
	 */
	const CONFIG_NO_DATABASE    = 11;
	/**
	 * sem usuario / usuario incorreto
	 * @var int
	 */
	const CONFIG_NO_USER        = 12;
	/**
	 * sem class-path definida
	 * @var int
	 */
	const CONFIG_NO_CLASSPATH   = 13;
	/**
	 * sem pacote definido
	 * @var int
	 */
	const CONFIG_NO_PACKAGE     = 14;
	
	/**
	 * erro de SQL
	 * @var int
	 */
	const QUERY_ERROR           = 20;
	
	/**
	 * metodo nao encontrado
	 * @var int
	 */
	const NO_SUCH_METHOD        = 21;
	
	/**
	 * Construtor da excecao
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $msg   Mensagem a ser disparada
	 * @param int    $code  Codigo de erro
	 * @return Lumine_Exception
	 */
	function __construct($msg, $code)
	{
		$debug = debug_backtrace();
		$bt = array_shift($debug);
		
		$file = $bt['file'];
		$line = $bt['line'];
		Lumine_log::log($code, $msg, $file, $line);
		parent::__construct($msg, $code);
	}
}


?>