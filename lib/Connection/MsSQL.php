<?php
/**
 * Conexao com MsSQL
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Connection
 */

Lumine::load('Connection_AbstractConnection');

/**
 * Conexao com MsSQL
 * @package Lumine_Connection
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Connection_MsSQL extends Lumine_Connection_AbstractConnection
{
	
	/**
	 * Construtor
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return Lumine_Connection_MsSQL
	 */
	public function __construct(){
		$this->randomFunction = 'NEWID()';
		$this->escapeChar = '\'';
	}
	
	/**
	 * @see Lumine_Connection_IConnection::connect()
	 */
	public function connect()
	{
		if($this->conn_id && $this->state == self::OPEN)
		{
			Lumine_Log::debug( 'Utilizando conexao cacheada com '.$this->getDatabase());
			mssql_select_db($this->getDatabase(), $this->conn_id);
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
			$this->conn_id = @mssql_pconnect($hostString, $this->getUser(), $this->getPassword(), $flags);
		} else {
			Lumine_Log::debug( 'Criando conexao com '.$this->getDatabase());
			$this->conn_id = @mssql_connect($hostString, $this->getUser(), $this->getPassword(), true);
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
		mssql_select_db($this->getDatabase(), $this->conn_id);
		$this->state = self::OPEN;
		
		$this->setCharset( $this->getCharset() );

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
			Lumine_Dialect_Factory::getByName('MsSQL')->freeAllResults();
				
			$this->state = self::CLOSED;
				
			Lumine_Log::debug( 'Fechando conexao com '.$this->getDatabase());
			mssql_close($this->conn_id);
		}
		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::POS_CLOSE, $this));
	}
	
	/**
	 * @see Lumine_Connection_IConnection::getErrorMsg()
	 */
	public function getErrorMsg()
	{
		$msg = mssql_get_last_message();

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

		$rs = $this->executeSQL("select name from ".$this->getDatabase()."..sysobjects where xtype = 'U'");

		$list = array();

		while($row = mssql_fetch_row($rs))
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

		$sql= 'SELECT
				CAST(OBJECT_NAME(f.parent_object_id) AS VARCHAR) AS source_table,
				CAST(COL_NAME(fc.parent_object_id,fc.parent_column_id) AS VARCHAR) AS source_column,
				CAST(OBJECT_NAME (f.referenced_object_id) AS VARCHAR) AS target_table,
				CAST(COL_NAME(fc.referenced_object_id,fc.referenced_column_id) AS VARCHAR) AS target_column,
				CAST(f.delete_referential_action_desc AS VARCHAR) as on_delete,
				CAST(f.update_referential_action_desc AS VARCHAR) as on_update
			FROM sys.foreign_keys AS f
			INNER JOIN sys.foreign_key_columns AS fc ON f.OBJECT_ID = fc.constraint_object_id
			WHERE OBJECT_NAME(f.parent_object_id) = \''.$tablename.'\'';

		$fks = array();
		$rs = $this->executeSQL($sql);

		while( $row = mssql_fetch_assoc($rs) ){
			$fks[ $row['source_table'] ]['from']      = $row['source_column'];
			$fks[ $row['source_table'] ]['to']        = $row['target_table'];
			$fks[ $row['source_table'] ]['to_column'] = $row['target_column'];
			$fks[ $row['source_table'] ]['update']    = $row['on_update'];
			$fks[ $row['source_table'] ]['delete']    = $row['on_delete'];
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
			// TODO
			return '';
				
		}
		throw new Lumine_Exception('A conexao nao esta aberta', Lumine_Exception::WARNING);
	}
	/**
	 * @see Lumine_Connection_IConnection::describe()
	 */
	public function describe($tablename)
	{
		$sql = 'SELECT
					c.column_id,
					CAST(c.name as TEXT) name,  
					CAST(d.definition as TEXT) "default",
					c.is_identity as autoincrement, 
					kc.name as pk,
					c.is_nullable,
					case c.precision when 0 then c.max_length else c.precision end as length,
					CAST(c.collation_name as TEXT) collation_name,
					CAST(st.name  as TEXT) as "type"
					
				FROM sys.columns c
				join sys.sysobjects o on c.object_id = o.id
				join sys.systypes st on st.xtype = c.system_type_id
				left join sys.default_constraints d on d.object_id = c.default_object_id
				left join sys.index_columns idx 
					on idx.column_id = c.column_id and idx.object_id = o.id
				left join sys.key_constraints kc
					on kc.parent_object_id = o.id and kc.unique_index_id = idx.index_id
				where o.name = \''.$tablename.'\'
					order by c.column_id';

		$rs = $this->executeSQL( $sql );

		$data = array();
		while($row = mssql_fetch_assoc($rs))
		{
			$name           = $row['name'];
			$type           = $row['type'];
			$length         = $row['length'];
			$notnull        = $row['is_nullable'] == 1 ? false : true;
			$primary        = !is_null($row['pk']) ? true : false;
			$default        = $this->parseDefaultValue( $row['default'] );
			$autoincrement  = $row['autoincrement'] == 1 ? true : false;
			$type_native    = $type;
				
			$find = array("('","')","'",'"');
			$default = str_replace($find,'',$default);
				
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

		if( preg_match('@\s*(LIMIT.+?)$@i', $sql, $reg))
		{
			$sql = str_replace($reg[1], '', $sql);

			preg_match('@LIMIT\s*(\d+)\s*(OFFSET\s*(\d+)?)?@i', $reg[1], $f);
			
			$limit = $f[1];
			$offset = isset($f[3]) ? $f[3] : 0;

			$sql = $this->modifyLimitQuery($sql, $limit, $offset);

			Lumine_Log::debug('Consulta transformada para MsSQL: ' . $sql);
		}

		$rs = @mssql_query($sql, $this->conn_id);

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
	public function escape($str) {
		$str = stripslashes($str);
		$str = str_replace("'","''",$str);
		return $str;
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
			return mssql_rows_affected($this->conn_id);
		}
		throw new Lumine_Exception('Conexao nao esta aberta', Lumine_Exception::ERRO);
	}
	/**
	 * @see Lumine_Connection_IConnection::num_rows()
	 */
	public function num_rows($rs)
	{
		return mssql_num_rows($rs);
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
	 * @see Lumine_EventListener::__destruct()
	 */


	/**
	 * Adds an adapter-specific LIMIT clause to the SELECT statement.
	 * [ original code borrowed from Zend Framework ]
	 *
	 * License available at: http://framework.zend.com/license
	 *
	 * Copyright (c) 2005-2008, Zend Technologies USA, Inc.
	 * All rights reserved.
	 *
	 * Redistribution and use in source and binary forms, with or without modification,
	 * are permitted provided that the following conditions are met:
	 *
	 * * Redistributions of source code must retain the above copyright notice,
	 * this list of conditions and the following disclaimer.
	 *
	 * * Redistributions in binary form must reproduce the above copyright notice,
	 * this list of conditions and the following disclaimer in the documentation
	 * and/or other materials provided with the distribution.
	 *
	 * * Neither the name of Zend Technologies USA, Inc. nor the names of its
	 * contributors may be used to endorse or promote products derived from this
	 * software without specific prior written permission.
	 *
	 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
	 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
	 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
	 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
	 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
	 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
	 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
	 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	 *
	 * @param string $query
	 * @param mixed $limit
	 * @param mixed $offset
	 * @link http://lists.bestpractical.com/pipermail/rt-devel/2005-June/007339.html
	 * @return string
	 */
	public function modifyLimitQuery($query, $limit = false, $offset = false, $isManip = false, $isSubQuery = false)
	{
		if ($limit > 0) {
			$count = intval($limit);
			$offset = intval($offset);

			if ($offset < 0) {
				throw new Lumine_Exception("LIMIT argument offset=$offset is not valid");
			}

			$orderby = stristr($query, 'ORDER BY');

			if ($orderby !== false) {
				$order = str_ireplace('ORDER BY', '', $orderby);
				$orders = explode(',', $order);

				for ($i = 0; $i < count($orders); $i++) {
					$sorts[$i] = (stripos($orders[$i], ' desc') !== false) ? 'DESC' : 'ASC';
					$orders[$i] = trim(preg_replace('/\s+(ASC|DESC)$/i', '', $orders[$i]));

					// find alias in query string
					$helper_string = stristr($query, $orders[$i]);

					$from_clause_pos = strpos($helper_string, ' FROM ');
					$fields_string = substr($helper_string, 0, $from_clause_pos + 1);

					$field_array = explode(',', $fields_string);
					$field_array = array_shift($field_array);
					$aux2 = spliti(' as ', $field_array);
					$aux2 = explode('.', end($aux2));

					$aliases[$i] = trim(end($aux2));
				}
			}

			$selectRegExp = 'SELECT\s+';
			$selectReplace = 'SELECT ';

			if (preg_match('/^SELECT(\s+)DISTINCT/i', $query)) {
				$selectRegExp .= 'DISTINCT\s+';
				$selectReplace .= 'DISTINCT ';
			}

			$fields_string = substr($query, strlen($selectReplace), strpos($query, ' FROM ') - strlen($selectReplace));
			$field_array = explode(',', $fields_string);
			$field_array = array_shift($field_array);
			$aux2 = spliti(' as ', $field_array);
			$aux2 = explode('.', end($aux2));
			$key_field = trim(end($aux2));

			$query = preg_replace('/^'.$selectRegExp.'/i', $selectReplace . 'TOP ' . ($count + $offset) . ' ', $query);

			if ($isSubQuery === true) {
				$query = 'SELECT TOP ' . $count . ' inner_tbl.' . $key_field . ' FROM (' . $query . ') AS inner_tbl';
			} else {
				$query = 'SELECT * FROM (SELECT TOP ' . $count . ' * FROM (' . $query . ') AS inner_tbl';
			}

			if ($orderby !== false) {
				$query .= ' ORDER BY ';

				for ($i = 0, $l = count($orders); $i < $l; $i++) {
					if ($i > 0) { // not first order clause
						$query .= ', ';
					}

					$query .= 'inner_tbl.' . $aliases[$i] . ' ';
					$query .= (stripos($sorts[$i], 'asc') !== false) ? 'DESC' : 'ASC';
				}
			}

			if ($isSubQuery !== true) {
				$query .= ') AS outer_tbl';

				if ($orderby !== false) {
					$query .= ' ORDER BY ';

					for ($i = 0, $l = count($orders); $i < $l; $i++) {
						if ($i > 0) { // not first order clause
							$query .= ', ';
						}

						$query .= 'outer_tbl.' . $aliases[$i] . ' ' . $sorts[$i];
					}
				}
			}
		}

		return $query;
	}
}


