<?php
/**
 * classe de conexao com o PostgreSQL
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Connection
 */

Lumine::load('Connection_AbstractConnection');
/**
 * classe de conexao com o PostgreSQL
 * @package Lumine_Connection
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Connection_PostgreSQL
	extends Lumine_Connection_AbstractConnection
{
	/**
	 * ultima consulta
	 * @var resource
	 */
	private $last_rs;
	
	/**
	 * Construtor
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return Lumine_Connection_PostgreSQL
	 */
	public function __construct(){
		$this->randomFunction = 'random()';
	}
	
	/**
	 * @see Lumine_Connection_IConnection::connect()
	 */
	public function connect()
	{
		if($this->conn_id && $this->state == self::OPEN)
		{
			Lumine_Log::debug( 'Utilizando conexao cacheada com '.$this->getDatabase());
			return true;
		}

		$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::PRE_CONNECT, $this));
		
		$hostString = 'host='.$this->getHost();
		$hostString .=  ' dbname=' . $this->getDatabase();
		if($this->getPort() != '') 
		{
			$hostString .=  ' port=' . $this->getPort();
		}
		
		if($this->getUser() != '') 
		{
			$hostString .=  ' user=' . $this->getUser();
		}
		
		if($this->getPassword() != '') 
		{
			$hostString .=  ' password=' . $this->getPassword();
		}
		
		if(isset($this->options['socket']) && $this->options['socket'] != '')
		{
			$hostString .= ' socket=' . $this->options['socket'];
		}
		$flags = isset($this->options['flags']) ? $this->options['flags'] : null;
					
		if(isset($this->options['persistent']) && $this->options['persistent'] == true)
		{
			Lumine_Log::debug('Criando conexao persistente com '.$this->getDatabase());
			$this->conn_id = pg_pconnect($hostString);
		} else {
			Lumine_Log::debug('Criando conexao com '.$this->getDatabase());
			$this->conn_id = pg_connect($hostString);
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
		
		$this->state = self::OPEN;
		
		// altera o charset
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
			Lumine_Dialect_Factory::getByName('PostgreSQL')->freeAllResults();
			
			$this->state = self::CLOSED;
			Lumine_Log::debug( 'Fechando conexao com '.$this->getDatabase());
			pg_close($this->conn_id);
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
			$msg = pg_last_error($this->conn_id);
		} else {
			$msg = pg_last_error();
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
		
		$sql = "select tablename from pg_tables where tablename not like 'pg\_%'
				and tablename not in ('sql_features', 'sql_implementation_info', 'sql_languages',
				'sql_packages', 'sql_sizing', 'sql_sizing_profiles','sql_parts') order by tablename asc";
				
		$rs = $this->executeSQL($sql);
		
		$list = array();
		
		while($row = pg_fetch_row($rs))
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
		
		$sql = "SELECT pg_catalog.pg_get_constraintdef(r.oid, true) as condef
				FROM pg_catalog.pg_constraint r, pg_catalog.pg_class c
				WHERE r.conrelid = c.oid AND r.contype = 'f'
				AND c.relname = '".$tablename."'";

		$fks = array();
		$rs = $this->executeSQL($sql);
		
		
		while($row = pg_fetch_row($rs))
		{
			// Exemplo:
			// FOREIGN KEY (idusuario) REFERENCES usuario(idusuario) ON UPDATE CASCADE ON DELETE CASCADE
			
			//preg_match('@FOREIGN KEY \((\w+)\) REFERENCES (\w+)\((\w+)\)(.*?)$@i', str_replace('"', '', $row[0]), $matches);
			//preg_match('@FOREIGN KEY \((\w+(.*?)?)\) REFERENCES (\w+)\((\w+(.*?))\)(.*?)$@i', str_replace('"', '', $row[0]), $matches);
			preg_match('@FOREIGN KEY \((?<from>\w+(.*?)?)\) REFERENCES (?<target_table>\w+\.?\w+)\((?<target_column>\w+\.?\w+(.*?))\)(.*?)$@i', str_replace('"', '', $row[0]), $matches);
			
			$listFrom = explode(',', str_replace(' ', '', $matches['from']));
			$listTo = explode(',', str_replace(' ', '', $matches['target_column']));
			
			if( count($listFrom) != count($listTo) ){
				Lumine_Log::error('O numero de itens de origem nao e igual ao numero de itens de destino');
				exit;
			}
			
			for($i=0; $i<count($listFrom); $i++){
				// removemos o nome do schema
				// 22/03/2011 - encontrado por Thiago Marsiglia
				$targetTable = end(explode('.', $matches['target_table']));
				
				// nome da fk
				$name = $targetTable;
				
				$fieldFrom = $listFrom[ $i ];
				$fieldTo = $listTo[ $i ];
				
				if(isset($fks[ $name ]))
				{
					$name = $name . '_' . $fieldTo;
				}
				
				$fks[ $name ]['from'] = $fieldFrom;
				$fks[ $name ]['to'] = $targetTable;
				$fks[ $name ]['to_column'] = $fieldTo;
				
				$reg = array();
				if(preg_match('@(.*?)ON UPDATE (RESTRICT|CASCADE)@i', $matches[5], $reg))
				{
					$fks[ $name ]['update'] = strtoupper($reg[2]);
				} else {
					$fks[ $name ]['update'] = 'RESTRICT';
				}
				if(preg_match('@(.*?)ON DELETE (RESTRICT|CASCADE)@i', $matches[5], $reg))
				{
					$fks[ $name ]['delete'] = strtoupper($reg[2]);
				} else {
					$fks[ $name ]['delete'] = 'RESTRICT';
				}
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
		// verificacao de existencia de esquema no nome da tabela.
		// sugestao de Tiago Hiller - 05/10/2011
		$hasSchema = preg_match('@\b(?P<schema>\w+)\.(?P<tablename>\w+)\b@', $tablename, $res);
			
		if($hasSchema){
			$tablename = $res['tablename'];
		}
	
		$sql = "
			SELECT 
				f.attname AS name,
				pg_catalog.format_type(f.atttypid,f.atttypmod) AS type,
			
				CASE
					WHEN t.typlen < 0 THEN CASE WHEN f.atttypmod > 0 THEN f.atttypmod - 4 ELSE NULL END
					ELSE t.typlen
				END as length,
				
			
				CASE
				WHEN p.contype = 'p'
					THEN 't'
					ELSE 'f'
				END AS primarykey,
			
				CASE
				WHEN f.atthasdef = 't'
					THEN d.adsrc
				END AS default,
			
				f.attnotnull AS notnull,
				
				f.attnum AS number,
				f.attnum,
				
				CASE
				WHEN p.contype = 'u'
					THEN 't'
					ELSE 'f'
				END AS uniquekey,
				
				CASE
				WHEN fk.contype = 'f'
					THEN g.relname
				END AS foreignkey,
			
				CASE
				WHEN fk.contype = 'f'
					THEN fk.confkey
				END AS foreignkey_fieldnum,
				
				n.nspname as schema
			
				FROM pg_attribute f
				JOIN pg_class c ON c.oid = f.attrelid
				JOIN pg_type t ON t.oid = f.atttypid
				LEFT JOIN pg_attrdef d ON d.adrelid = c.oid AND d.adnum = f.attnum
				LEFT JOIN pg_namespace n ON n.oid = c.relnamespace
				LEFT JOIN pg_constraint p ON p.conrelid = c.oid AND f.attnum = ANY ( p.conkey ) AND p.contype IN ('p')
				LEFT JOIN pg_constraint fk ON fk.conrelid = c.oid AND f.attnum = ANY ( fk.conkey ) AND fk.contype IN ('f')
				LEFT JOIN pg_class AS g ON fk.confrelid = g.oid
				WHERE c.relkind = 'r'::char
					AND c.relname = '$tablename' AND f.attnum > 0";
		
		if($hasSchema){
			$sql .= " AND n.nspname = '{$res['schema']}'";
		}
				
		$sql .= " ORDER BY number";
		
		$rs = $this->executeSQL( $sql );
		
		$data = array();
		while($row = pg_fetch_row($rs))
		{
			$options = array();
			
			$name           = $row[0];
			$type_native    = $row[1];
			
			if( preg_match('@numeric\s*\((\d+)\s*\,\s*(\d+)\)@i', $row[1], $reg) ){
				$type       = 'float';
				$options['length'] = $reg[1];
				$options['precision'] = $reg[2];
				
			} else {
				$type       = preg_replace('@(\(\d+\)|\d+)@','',$row[1]);
			}

			$length     = $row[2] == '' ? null : $row[2];

			$notnull        = $row[5] == 't' ? true : false;
			$primary        = $row[3] == 't' ? true : false;
			$default        = preg_match('@^nextval@i', $row[4]) ? null : $row[4];
			$autoincrement  = preg_match('@^nextval@i', $row[4]) ? true : false;
			
			// removemos o cast se tiver
			if(preg_match('@(.+?)::.+?$@', $default, $reg)){
				$default = str_replace("'", '', $reg[1]);
			}
			
			
			$row[4] = str_replace('"',"'",$row[4]);
			
			if( preg_match("@^nextval\('(\w+)'@i", $row[4], $reg) ) {
				$options['sequence'] = $reg[1];
			} else if(preg_match("@'(\w+)_seq'@i", $row[4], $reg)){
				$options['sequence'] = $reg[1].'_seq';
			}
			
			if(!empty($options['sequence']) && !empty($row[11])){
				$options['sequence'] = $row[11] . '.' . $options['sequence'];
			}
			
			$data[] = array($name, $type_native, $type, $length, $primary, $notnull, $default, $autoincrement, $options);
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
		$rs = @pg_query($this->conn_id, $sql);
		
		if( ! $rs )
		{
			$msg = $this->getErrorMsg();
			$this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::EXECUTE_ERROR, $this, $msg, $sql));
			throw new Lumine_Exception("Falha na consulta: " . $msg."<br>" . $sql, Lumine_Exception::QUERY_ERROR);
		}
		$this->last_rs = $rs;
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
			return sprintf("LIMIT %d OFFSET %d", $limit, $offset);
		}
	}
	/**
	 * @see Lumine_Connection_IConnection::escape()
	 */
	public function escape($str) 
	{
		$var = pg_escape_string($str);
		return $var;
	}
	/**
	 * @see Lumine_Connection_IConnection::escapeBlob()
	 */
	public function escapeBlob($blob)
	{
		return pg_escape_bytea($blob);
	}
	/**
	 * @see Lumine_Connection_IConnection::affected_rows()
	 */
	public function affected_rows()
	{
		if($this->state == self::OPEN && $this->last_rs)
		{
			return pg_affected_rows($this->last_rs);
		}
		throw new Lumine_Exception('conexao nao est� aberta', Lumine_Exception::ERRO);
	}
	/**
	 * @see Lumine_Connection_IConnection::num_rows()
	 */
	public function num_rows($rs)
	{
		return pg_num_rows($rs);
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
	
	public function setCharset($charset){
		if( $this->conn_id && !empty($charset) ){
			pg_set_client_encoding($this->conn_id, $charset);
		}
		parent::setCharset($charset);
	}
	
	public function toPHPValue($value, $field){
		if($field['type'] == 'boolean' && !is_null($value)){
			return $value == 't';
		}
		
		return $value;
	}
	
	public function toDatabaseValue($value, $field){
		if($field['type'] == 'boolean' && is_bool($value)){
			return $value ? 't' : 'f';
		}
		return $value;
	}
}


