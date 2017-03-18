<?php
/**
 * Conexao com MySQL
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Connection
 */

Lumine::load('Connection_AbstractConnection');

/**
 * Conexao com MySQL
 * @package Lumine_Connection
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Connection_MySQL extends Lumine_Connection_AbstractConnection 
{
	/**
	 * Construtor
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return Lumine_Connection_MySQL
	 */
	public function __construct(){
		$this->randomFunction = 'RAND()';
	}
	
	/**
	 * @see Lumine_Connection_IConnection::connect()
	 */
	public function connect()
	{
		if($this->conn_id && $this->state == self::OPEN)
		{
			Lumine_Log::debug( 'Utilizando conexao cacheada com '.$this->getDatabase());
			mysql_select_db($this->getDatabase(), $this->conn_id);
			return true;
		}

		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::PRE_CONNECT, $this));
		
		$hostString = $this->getHost();
		if($this->getPort() != '') 
		{
			$hostString .=  ':' . $this->getPort();
		}
		if(isset($this->options['socket']) && $this->options['socket'] != '')
		{
			$hostString .= ':' . $this->options['socket'];
		}
		$flags = isset($this->options['flags']) ? $this->options['flags'] : null;
					
		if(isset($this->options['persistent']) && $this->options['persistent'] == true)
		{
			Lumine_Log::debug( 'Criando conexao persistente com '.$this->getDatabase());
			$this->conn_id = @mysql_pconnect($hostString, $this->getUser(), $this->getPassword(), $flags);
		} else {
			Lumine_Log::debug( 'Criando conexao com '.$this->getDatabase());
			$this->conn_id = @mysql_connect($hostString, $this->getUser(), $this->getPassword(), true, $flags);
		}
		
		if( !$this->conn_id )
		{
			$this->state = self::CLOSED;
			$msg = 'nao foi possivel conectar no banco de dados: ' . $this->getDatabase().' - '.$this->getErrorMsg();
			Lumine_Log::error( $msg );
			
			$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::CONNECTION_ERROR, $this, $msg));
			throw new Exception( $msg );
			
			return false;
		}
		
		// seleciona o banco
		mysql_select_db($this->getDatabase(), $this->conn_id);
		$this->state = self::OPEN;
		
		$this->setCharset($this->getCharset());
		
		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::POS_CONNECT, $this));
		
		return true;
	}
	/**
	 * @see Lumine_Connection_IConnection::close()
	 */
	public function close()
	{
		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::PRE_CLOSE, $this));
		if($this->conn_id && $this->state != self::CLOSED)
		{
			Lumine_Log::debug( 'Liberando resultados todos os resultados' );
			Lumine_Dialect_Factory::getByName('MySQL')->freeAllResults();
			
			$this->state = self::CLOSED;
			
			Lumine_Log::debug( 'Fechando conexao com '.$this->getDatabase());
			mysql_close($this->conn_id);
		}
		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::POS_CLOSE, $this));
	}
	/**
	 * @see Lumine_Connection_IConnection::getErrorMsg()
	 */
	public function getErrorMsg()
	{
		$msg = '';
		if($this->conn_id) 
		{
			$msg = mysql_error($this->conn_id);
		} else {
			$msg = mysql_error();
		}
		return $msg;
	}
	/**
	 * @see Lumine_Connection_IConnection::getTables()
	 */
	public function getTables()
	{
		if( ! $this->connect() )
		{
			return false;
		}
		
		$rs = $this->executeSQL("show tables");
		
		$list = array();
		
		while($row = mysql_fetch_row($rs))
		{
			$list[] = $row[0];
		}
		return $list;
	}
	/**
	 * @see Lumine_Connection_IConnection::getForeignKeys()
	 */
	public function getForeignKeys($tablename)
	{
		if( ! $this->connect() )
		{
			return false;
		}
		
		$fks = array();
		$rs = $this->executeSQL("SHOW CREATE TABLE ".$tablename);
		
		$result = mysql_fetch_row($rs);
		$result[0] = preg_replace("(\r|\n)",'\n', $result[0]);
		$matches = array();

		preg_match_all('@FOREIGN KEY \(`([a-z,A-Z,0-9,_]+)`\) REFERENCES `([a-z,A-Z,0-9,_]+)` \(`([a-z,A-Z,0-9,_]+)`\)(.*?)(\r|\n|\,)@i', $result[1], $matches);
		
		for($i=0; $i<count($matches[0]); $i++)
		{
			$name = $matches[2][$i];
			if(isset($fks[ $name ]))
			{
				$name = $matches[1][$i] . '_' . $name . '_' . $matches[3][$i];
			}
			
			$fks[ $name ]['from'] = $matches[1][$i];
			$fks[ $name ]['to'] = $matches[2][$i];
			$fks[ $name ]['to_column'] = $matches[3][$i];
			
			$reg = array();
			if(preg_match('@(.*?)ON UPDATE (RESTRICT|CASCADE)@i', $matches[4][$i], $reg))
			{
				$fks[ $name ]['update'] = strtoupper($reg[2]);
			} else {
				$fks[ $name ]['update'] = 'RESTRICT';
			}
			if(preg_match('@(.*?)ON DELETE (RESTRICT|CASCADE)@i', $matches[4][$i], $reg))
			{
				$fks[ $name ]['delete'] = strtoupper($reg[2]);
			} else {
				$fks[ $name ]['delete'] = 'RESTRICT';
			}
			
		}
		
		return $fks;
	}
	/**
	 * @see Lumine_Connection_IConnection::getServerInfo()
	 */
	public function getServerInfo($type = null)
	{
		if($this->conn_id && $this->state == self::OPEN)
		{
			switch($type)
			{
				case self::CLIENT_VERSION:
					return mysql_get_client_info();
					break;
				case self::HOST_INFO:
					return mysql_get_host_info($this->conn_id);
					break;
				case self::PROTOCOL_VERSION:
					return mysql_get_proto_info($this->conn_id);
					break;
				case self::SERVER_VERSION:
				default:
					return mysql_get_server_info($this->conn_id);
					break;
			}
			return '';
			
		} 
		throw new Lumine_Exception('A conexao nao esta aberta', Lumine_Exception::WARNING);
	}
	/**
	 * @see Lumine_Connection_IConnection::describe()
	 */
	public function describe($tablename)
	{
		$sql = "DESCRIBE ". $tablename;
		$rs = $this->executeSQL( $sql );
		
		$data = array();
		while($row = mysql_fetch_row($rs))
		{
			$name           = $row[0];
			$type_native    = $row[1];
			if(preg_match('@(\w+)\((\d+)\)@', $row[1], $r))
			{
				$type       = $r[1];
				$length     = $r[2];
			} else {
				$type       = $row[1];
				$length     = null;
			}
			
			switch( strtolower($type) )
			{
				case 'tinyblob': $length = 255; break;
				case 'tinytext': $length = 255; break;
				case 'blob': $length = 65535; break;
				case 'text': $length = 65535; break;
				case 'mediumblob': $length = 16777215; break;
				case 'mediumtext': $length = 16777215; break;
				case 'longblob': $length = 4294967295; break;
				case 'longtext': $length = 4294967295; break;
				case 'enum': $length = 65535; break;
			}
			
			$notnull        = $row[2] == 'YES' ? false : true;
			$primary        = $row[3] == 'PRI' ? true : false;
			$default        = $row[4] == 'NULL' ? null : $this->parseDefaultValue( $row[4] );
			$autoincrement  = $row[5] == 'auto_increment' ? true : false;
			
			$data[] = array($name, $type_native, $type, $length, $primary, $notnull, $default, $autoincrement, array());
		}
		return $data;
	}
	
	/**
	 * Verifica se o valor informado como default e uma funcao do banco
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param mixed $value
	 * @return string
	 */
	private function parseDefaultValue( $value ){
		$types = array(
			'CURRENT_TIME'
			,'CURRENT_DATE'
			,'CURRENT_TIMESTAMP'
			,'NOW()'
			,'CURRENT_USER'
			,'LOCALTIME'
			,'LOCALTIMESTAMP'
		);
		
		if( !is_array($value) && in_array($value, $types) ){
			$value = Lumine::DEFAULT_VALUE_FUNCTION_IDENTIFIER . $value;
		}
		
		return $value;
	}
	
	/**
	 * @see Lumine_Connection_IConnection::executeSQL()
	 */
	public function executeSQL($sql)
	{
		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::PRE_EXECUTE, $this, '', $sql));
		$this->connect();
		$rs = @mysql_query($sql, $this->conn_id);
		
		if( ! $rs )
		{
			$msg = $this->getErrorMsg();
			$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::EXECUTE_ERROR, $this, $msg, $sql));
			throw new Lumine_Exception("Falha na consulta: " . $msg, Lumine_Exception::QUERY_ERROR);
		}
		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::POS_EXECUTE, $this, '', $sql));
		return $rs;
	}
	/**
	 * @see Lumine_Connection_IConnection::setLimit()
	 */
	public function setLimit($offset = null, $limit = null) 
	{
		if($offset == null && $limit == null)
		{
			return;
		} else if($offset == null && $limit != null) {
			return sprintf("LIMIT %d", $limit);
		} else if($offset != null && $limit == null) {
			return sprintf("LIMIT %d", $offset);
		} else {
			return sprintf("LIMIT %d, %d", $offset, $limit);
		}
	}
	/**
	 * @see Lumine_Connection_IConnection::escape()
	 */
	public function escape($str) 
	{
		if($this->state == self::OPEN)
		{
			return mysql_real_escape_string($str, $this->conn_id);
		} else {
			return mysql_escape_string($str);
		}
	}
	/**
	 * @see Lumine_Connection_IConnection::escapeBlob()
	 */
	public function escapeBlob($blob)
	{
		return $this->escape( $blob );
	}
	/**
	 * @see Lumine_Connection_IConnection::affected_rows()
	 */	
	public function affected_rows()
	{
		if($this->state == self::OPEN)
		{
			return mysql_affected_rows($this->conn_id);
		}
		throw new Lumine_Exception('Conexao nao esta aberta', Lumine_Exception::ERRO);
	}
	/**
	 * @see Lumine_Connection_IConnection::num_rows()
	 */
	public function num_rows($rs)
	{
		return mysql_num_rows($rs);
	}
	
	/**
	 * @see Lumine_Connection_IConnection::begin()
	 */
	public function begin($transactionID=null)
	{
		$this->executeSQL("BEGIN");
	}
	/**
	 * @see Lumine_Connection_IConnection::commit()
	 */
	public function commit($transactionID=null)
	{
		$this->executeSQL("COMMIT");
	}
	/**
	 * @see Lumine_Connection_IConnection::rollback()
	 */
	public function rollback($transactionID=null)
	{
		$this->executeSQL("ROLLBACK");
	}
	
	/**
	 * @see Lumine_Connection_AbstractConnection::setCharset()
	 */
	public function setCharset($charset){
		if( $this->conn_id && !empty($charset) ){
			$this->executeSQL( 'SET NAMES "' . $this->escape($charset) . '"' );
		}
		
		parent::setCharset($charset);
	}
}


?>