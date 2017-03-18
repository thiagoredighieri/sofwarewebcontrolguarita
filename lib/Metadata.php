<?php

/**
 * Classe de metadados de uma entidade
 */
class Lumine_Metadata extends Lumine_EventListener {
	/**
	 * indica que a relacao é de um para muitos
	 * @var int
	 */
	const ONE_TO_MANY = 1;
	/**
	 * indica que a relacao é de muitos para um
	 * @var int
	 */
	const MANY_TO_ONE = 2;
	/**
	 * indica que a relacao é de muitos para muitos
	 * @var int
	 */
	const MANY_TO_MANY = 3;

	/**
	 * Classes mapeadas
	 * @var array
	 */
	private static $mappedClasses = array();
	/**
	 * Nome desta classe
	 * @var string
	 */
	protected $classname = '';
	/**
	 * Nome da tabela que esta classe representa
	 * @var string
	 */
	protected $tablename = null;
	/**
	 * Nome do pacote
	 * @var string
	 */
	protected $package = null;
	/**
	 * Campos desta classe
	 * @var array
	 */
	protected $fields = array();
	/**
	 * Relacionamentos desta classe
	 * @var array
	 */
	protected $relations = array();
	/**
	 * Campos desta classe, indexados pelo nome da coluna
	 * @var array
	 */
	protected $fieldsByColumn = array();

	/**
	 * Construtor
	 *
	 * @param Lumine_Base $target
	 * @author Hugo Ferreira da Silva
	 */
	public function __construct(Lumine_Base $target) {
		$this->classname = get_class($target);
		
		if($this->isMapped()){
			$this->setDefaultMapping();
		}
	}

	/**
	 * Efetua o mapeamento da classe.
	 *
	 * @param Lumine_Base $target
	 * @author Hugo Ferreira da Silva
	 */
	public static function mapClass(Lumine_Descriptor_AbstractDescriptor $descriptor, Lumine_Configuration $config) {
		if(!isset(self::$mappedClasses[$descriptor->getClassname()])){
			$cache = $config->getCacheImpl();
			$key = 'lumine:map:'.$descriptor->getClassname();
			
			if($cache->exists($key)){
				$classmap = $cache->fetch($key);
				
				if($classmap['time'] != $descriptor->getModificationTime()){
					$classmap = $descriptor->parse();
					$cache->store($key, $classmap);
				}
				
			} else {
				$classmap = $descriptor->parse();
				$cache->store($key, $classmap);
			}
			
			self::$mappedClasses[$descriptor->getClassname()] = $classmap;
		}
	}

	/**
	 * Recupera o nome da classe
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	public function getClassname() {
		return $this->classname;
	}

	/**
	 * Recupera uma lista de chaves primarias
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getPrimaryKeys() {
		$pks = array();

		reset($this->fields);

		foreach ($this->fields as $name => $def) {
			if (!empty($def['options']['primary'])) {
				$def['name'] = $name;
				$pks[] = $def;
			}
		}

		return $pks;
	}

	/**
	 * Recupera as propriedades
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Recupera as propriedades indexadas pela coluna
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getFieldsByColumn() {
		return $this->fieldsByColumn;
	}

	/**
	 * Recupera todas as definições
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getFullDefinition() {
		return array_merge($this->fields, $this->relations);
	}

	/**
	 * Recupera os relacionamentos
	 * 
	 * @param boolean $includeManyToOne
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getRelations($includeManyToOne = TRUE) {
		$manyToOne = array();

		if ($includeManyToOne) {
			foreach ($this->fields as $name => $field) {
				if (!empty($field['options']['foreign'])) {
					$field['type'] = self::MANY_TO_ONE;
					$field['name'] = $name;
					$manyToOne[] = $field;
				}
			}
		}

		return array_merge($manyToOne, $this->relations);
	}

	/**
	 * Recupera as definicoes de um relacionamento
	 * 
	 * @param array $entity
	 * @param int $type
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getRelation($entity, $type = NULL) {
		foreach ($this->fields as $name => $prop) {
			if (!empty($prop['options']['foreign']) && !empty($prop['options']['class']) && $prop['options']['class'] == $entity) {
				if (is_null($type) || $type == self::MANY_TO_ONE) {
					$opt = $prop['options'];
					$opt['name'] = $name;
					$opt['type'] = self::MANY_TO_ONE;
					return $opt;
				}
			}
		}

		foreach ($this->relations as $name => $options) {
			if ($options['class'] == $entity && (is_null($type) || $type == $options['type'])) {
				$opt = $options;
				$opt['name'] = $name;
				return $opt;
			}
		}

		return null;
	}

	/**
	 * Recupera as definicoes de uma propriedade pelo nome
	 * 
	 * @param array $name
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getField($name) {
		if (isset($this->fields[$name])) {
			return array_merge(array('name' => $name), $this->fields[$name]);
		}

		if (isset($this->relations[$name])) {
			return array_merge(array('name' => $name), $this->relations[$name]);
		}

		throw new Lumine_Exception('O campo ' . $name . ' nao foi encontrado em ' . $this->getClassname(), Lumine_Exception::ERROR);
	}

	/**
	 * Recupera as definicoes de uma propriedade pelo nome do campo
	 * 
	 * @param array $column
	 * @author Hugo Ferreira da Silva
	 * @return array
	 */
	public function getFieldByColumn($column) {
		if (isset($this->fieldsByColumn[$column])) {
			return $this->fieldsByColumn[$column];
		}

		return null;
	}

	/**
	 * Recupera o nome da tabela
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	public function getTablename() {
		return $this->tablename;
	}

	/**
	 * Altera o nome da tabela
	 * 
	 * @param string $tablename
	 * @author Hugo Ferreira da Silva
	 */
	public function setTablename($tablename) {
		$this->tablename = $tablename;
	}

	/**
	 * Recupera o pacote
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	public function getPackage() {
		return $this->package;
	}

	/**
	 * Altera o pacote
	 * 
	 * @param string $package
	 * @author Hugo Ferreira da Silva
	 */
	public function setPackage($package) {
		$this->package = $package;
	}

	/**
	 * Adiciona um campo ao mapeamento
	 * 
	 * @param string $name Nome do campo
	 * @param string $column Nome da coluna no banco
	 * @param string $type Tipo do campo
	 * @param int $length Comprimento do campo
	 * @param array $options Opções do campo
	 * @throws Lumine_Exception
	 * @author Hugo Ferreira da Silva
	 */
	public function addField($name, $column, $type, $length, array $options) {
		if (!isset($this->fields[$name])) {
			$this->fields[$name]['column'] = $column;
			$this->fields[$name]['type'] = $type;
			$this->fields[$name]['length'] = $length;
			$this->fields[$name]['options'] = $options;

			if (isset($options['primary']) && $options['primary'] == true) {
				$this->fields[$name]['primary'] = true;
			}

		} else {
			throw new Lumine_Exception('Uma classe nao pode conter campos duplicados (' . $name . ').', Lumine_Exception::ERROR);
		}


		if (!isset($this->fieldsByColumn[$column])) {
			$this->fieldsByColumn[$column]['column'] = $column;
			$this->fieldsByColumn[$column]['name'] = $name;
			$this->fieldsByColumn[$column]['type'] = $type;
			$this->fieldsByColumn[$column]['length'] = $length;
			$this->fieldsByColumn[$column]['options'] = $options;

			if (isset($options['primary']) && $options['primary'] == true) {
				$this->fieldsByColumn[$column]['primary'] = true;
			}

		} else {
			throw new Lumine_Exception('Uma classe nao pode conter colunas duplicadas (' . $column . ').', Lumine_Exception::ERROR);
		}

		return $this;
	}

	/**
	 * Adiciona um relacionamento
	 * 
	 * @param string $name Nome do relacionamento
	 * @param int $type Tipo de relacionamento
	 * @param string $class Nome da classe de relacionamento
	 * @param string $linkOn Nome do campo para relacionamento
	 * @param string $table Nome da tabela, em caso de many-to-many
	 * @param string $column Nome da coluna, em caso de many-to-many
	 * @param boolean $lazy Indica se os objetos relacionados serão carregados 
	 * @throws Lumine_Exception
	 * @author Hugo Ferreira da Silva
	 */
	public function addRelation($name, $type, $class, $linkOn, $table = null, $column = null, $lazy = false) {
		if (!isset($this->relations[$name])) {
			switch ($type) {
				case self::ONE_TO_MANY:
					$this->relations[$name]['type'] = $type;
					$this->relations[$name]['class'] = $class;
					$this->relations[$name]['linkOn'] = $linkOn;
					$this->relations[$name]['lazy'] = $lazy;
					break;

				case self::MANY_TO_MANY:
					$this->relations[$name]['type'] = $type;
					$this->relations[$name]['class'] = $class;
					$this->relations[$name]['linkOn'] = $linkOn;
					$this->relations[$name]['table'] = $table;
					$this->relations[$name]['column'] = $column;
					$this->relations[$name]['lazy'] = $lazy;
					break;
				default:
					throw new Lumine_Exception('Tipo nao suportado:' . $type, Lumine_Exception::ERROR);
			}
		} else {
			throw new Lumine_Exception('Uma classe nao pode conter campos duplicados (' . $name . ').', Lumine_Exception::ERROR);
		}

		return $this;
	}

	/**
	 * altera uma opcao de um campo desejado
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name nome do campo desejado
	 * @param string $option nome da opcao desejada
	 * @param mixed $value novo valor
	 * @return Lumine_Base
	 */
	public function setFieldOption($name, $option, $value) {
		foreach ($this->fields as $fldname => $def) {
			if ($fldname == $name) {
				$this->fields[$fldname]['options'][$option] = $value;
			}
		}

		foreach ($this->relations as $fldname => $def) {
			if ($fldname == $name) {
				$this->relations[$fldname][$option] = $value;
			}
		}

		return $this;
	}

	/**
	 * recupera uma opcao de um campo desejado
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name nome do campo desejado
	 * @param string $option nome da opcao desejada
	 * @return mixed Valor da opcao
	 */
	public function getFieldOption($name, $option) {
		try {
			$fld = $this->getField($name);
			if (isset($fld['options'][$option])) {
				return $fld['options'][$option];
			} else if (isset($fld[$option])) {
				return $fld[$option];
			}
		} catch (Exception $e) {
			Lumine_Log::debug($e->getMessage());
		}
		return null;
	}
	
	/**
	 * Verifica se esta classe ja foi mapeada anteriormente
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return boolean
	 */
	public function isMapped(){
		return array_key_exists($this->getClassname(), self::$mappedClasses);
	}
	
	/**
	 * Preenche este objeto com o mapeamento padrão da classe informada 
	 * 
	 * @author Hugo Ferreira da Silva
	 */
	public function setDefaultMapping(){
		if(!$this->isMapped()){
			throw new Lumine_Exception('Classe nao mapeada: '.$this->getClassname());
		}
		
		$map = self::$mappedClasses[$this->getClassname()];
		
		if(!empty($map)){
			$this->setTablename($map['tablename']);
			$this->setPackage($map['package']);
			
			foreach($map['fields'] as $field) {
				call_user_func_array(array($this,'addField'), $field);
			}
			
			foreach($map['relations'] as $relation) {
				call_user_func_array(array($this,'addRelation'), $relation);
			}
		}
	}

}