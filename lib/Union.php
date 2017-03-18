<?php
/**
 * Classe responsavel para poder realizar consultas uniao.
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine
 */

/**
 * Classe responsavel para poder realizar consultas uniao.
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine
 */
class Lumine_Union extends Lumine_Base
{
	/**
	 * Classes que ja foram unidas
	 * @var unknown_type
	 */
	private $_union          = array();

	/**
	 * Construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Configuration $cfg
	 * @return Lumine_Union
	 */
	function __construct( Lumine_Configuration $cfg )
	{
                $this->_metadata = new Lumine_Metadata($this);
		$clname = 'Lumine_Dialect_' . $cfg->getProperty('dialect');
		$this->metadata()->setPackage( $cfg->getProperty('package') );
		$this->metadata()->setTablename('union');
		
		parent::__construct();
		
		// $this->_bridge = new $clname( $this );
	}
	
	/**
	 * Adiciona mais uma classe a lista de uniao
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return Lumine_Union 
	 */
	public function add(Lumine_Base $obj)
	{
		$this->_union[] = $obj;
		return $this;
	}
	
	/**
	 * @see Lumine_Base::where()
	 */
	public function where($str = null)
	{
		if(is_null($str))
		{
			$this->_where = array();
		} else {
			$this->_where[] = $str;
		}
		return $this;
	}
	
	/**
	 * @see Lumine_Base::order()
	 */
	public function order($str = null)
	{
		if(is_null($str))
		{
			$this->_order = array();
		} else {
			$this->_order[] = $str;
		}
		return $this;
	}
	
	/**
	 * @see Lumine_Base::having()
	 */
	public function having($str = null)
	{
		if(is_null($str))
		{
			$this->_having = array();
		} else {
			$this->_having[] = $str;
		}
		return $this;
	}
	
	/**
	 * @see Lumine_Base::group()
	 */
	public function group($str = null)
	{
		if(is_null($str))
		{
			$this->_group = array();
		} else {
			$this->_group[] = $str;
		}
		return $this;
	}
	
	/**
	 * @see Lumine_Base::count()
	 */
	public function count($what='*')
	{
		$sql = "SELECT COUNT({$what}) as lumine_count FROM ( " . $this->getSQL() . ") as consulta";
		$res = $this->_execute($sql);
		
		if($res == true)
		{
			$total = $this->_getDialect()->fetch();
			return $total['lumine_count'];
		}
		
		return 0;
	}
	
	/**
	 * @see Lumine_Base::find()
	 */
	public function find( $auto_fetch = false )
	{
		$this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_FIND, $this));
		$sql = $this->getSQL();
		
		$result = $this->_execute($sql);
		
		if($result == true)
		{
			if($auto_fetch == true)
			{
				$this->fetch();
			}
		}
		
		$this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_FIND, $this));
		
		return $this->_getDialect()->num_rows();
		
	}
	
	/**
	 * @see Lumine_Base::limit()
	 */
	public function limit($offset = null, $limit = null)
	{
		if( empty($limit))
		{
			$this->_limit = $offset;
		} else {
			$this->_offset = $offset;
			$this->_limit = $limit;
		}
		
		return $this;
	}

	/**
	 * Monta a SQL que sera executada
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	public function getSQL()
	{
		if( empty($this->_union))
		{
			Lumine_Log::warning('Nenhuma classe incluida para realizar a uniao');
			return false;
		}
		
		$sql = array();
		foreach($this->_union as $obj)
		{
			$sql[] = "(" . trim( $obj->_getSQL(Lumine_Base::SQL_SELECT) ) . ")";
		}
		
		if( !empty($this->_data) ){
			$strSQL = 'SELECT ' . Lumine_Parser::parseSQLValues( $this, implode(', ', $this->_data)) . ' FROM ';
		} else {
			$strSQL = ' SELECT * FROM ';
		}
		
		$strSQL .= '(' . implode(PHP_EOL . ' UNION ' . PHP_EOL, $sql) . ') AS LUMINE_UNION';
		
		if( !empty($this->_where))
		{
			$strSQL .= PHP_EOL . " WHERE " . implode(' AND ', $this->_where);
		}

		if( !empty($this->_group))
		{
			$strSQL .= PHP_EOL . " GROUP BY " . implode(', ', $this->_group);
		}
		
		if( !empty($this->_having))
		{
			$strSQL .= PHP_EOL . " HAVING " . implode(' AND ', $this->_having);
		}
		
		if( !empty($this->_order))
		{
			$strSQL .= PHP_EOL . " ORDER BY " . implode(', ', $this->_order);
		}
		
		$strSQL .= PHP_EOL . $this->_union[0]->_getConnection()->setLimit($this->_offset, $this->_limit);
		
		return $strSQL;
	}

	/**
	 * @see Lumine_Base::join()
	 */
	public function join( Lumine_Base $obj, $type = 'INNER', $alias = '', $linkName = null, $linkTo = null, $extraCondition = null )
	{
		$this->negado();
	}	
	
	/**
	 * @see Lumine_Base::save()
	 */
	public function save( $whereAddOnly = false  )
	{
		$this->negado();
	}
	
	/**
	 * @see Lumine_Base::insert()
	 */
	public function insert()
	{
		$this->negado();
	}
	
	/**
	 * @see Lumine_Base::update()
	 */
	public function update( $whereAddOnly = false )
	{
		$this->negado();
	}
	
	/**
	 * @see Lumine_Base::delete()
	 */
	public function delete( $whereAddOnly = false )
	{
		$this->negado();
	}
	
	/**
	 * @see Lumine_Base::get()
	 */
	public function get( $pk, $pkValue = null )
	{
		$this->negado();
	}
	
	/**
	 * Exibe uma mensagem no log dizendo que este metodo nao pode ser feito para esta instancia
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	private function negado()
	{
		$x = debug_backtrace();
		
		$str = 'Rotina "' . $x[1]['function'] . '" negada nesta classe';
		Lumine_Log::warning( $str );
	}
}


?>