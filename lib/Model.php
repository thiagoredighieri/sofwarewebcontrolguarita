<?php
/**
 * **********************************************************************
 * Classe base para Model, em arquiteturas MVC
 * 
 * Todas as models da aplicacao devem extender esta classe base,
 * para que a integracao com Lumine seja feita.
 * 
 * No construtor da classe filha o objeto que a model representa
 * sera instanciado para utilizacao. 
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 * 
 * **********************************************************************
 */ 

/**
 * Classe abstrata para servir como base para Models
 * 
 * @author Hugo Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */
abstract class Lumine_Model extends Lumine_EventListener {
	
	/**
	 * Objeto que sera usado nos metodos padroes 
	 * @var Lumine_Base
	 */
	protected $obj;
	
	/**
	 * Numero de linhas encontradas
	 * @var int
	 */
	protected $rows = 0;
	
	/**
	 * Variaveis geradas dinamicamente
	 * @var array
	 */
	protected $vars = array();
	
	/**
	 * Tipos de eventos disparados
	 * @var array
	 */
	protected $_event_types  = array(
		Lumine_Event::PRE_INSERT,
		Lumine_Event::POS_INSERT,
		Lumine_Event::PRE_SAVE,
		Lumine_Event::POS_SAVE,
		Lumine_Event::PRE_GET,
		Lumine_Event::POS_GET,
		Lumine_Event::PRE_UPDATE,
		Lumine_Event::POS_UPDATE,
		Lumine_Event::PRE_DELETE,
		Lumine_Event::POS_DELETE,
		Lumine_Event::PRE_FIND,
		Lumine_Event::POS_FIND,
	);
	
	/**
	 * Inicia as configuracoes
	 * 
	 * <p>Sera sobrescrito nas classes filhas.<br>
	 * Utilizado primariamente para iniciar o objeto<br>
	 * de persistencia.</p>
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	public function __construct(){
		
		$this->obj->addEventListener(Lumine_Event::PRE_INSERT, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::POS_INSERT, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::PRE_GET, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::POS_GET, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::PRE_FIND, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::POS_FIND, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::PRE_SAVE, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::POS_SAVE, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::PRE_DELETE, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::POS_DELETE, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::PRE_UPDATE, array($this,'redispatchSQLEvent'));
		$this->obj->addEventListener(Lumine_Event::POS_UPDATE, array($this,'redispatchSQLEvent'));
		
		$this->_initialize();
		
	}

	/**
	 * Recupera um objeto pela chave primaria ou chave => valor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param mixed $pk Nome da chave ou valor
	 * @param mixed $pkValue Valor do campo
	 * @param boolean $allRows Se retorna todas as linhas ou somente uma
	 * @return array Dados encontrados em formato de array associativo
	 */
	public function get($pk, $pkValue=null, $allRows = false){
		$this->obj->reset();
		
		// se a chave (que agora eh o valor) for array
		if( is_array($pk) ){
			// pega a lista de chaves primarias
			$pks = $this->obj->metadata()->getPrimaryKeys();
			// pega a primeira
			$first = array_shift($pks);
			
			// faz a consulta
			$this->obj
				->where('{'.$first['name'].'} IN (?)', $pk)
				->find(true);
		
		// se o valor nao eh array
		} else if( !is_array($pkValue) ){
			// faz a consulta normal
			$this->obj->get($pk, $pkValue);
			
		// se o valor for array
		} else  {
			$this->obj
				->where('{' . $pk . '} IN (?)', $pkValue)
				->find(true);
		}
		
		// resultado
		$result = array();
		
		// se for para retornar todas as linhas
		if( $allRows ){
			// retorna o allToArray
			$result = $this->obj->allToArray();
			
		// do contrario
		} else {
			// retorna somente a primeira
			$result = $this->obj->toArray();
		}
		
		return $result;
	}
	
	/**
	 * Recupera uma lista de itens
	 * 
	 * Permite tambem que sejam passados parametros adicionais,
	 * onde o usuario praticamente pode fazer qualquer operacao,
	 * como se estivesse trabalhando com os DAO's diretamente.
	 * 
	 * Exemplode uso:
	 * 
	 * <code>
	 * # preferencias de pesquisa
	 * $prefs = array(
	 *   # clausula having
	 *   'having' => 'contador > 10',
	 *   # adiciona campos a selecao
	 *   'selectAdd' => 'count(idpessoa) as contador',
	 *   # seleciona SOMENTE os campos abaixo
	 *   'select' => 'c.campo1, c.campo2, p.campo1',
	 *   # agrupamento
	 *   'group' => 'c.idcategoria',
	 *   # join
	 *   'join' => array(
	 *   	array(
	 *        'class'=>'Categoria', # obrigatorio
	 *        'alias'=>'c',         # opcional, mas altamente recomendado
	 *        'type'=>'inner',      # obrigatorio
	 *        'fieldFrom'=>'idCategoria',  # opcional
	 *        'fieldTo'=>'idCategoria',    # opcional
	 *        'extra' => 'c.status = ?', #opcional
	 *        'extraArgs' => array(1)    # opcional, utilizado em conjunto com o "extra",
	 *      ),
	 *    ), 
	 *      
	 *    'whereFilters' => array(   # opcional, utilizado para alterar como os filtros where se comportam
	 *			'o.idendereco' => ' > ?',
	 * 			'o.logradouro' => ' like ?',
	 *			'l.numero' => ' in (?)'
	 *    ),
	 *      
	 *    'whereExtra' => 'o.condicao > ?', # opcional
	 *    'whereExtraArgs' => array(1000)   # opcional
	 * );
	 * 
	 * $filtros['p.nome'] = 'hugo';
	 * $filtros['c.idCategoria'] = 1;
	 * $orderBy = 'p.nome asc, c.nome asc';
	 * $offset = 0;
	 * $limit = 20;
	 * 
	 * $results = PessoaModel::getInstance($filtros, $orderBy, $offset, $limit, $prefs);
	 * 
	 * </code>
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param array $filters Filtros a serem usados
	 * @param string $order Ordenacao dos resultados
	 * @param int $offset Inicio dos resultados
	 * @param int $limit Limite de itens
	 * @param array $prefs Preferencias para busca
	 * @return array Lista de itens encontrados
	 */
	public function find(array $filters = array(), $order = '', $offset = null, $limit = null, array $prefs = array()){
		$this->obj->reset();
		$this->obj->alias('o');
		$this->obj->selectAs();
		// se indicou uma lista de join's
		if( isset($prefs['join']) && is_array($prefs['join']) ){
			// para cada item
			foreach($prefs['join'] as $item){
				// faz o join
				$this->makeJoins($this->obj, $item);
			}
		}
		
		$this->setFilters($filters, array_key_exists('whereFilters',$prefs) ? $prefs['whereFilters'] : array());
		
		// se definiu um whereExtra 
		if( array_key_exists('whereExtra', $prefs) && !empty($prefs['whereExtra']) ){
			// se definiu argumentos extra
			if( array_key_exists('whereExtraArgs', $prefs) && !empty($prefs['whereExtraArgs']) ){
				// cria a listagem de argumentos em conjunto com o extra
				$args = array($prefs['whereExtra']);
				$args = array_merge($args, $prefs['whereExtraArgs']);
				
				// chama o metodo where por reflexao
				$method = new ReflectionMethod($this->obj, 'where');
				$method->invokeArgs($this->obj, $args);
				
			// se nao definiu valores extras
			} else {
				$this->obj->where($prefs['whereExtra']);
			}
		}
		
		// conta os registros
		$this->rows = $this->obj->count( isset($prefs['countString']) ? $prefs['countString'] : '*' );
		
		// se informou o having
		if(isset($prefs['having'])){
			$this->obj->having($prefs['having']);
		}
		
		// se informou group by
		if(isset($prefs['group'])){
			$this->obj->group($prefs['group']);
		}
		
		// se informou select
		if( isset($prefs['select']) ){
			// se for um array
			if( is_array($prefs['select']) ){
				// une as colunas
				$this->obj->select(implode(', ', $prefs['select']));
				
			// se for string
			} else if(is_string($prefs['select'])) {
				$this->obj->select($prefs['select']);
			}
		}
		
		// se informou uma ordem
		if(!empty($order)){
			$this->obj->order($order);
		}
		
		// limita e executa a consulta
		$this->obj->limit($offset, $limit)
			->find();
		
		return $this->obj->allToArray();
	}
	
	/**
	 * 
	 * @link http://www.hufersil.com.br
	 * @author Hugo Ferreira da Silva
	 * @param array $filters Filtros que serao aplicados
	 * @return void
	 */
	protected function setFilters(array $filters, array $where = array()){
		foreach($filters as $key => $value){
			// iremos ignorar valores vazios
			if( $value === '' ){
				continue;
			}
			
			try {
				$target = $this->obj;
				$alias = $this->obj->alias();
				
				// se indicou o alias
				if( preg_match('@^(\w+)\.(\w+)$@', $key, $reg) ){
					$list = $this->obj->_getObjectPart('_join_list');
					// para cada item de classes unidas
					foreach($list as $class){
						// se encontrar o alias
						if($class->alias() == $reg[1]){
							$target = $class;
							$alias = $class->alias();
							$key = $reg[2];
							break;
						}
					}
				} 
				
				
				$field = $target->metadata()->getField($key);
				
				// se o valor for nulo
				if( is_null($value) ){
					// colocamos um IS NULL como condicao
					$this->obj->where($alias.'.'.$key .' IS NULL');
					continue;
				}
				
				// se o usuario informou uma forma de filtro
				if( array_key_exists($alias.'.'.$key, $where) ){
					// assim o usuario pode personalizar o filtro
					$this->obj->where($alias . '.' . $key . ' ' . $where[$alias.'.'.$key], $value);
					continue;
				}
				
				// se o valor for array
				if( is_array($value) ){
					$this->obj->where($alias.'.'.$key . ' IN (?)', $value);
					continue;
				}
				
				switch($field['type']){
					case 'char':
					case 'varchar':
					case 'text':
					case 'enum':
					case 'blob':
					case 'longblob':
					case 'tinyblob':
						$this->obj->where($alias.'.'.$key.' like ?', $value);
					break;
					
					// se nao for texto, nao fazemos por like
					// fazemos uma comparacao direta
					default:
						$this->obj->where($alias.'.'.$key.' = ?', $value);
				}
			
			} catch(Exception $e) {
				// quando o campo que a pessoa tentou pegar nao existe
				// eh disparada uma excecao, mas neste caso nao eh um erro
				// por isso capturamos a excecao para que nao de problemas para o usuario
			}
		}
	}
	
	
	/**
	 * Insere os dados no banco
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param array $data Dados a serem persistidos
	 * @return int Codigo do registro inserido
	 */
	public function insert(array $data){
		$this->obj->reset();
		$this->obj->populateFrom($data);
		$this->obj->insert();
		
		// pegamos a(s) chave(s) primaria(s)
		// so retornamos quando tem uma unica chave primaria
		$pk = $this->obj->metadata()->getPrimaryKeys();
		
		if(count($pk) == 1){
			$key = $pk[0]['name'];
			
			return $this->obj->$key;
		}
		
		return 0;
		
	}
	
	/**
	 * Salva os dados do registro
	 * 
	 * <p>Insere ou atualiza. <br>
	 * Caso exista a chave primaria, atualiza.<br>
	 * Se nao tiver, insere</p>
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param array $data Dados a serem persistidos
	 * @return int Codigo do registro salvo
	 */
	public function save(array $data){
		$this->obj->reset();
		$this->obj->populateFrom($data);
		$this->obj->save();
		
		// pegamos a(s) chave(s) primaria(s)
		// so retornamos quando tem uma unica chave primaria
		$pk = $this->obj->metadata()->getPrimaryKeys();
		
		if(count($pk) == 1){
			$key = $pk[0]['name'];
			
			return $this->obj->$key;
		}
		
		return 0;
	}
	
	/**
	 * Remove registros pelo id
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param int $id Codigo do registro a ser removido
	 * @return void
	 */
	public function delete($id){
		$this->obj->reset();
		
		// pegamos a(s) chave(s) primaria(s)
		// so removemos quando tem uma unica chave primaria
		$pk = $this->obj->metadata()->getPrimaryKeys();
		
		if(count($pk) == 1){
			$key = $pk[0]['name'];
			
			$this->obj->$key = $id;
			$this->obj->delete();
		}
	}
	
	/**
	 * Atualiza registros baseados pelo id
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param int $id Codigo do registro a ser atualizado
	 * @param array $data Dados a serem atualizados
	 * @return void
	 */
	public function update($id, array $data){
		$this->obj->reset();
		$this->obj->populateFrom($data);
		
		// pegamos a(s) chave(s) primaria(s)
		// so removemos quando tem uma unica chave primaria
		$pk = $this->obj->metadata()->getPrimaryKeys();
		
		if(count($pk) == 1){
			$key = $pk[0]['name'];
			
			$this->obj->$key = $id;
			$this->obj->update();
		}
	}
	
	/**
	 * Efetua um delete baseado em clausulas where
	 * 
	 * <p>Qualquer parametro depois de $clause sera usado como prepared statement
	 * para remocao dos dados.
	 * 
	 * Caso for usar prepared statement, colocar o alias do objeto como a letra "o" 
	 * </p>
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $clause condicao para remocao
	 * @return void
	 */
	public function deleteWhere($clause){
		$this->obj->reset();
		$this->obj->alias('o');
		$args = func_get_args();
		
		// se a pessoa passou parametros a mais do que a clausula
		if($args > 1){
			// entao eh prepared statement, chamamos o where com os argumentos
			call_user_func_array(array($this->obj,'where'), $args);
			
		} else {
			// NAO eh prepared statement, chamamos o where 
			$this->obj->where($clause);
		}
		
		$this->obj->delete(true);
	}
	
	/**
	 * Efetua um update baseado em clausulas where
	 * 
	 * <p>Qualquer parametro depois de $clause sera usado como prepared statement
	 * para atualizacao dos dados.
	 * Caso for usar prepared statement, colocar o alias do objeto como a letra "o" 
	 * </p>
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param array $data Dados a serem atualizados
	 * @param string $clause condicao para atualizacao
	 * @return void
	 */
	public function updateWhere(array $data, $clause){
		$this->obj->reset();
		$this->obj->populateFrom($data);
		$this->obj->alias('o');
		
		$args = func_get_args();
		array_shift($args);
		
		// se a pessoa passou parametros a mais do que a clausula
		if($args > 1){
			// entao eh prepared statement, chamamos o where com os argumentos
			call_user_func_array(array($this->obj,'where'), $args);
			
		} else {
			// NAO eh prepared statement, chamamos o where 
			$this->obj->where($clause);
		}
		
		$this->obj->update(true);
	}
	
	/**
	 * Adiciona uma validacao ao objeto
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $type Tipo de validacao
	 * @param string $field Nome do campo que tera a validacao
	 * @param mixed $msg Mensagem de erro quando houver problema, nome da funcao ou array com o objeto e metodo
	 * @param int $min Minimo de caracteres ou valor minimo
	 * @param int $max Maximo de caracteres ou valor maximo
	 * @return void
	 */
	public function addValidation($type, $field, $msg, $min=null, $max=null){
		Lumine_Validator_PHPValidator::addValidation($this->obj,$field,$type,$msg,$min,$max);
	}
	
	/**
	 * Valida se as entradas nos campos estao de acordo com as regras de validacao
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param array $data Dados a serem validados
	 * @return array Lista contendo os erros encontrados
	 */
	public function validate(array $data){
		$this->obj->reset();
		$this->obj->populateFrom($data);
		return $this->obj->validate();
	}
	
	/**
	 * Numero de linhas encontradas na ultima consulta
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return int
	 */
	public function rows(){
		return $this->rows;
	}
	
	/**
	 * Recupera os registros de um link
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @param string $linkName 
	 * @param mixed $pk Nome ou valor da chave 
	 * @param mixed $pkValue Valor da chave
	 * @return array Links encontrados
	 */
	public function fetchLink($linkName, $pk, $pkValue = null){
		$res = $this->get($pk, $pkValue);
		
		$res = $this->obj->fetchLink($linkName);
		
		$list = array();
		
		if( $res instanceof Lumine_Base ){
			$list[] = $res->toArray();
		} else {
			foreach($res as $item){
				$list[] = $item->toArray();
			}
		}
		
		return $list;
	}
	
	/**
	 * Remove os links de uma entidade 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @param string $linkName
	 * @param mixed $pk
	 * @param mixed $pkValue
	 * @return void
	 */
	public function removeLinks($linkName, $pk, $pkValue = null){
		$res = $this->get($pk, $pkValue);
		$this->obj->removeAll($linkName);
	}
	
	/**
	 * Remove os itens especificados 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @param string $linkName nome do link
	 * @param array $items codigos dos itens ou as instancias Lumine_Base
	 * @param mixed $pk nome ou valor da chave 
	 * @param mixed $pkValue valor da chave
	 * @return void
	 */
	public function remove($linkName, $items, $pk, $pkValue = null){
		$res = $this->get($pk, $pkValue);
		$this->obj->remove($linkName, $items);
	}
	
	/**
	 * Salva (insere) ou atualiza (update) um registro
	 * 
	 * Este metodo e um pouco diferente do que somente o save.
	 * Ele checa se existe uma chave primaria auto-incrementavel.
	 * Se existir, usa o save normal, se nao existir, ira buscar os
	 * registros com base nas chaves primarias encontradas e os valores
	 * encontrados nos dados informados para persistencia.
	 * 
	 * Se encontrar algum registro com as chaves informadas, ira atualizar,
	 * do contrario insere.
	 * 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @param $data
	 * @return unknown_type
	 */
	public function saveOrUpdate(array $data){
		$this->obj->reset();
		
		$pks = $this->obj->metadata()->getPrimaryKeys();
		$hasAutoInc = false;
		
		foreach($pks as $pk){
			if(!empty($pk['options']['autoincrement'])){
				$hasAutoInc = true;
				break;
			}
			$name = $pk['name'];
			$this->obj->$name = array_key_exists($name, $data) ? $data[ $name ] : null;
		}
		
		if( $hasAutoInc ){
			return $this->save($data);
		}
		
		$total = $this->obj->count();
		
		$this->obj->populateFrom($data);
		
		if( $total == 0 ){
			$this->obj->insert();
		} else {
			$this->obj->save();
		}
		
		return 0;
	}
	
	/**
	 * Set implicito
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $key
	 * @param mixed $val
	 * @return void
	 */
	public function __set($key, $val){
		$this->vars[$key] = $val;
	}
	
	/**
	 * Get implicito
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param $key
	 * @return mixed
	 */
	public function __get($key){
		if(!isset($this->vars[$key])){
			return null;
		}
		
		return $this->vars[$key];
	}
	
	/**
	 * Redispara eventos
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param Lumine_Events_SQLEvent $e
	 * @return void
	 */
	public function redispatchSQLEvent(Lumine_Events_SQLEvent $e){
		$this->dispatchEvent($e);
	}
	
	/**
	 * Inicializador
	 * 
	 * <p>Metodo utilitario chamado no construtor.
	 * Se o usuario precisar de algo que seja feito na construcao do objeto,
	 * basta sobrecarregar este metodo na classe nova, assim nao perde
	 * nada com a engenharia reversa</p>
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	protected function _initialize(){
		
	}
	
	/**
	 * Realiza os joins informados em uma model
	 * 
	 * Quando chamar o metodo find, o usuario podera informar que uma
	 * classe une com a outra, em varios niveis.
	 * 
	 * Este metodo auxilia para poder fazer as unioes de forma recursiva,
	 * para nao ter limite de unioes de classe.
	 * 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @param Lumine_Base $base Arquivo que tera as unioes incluidas
	 * @param array $config Configuracoes de preferencia do find
	 * @return void
	 */
	protected function makeJoins(Lumine_Base $base, array $config){
		// se informou o nome da classe
		if(isset($config['class'])){
			// importamos a classe
			$base->_getConfiguration()->import($config['class']);
			// reflexao
			$ref = new ReflectionClass($config['class']);
			$target = $ref->newInstance();
			
			// se indicou um alias
			if(isset($config['alias'])){
				$target->alias($config['alias']);
			}
			
			// se tiver join dentro dele
			if( !empty($config['join']) ){
				foreach($config['join'] as $join){
					// faz os join's aninhados
					$this->makeJoins($target, $join);
				}
			}
			
			
			// tipo de uniao
			$joinType = isset($config['type']) ? $config['type'] : 'INNER';
			
			// se indicou os campos de uniao
			if(isset($config['fieldFrom']) && isset($config['fieldTo'])){
				// se indicou um extra
				if( isset($config['extra']) ){
					// unimos as classes
					$base->join($target, $joinType, $target->alias(), $config['fieldFrom'], $config['fieldTo'], $config['extra'], isset($config['extraArgs']) ? $config['extraArgs'] : '');
				// se nao indicou extra
				} else {
					// une as classes sem extra
					$base->join($target, $joinType, $target->alias(), $config['fieldFrom'], $config['fieldTo']);
				}
			// se nao indicou os campos mas indicou extra
			} else if(isset($config['extra'])) {
				// une as classes sem indicar os campos, mas indica os argumentos extras
				$base->join($target, $joinType, $target->alias(), null, null, $config['extra'], isset($config['extraArgs']) ? $config['extraArgs'] : '');
			// une as classes
			} else {
				$base->join($target, $joinType, $target->alias());
			}
			
			// se indicou alias
			if( isset($config['alias']) ){
				// muda o selectAs
				$base->selectAs($target, $config['alias'].'%s');
			}
		}
	}
}


