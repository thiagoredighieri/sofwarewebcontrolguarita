<?php
/**
 * Interface para dialetos
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Dialect
 */

/**
 * Interface para dialetos
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Dialect
 */
interface Lumine_Dialect_IDialect
{
	/**
	 * Assinatura do construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return Lumine_Dialect_IDialect
	 */
	function __construct(Lumine_Base $obj = null);
	/**
	 * Altera a conexao
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Connection_IConnection $cnn
	 * @return void
	 */
	function setConnection(Lumine_Connection_IConnection $cnn);
	/**
	 * Recupera a conexao
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return Lumine_Connection_IConnection
	 */
	function getConnection();
	/**
	 * Altera o tipo de retorno dos dados
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int $mode
	 * @return void
	 */
	function setFetchMode($mode);
	/**
	 * Recupera o tipo de retorno dos dados
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return int
	 */
	function getFetchMode();
	/**
	 * Altera o nome da tabela
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $tablename
	 * @return void
	 */
	function setTablename($tablename);
	/**
	 * Recupera o nome da tabela
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getTablename();
	
	/**
	 * Altera o id do objeto
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param int $objectID
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function setObjectId($objectID);
	/**
	 * Recupera o ID do objeto
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return int
	 */
	function getObjectId();
	
	/**
	 * Executa uma SQL
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $sql
	 * @throws Lumine_SQLException Excecao em caso de erro
	 * @return resource|boolean
	 */
	function execute($sql);
	/**
	 * Recupera o numero de linhas encontradas
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return int
	 */
	function num_rows();
	/**
	 * Recupera o numero de linha afetadas
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return int
	 */
	function affected_rows();
	/**
	 * Move para a proxima linha
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function moveNext();
	/**
	 * Move para a linha anterior
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function movePrev();
	/**
	 * Move para a primeira anterior
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function moveFirst();
	/**
	 * Move para a ultima linha
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function moveLast();
	/**
	 * Recupera uma determinada linha
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int $rowNumber
	 * @return array|boolean false se nao tiver mais resultados ou um array contendo os dados
	 */
	function fetch_row($rowNumber);
	/**
	 * Passa para o proximo registro
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return array|boolean false se nao tiver mais resultados ou um array contendo os dados
	 */
	function fetch();
	/**
	 * Recupera a mensagem de erro
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	function getErrorMsg();
	
	/**
	 * Recupera o dataset
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return array
	 */
	function getDataset();
	/**
	 * Altera o dataset
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $dataset
	 * @return void
	 */
	function setDataset(array $dataset);
	
	/**
	 * Recupera a posicao do ponteiro
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return int
	 */
	function getPointer();
	/**
	 * altera a posicao do ponteiro
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int $pointer
	 * @return void
	 */
	function setPointer($pointer);
	
	/**
	 * Recupera o tipo do campo para lumine conforme o tipo nativo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $nativeType tipo do campo nativo no banco
	 * @return string tipo no lumine
	 */
	function getLumineType($nativeType);
	
	/**
	 * Libera um resultado
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int $resultID codigo do resultado
	 * @return void
	 */
	function freeResult($resultID);
	
	/**
	 * Libera todos os resultados que estao no dialeto 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return void
	 */
	function freeAllResults();
}

