<?php
/**
 * Classe para manipulacao dos dados PostgreSQL
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Dialect
 */

Lumine::load('Dialect_Exception');
Lumine::load('Dialect_IDialect');

/**
 * Classe para manipulacao dos dados PostgreSQL
 * @package Lumine_Dialect
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Dialect_PostgreSQL extends Lumine_EventListener implements Lumine_Dialect_IDialect
{
	/**
	 * conexao ativa
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
	 * 
	 * @var array
	 */
	private $datasetList = array();
	/**
	 * 
	 * @var array
	 */
	private $resultList = array();
	/**
	 * 
	 * @var int
	 */
	private $objectID    = 0;
	
	/**
	 * 
	 * @var array
	 */
	private $pointerList = array();
	
	/**
	 * Modo de recuperacao dos nomes das colunas
	 *
	 * @var string
	 */
	private $fetchMode  = '';

	/**
	 * Construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return Lumine_Dialect_IDialect
	 */
	function __construct(Lumine_Base $obj = null)
	{
		$this->obj = $obj;
	}
	/**
	 * @see Lumine_Dialect_IDialect::setConnection()
	 */
	public function setConnection(Lumine_Connection_IConnection $cnn)
	{
		$this->connection = $cnn;
	}

	/**
	 * @see Lumine_Dialect_IDialect::getConnection()
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::getFetchMode()
	 */
	public function getFetchMode()
	{
		return $this->fetchMode;
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
	 * @see Lumine_Dialect_IDialect::setFetchMode()
	 */
	public function setFetchMode( $mode )
	{
		$this->fetchMode = $mode;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::getTablename()
	 */
	public function getTablename()
	{
	    return $this->tablename;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::setTablename()
	 */
	public function setTablename( $tablename )
	{
	    $this->tablename = $tablename;
	}

	/**
	 * @see Lumine_Dialect_IDialect::execute()
	 */
	public function execute($sql)
	{
		$cn = $this->getConnection();
		if( $cn == null )
		{
			throw new Lumine_Dialect_Exception('conexao nao setada');
		}

		$cn->connect();		
		$this->setConnection($cn);
		
		try
		{
			
			
			Lumine_Log::debug( 'Executando consulta: ' . $sql);
			$rs = $cn->executeSQL($sql);
			
			$mode = $this->getFetchMode();
			$native_mode = null;
			switch($mode)
			{
				case Lumine_Base::FETCH_ROW:
					$native_mode = PGSQL_NUM;
				break;
				
				case Lumine_Base::FETCH_BOTH:
					$native_mode = PGSQL_BOTH;
				break;
				
				case Lumine_Base::FETCH_ASSOC:
				default:
					$native_mode = PGSQL_ASSOC;
			}
			
			
			if( gettype($rs) != 'boolean')
			{
				// limpa o resultado anterior
				$this->freeResult($this->getObjectId());
				
				$this->resultList[$this->getObjectId()] = $rs;
				$this->setDataset(array());
				
				/*
				if($this->getConnection()->num_rows($this->result_set) > 0)
				{
					while($row = pg_fetch_array($this->result_set, null, $native_mode))
					{
						$this->dataset[] = $row;
					}
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
	 * @see Lumine_Dialect_IDialect::affected_rows()
	 */
	public function affected_rows()
	{
		$cn = $this->getConnection();
		if( empty($cn) )
		{
			throw new Lumine_Dialect_Exception('conexao nao setada');
		}
		return $cn->affected_rows();
	}
	
	/**
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
	 * @see Lumine_Dialect_IDialect::moveFirst()
	 */
	public function moveFirst()
	{
		$this->pointerList[$this->getObjectId()] = 0;
	}
	
	/**
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
	 * @see Lumine_Dialect_IDialect::fetch_row()
	 */
	public function fetch_row($rowNumber)
	{
	    if( $rowNumber < 0 || $rowNumber > $this->num_rows() - 1 )
	    {
	        return false;
	    }
	    
		$this->setPointer($rowNumber);
		$row = pg_fetch_assoc($this->resultList[$this->getObjectId()], $rowNumber);
		
		$this->setDataset($row);
		
		return $row;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::fetch()
	 */
	public function fetch()
	{
	    if( $this->pointerList[$this->getObjectId()] < 0 || $this->pointerList[$this->getObjectId()] > $this->num_rows() - 1 )
	    {
	        Lumine_Log::debug( 'Nenhum resultado para o cursor '.$this->pointerList[$this->getObjectId()]);
	        $this->moveFirst();
	        return false;
	    }
	    
	    
		Lumine_Log::debug( 'Retornando linha: '.$this->pointerList[$this->getObjectId()]);
		
		$row = pg_fetch_assoc($this->resultList[$this->getObjectId()], $this->pointerList[$this->getObjectId()]);
		$this->pointerList[$this->getObjectId()]++;
		
		$this->setDataset($row);

		return $row;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::getErrorMsg()
	 */
	public function getErrorMsg()
	{
		if($this->getConnection() == null)
		{
			throw new Lumine_Dialect_Exception('conexao nao setada');
		}
		return $this->getConnection()->getErrorMsg();
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::getDataset()
	 */
	public function getDataset()
	{
		$data = empty($this->datasetList[$this->getObjectId()]) ? array() : $this->datasetList[$this->getObjectId()];
		return $data;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::setDataset()
	 */
	public function setDataset(array $dataset)
	{
		$this->datasetList[$this->getObjectId()] = $dataset;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::getPointer()
	 */
	public function getPointer()
	{
		return $this->pointerList[$this->getObjectId()];
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::setPointer()
	 */
	public function setPointer($pointer)
	{
		$this->pointerList[$this->getObjectId()] = $pointer;
	}
	
	/**
	 * @see Lumine_Dialect_IDialect::getLumineType()
	 */
	public function getLumineType($nativeType)
	{
		// inteiros
		if(preg_match('@^(int|integer|longint|mediumint)$@i', $nativeType))
		{
			return 'int';
		}
		// textos longos
		if(preg_match('@^(text|mediumtext|tinytext|longtext|enum)$@i', $nativeType))
		{
			return 'text';
		}
		// booleanos
		if(preg_match('@^(tinyint|boolean|bool)$@i', $nativeType))
		{
			return 'boolean';
		}
		// datas
		if(preg_match('@^timestamp@i', $nativeType))
		{
			return 'datetime';
		}
		return $nativeType;
	}
	
	/**
	 * Retorna o ultimo ID da tabela para campos auto-increment
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $campo Nome do campo da tabela de auto-increment
	 * @return int Valor da ultima insercao
	 */
	public function getLastId( $campo ) {
		
		$obj = Lumine::factory($this->getTablename());
		$field = $obj->metadata()->getFieldByColumn($campo);
		$obj->destroy();
		
		// se nao tiver sequence
		if(empty($field['options']['sequence'])){
			
			// verificacao de existencia de esquema no nome da tabela.
			// sugestao de Tiago Hiller - 05/10/2011
			$tablename = $this->getTablename();
			$hasSchema = preg_match('@\b(?P<schema>\w+)\.(?P<tablename>\w+)\b@', $this->getTablename(), $res);
			
			if($hasSchema){
				$tablename = $res['tablename'];
			}
			
			$sql = "SELECT currval( s2.nspname || '.' || t2.relname ) AS id
					FROM pg_depend AS d
					JOIN pg_class AS t1 ON t1.oid = d.refobjid
					JOIN pg_class AS t2 ON t2.oid = d.objid
					JOIN pg_namespace AS s1 ON s1.oid = t1.relnamespace
					JOIN pg_namespace AS s2 ON s2.oid = t2.relnamespace
					JOIN pg_attribute AS a ON a.attrelid = d.refobjid AND a.attnum = d.refobjsubid
					WHERE t1.relkind = 'r'
					AND t2.relkind = 'S'
					AND t1.relname = '".$tablename."'
					AND attname = '".$campo."'";
			
			if($hasSchema){
				$sql .= " AND s2.nspname = '{$res['schema']}'";
			}
			
		} else {
			$sql = "SELECT currval('".$field['options']['sequence']."') as id";
			
		}
		
		$cn = $this->getConnection();
		
		$rs = $cn->executeSQL( $sql );
		
		if(pg_num_rows($rs) > 0)
		{
			$line = pg_fetch_row($rs);
			pg_free_result($rs);
			
			return $line[0];
		}
		
		pg_free_result($rs);
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
			pg_free_result($this->resultList[ $resultID ]);
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
}

