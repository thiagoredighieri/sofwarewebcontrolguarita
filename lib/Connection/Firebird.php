<?php
/**
 * Conexao com o firebird
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Connection
 */


Lumine::load('Connection_AbstractConnection');

/**
 * Conexao com o firebird
 * @package Lumine_Connection
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Connection_Firebird
	extends Lumine_Connection_AbstractConnection
{

    /**
     * formato de data
     * @var string
     */
    private $ibase_datefmt = '%Y-%m-%d';
    /**
     * formato de horas
     * @var string
     */
    private $ibase_timefmt = '%H:%M:%S';
    /**
     * formato do timestamp
     * @var string
     */
    private $ibase_timestampfmt = '%Y-%m-%d %H:%M:%S';

	/**
	 * Construtor
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return Lumine_Connection_Firebird
	 */
	public function __construct(){
		$this->randomFunction = '';
		$this->escapeChar = '\\';
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
        
        $hostString = $this->getHost();
        if($this->getPort() != '') 
        {
        	// nao colocamos a porta uma vez que a string de conexao
        	// nao suporta a informacao da porta 
            //$hostString .=  ':' . $this->getPort();
        }
        
        $hostString = empty($hostString) ? $this->getDatabase() : $hostString . ':' . $this->getDatabase();
        
        if(isset($this->options['socket']) && $this->options['socket'] != '')
        {
            $hostString .= ':' . $this->options['socket'];
        }

        $flags = isset($this->options['flags']) ? $this->options['flags'] : null;

        if(isset($this->options['persistent']) && $this->options['persistent'] == true)
        {
            Lumine_Log::debug( 'Criando conexao persistente com '.$this->getDatabase());

            $this->conn_id = @ibase_pconnect($hostString, $this->getUser(), $this->getPassword());
        } else {
            Lumine_Log::debug( 'Criando conexao com '.$this->getDatabase());
            $this->conn_id = @ibase_connect($hostString, $this->getUser(), $this->getPassword());
        }
        
        if( !$this->conn_id )
        {
            $this->state = self::CLOSED;
            $msg = 'Nao foi possivel conectar no banco de dados: ' . $this->getDatabase().' - '.$this->getErrorMsg();
            Lumine_Log::error( $msg );
            
            $this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::CONNECTION_ERROR, $this, $msg));
            throw new Exception( $msg );
            
            return false;
        }
        
        if (function_exists('ibase_timefmt'))
        {
            ibase_timefmt($this->ibase_datefmt,IBASE_DATE );
            if ($this->dialect == 1) ibase_timefmt($this->ibase_datefmt,IBASE_TIMESTAMP );
            else ibase_timefmt($this->ibase_timestampfmt,IBASE_TIMESTAMP );
            ibase_timefmt($this->ibase_timefmt,IBASE_TIME );
            
        } else {
            ini_set("ibase.timestampformat", $this->ibase_timestampfmt);
            ini_set("ibase.dateformat", $this->ibase_datefmt);
            ini_set("ibase.timeformat", $this->ibase_timefmt);
        }
        
        $this->state = self::OPEN;
        $this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::POS_CONNECT, $this));
        
        $this->setCharset( $this->getCharset() );
        
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
			Lumine_Dialect_Factory::getByName('Firebird')->freeAllResults();
			
            $this->state = self::CLOSED;
			
            Lumine_Log::debug( 'Fechando conexao com '.$this->getDatabase());
            ibase_close($this->conn_id);
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
            $msg = ibase_errmsg();
        } else {
            $msg = ibase_errmsg();
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
        
        $rs = $this->executeSQL("SELECT RDB\$RELATION_NAME FROM RDB\$RELATIONS WHERE RDB\$SYSTEM_FLAG=0 AND RDB\$VIEW_BLR IS NULL;");
        
        $list = array();
        
        while($row = ibase_fetch_row($rs))
        {
            $list[] = trim($row[0]);
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
        $rs = $this->executeSQL("
               SELECT rc.RDB\$CONSTRAINT_NAME,
                      s.RDB\$FIELD_NAME AS \"field_name\",
                      refc.RDB\$UPDATE_RULE AS \"on_update\",
                      refc.RDB\$DELETE_RULE AS \"on_delete\",
                      i2.RDB\$RELATION_NAME AS \"references_table\",
                      s2.RDB\$FIELD_NAME AS \"references_field\",
                      (s.RDB\$FIELD_POSITION + 1) AS \"field_position\"
                 FROM RDB\$INDEX_SEGMENTS s
            LEFT JOIN RDB\$INDICES i ON i.RDB\$INDEX_NAME = s.RDB\$INDEX_NAME
            LEFT JOIN RDB\$RELATION_CONSTRAINTS rc ON rc.RDB\$INDEX_NAME = s.RDB\$INDEX_NAME
            LEFT JOIN RDB\$REF_CONSTRAINTS refc ON rc.RDB\$CONSTRAINT_NAME = refc.RDB\$CONSTRAINT_NAME
            LEFT JOIN RDB\$RELATION_CONSTRAINTS rc2 ON rc2.RDB\$CONSTRAINT_NAME = refc.RDB\$CONST_NAME_UQ
            LEFT JOIN RDB\$INDICES i2 ON i2.RDB\$INDEX_NAME = rc2.RDB\$INDEX_NAME
            LEFT JOIN RDB\$INDEX_SEGMENTS s2 ON i2.RDB\$INDEX_NAME = s2.RDB\$INDEX_NAME
                WHERE i.RDB\$RELATION_NAME='".$tablename."'
                  AND rc.RDB\$CONSTRAINT_TYPE = 'FOREIGN KEY'
             ORDER BY s.RDB\$FIELD_POSITION");
        
        while( $row = ibase_fetch_assoc($rs, IBASE_FETCH_BLOBS) )
        {
            $name = trim($row['references_table']);
            
            if(isset($fks[ $name ]))
            {
                $name = $name . '_' . trim($row['references_field']);
            }
            
            $fks[ $name ]['from'] = trim($row['field_name']);
            $fks[ $name ]['to'] = trim($row['references_table']);
            $fks[ $name ]['to_column'] = trim($row['references_field']);
            $fks[ $name ]['delete'] = empty($row['on_delete']) ? 'RESTRICT' : trim(strtoupper($row['on_delete']));
            $fks[ $name ]['update'] = empty($row['on_update']) ? 'RESTRICT' : trim(strtoupper($row['on_update']));
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
        $sql = "
            SELECT r.RDB\$FIELD_NAME AS field_name,
                    r.RDB\$DESCRIPTION AS field_description,
                    r.RDB\$DEFAULT_VALUE AS field_default_value,
                    r.RDB\$NULL_FLAG AS field_not_null_constraint,
                    f.RDB\$FIELD_LENGTH AS field_length,
                    f.RDB\$FIELD_PRECISION AS field_precision,
                    f.RDB\$FIELD_SCALE AS field_scale,
                    CASE f.RDB\$FIELD_TYPE
                      WHEN 261 THEN 'BLOB'
                      WHEN 14 THEN 'CHAR'
                      WHEN 40 THEN 'VARCHAR'
                      WHEN 11 THEN 'FLOAT'
                      WHEN 27 THEN 'FLOAT'
                      WHEN 10 THEN 'FLOAT'
                      WHEN 16 THEN 'INT64'
                      WHEN 8 THEN 'INTEGER'
                      WHEN 9 THEN 'QUAD'
                      WHEN 7 THEN 'SMALLINT'
                      WHEN 12 THEN 'DATE'
                      WHEN 13 THEN 'TIME'
                      WHEN 35 THEN 'DATETIME'
                      WHEN 37 THEN 'VARCHAR'
                      ELSE 'UNKNOWN'
                    END AS field_type,
                    f.RDB\$FIELD_SUB_TYPE AS field_subtype,
                    coll.RDB\$COLLATION_NAME AS field_collation,
                    cset.RDB\$CHARACTER_SET_NAME AS field_charset,
                    
                    (SELECT count(*)
                     FROM RDB\$INDEX_SEGMENTS s
                LEFT JOIN RDB\$RELATION_CONSTRAINTS rc ON rc.RDB\$INDEX_NAME = s.RDB\$INDEX_NAME
                LEFT JOIN RDB\$INDICES i ON i.RDB\$INDEX_NAME = s.RDB\$INDEX_NAME
                    WHERE i.RDB\$RELATION_NAME = r.RDB\$RELATION_NAME
                      AND rc.RDB\$CONSTRAINT_TYPE = 'PRIMARY KEY'
                      AND s.RDB\$FIELD_NAME = r.RDB\$FIELD_NAME
                  ) as primary_key
                  
               FROM RDB\$RELATION_FIELDS r
               LEFT JOIN RDB\$FIELDS f ON r.RDB\$FIELD_SOURCE = f.RDB\$FIELD_NAME
               LEFT JOIN RDB\$COLLATIONS coll ON r.RDB\$COLLATION_ID = coll.RDB\$COLLATION_ID
                AND f.RDB\$CHARACTER_SET_ID = coll.RDB\$CHARACTER_SET_ID
               LEFT JOIN RDB\$CHARACTER_SETS cset ON f.RDB\$CHARACTER_SET_ID = cset.RDB\$CHARACTER_SET_ID
              WHERE r.RDB\$RELATION_NAME='".$tablename."'
            ORDER BY r.RDB\$FIELD_POSITION
        ";
        $rs = $this->executeSQL( $sql );
        
        $data = array();
        while($row = ibase_fetch_assoc($rs, IBASE_FETCH_BLOBS))
        {
            $name           = trim($row['FIELD_NAME']);
            $type_native    = trim(strtolower($row['FIELD_TYPE']));
            $type           = $type_native;
            $length         = $row['FIELD_LENGTH'];

            $notnull        = empty($row['FIELD_NOT_NULL_CONSTRAINT']) ? false : true;
            $primary        = ! empty($row['PRIMARY_KEY'])  ? true : false;
            $default        = empty($row['FIELD_DEFAULT_VALUE'])  ? null : $this->parseDefaultValue( $row['FIELD_DEFAULT_VALUE'] );
            $autoincrement  =  $this->checaAutoIncrement( $tablename, $name );
            
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
            
            $limite = strtoupper($reg[1]);
            $limite = str_replace('LIMIT','FIRST', $limite);
            $limite = str_replace('OFFSET','SKIP', $limite);
            
            $sql = preg_replace('@^SELECT\s+@i', 'SELECT ' . $limite. ' ', $sql);
            
            Lumine_Log::debug('Consulta transformada para Firebird: ' . $sql);
        }
        
        $rs = @ibase_query($sql, $this->conn_id);
        
        if( ! $rs )
        {
            $msg = $this->getErrorMsg();
            $this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::EXECUTE_ERROR, $this, $msg, $sql));
            throw new Lumine_Exception("Falha na consulta: " . $msg, Lumine_Exception::QUERY_ERROR);
        }
        $this->dispatchEvent(new Lumine_Events_ConnectionEvent(Lumine_Event::CONNECTION_ERROR, $this, '', $sql));
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
        /**
         * TODO: fazer um escape decente, uma vez que o Firebird nao tem um
         */
        return addslashes( $str );
        
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
            return ibase_affected_rows($this->conn_id);
        }
        throw new Lumine_Exception('conexao nao esta aberta', Lumine_Exception::ERRO);
    }
    /**
     * @see Lumine_Connection_IConnection::num_rows()
     */
    public function num_rows($rs)
    {
        /**
         * TODO: um algoritimo mais otimizado para fazer isso
         */
        
        $rows = 0;
        if( is_resource($rs) ){
	        while( ibase_fetch_row($rs) )
	        {
	            $rows++;
	        }
        }
        
        return $rows;
    }
    
   /**
    * @see Lumine_Connection_IConnection::begin()
    */
    public function begin($transactionID=null)
    {
        $id = $this->transactions_count++;
        $this->transactions[ $id ] = ibase_trans( IBASE_DEFAULT, $this->conn_id );
        
        return $id;
    }
	/**
	 * @see Lumine_Connection_IConnection::commit()
	 */
    public function commit($transactionID=null)
    {
        if( is_null($transactionID) )
        {
            $id = $this->transactions_count-1;
        } else {
            $id = $transactionID;
        }
        
        if( isset($this->transactions[$id]) )
        {
            ibase_commit($this->conn_id);
            unset($this->transactions[$id]);
        }
    }
	/**
	 * @see Lumine_Connection_IConnection::rollback()
	 */
    public function rollback($transactionID=null)
    {
        if( is_null($transactionID) )
        {
            $id = $this->transactions_count-1;
        } else {
            $id = $transactionID;
        }
        
        if( isset($this->transactions[$id]) )
        {
            ibase_rollback($this->conn_id, $this->transactions[$id]);
            unset($this->transactions[$id]);
        }
    }
    
    /**
     * funcao que checa se o campo possui uma trigger que pega um contador 
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $tablename Nome da tabela
     * @param string $fieldname Nome do campo
     * @return boolean True se encontrar a trigger com o gerador do contador do contrario false 
     */
    public function checaAutoIncrement( $tablename, $fieldname )
    {
        $sql = "SELECT RDB\$TRIGGER_SOURCE AS triggers FROM RDB\$TRIGGERS
                 WHERE (RDB\$SYSTEM_FLAG IS NULL
                    OR RDB\$SYSTEM_FLAG = 0)
                   AND RDB\$RELATION_NAME='".$tablename."'";

        $rs = $this->executeSQL($sql);
        
        while( $row = ibase_fetch_assoc($rs, IBASE_FETCH_BLOBS) )
        {
            $exp = '@new\.'.$fieldname.'\s+=\s+gen_id\((\w+)@is';
            $res = preg_match($exp, trim($row['TRIGGERS']), $reg);

            // oba! achamos o lance            
            if( $res )
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Recupera o proximo ID para o gerador
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $generator Nome do gerador
     * @param int $step Step para incrementar no gerador
     * @return int Novo valor 
     */
    public function genID( $generator, $step = 1 )
    {
        $sql = sprintf("SELECT gen_id(%s, %d) as CODIGO FROM RDB\$DATABASE ", $generator, $step);

        $rs = $this->executeSQL( $sql );
        
        if( $row = ibase_fetch_row($rs) )
        {
            return $row[0];
        }
        return 1;
    }
    
}


