<?php
/**
 * Classe de configuracao
 *
 * Para cada banco de dados que sera utilizado, e necessario inicializar a configuracao,
 * de forma que lumine possa encontrar corretamente as informacoes de conexao
 * com o banco de dados correto.
 *
 * <code>
 * $cfg = new Lumine_Configuration($lumineConf)
 * </code>
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

// carrega a classe de logs
Lumine::load('Log','Events_ConfigurationEvent');

/**
 * Classe de configuracao
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Configuration extends Lumine_EventListener
{
	/**
	 * armazena as opcoes de configuracao
	 * @var array
	 */
	public $options;
	/**
	 * Conexao com o banco
	 * @var Lumine_Connection_IConnection
	 */
	private $connection = null;
	
	/**
	 * Metodo de cache a ser usado
	 * @var Lumine_Cache_Abstract
	 */
	private $cacheImpl;

	/**
	 * tipos de eventos disparados
	 * @var array
	 */
	protected $_event_types = array(
        Lumine_Event::CREATE_OBJECT
    );

    /**
     * Construtor da classe
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param array $options lista de opcoes
     * @return Lumine_Configuration
     */
	function __construct(array $options)
	{
		if(empty($options['dialect']))
		{
			throw new Lumine_Exception("Dialeto nao definido na configuracao", Lumine_Exception::CONFIG_NO_DIALECT);
			return;
		}
		if(empty($options['database']))
		{
			throw new Lumine_Exception("Banco de dados nao definido na configuracao", Lumine_Exception::CONFIG_NO_DIALECT);
			return;
		}
		if(empty($options['user']))
		{
			throw new Lumine_Exception("Usuario nao definido na configuracao", Lumine_Exception::CONFIG_NO_DIALECT);
			return;
		}
		if(empty($options['class_path']))
		{
			throw new Lumine_Exception("Class-path nao definida na configuracao", Lumine_Exception::CONFIG_NO_DIALECT);
			return;
		}
		if(empty($options['package']))
		{
			throw new Lumine_Exception("Pacote nao definido na configuracao", Lumine_Exception::CONFIG_NO_DIALECT);
			return;
		}

		// opcionais, coloca valores padroes se nao informados
		if(!isset($options['password']))
		{
			Lumine_Log::debug('Senha nao definida na configuracao');
			$options['password'] = '';
		}
		if(!isset($options['port']))
		{
			Lumine_Log::debug('Porta nao definido na configuracao');
			$options['port'] = '';
		}
		if(!isset($options['host']))
		{
			Lumine_Log::debug('Host nao definido na configuracao');
			$options['host'] = 'localhost';
		}

		$this->options = $options;
		
		$cnManager = Lumine_ConnectionManager::getInstance();
		$cnManager->create($this->options['package'], $this);
		
		$cache = 'Lumine_Cache_' . ($this->getOption('cache') == null ? 'Array' : $this->getOption('cache'));
		$this->setCacheImpl(new $cache());
	}

	/**
	 * Altera a conexao ativa da configuracao
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Connection_IConnection $conn
	 * @return void
	 */
	public function setConnection(Lumine_Connection_IConnection $conn)
	{
		$this->connection = $conn;
	}

	/**
	 * Retorna a conexao ativa
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return Lumine_Connection_IConnection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Altera uma propriedade da configuracao
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setProperty( $name, $value )
	{
		$this->options[ $name ] = $value;
	}

	/**
	 * Recupera um valor do array de configuracoes
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name
	 * @return mixed
	 */
	public function getProperty( $name )
	{
		if( ! isset($this->options[ $name ]) )
		{
			Lumine_Log::warning('Propriedade inexistente: ' . $name);
			return null;
		}

		return $this->options[ $name ];
	}

	/**
	 * Altera uma opcao na configuracao
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setOption($name, $value)
	{
		$this->options['options'][ $name ] = $value;
	}

	/**
	 * Recupera o valor de uma opcao do array de configuracoes
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name
	 * @return mixed
	 */
	public function getOption( $name )
	{
		if( ! array_key_exists($name, $this->options['options']) )
		{
			Lumine_Log::warning('Opcao inexistente: ' . $name);
			$this->options['options'][ $name ] = null;
			return null;
		}

		return $this->options['options'][ $name ];
	}

	/**
	 * Importa classes para poderem ser utilizadas
	 *
	 * <code>
	 * $cfg = new Lumine_Configuration($lumineConf);
	 * $cfg->import('Pessoa','Carro','Bicicleta');
	 *
	 * // agora as classes ja podem ser usadas
	 * $obj = new Pessoa;
	 * $car = new Carro;
	 * </code>
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	public function import()
	{
		$list = func_get_args();
		$notfound = array();

		foreach($list as $className)
		{
			$arr_path = explode('.', $this->getProperty('package'));
			$ps = DIRECTORY_SEPARATOR;
			$path = $this->getProperty('class_path') . $ps . implode($ps, $arr_path) . $ps;

			$sufix = $this->getOption('class_sufix');

			if($sufix != null)
			{
				$sufix = '.' . $sufix;
			}

			$sufix = $sufix . '.php';
			$filename = $path . $className . $sufix;

			if( class_exists($className) )
			{
				Lumine_Log::debug('Classe ja existente: '.$className);
			}

			if( file_exists($filename) )
			{
				require_once $filename;

				if( ! class_exists($className) )
				{
					throw new Lumine_Exception('A classe '.$className.' nao existe no arquivo '.$filename);
				}

				Lumine_Log::debug('Classe carregada: '.$className);

				// faz o mapeamento da classe 
				$classDescriptor = ucfirst($this->getOption('classDescriptor'));
				if(!empty($classDescriptor)){
					$classDescriptor = 'Lumine_Descriptor_'.$classDescriptor;
					Lumine_Metadata::mapClass(new $classDescriptor($className, $filename), $this);
				}
				
			} else {
				$notfound[] = $className;
			}
		}
	}

	/**
	 * Carrega models para serem utilizadas com projetos em MVC
	 *
	 * <code>
	 * $cfg = new Lumine_Configuration($lumineConf);
	 * $cfg->loadModel('PessoaModel','CarroModel','BicicletaModel');
	 *
	 * // agora as classes ja podem ser usadas
	 * $obj = new PessoaModel;
	 * $car = new CarroModel;
	 * </code>
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	public function loadModel() {
		$list = func_get_args();
		$notfound = array();

		foreach($list as $className) {

			$ps = DIRECTORY_SEPARATOR;
			$path = $this->getProperty('class_path') . $ps . $this->getOption('model_path') . $ps;

			$sufix = $this->getOption('class_sufix');

			if($sufix != null) {
				$sufix = '.' . $sufix;
			}

			$sufix = $sufix . '.php';
			$filename = $path . $className . $sufix;

			if( class_exists($className) ) {
				Lumine_Log::debug('Model ja existente: '.$className);
				return;
			}

			if( file_exists($filename) ) {
				require_once $filename;

				if( ! class_exists($className) ) {
					throw new Lumine_Exception('A model '.$className.' nao existe no arquivo '.$filename);
				}

				Lumine_Log::debug('Model carregada: '.$className);

			} else {
				Lumine_Log::warning('Arquivo nao encontrado: '.$filename);
				$notfound[] = $className;
			}
		}
	}

	/**
	 * Exporta o schema para o banco
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	public function export()
	{
		$class = 'Lumine_Export_' . $this->options['dialect'];
		Lumine::load( $class );

		$reflection = new ReflectionClass( $class );
		$instance = $reflection->newInstance();
		$instance->export( $this );
	}

	/**
	 * Cria uma classe pelo nome da tabelaon the fly
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $tablename Nome da tabela
	 * @return Lumine_Factory
	 */
	public function factory($tablename){
		Lumine_Log::debug('Criando entidade para a tabela '.$tablename);
		return Lumine_Factory::create($tablename, $this);
	}
	
	/**
	 * Retorna a implementacao de cache utilizada
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Cache_Abstract
	 */
	public function getCacheImpl()
	{
	    return $this->cacheImpl;
	}

	/**
	 * Altera a implementacao de cache utilizada
	 * 
	 * @param Lumine_Cache_Abstract $cacheImpl
	 * @author Hugo Ferreira da Silva
	 */
	public function setCacheImpl(Lumine_Cache_Abstract $cacheImpl)
	{
	    $this->cacheImpl = $cacheImpl;
	}
}


