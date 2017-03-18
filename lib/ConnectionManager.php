<?php
/**
 * Classe de gerenciamento de conexao com o banco de dados
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br 
 * @package Lumine
 */

/**
 * Classe de gerenciamento de conexao com o banco de dados
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */
class Lumine_ConnectionManager extends Lumine_EventListener
{
	/**
	 * Instancia da classe
	 * @var Lumine_ConnectionManager
	 */
	private static $instance;
	/**
	 * Lista de conexoes ativas
	 * @var array
	 */
	private $connections = array();
	
	/**
	 * Recupera a instancia da classe
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return Lumine_ConnectionManager
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new Lumine_ConnectionManager;
		} 
		
		return self::$instance;
	}
	
	/**
	 * Cria uma nova referencia de conexao com o banco
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $connectionName Nome da conexao
	 * @param Lumine_Configuration $config Objeto de configuracao
	 * @return void
	 */
	public function create($connectionName, Lumine_Configuration $config)
	{
		if( $this->getConnection($connectionName) != false )
		{
			Lumine_Log::warning('Ja existe uma conexao com este nome: ' .$connectionName );
		} else {
			Lumine_Log::debug('Armazenando conexao: ' .$connectionName);
			
			$connObj = $this->getConnectionClass( $config->options['dialect'] );
			
			if($connObj == false)
			{
				Lumine_Log::error( 'Dialeto nao implementado: ' .$config->options['dialect']);
				return;
			}
			
			$connObj->setDatabase( $config->options['database'] );
			$connObj->setHost( $config->options['host'] );
			$connObj->setPort( $config->options['port'] );
			$connObj->setUser( $config->options['user'] );
			$connObj->setPassword( $config->options['password'] );
			
			if(isset($config->options['options']))
			{
				$connObj->setOptions( $config->options['options'] );
			}
			
			if( $config->getOption('charset') != '' ){
				$connObj->setCharset( $config->getOption('charset') );
			}
			
			$config->setConnection( $connObj );
			$this->connections[ $connectionName ] = $config;
		}
	}
	
	/**
	 * Recupera uma conexao com o nome informado
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $connectionName Nome da conexao desejada
	 * @return Lumine_Configuration Configuracao / conexao encontrada ou false se nao recuperar
	 */
	public function getConnection( $connectionName ) 
	{
		if( ! isset($this->connections[ $connectionName ]))
		{
			Lumine_Log::warning('Conexao inexistente: ' .$connectionName);
			return false;
		}
		return $this->connections[ $connectionName ]->getConnection();
	}
	
	/**
	 * Recupera um objeto de conexao pelo nome da classe
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $dialect nome do dialeto
	 * @return Lumine_Connection_IConnection
	 */
	public function getConnectionClass( $dialect ) 
	{
		$file = LUMINE_INCLUDE_PATH . '/lib/Connection/'.$dialect.'.php';
		if(file_exists($file) == false)
		{
			throw new Lumine_Exception('Tipo de conexao inexistente: ' .$connectionName, Lumine_Exception::ERROR);
		}
		
		$class_name = 'Lumine_Connection_' . $dialect;
		
		require_once $file;
		$obj = new $class_name;
		return $obj;
	}
	
	/**
	 * Recupera uma configuracao pelo nome do pacote 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name
	 * @return Lumine_Configuration
	 */
	public function getConfiguration( $name )
	{
		if( ! isset($this->connections[ $name ])) 
		{
			throw new Lumine_Exception('Configuracao inexistente: ' .$name, Lumine_Exception::WARNING);
		}
		
		return $this->connections[ $name ];
	}
	
	/**
	 * Recupera a lista de configuracoes
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return array
	 */
	public function getConfigurationList()
	{
		return $this->connections;
	}
	
	/**
	 * @see Lumine_EventListener::__destruct()
	 */
	function __destruct()
	{
	    self::$instance = null;
	    $this->connections = array();
	    parent::__destruct();
	}
}




?>