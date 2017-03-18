<?php
/**
 * Classe para dialeto com o banco MySQL
 * @package Lumine_Dialect
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

Lumine::load('Dialect_Exception');
Lumine::load('Dialect_IDialect');

/**
 * Classe para dialeto com o banco MySQL
 * @package Lumine_Dialect
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Dialect_MySQL extends Lumine_EventListener implements Lumine_Dialect_IDialect
{

	/**
	 * Conexao ativa
	 *
	 * @var Lumine_Connection_IConnection
	 */
	private $connection = null;
	
	/**
	 * Resultset atual
	 *
	 * @var resource
	 */
	private $result_set = null;
	
	/**
	 * Objeto que requisitou a "ponte"
	 *
	 * @var Lumine_Base
	 */
	private $obj        = null;
	
	/**
	 * Dataset do registro atual
	 *
	 * @var array
	 */
	private $dataset    = array();
	
	/**
	 * Ponteiro atual
	 *
	 * @var integer
	 */
	private $pointer    = 0;
	
	/**
	 * Modo de recuperacao dos nomes das colunas
	 *
	 * @var string
	 */
	private $fetchMode  = '';

	/**
	 * 
	 * @var array
	 */
	private $resultList = array();

	/**
	 * 
	 * @var array
	 */
	private $pointerList = array();
	
	/**
	 * 
	 * @var int
	 */
	private $objectID;
	
	/**
	 * Construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return Lumine_Dialect_IDialect
	 */
	function __construct(Lumine_Base $obj = null) {
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getObjectId()
	 */
	public function getObjectId(){
		return $this->objectID;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::setObjectId($objectID)
	 */
	public function setObjectId($objectID){
		$this->objectID = $objectID;
	}

	/**
	 * 
	 * @see Lumine_Dialect_IDialect::setConnection()
	 */
	public function setConnection(Lumine_Connection_IConnection $cnn)
	{
		$this->connection = $cnn;
	}

	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getConnection()
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getFetchMode()
	 */
	public function getFetchMode()
	{
		return $this->fetchMode;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::setFetchMode()
	 */
	public function setFetchMode( $mode )
	{
		$this->fetchMode = $mode;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getTablename()
	 */
	public function getTablename()
	{
	    return $this->tablename;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::setTablename()
	 */
	public function setTablename( $tablename )
	{
	    $this->tablename = $tablename;
	}

	/**
	 * 
	 * @see Lumine_Dialect_IDialect::execute()
	 */
	public function execute($sql)
	{
		$cn = $this->getConnection();
		if( $cn == null )
		{
			throw new Lumine_Dialect_Exception('Conexao nao setada');
		}

		$cn->connect();		
		
		try
		{
			Lumine_Log::debug( 'Executando consulta: ' . $sql);
			$rs = $cn->executeSQL($sql);
			
			$mode = $this->getFetchMode();
			$native_mode = null;
			switch($mode)
			{
				case Lumine_Base::FETCH_ROW:
					$native_mode = MYSQL_ROW;
				break;
				
				case Lumine_Base::FETCH_BOTH:
					$native_mode = MYSQL_BOTH;
				break;
				
				case Lumine_Base::FETCH_ASSOC:
				default:
					$native_mode = MYSQL_ASSOC;
			}
			
			
			//$this->pointer = 0;
			
			if( gettype($rs) != 'boolean')
			{
				// limpa o resultado anterior
				$this->freeResult($this->getObjectId());
				
				$this->resultList[$this->getObjectId()] = $rs;
				$this->setDataset( array() );

				/*while($row = mysql_fetch_array($this->result_set, $native_mode))
				{
					$this->dataset[] = $row;
				}*/
				$this->pointerList[$this->getObjectId()] = 0;
				return true;
			} else {
				return $rs;
			}
			
		} catch (Exception $e) {
			Lumine_Log::warning( 'Falha na consulta: ' . $cn->getErrorMsg());
			throw new Lumine_SQLException($cn, $sql, $cn->getErrorMsg());
			return false;
		}
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::num_rows()
	 */
	public function num_rows()
	{
		if( empty($this->resultList[$this->getObjectId()]) )
		{
			Lumine_Log::warning('A consulta deve primeiro ser executada');
			return 0;
		}
		
		return $this->getConnection()->num_rows($this->resultList[$this->getObjectId()]);
	}
	
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::affected_rows()
	 */
	public function affected_rows()
	{
		$cn = $this->getConnection();
		if( empty($cn) )
		{
			throw new Lumine_Dialect_Exception('Conexao nao setada');
		}
		return $cn->affected_rows();
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::moveNext()
	 */
	public function moveNext()
	{
		$this->pointerList[$this->getObjectId()]++;
		if($this->pointerList[$this->getObjectId()] >= $this->num_rows())
		{
			$this->pointerList[$this->getObjectId()] = $this->num_rows() - 1;
		}
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::movePrev()
	 */
	public function movePrev()
	{
		$this->pointerList[$this->getObjectId()]--;
		if($this->pointerList[$this->getObjectId()] < 0)
		{
			$this->pointerList[$this->getObjectId()] = 0;
		}
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::moveFirst()
	 */
	public function moveFirst()
	{
		$this->pointerList[$this->getObjectId()] = 0;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::moveLast()
	 */
	public function moveLast()
	{
		$this->pointerList[$this->getObjectId()] = $this->num_rows() - 1;
		if($this->pointerList[$this->getObjectId()] < 0)
		{
			$this->pointerList[$this->getObjectId()] = 0;
		}
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::fetch_row()
	 */
	public function fetch_row($rowNumber)
	{
	    if( $rowNumber < 0 || $rowNumber > $this->num_rows() - 1 )
	    {
	        return false;
	    }
	    
	    mysql_data_seek($this->resultList[$this->getObjectId()], $rowNumber);
	    
	    /*
		if( empty($this->dataset[ $rowNumber ]))
		{
			return false;
		}
		*/
		$this->setPointer($rowNumber);
		$row = mysql_fetch_assoc($this->resultList[$this->getObjectId()]);
		
		$this->setDataset($row);
		
		return $row;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::fetch()
	 */
	public function fetch()
	{
		$pointer = $this->pointerList[$this->getObjectId()];
	    if( $pointer < 0 || $pointer > $this->num_rows() - 1 )
	    {
	        Lumine_Log::debug( 'Nenhum resultado para o cursor '.$pointer);
	        $this->moveFirst();
	        return false;
	    }
	    
		Lumine_Log::debug( 'Retornando linha: '.$pointer);
		mysql_data_seek( $this->resultList[$this->getObjectId()], $pointer );
		$row = mysql_fetch_assoc($this->resultList[$this->getObjectId()]);
		$this->pointerList[$this->getObjectId()]++;
		
		$this->setDataset($row);

		return $row;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getErrorMsg()
	 */
	public function getErrorMsg()
	{
		if($this->getConnection() == null)
		{
			throw new Lumine_Dialect_Exception('Conexao nao setada');
		}
		return $this->getConnection()->getErrorMsg();
	}

	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getDataset()
	 */
	public function getDataset()
	{
		$dataset = empty($this->datasetList[$this->getObjectId()]) ? array() : $this->datasetList[$this->getObjectId()];
		return $dataset;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::setDataset()
	 */
	public function setDataset(array $dataset)
	{
		$this->datasetList[$this->getObjectId()] = $dataset;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getPointer()
	 */
	public function getPointer()
	{
		return $this->pointerList[$this->getObjectId()];
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::setPointer()
	 */
	public function setPointer($pointer)
	{
		$this->pointerList[$this->getObjectId()] = $pointer;
	}
	
	/**
	 * 
	 * @see Lumine_Dialect_IDialect::getLumineType()
	 */
	public function getLumineType($nativeType)
	{
		preg_match('@^(\w+)\b(.*?)$@', $nativeType, $reg);
		
		// inteiros
		if(preg_match('@^(int|integer|longint|mediumint)$@i', $reg[1]))
		{
			return 'int';
		}
		// textos longos
		if(preg_match('@^(text|mediumtext|tinytext|longtext)$@i', $reg[1]))
		{
			return 'text';
		}
		// booleanos
		if(preg_match('@^(tinyint|boolean|bool)$@i', $reg[1]))
		{
			return 'boolean';
		}
		// datas
		if(preg_match('@^(timestamp)$@i', $reg[1])) {
			return 'datetime';
		}
		
		return $reg[1];
	}
	
	/**
	 * Retorna o ultimo ID da tabela para campos auto-increment
	 * @author Hugo Ferreira da Silva
	 * @param string $campo Nome do campo da tabela de auto-increment
	 * @return int Valor da ultima insercao
	 */
	public function getLastId( $campo )
	{
		$cn = $this->getConnection();
		$rs = $cn->executeSQL("select last_insert_id() as id");
		if(mysql_num_rows($rs) > 0)
		{
			$ultimo_id = mysql_result($rs, 0, 0);
			mysql_free_result($rs);
			
			return $ultimo_id;
		}
		
		mysql_free_result($rs);
		return 0;
	}
	
	/**
	 * Limpa o resultado armazenado para o objeto atual
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return void
	 */
	public function freeResult($resultID){
		if( isset($this->resultList[ $resultID ]) && is_resource($this->resultList[ $resultID ]) ){
			Lumine_Log::debug('Liberando o registro #' . $resultID);
			mysql_free_result($this->resultList[ $resultID ]);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see lib/Dialect/Lumine_Dialect_IDialect#freeAllResults()
	 */
	public function freeAllResults(){
		foreach($this->resultList as $id => $result) {
			$this->freeResult($id);
		}
	}
	
	/**
	 * 
	 * @see Lumine_EventListener::__destruct()
	 */
	function __destruct()
	{
		$this->connection = null;
		$this->result_set = null;
		$this->obj = null;
		$this->dataset    = array();
		$this->pointer    = 0;
		
		parent::__destruct();
	}
}

