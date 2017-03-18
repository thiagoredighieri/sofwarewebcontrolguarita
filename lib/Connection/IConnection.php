<?php
/**
 * @package Lumine_Connection
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

Lumine::load('Events_ConnectionEvent');

/**
 * Interface para conexoes
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Connection
 */
interface Lumine_Connection_IConnection extends Lumine_IEventListener 
{
	/**
	 * Abre a conexao com o banco
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function connect();
	/**
	 * Fecha a conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function close();
	
	/**
	 * Recupera o estado atual
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return int
	 */
	function getState();
	
	/**
	 * Altera o nome do banco de dados da conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $database
	 * @return void
	 */
	function setDatabase($database);
	/**
	 * Recuera o nome do banco de dados da conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getDatabase();
	
	/**
	 * Altera o usuario de conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $user
	 * @return void
	 */
	function setUser($user);
	/**
	 * Recupera o usuario de conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getUser();

	/**
	 * Altera a senha de conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $password
	 * @return void
	 */
	function setPassword($password);
	/**
	 * Recupera a senha de conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getPassword();

	/**
	 * Altera a porta de conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $port
	 * @return void
	 */
	function setPort($port);
	/**
	 * Recupera a porta de conexao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getPort();
	
	/**
	 * Altera o nome do host
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $host
	 * @return void
	 */
	function setHost($host);
	
	/**
	 * Recupera o nome do host
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getHost();
	
	/**
	 * Altera as opcoes
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $options
	 * @return void
	 */
	function setOptions($options);
	
	/**
	 * Recupera as opcoes
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return array
	 */
	function getOptions();
	
	/**
	 * Altera uma opcao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name nome da opcao
	 * @param mixed  $val  novo valor da opcao
	 * @return void
	 */
	function setOption($name, $val);
	
	/**
	 * Recupera uma opcao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name Nome da opcao
	 * @return string
	 */
	function getOption($name);
	
	/**
	 * Recupera um mensagem de erro
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getErrorMsg();
	/**
	 * Recupera as tabelas do banco de dados
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return array
	 */
	function getTables();
	/**
	 * Recupera as chaves estrangeiras de uma tabela
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $tablename nome da tabela
	 * @return array
	 */
	function getForeignKeys($tablename);
	/**
	 * Recupera informacoes do servidor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $type
	 * @return string
	 */
	function getServerInfo($type = null);
	/**
	 * Traz os detalhas em relacao a uma tabela
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $tablename nome da tabela
	 * @return array
	 */
	function describe($tablename);
	
	/**
	 * Executa uma sql no banco de dados
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $sql SQL ser executada
	 * @return resource o Recordset da consulta
	 */
	function executeSQL($sql);
	/**
	 * Altera o limite na consulta
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int $offset
	 * @param int $limit
	 * @return void
	 */
	function setLimit($offset = null, $limit = null);
	/**
	 * Escapa uma string para ser armazenada em banco
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $str
	 * @return string
	 */
	function escape($str);
	/**
	 * Escapa o valor de um objeto para ser armazenado em banco
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $blob
	 * @return string
	 */
	function escapeBlob($blob);
	/**
	 * Retorna o numero de linhas afetadas
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return int
	 */
	function affected_rows();
	/**
	 * Recupera o numero de linhas encontradas
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param resource $rs
	 * @return string
	 */
	function num_rows($rs);
	/**
	 * retorna o nome da funcao que traz resultados aleatoriamente
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function random();
	/**
	 * inicia uma transacao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $transactionID
	 * @return void
	 */
	function begin($transactionID=null);
	/**
	 * persiste uma transacao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $transactionID
	 * @return void
	 */
	function commit($transactionID=null);
	/**
	 * faz um o rollback em uma trasacao
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $transactionID
	 * @return void
	 */
	function rollback($transactionID=null);
	
	/**
	 * recupera o caractere de escape
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getEscapeChar();
	
	/**
	 * recupera o charset utilizado
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return string
	 */
	function getCharset();
	
	/**
	 * altera o charset utilizado
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $charset 
	 * @return void
	 */
	function setCharset($charset);
	
	/**
	 * Retorna o valor do banco formatado para o PHP
	 * 
	 * @param mixed $value Valor do banco
	 * @param array $field Dados do campo
	 * @author Hugo Ferreira da Silva
	 * @return mixed
	 */
	function toPHPValue($value, $field);
	
	/**
	 * Retorna o valor do PHP formatado para o banco
	 * 
	 * @param mixed $value Valor do banco
	 * @param array $field Dados do campo
	 * @author Hugo Ferreira da Silva
	 * @return mixed
	 */
	function toDatabaseValue($value, $field);
	
}




