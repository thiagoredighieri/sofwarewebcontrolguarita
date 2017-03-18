<?php
/**
 * Conexao com MySQL
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine_Connection
 */

/**
 * Conexao abstrata
 * 
 * @package Lumine_Connection
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
abstract class Lumine_Connection_AbstractConnection
	extends Lumine_EventListener
	implements Lumine_Connection_IConnection {

	/**
	 * Estado fechado
	 * @var int
	 */
	const CLOSED           = 0;
	/**
	 * Estado aberto
	 * @var int
	 */
	const OPEN             = 1;

	/**
	 * Constante para versao do servidor
	 * @var int
	 */
	const SERVER_VERSION   = 10;
	/**
	 * Constante para versao do cliente
	 * @var int
	 */
	const CLIENT_VERSION   = 11;
	/**
	 * Constante para informacoes do host
	 * @var int
	 */
	const HOST_INFO        = 12;
	/**
	 * tipo de protocolo
	 * @var int
	 */
	const PROTOCOL_VERSION = 13;
	/**
	 * Tipos de eventos disparados pela classe
	 * @var array
	 */
	protected $_event_types = array(
		Lumine_Event::PRE_EXECUTE,
    	Lumine_Event::POS_EXECUTE,
    	Lumine_Event::PRE_CONNECT,
    	Lumine_Event::POS_CONNECT,
    	Lumine_Event::PRE_CLOSE,
    	Lumine_Event::POS_CLOSE,
    	Lumine_Event::EXECUTE_ERROR,
    	Lumine_Event::CONNECTION_ERROR
	);
	
	/**
	 * ID da conexao
	 * @var resource
	 */
	protected $conn_id;
	/**
	 * nome do banco de dados
	 * @var string
	 */
	protected $database;
	/**
	 * nome do usuario
	 * @var string
	 */
	protected $user;
	/**
	 * senha do usuario
	 * @var string
	 */
	protected $password;
	/**
	 * porta de conexao
	 * @var integer
	 */
	protected $port;
	/**
	 * host do banco de dados
	 * @var string
	 */
	protected $host;
	/**
	 * opcoes
	 * @var array
	 */
	protected $options;
	/**
	 * Estado atual
	 * @var int
	 */
	protected $state;
	/**
	 * charset da conexao 
	 * @var string
	 */
	protected $charset;
	/**
	 * caracter de escape
	 * @var string
	 */
	protected $escapeChar = '\\';
	/**
	 * string identificadora da funcao random
	 * @var string
	 */
	protected $randomFunction = '';
	/**
     * referencias de transacoes abertas
     * @var array
     */
    protected $transactions = array();
    /**
     * numero de transacoes abertas
     * @var int
     */
    protected $transactions_count = 0;
	
	
	/**
	 * @see Lumine_Connection_IConnection::connect()
	 */
	public function connect(){}
	
	/**
	 * @see Lumine_Connection_IConnection::close()
	 */
	public function close(){}
	
	/**
	 * @see Lumine_Connection_IConnection::getState()
	 */
	public function getState()
	{
		return $this->state;
	}
	/**
	 * @see Lumine_Connection_IConnection::setDatabase()
	 */
	public function setDatabase($database)
	{
		$this->database = $database;
	}
	/**
	 * @see Lumine_Connection_IConnection::getDatabase()
	 */
	public function getDatabase()
	{
		return $this->database;
	}
	/**
	 * @see Lumine_Connection_IConnection::setUser()
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}
	/**
	 * @see Lumine_Connection_IConnection::getUser()
	 */
	public function getUser()
	{
		return $this->user;
	}
	/**
	 * @see Lumine_Connection_IConnection::setPassword()
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}
	/**
	 * @see Lumine_Connection_IConnection::getPassword()
	 */
	public function getPassword()
	{
		return $this->password;
	}
	/**
	 * @see Lumine_Connection_IConnection::setPort()
	 */
	public function setPort($port)
	{
		$this->port = $port;
	}
	/**
	 * @see Lumine_Connection_IConnection::getPort()
	 */
	public function getPort()
	{
		return $this->port;
	}
	/**
	 * @see Lumine_Connection_IConnection::setHost()
	 */
	public function setHost($host)
	{
		$this->host = $host;
	}
	/**
	 * @see Lumine_Connection_IConnection::getHost()
	 */
	public function getHost()
	{
		return $this->host;
	}
	/**
	 * @see Lumine_Connection_IConnection::setOptions()
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}
	/**
	 * @see Lumine_Connection_IConnection::getOptions()
	 */
	public function getOptions()
	{
		return $this->options;
	}
	/**
	 * @see Lumine_Connection_IConnection::setOption()
	 */
	public function setOption($name, $val)
	{
		$this->options[ $name ] = $val;
	}
	/**
	 * @see Lumine_Connection_IConnection::getOption()
	 */
	public function getOption($name)
	{
		if(empty($this->options[$name]))
		{
			return null;
		}
		return $this->options[$name];
	}
	
	/**
	 * @see Lumine_Connection_IConnection::setCharset()
	 */
	public function setCharset($charset){
		$this->charset = $charset;
	}
	
	/**
	 * @see Lumine_Connection_IConnection::getCharset()
	 */
	public function getCharset(){
		return $this->charset;
	}
	
	/**
	 * @see Lumine_Connection_IConnection::getErrorMsg()
	 */
	public function getErrorMsg(){}
	
	/**
	 * @see Lumine_Connection_IConnection::getTables()
	 */
	public function getTables(){}
	
	/**
	 * @see Lumine_Connection_IConnection::getForeignKeys()
	 */
	public function getForeignKeys($tablename){}

	/**
	 * @see Lumine_Connection_IConnection::getServerInfo()
	 */
	public function getServerInfo($type = null){}
	
	/**
	 * @see Lumine_Connection_IConnection::describe()
	 */
	public function describe($tablename){}
	
	/**
	 * @see Lumine_Connection_IConnection::executeSQL()
	 */
	public function executeSQL($sql){}
	
	/**
	 * @see Lumine_Connection_IConnection::setLimit()
	 */
	public function setLimit($offset = null, $limit = null){} 
	
	/**
	 * @see Lumine_Connection_IConnection::escape()
	 */
	public function escape($str){} 
	
	/**
	 * @see Lumine_Connection_IConnection::escapeBlob()
	 */
	public function escapeBlob($blob){}
	
	/**
	 * @see Lumine_Connection_IConnection::affected_rows()
	 */	
	public function affected_rows(){}
	
	/**
	 * @see Lumine_Connection_IConnection::num_rows()
	 */
	public function num_rows($rs){}
	
	/**
	 * @see Lumine_Connection_IConnection::random()
	 */
	public function random(){
		return $this->randomFunction;
	}
	
	/**
	 * @see Lumine_Connection_IConnection::getEscapeChar()
	 */
	public function getEscapeChar(){}
	
	
	/**
	 * @see Lumine_Connection_IConnection::begin()
	 */
	public function begin($transactionID=null){}
	
	/**
	 * @see Lumine_Connection_IConnection::commit()
	 */
	public function commit($transactionID=null){}

	/**
	 * @see Lumine_Connection_IConnection::rollback()
	 */
	public function rollback($transactionID=null){}
	
	public function toPHPValue($value, $field){
		return $value;
	}
	
	public function toDatabaseValue($value, $field){
		return $value;
	}
	
	/**
	 * @see Lumine_EventListener::__destruct()
	 */
    function __destruct()
    {
        unset($this->conn_id);
        unset($this->database);
        unset($this->user);
        unset($this->password);
        unset($this->port);
        unset($this->host);
        unset($this->options);
        unset($this->state);
        unset($this->transactions);
        unset($this->transactions_count);
        //unset(self::$instance);
        
        parent::__destruct();
    }
}

