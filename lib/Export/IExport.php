<?php
/**
 * Classe abstrata para servir de base para exportacao das entidades para o banco
 * 
 * @package Lumine_Export
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

/**
 * Classe abstrata para servir de base para exportacao das entidades para o banco
 * 
 * @package Lumine_Export
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
abstract class Lumine_Export_IExport {
	/**
	 * tabelas
	 * @var array
	 */
	protected $tables      = array();
	/**
	 * Indices
	 * @var array
	 */
	protected $indexes     = array();
	/**
	 * Chaves estrageiras
	 * @var array
	 */
	protected $foreignKeys = array();
	/**
	 * Conexao
	 * @var Lumine_Connection_IConnection
	 */
	protected $cnn;
	/**
	 * Configuracao
	 * @var Lumine_Configuration
	 */
	protected $cfg;
	/**
	 * Lista de arquivos
	 * @var array
	 */
	protected $fileList = array();
	/**
	 * Lista de classes
	 * @var array
	 */
	protected $classList = array();
	/**
	 * indicacao se ja foi carregado a lista de classes
	 * @var boolean
	 */
	protected $loaded = false;
	
	/**
	 * Inicia a exportacao para o banco
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Configuration $cfg
	 * @return void
	 */
	public function export(Lumine_Configuration $cfg)
	{
		$this->cfg = $cfg;
		$this->cnn = $cfg->getConnection();

		$this->create();
	}
	
	/**
	 * Efetua a criacao das tabelas no banco
	 * Deve ser especializado pela sub-classe
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	protected function create()
	{
	}

	/**
	 * Recupera as definicoes de tabelas a serem criadas
	 * Deve ser especializado pela sub-classe
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */	
	protected function getTablesDefinition()
	{
	}
	
	/**
	 * Recupera os indices a serem criados. 
	 * Deve ser especializado pela sub-classe
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	protected function getIndexes()
	{
	}
	
	/**
	 * Carrega a lista de arquivos e classes instanciadas da configuracao indicada
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	protected function loadClassFileList ()
	{
		if( $this->loaded == true )
		{
			return;
		}
		
		$this->loaded = true;
		
		$dir = $this->cfg->getProperty('class_path') . DIRECTORY_SEPARATOR;
		$dir .= str_replace('.', DIRECTORY_SEPARATOR, $this->cfg->getProperty('package'));
		$dir .= DIRECTORY_SEPARATOR;
		
		if( is_dir($dir) )
		{
			$dh = opendir($dir);
			
			while( ($file=readdir($dh)) !== false )
			{
				if( preg_match('@\.php$@', $file) )
				{
					$className = str_replace('.php', '', $file );
					$this->cfg->import( $className );
					
					if( class_exists($className) )
					{
						$oReflection = new ReflectionClass( $className );
						$oClass = $oReflection->newInstance();
						
						if( $oClass instanceof Lumine_Base )
						{
							$this->fileList[] = $dir . $file;
							$this->classList[ $className ] = $oClass;
						} else {
							unset($oClass);
						}
						
						unset($oReflection);
					}
				}
			}
		}
	}

	/**
	 * Este metodo so recupera as referencias de chaves estrangeiras, quem vai gerar e o metodo especializado create
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	protected function getForeignKeys()
	{
		$this->loadClassFileList();

		$tmp = array();
		
		foreach( $this->classList as $obj )
		{
			$list = $obj->metadata()->getRelations();
			
			foreach( $list as $fk )
			{
				if( $fk['type'] == Lumine_Metadata::MANY_TO_ONE )
				{
					$foreign = $this->classList[ $fk['class'] ];
					$field = $foreign->metadata()->getField( $fk['linkOn'] );
					
					$this->foreignKeys[] = array(
						'table' => $obj->metadata()->getTablename(),
						'column' => $fk['column'],
						'reftable' => $foreign->metadata()->getTablename(),
						'refcolumn' => $field['column'],
						'onUpdate' => $fk['onUpdate'],
						'onDelete' => $fk['onDelete']
					);
				}
			}
		}
	}
	
}

?>