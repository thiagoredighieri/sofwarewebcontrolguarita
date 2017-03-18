<?php

/**
 * Classe principal para as entidades. 
 * Todas as classes de interace com o banco devem extender esta classe
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine
 */
Lumine::load('Sequence', 'Lumine_Dialect_Factory');

/**
 * Classe principal
 *
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 */
class Lumine_Base extends Lumine_EventListener implements Iterator {
    const BASE_CLASS = 'Lumine_Base';

    const STATE_TRANSIENT = 10;
    const STATE_PERSISTENT = 11;

    const WHERE_ADD_ONLY = 30;

    const SQL_SELECT = 40;
    const SQL_SELECT_COUNT = 41;
    const SQL_UPDATE = 42;
    const SQL_INSERT = 43;
    const SQL_DELETE = 44;
    const SQL_MULTI_INSERT = 45;

    const FETCH_ROW = 50;
    const FETCH_ASSOC = 51;
    const FETCH_BOTH = 52;

    /**
     * contagem interna de objetos criados
     * @var int
     */
    protected static $_objectCount = 0;

    /**
     * ID interno do objeto
     * @var int
     */
    protected $_objectID;

    /**
     * Armazena as configuracoes
     * @var Lumine_Configuration
     */
    protected $_config;
    
    /**
     * Metadados da classe
     * @var Lumine_Metadata
     */
    protected $_metadata;

    /**
     * Guarda as propriedades setadas estaticamente
     * @var array
     */
    protected $_staticFieldOptions = array();

    /**
     * Faz a ponte com o dialeto
     * @var Lumine_Dialect_IDialect
     */
    protected $_bridge = null;

    /**
     * Lista de classes que foram utilizadas no inner|left|right join
     * @var array
     */
    protected $_join_list = array();

    /**
     * Alias para a tabela
     * @var string
     */
    protected $_alias = '';

    /**
     * Campos a serem selecionados (data select)
     * @var array
     */
    protected $_data = array();

    /**
     * tabelas para consulta (FROM)
     * @var array
     */
    protected $_from = array();

    /**
     * condicoes de pesquisa
     * @var array
     */
    protected $_where = array();

    /**
     * Clausula having
     * @var array
     */
    protected $_having = array();

    /**
     * Clausula order by
     * @var array
     */
    protected $_order = array();

    /**
     * Clausula de agrupamento
     * @var array
     */
    protected $_group = array();

    /**
     * lista de strings de uniao das classes
     * @var array
     */
    protected $_join = array();

    /**
     * Limite de registros em uma consulta
     * @var int
     */
    protected $_limit = null;

    /**
     * Inicio dos registros em uma consulta com limit
     * @var int
     */
    protected $_offset = null;

    /**
     * Modo do resultado 
     * @var int
     */
    protected $_fetch_mode = self::FETCH_ASSOC;

    /**
     * Armazena os dados da linha atual
     * @var array
     */
    protected $_dataholder = array();

    /**
     * Armazena os dados originais da linha atual
     * @var array
     */
    protected $_original_dataholder = array();

    /**
     * Formatadores de campos
     * @var array
     */
    protected $_formatters = array();

    /**
     * Ponteiro para Iterator, para utilizar com foreach
     * @var int
     */
    protected $_iteratorPosition = 0;

    /**
     * Expressoes utilizadas em updates
     * @var array
     */
    protected $_updateExpressions = array();
    
    /**
     * Validadores para esta classe
     * @var array
     */
    protected $_validators = array();

    /**
     * Eventos disparados por esta classe
     * @var array
     */
    protected $_event_types = array(
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
        Lumine_Event::PRE_FETCH,
        Lumine_Event::POS_FETCH,
        Lumine_Event::PRE_SELECT,
        Lumine_Event::POS_SELECT,
        Lumine_Event::PRE_QUERY,
        Lumine_Event::POS_QUERY,
        Lumine_Event::PRE_FORMAT,
        Lumine_Event::POS_FORMAT,
        Lumine_Event::PRE_MULTI_INSERT,
        Lumine_Event::POS_MULTI_INSERT
    );
    // devem ser sobrecarregados para carregamento correto
    /**
     * Armazena a lista de multi-inserts
     * @var array
     */
    protected $_multiInsertList = array();

    /**
     * armazena as definicoes da classe feita pelo usuario
     * @var array
     */
    protected $_classDefinition = array();

    /**
     * armazena os nomes dos metodos da classe
     * @var array
     */
    protected $_classMethods = array();

    /**
     * Construtor da classe
     * 
     * Devera ser instanciado diretamente, e se a classe filha tiver um construtor
     * devera chamar este para que funcione corretamente.
     * 
     * @author Hugo Ferreira da Silva
     */
    function __construct($data = null) {
        // id deste objeto
        $this->_objectID = ++self::$_objectCount;

        $cm = Lumine_ConnectionManager::getInstance();

        if(empty($this->_metadata)){
            $this->_metadata = new Lumine_Metadata($this);
        }
        
        $this->_initialize();
        $this->_join_list[] = $this;
        $this->_from[] = $this;
        //$this->_bridge      = new $class_dialect( $this );
        // varre as chaves primarias em busca de classes pais
        $this->_joinSubClasses($this);

        if ($this->metadata()->getPackage() === null || $this->metadata()->getTablename() === null) {
            throw new Lumine_Exception('Voce nao pode acessar esta classe diretamente.', Lumine_Exception::ERROR);
        }
        
        $this->_getConfiguration()->dispatchEvent(
                new Lumine_Events_ConfigurationEvent(Lumine_Event::CREATE_OBJECT, $this->_getConfiguration(), $this)
        );

        // se informou algum dado na inicializacao
        if (!empty($data)) {
            // seta os dados
            $this->populateFrom($data);
        }
    }

    /**
     * Destrutor da classe
     * @see Lumine_Base::__destruct()
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return void
     */
    public function destroy() {
        // procura os itens relacionados na lista de estrangeiros
        if (count($this->metadata()->getRelations()) > 0) {
            foreach ($this->metadata()->getRelations() as $key => $name) {
                if (!empty($this->$key)) {
                    foreach ($this->$key as $item) {
                        if ($item instanceof Lumine_Base) {
                            $item->destroy();
                        }
                    }
                }
            }
        }

        // procura outros objetos que tenham referencia deste, e remove de la
        while (!empty($this->_join_list)) {
            $item = array_pop($this->_join_list);

            if ($item !== $this) {
                $item->_removeFromJoin($this);
                $item->destroy();
            }
        }
        $this->__destruct();
    }

    /**
     * @see Lumine_EventListener::__destruct()
     */
    public function __destruct() {
        $this->_join_list = array();
        $this->_from = array();

        $list = get_object_vars($this);
        //print_r($list);

        foreach ($list as $key => $val) {
            if (isset($this->$key)) {
                unset($this->$key);
            }
        }

        $list = array();

        parent::__destruct();
    }
    
    /**
     * Retorna o objeto de metadados 
     * @return Lumine_Metadata
     */
    public function metadata(){
        return $this->_metadata;
    }

    //----------------------------------------------------------------------//
    // metodos publicos
    //----------------------------------------------------------------------//
    /**
     * Recupera registros a partir da chave primaria ou chave = valor
     * <code>
     * $obj = new Pessoa;
     * $obj->get(1); // somente pela chave primaria
     * $obj->get('email','eu@hufersil.com.br'); // campo e valor
     * </code>
     * @param mixed $pk Valor da chave primaria ou nome do membro a ser pesquisado
     * @param mixed $pkValue Valor do campo quando pesquisado por um campo em especifico
     * @author Hugo Ferreira da Silva
     * @return int Numero de registros encontrados
     */
    public function get($pk, $pkValue = null) {
        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_GET, $this));

        if (!empty($pk) && !empty($pkValue)) {
            $field = $this->metadata()->getField($pk);
            $this->$field['name'] = $pkValue;
            $this->find(true);
            return $this->_getDialect()->num_rows();
        } else if (!empty($pk)) {
            $list = $this->metadata()->getPrimaryKeys();

            if (empty($list)) {
                Lumine_Log::warning('A entidade ' . $this->metadata()->getClassname() . '  possui chave primaria. Especifique um campo.');
                return 0;
            }

            $this->$list[0]['name'] = $pk;
            $this->find(true);

            $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_GET, $this));
            return $this->_getDialect()->num_rows();
        }
        Lumine_Log::warning('Nenhum valor informado para recuperacao em ' . $this->metadata()->getClassname());
    }

    /**
     * Efetua uma consulta a partir dos valores dos membros
     * <code>
     * var $pessoa = new Pessoa;
     * $pessoa->email = 'eu@hufersil.com.br';
     * $pessoa->find();
     * </code>
     * Gerara 
     * <code>
     * SELECT pessoa.nome, pessoa.email, pessoa.codpessoa, pessoa.data_cadastro FROM pessoa WHERE pessoa.email = 'eu@hufersil.com.br'
     * </code>
     *
     * @param boolean $auto_fetch Ir para o primeiro registro assim que finalizado
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine 
     * @return int Numero de registros encontrados
     */
    public function find($auto_fetch = false) {
        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_FIND, $this));

        $sql = $this->_getSQL(self::SQL_SELECT);
        $result = $this->_execute($sql);

        if ($result == true) {
            if ($auto_fetch == true) {
                $this->fetch();
            }
        }

        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_FIND, $this));

        return $this->_getDialect()->num_rows();
    }

    /**
     * Move o cursor para o proximo registro
     *
     * @param boolean $getLinks Recuperar automaticamente os links do tipo Lazy
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return boolean True se existir registros, do contrario false
     */
    public function fetch($getLinks = true) {
        $this->dispatchEvent(new Lumine_Events_IteratorEvent(Lumine_Event::PRE_FETCH, $this));
        $result = $this->_getDialect()->fetch();

        if ($result === false) {
            return false;
        }

        $this->_cleanFields();
        $this->_dataholder = array();
        $this->_original_dataholder = array();

        foreach ($result as $key => $val) {
            $def = $this->metadata()->getFieldByColumn($key);
            if (!empty($def)) {
                $key = $def['name'];
                $val = $this->_getConnection()->toPHPValue($val, $def);
            }

            $this->_dataholder[$key] = $val;
            $this->$key = $val;
            $this->_original_dataholder[$key] = $val;
        }
        // agora rodamos os relacionamentos para setar 
        foreach ($this->metadata()->getRelations(FALSE) as $name => $def) {
            // se esta chave estrangeira  tiver valores, 
            // colocamos como array vazio
            if (!isset($this->_dataholder[$name]) || is_null($this->_dataholder[$name])) {
                $this->setFieldValue($name, array());
            }
        }

        $this->loadLazy();

        $this->dispatchEvent(new Lumine_Events_IteratorEvent(Lumine_Event::POS_FETCH, $this));

        return true;
    }

    /**
     * Retorna os valores de uma linha especifica
     * @author Hugo Ferreira da Silva
     * @param int $idx Numero do registro
     * @return boolean 
     */
    public function fetch_row($idx) {
        $idx = sprintf('%d', $idx);
        if ($idx < 0) {
            $idx = 0;
        }

        if ($idx > $this->numrows() || $this->numrows() == 0) {
            Lumine_Log::debug('Numero de registro inexistente ' . $idx);
            return false;
        }

        Lumine_Log::debug('Indo para a linha ' . $idx);
        if ($idx == 0) {
            $this->_getDialect()->moveFirst();
        } else {
            $this->_getDialect()->fetch_row($idx);
        }

        return $this->fetch();
    }

    /**
     * Numero de registros encontrados na ultima consulta
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine Lumine - Mapeamento para banco de dados em PHP
     * @return int Numero de registros encontrados
     */
    public function numrows() {
        return $this->_getDialect()->num_rows();
    }

    /**
     * Numero de linhas afetadas apos um UPDATE ou DELETE
     *
     * <code>
     * $obj = new Pessoa;
     * $obj->nome = 'hugo';
     * $obj->where('1=1')->update();
     * echo $obj->affected_rows();
     * </code>
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return int Numero de linhas afetadas
     */
    public function affected_rows() {
        return $this->_getDialect()->affected_rows();
    }

    /**
     * Efetua uma consulta de contagem 
     *
     * <code>
     * $obj->count();
     * </code>
     * Ira produzir
     * <code>
     * SELECT count(*) FROM tabela
     * </code>
     * @param string $what coluna ou condicionamento desejado para efetuar a contagem
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return int Numero de registros 
     */
    public function count($what = '*') {
        $ds = $this->_getDialect()->getDataset();

        $sql = $this->_prepareSQL(true, $what);
        $res = $this->_execute($sql);

        if ($res == true) {
            $total = $this->_getDialect()->fetch();
            $this->_getDialect()->setDataset($ds);

            return $total['lumine_count'];
        }

        $this->_getDialect()->setDataset($ds);
        return 0;
    }

    /**
     * Indica quais campos deverao ser selecionados em uma consulta (SELECT)
     *
     * <code>
     * $obj->select('nome, data_nascimento, codpessoa);
     * $obj->find();
     * // SELECT nome, data_nascimento, codpessoa FROM pessoas
     * </code>
     * @param string $data String contendo os valores a serem selecionados
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base A propria classe
     */
    public function select($data = null) {
        if (is_null($data)) {
            $this->_data = array();
        } else {

            // pega todos os argumentos
            $args = func_get_args();
            // se tiver dados para prepared statement
            if (count($args) > 1) {
                // remove o primeiro arg
                array_shift($args);
                $data = Lumine_Parser::parsePart($this, $data, $args);
            }

            $parts = Lumine_Tokenizer::dataSelect($data, $this);
            $this->_data = array_merge($this->_data, $parts);
        }

        return $this;
    }

    /**
     * Adiciona a selecao de campos de outra classe permitindo alterar seu padrao para  mesclar
     *
     * <code>
     * $pessoa = new Pessoa;
     * $carro = new Carro;
     * $pessoa->join($carro);
     * $pessoa->selectAs($carro, '%s_carro');
     * $obj->find();
     * // SELECT pessoa.nome, pessoa.data_nascimento, pessoa.codpessoa, carro.nome as nome_carro, carro.modelo_carro FROM pessoa inner join carro on (pessoa.idpessoa=carro.idpessoa)
     * </code>
     * @param string $data String contendo os valores a serem selecionados
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base A propria classe
     */
    public function selectAs(Lumine_Base $obj = null, $format = '%s') {
        if (empty($obj)) {
            $obj = $this;
        }

        $list = $obj->metadata()->getFields();
        $objName = $obj->metadata()->getClassname();
        $alias = $obj->alias();

        foreach ($list as $name => $options) {
            if (empty($alias)) {
                $this->_data[] = sprintf('{%s.%s} as "' . $format . '"', $objName, $name, $name);
            } else {
                $this->_data[] = sprintf('%s.%s as "' . $format . '"', $alias, $name, $name);
            }
        }
        return $this;
    }

    /**
     * Adiciona uma classe (tabela) a lista de selecao (SELECT .. FROM tabela1, tabela2)
     *
     * <code>
     * $car = new Carro;
     * $pes = new Pessoa;
     * $pes->from($car);
     * // SELECT * FROM pessoa, carro
     * </code>
     * @param Lumine_Base $obj Objeto para uniao
     * @param strin $alias Alias para a tabela de uniao
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base O proprio objeto
     */
    public function from(Lumine_Base $obj = null, $alias = null) {
        if (is_null($obj)) {
            $this->_from = array($this);
        } else {
            if (!empty($alias)) {
                $obj->alias($alias);
            }
            $this->_from[] = $obj;
            // adiciona tamb�m na lista de join's
            $list = $obj->_getObjectPart('_join_list');
            foreach ($list as $ent) {
                $add = true;
                foreach ($this->_join_list as $this_ent) {
                    if ($ent->metadata()->getClassname() == $this_ent->metadata()->getClassname() && $ent->alias() == $this_ent->alias()) {
                        $add = false;
                        break;
                    }
                }
                if ($add) {
                    $this->_join_list[] = $ent;
                    // verifica a lista de join string
                    $this->_join = array_merge($this->_join, $ent->_getStrJoinList());
                    $this->_join = array_unique($this->_join);
                }
            }
        }
        return $this;
    }

    /**
     * Adiciona a consulta de uma classe para realizar uma consulta uniao
     * <code>
     * $obj1 = new Teste;
     * $obj1->where('nome like ?', 'hugo');
     * $obj2 = new Teste;
     * $obj2->where('nome like ?', 'mirian');
     * $obj2->union( $obj1 );
     * $obj2->find();
     * // (SELECT * FROM teste WHERE nome like '%hugo%') UNION (SELECT * FROM teste WHERE nome like '%mirian%')
     * </code>
     * @param Lumine_Base $obj Objeto para unir com esta classe
     * @return Lumine_Union Uma instancia de Lumine_Union contendo as unioes realizadas
     * @link http://www.hufersil.com.br/lumine
     * @author Hugo Ferreira da Silva
     */
    public function union(Lumine_Base $obj) {
        $union = new Lumine_Union($this->_getConfiguration());
        $union->add($this)
                ->add($obj);

        return $union;
    }

    /**
     * Une uma classe com outra para efetuar uma consulta (inner|left|right) join
     *
     * <code>
     * $car = new Carro;
     * $pes = new Pessoa;
     * $car->join($car);
     * // SELECT pessoa.nome, pessoa.idpessoa, carro.modelo FROM pessoa inner join carro on(carro.idpessoa=pessoa.idpessoa)
     * </code>
     * @param Lumine_Base $obj Objeto para uniao
     * @param string $type Tipo de uniao (LEFT|INNER|RIGHT)
     * @param string $alias Alias para a tabela de uniao
     * @param string $linkName Nome especifico do link desta entidade
     * @param string $linkTo Nome da propriedade que se deseja linkar na outra entidade
     * @param string $extraCondition Condicao extra para adicionar a clausula ON da uniao
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base O proprio objeto
     */
    public function join(Lumine_Base $obj, $type = 'INNER', $alias = '', $linkName = null, $linkTo = null, $extraCondition = null) {
        if (!preg_match('@^(INNER|LEFT|CROSS|RIGHT)$@i', $type)) {
            Lumine_Log::error('Tipo de uniao nao permitida: ' . $type);
            return $this;
        }

        $type = strtoupper($type);

        // verifica as chaves daqui pra la
        $name = $obj->metadata()->getClassname();
        if (is_null($linkName)) {
            Lumine_Log::debug('Nome do link nao especificado. Tentando recuperar automaticamente de ' . $name);
            $opt = $this->metadata()->getRelation($name);
            
        } else {
            Lumine_Log::debug('Nome de link especificado: ' . $linkName);
            $opt = $this->metadata()->getField($linkName);
        }
        
        if (!empty($alias)) {
            Lumine_Log::debug('Alterando o alias de ' . $name . ' para ' . $alias);
            $obj->alias($alias);
        }

        $dest = null;              // link de destino
        if (!empty($linkTo)) {            // se foi especificado um link de destino
            Lumine_Log::debug('Link de destino especificado: ' . $linkTo);
            $dest = $obj->metadata()->getField($linkTo);        // pega o link de destino
        }

        if (!is_null($extraCondition)) {          // se a pessoa definiu uma condicao extra
            $args = func_get_args();
            if (count($args) > 6) {
                $args = array_slice($args, 6);
            } else {
                $args = null;
            }

            $extraCondition = trim($extraCondition);      // remove espacos em branco

            try {
                $extraCondition = Lumine_Parser::parsePart($obj, $extraCondition, $args); // faz o parser para certificacao que os campos existem certinho
                $extraCondition = Lumine_Parser::parseEntityNames($obj, $extraCondition);
            } catch (Exception $e) {

                try {
                    $extraCondition = Lumine_Parser::parsePart($this, $extraCondition, $args); // faz o parser para certificacao que os campos existem certinho
                    $extraCondition = Lumine_Parser::parseEntityNames($this, $extraCondition);
                } catch (Exception $e) {
                    Lumine_Log::warning('Houve um erro na analise da condicao extra');
                }
            }

            if (!preg_match('@^(ON|AND|OR)@i', $extraCondition)) {    // se nao definiu o tipo de logica inicial
                $extraCondition = " AND " . $extraCondition;    // o padrao e AND
            } else {
                $extraCondition = " " . $extraCondition;
            }
            $extraCondition .= " ";           // adiciona um espaco em branco para ficar certinho
        }

        $schema = '';             // schema das tabelas
        $cfg = $this->_getConfiguration();        // pega o objeto de configuracao
        if ($cfg->getOption('schema_name') != null) {     // se especificou um schema
            $schema = $cfg->getOption('schema_name') . '.';    // coloca o nome do schema mais um ponto
        }

        // se a pessoa especificou um linkTo e linkName e ambos existem
        if ($opt != null && $dest != null) {
            Lumine_Log::debug('Ambos links especificados, fazendo uniao...');

            // se for uma uniao many-to-many e ambas tabelas forem iguais
            if ($opt['type'] == Lumine_Metadata::MANY_TO_MANY && $dest['type'] == Lumine_Metadata::MANY_TO_MANY && $opt['table'] == $dest['table']) {
                Lumine_Log::debug('Link do tipo N-N');
                $joinString = "%s JOIN %s ON %s.%s = %s.%s " . PHP_EOL;  // prepara a string de uniao
                $joinString .= " %s JOIN %s %s ON %s.%s = %s.%s ";

                $this_link = $this->metadata()->getField($opt['linkOn']);   // pega o campo referente a uniao desta entidade
                $dest_link = $obj->metadata()->getField($dest['linkOn']);   // pega o campo referente a uniao da entidade que esta sendo unida

                $joinString = sprintf($joinString, // monta a string de join ...
                        // primeiro, a uniao da tabela de N-N com esta entidade
                        $type, // ... tipo de uniao
                        $schema  . $opt['table'], // ... nome da tabela N-N
                        $opt['table'], // ... nome da tabela N-N com...
                        $opt['column'], // ... o nome do campo N-N
                        $this->alias(), // ... alias desta entidade
                        $this_link['column'], // ... coluna desta entidade
                        // agora, a uniao da tabela de N-N com a outra entidade
                        $type, // tipo de uniao
                        $schema  . $obj->metadata()->getTablename(), // nome da tabela estrangeira
                        $obj->alias(), // alias da tabela entrangeira
                        $obj->alias(), // alias da tabela entrangeira
                        $dest_link['column'], // nome do campo da tabela estrangeira
                        $dest['table'], // nome da tabela N-N
                        $dest['column']           // nome da coluna da tabela N-N
                );

                $this->_join[] = $joinString . $extraCondition;    // coloca a string de uniao na lista
            } else {
                Lumine_Log::debug('Link do tipo 1-N');

                $this_alias = $this->alias() == '' ? $this->metadata()->getTablename() : $this->alias();
                $obj_alias = $obj->alias() == '' ? $obj->metadata()->getTablename() : $obj->alias();

                $joinString = "%s JOIN %s %s ON %s.%s = %s.%s";    // inicia a string do join
                $joinString = sprintf($joinString, // faz o parse colocando...
                        $type, // ... o tipo de uniao
                        $schema  . $obj->metadata()->getTablename(), // ... o nome da tabela que esta sendo unida
                        $obj_alias, // ... o alias usado na tabela que esta sendo unida
                        $this_alias, // ... o alias desta tabela
                        $opt['column'], // ... a coluna desta tabela
                        $obj_alias, // ... o alias da tabela que esta sendo unida
                        $dest['column']           // ... a coluna que esta sendo unida
                );

                $this->_join[] = $joinString . $extraCondition;    // adiciona a string montada na lista
            }
        } else {               // mas se nao especificou o linkName e linkTo
            // achou o relacionamento na outra entidade
            // significa que la tem a chave que liga aqui ou vice-e-versa
            if ($opt != null) {
                Lumine_Log::debug('Join de ' . $obj->metadata()->getClassname() . ' com ' . $this->metadata()->getClassname() . ' do tipo ' . $opt['type'], __FILE__, __LINE__);

                switch ($opt['type']) {
                    case Lumine_Metadata::MANY_TO_ONE:
                        $res = $obj->metadata()->getField($opt['linkOn']);

                        $this_alias = $this->alias();
                        if (empty($this_alias)) {
                            $this_alias = $this->metadata()->getTablename();
                        }

                        $ent_alias = $obj->alias();
                        $field = $this->metadata()->getField($opt['name']);

                        $joinStr = $type . " JOIN " . $schema . $obj->metadata()->getTablename() . " " . $ent_alias . " ON ";
                        if (empty($ent_alias)) {
                            $ent_alias = $obj->metadata()->getTablename();
                        }

                        $joinStr .= $ent_alias . '.' . $res['column'] . ' = ';
                        $joinStr .= $this_alias . '.' . $field['column'];

                        $this->_join[] = $joinStr . $extraCondition;

                        break;

                    case Lumine_Metadata::ONE_TO_MANY:
                        $res = $obj->metadata()->getField($opt['linkOn']);
                        $this_ref = $this->metadata()->getField($res['options']['linkOn']);
                        $obj_alias = $obj->alias();
                        $this_alias = $this->alias();

                        if (empty($obj_alias)) {
                            $obj_alias = $obj->metadata()->getTablename();
                        }
                        if (empty($this_alias)) {
                            $this_alias = $this->metadata()->getTablename();
                        }

                        $joinStr = $type . " JOIN " . $schema . $obj->metadata()->getTablename() . ' ' . $obj_alias . ' ON ';
                        $joinStr .= sprintf('%s.%s = %s.%s', $obj_alias, $res['column'], $this_alias, $this_ref['column']);

                        $this->_join[] = $joinStr . $extraCondition;
                        break;

                    case Lumine_Metadata::MANY_TO_MANY:
                        $lnk = $obj->metadata()->getRelation($this->metadata()->getClassname());

                        $this_table = $opt['table'];
                        $obj_table = $lnk['table'];

                        if ($this_table != $obj_table) {
                            throw new Lumine_Exception('As tabelas de relacionamento devem ser iguais em ' . $obj->metadata()->getClassname() . ' e ' . $this->metadata()->getClassname(), Lumine_Exception::ERROR);
                        }

                        $schema = $this->_getConfiguration()->getOption('schema_name');
                        if (!empty($schema)) {
                            $schema .= '.';
                        }

                        $this_res = $this->metadata()->getField($opt['linkOn']);
                        $obj_res = $obj->metadata()->getField($lnk['linkOn']);

                        if (empty($opt['column'])) {
                            $mtm_column = $this_res['column'];
                        } else {
                            $mtm_column = $opt['column'];
                        }

                        if (empty($lnk['column'])) {
                            $mtm_column_2 = $obj_res['column'];
                        } else {
                            $mtm_column_2 = $lnk['column'];
                        }

                        $alias_1 = $this->alias();
                        $alias_2 = $obj->alias();

                        if (empty($alias_1)) {
                            $alias_1 = $this->metadata()->getTablename();
                        }
                        if (empty($alias_2)) {
                            $alias_2 = $obj->metadata()->getTablename();
                        }

                        $joinStr = sprintf('%s JOIN %s ON %s.%s = %s.%s', $type, $schema . $this_table, $this_table, $mtm_column, $alias_1, $this_res['column']);
                        $this->_join[] = $joinStr;

                        $joinStr = sprintf('%s JOIN %s %s ON %s.%s = %s.%s', $type, $schema . $obj->metadata()->getTablename(), $alias_2, $obj_table, $mtm_column_2, $alias_2, $obj_res['column']);
                        $this->_join[] = $joinStr . $extraCondition;
                        break;

                    default:
                        throw new Lumine_Exception('Tipo de uniao nao encontrada: ' . $opt['type'], Lumine_Exception::ERROR);
                }
            }
        }

        $list = $obj->_getObjectPart('_join_list');

        reset($this->_join_list);

        foreach ($list as $ent) {
            $add = true;
            foreach ($this->_join_list as $this_ent) {
                if ($ent->metadata()->getClassname() == $this_ent->metadata()->getClassname() && $ent->alias() == $this_ent->alias()) {
                    $add = false;
                    break;
                }
            }
            if (!$add) {
                continue;
            }

            // ok pode adicionar
            $this->_join_list[] = $ent;
            $this->_join = array_merge($this->_join, $ent->_getStrJoinList());

            $where = $ent->_makeWhereFromFields();

            if (!empty($where)) {
                $this->where($where);
            }
        }

        $this->_join = array_unique($this->_join);

        return $this;
    }

    /**
     * Permite adicionar um JOIN com uma expressao livre.
     * 
     * @param Lumine_Base $obj Objeto que sera unido
     * @param string $expression Expressao que sera utilizada no join
     * @param string $alias Apelido para a classe que esta sendo unida
     * @author Hugo Ferreira da Silva
     * @return Lumine_Base o proprio objeto
     */
    public function joinExpression($obj, $type, $expression, $alias = null) {

        $type = trim(strtoupper($type));

        if (!preg_match('@^(LEFT|LEFT OUTER|INNER|RIGHT|RIGHT OUTER|CROSS)$@', $type)) {
            throw new Lumine_Exception('Tipo nao suportado: ' . $type, Lumine_Exception::ERROR);
        }

        // se indicar o alias
        if (!is_null($alias)) {
            $obj->alias($alias);
        } else {
            $alias = $obj->alias();
            if (empty($alias)) {
                $alias = $obj->metadata()->getTablename();
            }
        }

        // pega a lista de join's do objeto que esta sendo unido
        $list = $obj->_getObjectPart('_join_list');

        // reinicia a lista de join's deste objeto
        reset($this->_join_list);

        // argumentos extras
        $args = func_get_args();
        array_splice($args, 0, 4);

        $expression = trim($expression);      // remove espacos em branco

        try {
            $expression = Lumine_Parser::parsePart($obj, $expression, $args); // faz o parser para certificacao que os campos existem certinho
            $expression = Lumine_Parser::parseEntityNames($obj, $expression);
        } catch (Exception $e) {

            try {
                $expression = Lumine_Parser::parsePart($this, $expression, $args); // faz o parser para certificacao que os campos existem certinho
                $expression = Lumine_Parser::parseEntityNames($this, $expression);
            } catch (Exception $e) {
                Lumine_Log::warning('Houve um erro na analise da condicao extra');
            }
        }

        // adiciona a expressao
        $this->_join[] = sprintf('%s JOIN %s %s ON %s', $type, $obj->metadata()->getTablename(), $alias, $expression);

        // para cada item na lista do objeto alvo
        foreach ($list as $ent) {
            // indica que pode adicionar
            $add = true;
            // para cada item na lista deste objeto
            foreach ($this->_join_list as $this_ent) {
                // se for a mesma classe e tiver o mesmo alias
                if ($ent->metadata()->getClassname() == $this_ent->metadata()->getClassname() && $ent->alias() == $this_ent->alias()) {
                    // nao pode fazer o join
                    $add = false;
                    break;
                }
            }

            // se nao puder fazer o join
            if (!$add) {
                // pula para o proximo item
                continue;
            }

            // ok pode adicionar
            $this->_join_list[] = $ent;
            $this->_join = array_merge($this->_join, $ent->_getStrJoinList());

            // faz o where
            $where = $ent->_makeWhereFromFields();

            // se teve condicoes
            if (!empty($where)) {
                // inclui neste objeto
                $this->where($where);
            }
        }

        // deixa a lista unica
        $this->_join = array_unique($this->_join);

        return $this;
    }

    /**
     * Efetua um INSERT
     *
     * <code>
     * $obj->nome = 'hugo';
     * $obj->data_cadastro = time();
     * $obj->insert();
     * // INSERT INTO pessoas (nome, data_cadastro) VALUES ('hugo','2007-08-20');
     * </code>
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return boolean|Lumine_Base A instancia da propria classe ou false em caso de falhas
     */
    public function insert() {
        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_INSERT, $this));

        $this->savePendingObjects();

        $sql = $this->_getSQL(self::SQL_INSERT);

        if ($sql === false) {
            return false;
        }

        try {
            $result = $this->_execute($sql);
        } catch (Lumine_SQLException $lse) {
            throw $lse;
        }

        // vejamos se inseriu
        if ($result == true) {
            // vamos analisar as chaves primarias e auto-incrementaveis
            // para ver os valores e pegar do banco
            $pks = $this->metadata()->getPrimaryKeys();
            foreach ($pks as $pk) {
                // se o valor for nulo e for um campo auto-increment
                $pkvalue = $this->fieldValue($pk['name']);

                // se estiver vazio e o campo for auto-increment
                // nao podemos colocar null porque o campo pode ser zero e MySQL
                // deixa inserir valores como zero e ele aumenta o autoincrement
                if (empty($pkvalue) && !empty($pk['options']['autoincrement'])) {
                    // pega o ultimo ID do campo
                    $valor = $this->_getDialect()->getLastId($pk['column']);
                    $this->setFieldValue($pk['name'], $valor);
                }
            }

            $this->saveDependentObjects();
            $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_INSERT, $this, $sql));
        }

        return $this;
    }

    /**
     * Salva / insere o objeto
     * Se a chave primaria estiver definida, efetua um update
     * do contrario, efetua um insert
     *
     * @param boolean $whereAddOnly Utilizar somente os parametros definidos com where para atualizar
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base A instancia da classe
     */
    public function save($whereAddOnly = false) {
        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_SAVE, $this));
        // para chamar o update, todas as chaves primarias tem que ter valor
        $pks = $this->metadata()->getPrimaryKeys();
        $all = true;

        // salva os objetos principais (classes extendidas)
        // $this->savePendingObjects();

        foreach ($pks as $def) {
            if ($this->$def['name'] == null) {
                $all = false;
                break;
            }
        }

        try {
            if ($all == true) {
                $this->update($whereAddOnly);
            } else {
                $this->insert();
            }
        } catch (Lumine_SQLException $lse) {
            throw $lse;
        }

        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_SAVE, $this));

        return $this;
    }

    /**
     * Efetua um update
     *
     * @param boolean $whereAddOnly Utilizar somente os parametros definidos com where para atualizar
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return int Numero de linhas atualizadas
     */
    public function update($whereAddOnly = false) {
        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_UPDATE, $this));

        $this->savePendingObjects();

        $sql = $this->_getSQL(self::SQL_UPDATE, $whereAddOnly);

        if ($sql !== false) {
            try {
                $this->_execute($sql);
            } catch (Lumine_SQLException $lse) {
                throw $lse;
            }

            $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_UPDATE, $this, $sql));
            $this->saveDependentObjects();

            return $this->affected_rows();
        } else {
            $this->saveDependentObjects();
        }

        return 0;
    }

    /**
     * Efetua um delete
     *
     * @param boolean $whereAddOnly Utilizar somente os parametros definidos com where para remover
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return int Numero de linhas afetadas
     */
    public function delete($whereAddOnly = false) {
        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_DELETE, $this));

        $sql = $this->_getSQL(self::SQL_DELETE, $whereAddOnly);

        try {
            $this->_execute($sql);
        } catch (Lumine_SQLException $lse) {
            throw $lse;
        }

        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_DELETE, $this, $sql));

        return $this->affected_rows();
    }

    /**
     * Adiciona a clausula LIMIT a consulta
     *
     * @param int $offset Inicio dos registros ou limite se o segundo argumento for omitido
     * @param int $limit Numero de registros a serem limitados
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base O proprio objeto
     */
    public function limit($offset = null, $limit = null) {
        if (empty($limit)) {
            $this->_limit = $offset;
        } else {
            $this->_offset = $offset;
            $this->_limit = $limit;
        }

        return $this;
    }

    /**
     * Adiciona uma clausula having
     *
     * @param string $havingStr String para ser adiciona ao having. Se for nulo, limpa as clausulas
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Luminie_Base O proprio objeto
     */
    public function having($havingStr = null) {
        $args = func_get_args();

        if (gettype($havingStr) == 'NULL') {
            $this->_having = array();
            return $this;
        }

        if (count($args) > 1) {
            array_shift($args);
            $result = Lumine_Parser::parsePart($this, $havingStr, $args);
        } else {
            $result = Lumine_Parser::parsePart($this, $havingStr);
        }

        $this->_having[] = $result;
        return $this;
    }

    /**
     * Adiciona clausulas where a consulta
     * E possivel adicionar clausulas no modo de preparedStatment
     * Funciona somente quando se esta comparando com os termos abaixo:
     * =, >=, <=, !=, <>, >, <, like, ilike, not like
     * <code>
     * $obj = new Pessoa;
     * $obj->alias('p');
     * $obj->where('p.nome = ? AND p.idade = ?', 'hugo', '23');
     * $obj->find();
     * // SELECT p.idpessoa, p.nome, p.idade, p.data_cadastro FROM pessoa WHERE p.nome = 'hugo' AND p.idade = 23
     * </code>
     * @param string $whereStr String para adicionar a clausula where
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base A propria instancia
     */
    public function where($whereStr = null) {
        $args = func_get_args();

        if (gettype($whereStr) == 'NULL') {
            $this->_where = array();
            return $this;
        }

        if (count($args) > 1) {
            array_shift($args);
            $result = Lumine_Parser::parsePart($this, $whereStr, $args);
        } else {
            $result = Lumine_Parser::parsePart($this, $whereStr);
        }

        $this->_where[] = $result;
        return $this;
    }

    /**
     * Adiciona clausulas order by
     * 
     * <code>
     * $obj = new Pessoa;
     * $obj->order('nome asc');
     * // SELECT pessoa.idpessoa,pessoa.nome,pessoa.data_nascimento FROM pessoa ORDER BY nome ASC;
     * </code>
     *
     * @param string $orderStr String para utilizar no order by
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base O proprio objeto
     */
    public function order($orderStr = null) {
        if (is_null($orderStr)) {
            $this->_order = array();
        } else {
            $list = Lumine_Tokenizer::dataSelect($orderStr, $this);
            $this->_order = array_merge($this->_order, $list);
        }
        return $this;
    }

    /**
     * Adiciona clausulas de agrupamento (group by)
     *
     * @param strin $groupStr String para adicionar ao agrupamento
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base O prorio objeto
     */
    public function group($groupStr = null) {
        if (!is_null($groupStr)) {
            $list = Lumine_Tokenizer::dataSelect($groupStr, $this);
            $this->_group = array_merge($this->_group, $list);
        } else {
            $this->_group = array();
        }
        return $this;
    }

    /**
     * Seta as variaveis internas atraves de um array associativo enviado
     *
     * <code>
     * $obj->populateFrom($_POST);
     * echo $obj->nome;
     * </code>
     * @param mixed $arr Array associativo ou objeto contendo os valores
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Base O proprio objeto
     */
    public function populateFrom($arr) {
        $map = is_object($arr) ? get_object_vars($arr) : array();

        foreach ($this->metadata()->getFields() as $name => $def) {
            if (is_array($arr)) {
                if (array_key_exists($name, $arr)) {
                    $this->$name = $arr[$name];
                    $this->_dataholder[$name] = $arr[$name];
                }
            } else if (is_object($arr)) {
                if (array_key_exists($name, $map)) {
                    $this->$name = $arr->$name;
                    $this->_dataholder[$name] = $arr->$name;
                }
            }
        }

        // agora pegamos as relacoes
        foreach ($this->metadata()->getRelations(FALSE) as $name => $def) {
            if (is_array($arr) && array_key_exists($name, $arr)) {
                $this->$name = $arr[$name];
                $this->_dataholder[$name] = $arr[$name];
            } else if (is_object($arr)) {
                if (array_key_exists($name, $map)) {
                    $this->$name = $arr->$name;
                    $this->_dataholder[$name] = $arr->$name;
                }
            }
        }

        return $this;
    }

    /**
     * Recupera/altera o alias atual
     *
     * @param mixed $alias null => retorna o alias atual, false => reinicia o alias, outro valor => altera o valor do alias
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return string|Lumine_Base Alias atual da classe se for null, ou a instancia da classe
     * @see Lumine_Base::alias()
     * @see Lumine_Base::alias()
     */
    public function alias($alias = null) {
        if (is_null($alias)) {
            return $this->_alias;
        }

        if ($alias === false) {
            $this->_alias = '';
        } else {
            $this->_alias = $alias;
        }
        return $this;
    }

    /**
     * Recupera uma determinada parte do objeto que seja privada
     * Este metodo e usado mais internamente, para facilitar na manipulacao das partes privadas do objeto
     * @param string $partName Nome da parte a ser recuperada
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return mixed Parte do objeto recuperada
     */
    public function _getObjectPart($partName) {
        if (!isset($this->$partName)) {
            throw new Lumine_Exception('Parte nao encontrada: ' . $partName, Lumine_Exception::ERROR);
        }
        return $this->$partName;
    }

    /**
     * Recupera o objeto de configuracao atual
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Configuration Objeto de configuracao atual
     */
    public function _getConfiguration() {
        $pkg = $this->metadata()->getPackage();
        if (!empty($pkg)) {
            return Lumine_ConnectionManager::getInstance()->getConfiguration($pkg);
        }
    }

    /**
     * Altera o objeto de conexao com o banco utilizado
     *
     * @param Lumine_Connection_IConnection $cn Novo objeto de conexao
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return void
     */
    public function _setConnection(Lumine_Connection_IConnection $cn) {
        $this->_getConfiguration()->setConnection($cn);
    }

    /**
     * Recupera a conexao atual
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return Lumine_Connection_IConnection Objeto de conexao com o banco atual
     */
    public function _getConnection() {

        return $this->_getConfiguration()->getConnection();
    }

    /**
     * Recupera o tipo de SQL que sera executada
     * Voce podera chamar este metodo para saber como esta ficando a estrutura da consulta, por exemplo:
     * <code>
     * $obj = new Pessoa;
     * $obj->get(20);
     * $obj->nome = 'hugo';
     * $obj->idade = 23;
     * echo $obj->_getSQL(Lumine_Base::SQL_SELECT);
     * // SELECT pessoa.idpessoa, pessoa.nome, pessoa.idade FROM pessoa WHERE pessoa.nome = 'hugo' AND pessoa.idade = 23
     * echo $obj->_getSQL(Lumine_Base::SQL_INSERT);
     * // INSERT INTO pessoa (idpessoa, nome, idade) VALUES (20, 'hugo', 23)
     * echo $obj->_getSQL(Lumine_Base::SQL_UPDATE);
     * // UPDATE pessoa SET nome = 'hugo', idade = 23 WHERE idpessoa = 20;
     * echo $obj->_getSQL(Lumine_Base::SQL_DELETE);
     * // DELETE FROM pessoa WHERE idpessoa = 20
     * </code>
     * @param int $type Tipo de SQL a ser retornada
     * @param mixed $opt Opcoes a serem usadas, dependendo do tipo de SQL a ser retornada
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return string SQL desejada
     * @see Lumine_Base::save()
     * @see Lumine_Base::update()
     * @see Lumine_Base::insert()
     * @see Lumine_Base::delete()
     */
    public function _getSQL($type = self::SQL_SELECT, $opt = null) {
        switch ($type) {
            case self::SQL_SELECT:
                return $this->_prepareSQL();

            case self::SQL_SELECT_COUNT:
                return $this->_prepareSQL(true, $opt);

            case self::SQL_UPDATE:
                return $this->_updateSQL($opt);

            case self::SQL_DELETE:
                return $this->_deleteSQL($opt);

            case self::SQL_INSERT:
                return $this->_insertSQL($opt);

            case self::SQL_MULTI_INSERT;
                return $this->_getMultiInsertSQL($opt);
        }

        throw new Lumine_Exception('Tipo nao suportado: ' . $type, Lumine_Exception::ERROR);
    }

    /**
     * Recupera/Altera o modo de recuperacao dos registros do banco.
     *
     * @param int $mode Modo a ser utilizado ou null para recuperar o atual
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return int Modo atual de recueperacao de registros
     */
    public function fetchMode($mode = null) {
        if (!empty($mode)) {
            $this->_fetch_mode = $mode;
        }

        return $this->_fetch_mode;
    }

    /**
     * Recupera um determinado link da classe
     *
     * <code>
     * $pes = new Pessoa;
     * $pes->get(20);
     * $carros = $pes->fetchLink('carros');
     * foreach($carros as $carro) {
     *     echo $carro->modelo  .'<br>';
     * }
     * </code>
     * @param string $linkName Nome do link
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return mixed Lumine_Base para Many-to-ONE, do contrario, uma lista de objetos Lumine_Base
     */
    public function fetchLink($linkName) {
        try {
            $field = $this->metadata()->getField($linkName);

            $class = empty($field['class']) ? $field['options']['class'] : $field['class'];

            $this->_getConfiguration()->import($class);
            $obj = new $class;

            switch ($field['type']) {
                case Lumine_Metadata::ONE_TO_MANY:
                    Lumine_Log::debug('Pegando link do tipo one-to-many de ' . $obj->metadata()->getClassname());
                    $ref = $obj->metadata()->getField($field['linkOn']);

                    $obj->setFieldValue($field['linkOn'], $this->fieldValue($ref['options']['linkOn']));
                    $obj->find();

                    $newlist = array();
                    while ($obj->fetch()) {
                        // valores da nova lista para a linha atual
                        $row = $obj->toArray();
                        // cria a classe nova
                        $new_obj = new $field['class'];
                        // seta os valores encontrados no novo objeto
                        $new_obj->populateFrom($row);

                        // vamos checar se os relacionamentos tambem foram carregados
                        foreach ($new_obj->metadata()->getRelations() as $name => $def) {
                            // se carregou e for lazy
                            if (!empty($row[$name]) && $def['lazy'] == true) {
                                // nova lista de objetos
                                $newlist_child = array();
                                // para cada item encontrado
                                foreach ($row[$name] as $child) {
                                    // carregamos a classe
                                    $cls = new $def['class'];
                                    // aplicamos os valores
                                    $cls->populateFrom($child);
                                    // colocamos a classe na nova lista
                                    $newlist_child[] = $cls;
                                }
                                // atribuimos a nova lista ao objeto novo
                                $new_obj->setFieldValue($name, $newlist_child);
                            }
                        }

                        $newlist[] = $new_obj;
                    }

                    $obj->destroy();
                    unset($obj);

                    //$this->setFieldValue($linkName, $newlist);
                    return $newlist;

                    break;

                case Lumine_Metadata::MANY_TO_MANY:
                    Lumine_Log::debug('Pegando link do tipo many-to-many de ' . $obj->metadata()->getClassname());
                    $this->_getConfiguration()->import($field['class']);

                    $list = new $field['class'];
                    $sql = "SELECT __a.* FROM %s __a, %s __b WHERE ";
                    $sql .= " __a.%s = __b.%s AND __b.%s = %s";

                    $campoEstrangeiro = null;
                    foreach ($list->metadata()->getRelations() as $item) {
                        if (!empty($item['table']) && $item['table'] == $field['table']) {
                            $campoEstrangeiro = $item;
                            break;
                        }
                    }
                    if (is_null($campoEstrangeiro) == true) {
                        throw new Exception("Deve haver relacionamento many-to-many em ambas as entidades");
                    }

                    $fieldlink = $list->metadata()->getField($campoEstrangeiro['linkOn']);   // pega a definicao do campo estrangeiro
                    $reffield = $this->metadata()->getField($field['linkOn']);     // pega a definicao do campo desta entidade
                    $valor = $this->fieldValue($field['linkOn']);      // pega o valor do campo de linkagem desta entidade
                    $colunaUniao = $campoEstrangeiro['column'];        // pega o nome da coluna de uniao da entidade que sera unida
                    $colunaLink = $fieldlink['column'];         // pega o nome da coluna de link na tabela mtm estrangeira
                    $colunaWhere = $field['column'];          // pega o nome da coluna desta entidade para fazer o where
                    $tabelaUniao = $field['table'];           // pega o nome da tabela de uniao
                    $tabelaLink = $list->metadata()->getTablename();          // pega o nome da entidade que sera linkada

                    if (is_null($valor)) {
                        //throw new Exception("Sem valores no campo {$field['linkOn']}, logo e impossivel encontrar o relacionamento");
                        return array();
                    }

                    $schema = $this->_getConfiguration()->getOption('schema_name');
                    if (!empty($schema)) {
                        $tabelaUniao = $schema . '.' . $tabelaUniao;
                        $tabelaLink = $schema . '.' . $tabelaLink;
                    }

                    $valor = Lumine_Parser::getParsedValue($reffield, $valor, $reffield['type']);

                    $sql = sprintf($sql, $tabelaLink, $tabelaUniao, $colunaLink, $colunaUniao, $colunaWhere, $valor);
                    $list->query($sql);

                    $arr_list = array();

                    while ($list->fetch()) {
                        $dummy = new $field['class'];
                        $dummy->populateFrom($list->toArray());
                        $arr_list[] = $dummy;
                    }

                    $list->destroy();
                    unset($list);

                    //$this->$linkName = $arr_list;
                    return $arr_list;

                    break;

                case Lumine_Metadata::MANY_TO_ONE:
                default:
                    Lumine_Log::debug('Pegando link do tipo many-to-one de ' . $obj->metadata()->getClassname());

                    $valor = $this->fieldValue($linkName);

                    if ($valor instanceof Lumine_Base) {
                        return $valor;
                    }

                    if (!empty($valor)) {
                        $obj->setFieldValue($field['options']['linkOn'], $valor);
                        $obj->find(true);

                        //$this->setFieldValue($linkName, $obj);
                    }


                    //$obj->reset();
                    //unset($obj);

                    return $obj;
                    break;
            }
        } catch (Lumine_Exception $e) {
            Lumine_Log::warning($e->getMessage());
        }
    }

    /**
     * Recupera todos os registros em formato de array
     * Cada linha do array representa uma linha de registro encontrado
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param boolean $returnRealValues Forca o retorno dos valores reais do banco
     * @param boolean $considerDataSelect Indica se pegara somente os dados informados no data select
     * @return array Todos registros em um array
     */
    public function allToArray($returnRealValues = false, $considerDataSelect = false) {
        $p = $this->_getDialect()->getPointer();
        $this->_getDialect()->moveFirst();

        $dataholder = $this->_dataholder;

        $nova = array();

        while ($this->fetch()) {
            $row = $this->toArray('%s', $returnRealValues, $considerDataSelect);
            $nova[] = $row;
        }


        $this->_getDialect()->setPointer($p);
        $this->_dataholder = $dataholder;

        return $nova;
    }

    /**
     * Converte o registro atual para um array
     *
     * @author Hugo Ferreira da Silva
     * @param boolean $returnRealValues Forca o retorno dos valores reais do banco
     * @param String $format Formato do nome do campo para ser utilizado com sprintf
     * @param boolean $considerDataSelect Considera somente os dados usados no dataselect
     * @link http://www.hufersil.com.br/lumine
     * @return array Array do registro atual
     */
    public function toArray($format = '%s', $returnRealValues = false, $considerDataSelect = false) {
        $list = array();
        $keyList = array();

        if (!$considerDataSelect) {
            // valores vindos da consulta
            foreach ($this->_dataholder as $key => $val) {
                $keyList[] = $key;
            }
            // definicao da classe
            foreach ($this->metadata()->getFields() as $name => $def) {
                $keyList[] = $name;
            }
            // chaves estrangeiras
            foreach ($this->metadata()->getRelations(FALSE) as $name => $def) {
                $keyList[] = $name;
            }
        } else {
            $keyList = array_keys($this->_original_dataholder);
        }

        $keyList = array_unique($keyList);

        // agora fazemos o loop
        foreach ($keyList as $key) {
            $newkey = sprintf($format, $key);

            try {
                $fld = $this->metadata()->getField($key);
            } catch (Exception $e) {
                $fld = $this->metadata()->getFieldByColumn($key);
            }

            $val = $this->fieldValue($key);

            if (!empty($fld) && isset($fld['name'])) {
                $key = $fld['name'];
            }

            // se nao tiver um format associado e nao for para retornar os valores reais
            if (empty($fld['options']['format']) && !$returnRealValues) {
                $val = $this->formattedValue($key);
            }


            if ($val instanceof Lumine_Base) {
                $list[$newkey] = $val->toArray($format);
            } else if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $nk = sprintf($format, $k);
                    if ($v instanceOf Lumine_Base) {
                        $val[$nk] = $v->toArray($format);
                    } else {
                        $val[$nk] = $v;
                    }
                }

                $list[$newkey] = $val;
            } else {
                $list[$newkey] = $val;
            }
        }

        return $list;
    }

    /**
     * retorna o registro atual em formato XML
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param boolean $utf8 Converter os dados para UTF8
     * @param boolean $realValues Indica se passara os formatadores ou nao
     * @param boolean $considerDataSelect Indica se pegara somente os dados informados no data select
     * @return string
     */
    public function toXML($utf8=true, $realValues = false, $considerDataSelect = false) {
        return Lumine_Util::array2xml($this->toArray('%s', $realValues, $considerDataSelect), $utf8);
    }

    /**
     * retorna o registro atual na representacao JSON
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param boolean $utf8 Indica se o valor devera ser convertido em UTF8 primeiro
     * @param boolean $realValues Indica se passara os formatadores ou nao
     * @param boolean $considerDataSelect Indica se pegara somente os dados informados no data select
     * @return string 
     */
    public function toJSON($utf8=false, $realValues = false, $considerDataSelect = false) {
        return Lumine_Util::json($this->toArray('%s', $realValues, $considerDataSelect), $utf8);
    }

    /**
     * retorna o registro atual em objeto (stdClass)
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $format Formato para enviar a chave diferenciada
     * @param string $className Nome da classe que sera instanciada
     * @param boolean $considerDataSelect Indica se pegara somente os dados informados no data select
     * @return stdClass
     */
    public function toObject($format='%s', $className = null, $considerDataSelect = false) {
        // se a opcao de autocast for definida e for stdClass
        if (is_null($className) && $this->_getConfiguration()->getOption('auto_cast_dto') == 1) {
            $className = sprintf($this->_getConfiguration()->getOption('dto_format'), get_class($this));
        }

        if (is_null($className)) {
            $className = 'stdClass';
        }

        if (!class_exists($className)) {
            throw new Exception('A classe "' . $className . '" nao existe');
        }

        $ref = new ReflectionClass($className);
        $obj = $ref->newInstance();

        $keyList = array();

        if (!$considerDataSelect) {
            // valores vindos da consulta
            foreach ($this->_dataholder as $key => $val) {
                $keyList[] = $key;
            }
            // definicao da classe
            foreach ($this->metadata()->getFields() as $name => $def) {
                $keyList[] = $name;
            }
            // chaves estrangeiras
            foreach ($this->metadata()->getRelations(FALSE) as $name => $def) {
                $keyList[] = $name;
            }
        } else {
            $keyList = array_keys($this->_original_dataholder);
        }

        // agora fazemos o loop
        foreach ($keyList as $key) {
            // pegamos o nome formatado do campo
            $newkey = sprintf($format, $key);

            try {
                // pegamos os dados do campo
                $fld = $this->metadata()->getField($key);
            } catch (Exception $e) {
                // pegamos os dados do campo
                $fld = $this->metadata()->getFieldByColumn($key);
            }

            // se encontrar o campo
            if (!empty($fld) && isset($fld['name'])) {
                // pega a propriedade name
                $key = $fld['name'];
            }
            // pega o valor campo
            $val = $this->fieldValue($key);
            // se nao for uma instancia de Lumine_Base
            if (!($val instanceof Lumine_Base)) {
                // pega o valor aplicando os formatadores
                $val = $this->formattedValue($key);
            }
            // se for uma instancia de Lumine_base
            if ($val instanceof Lumine_Base) {
                // chama o toObject do elemento tambem
                $obj->$newkey = $val->toObject($format);
                // mas se for um array
            } else if (is_array($val)) {

                // para cada elemento do array
                foreach ($val as $k => $v) {
                    // pega o novo nome
                    $nk = sprintf($format, $k);
                    // se o elemento for lumine_base
                    if ($v instanceOf Lumine_Base) {
                        // chama o toObjecto do elemento
                        $val[$nk] = $v->toObject($format);
                        // do contrario
                    } else {
                        // armazena o valor
                        $val[$nk] = $v;
                    }
                }
                // atribui os valores no objeto de retorno
                $obj->$newkey = $val;
            } else {
                // se nao for array, simplesmente coloca
                // o valor no objeto de retorno
                $obj->$newkey = $val;
            }
        }
        // retorna o objeto
        return $obj;
    }

    /**
     * retorna a lista de resultados em formato xml
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param boolean $utf8 Converter os dados para UTF8
     * @param boolean $considerDataSelect Indica se pegara somente os dados informados no data select
     * @return string
     */
    public function allToXML($utf8 = true, $considerDataSelect = false) {
        return Lumine_Util::array2xml($this->allToArray(false, $considerDataSelect), $utf8);
    }

    /**
     * Retorna a lista de resultados na representacao JSON
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param boolean $utf8 Converter os dados para UTF8
     * @param boolean $considerDataSelect Indica se pegara somente os dados informados no data select
     * @return string
     */
    public function allToJSON($utf8 = false, $considerDataSelect = false) {
        return Lumine_Util::json($this->allToArray(false, $considerDataSelect), $utf8);
    }

    /**
     * Retorna todos os elementos em formato de objeto
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $format Formato para alterar o nome do array
     * @param string $className Nome da classe que sera instanciada
     * @param boolean $considerDataSelect Indica se pegara somente os dados informados no data select
     * @return array
     */
    public function allToObject($format = '%s', $className = null, $considerDataSelect = false) {
        // se a opcao de autocast for definida e for stdClass
        if (is_null($className) && $this->_getConfiguration()->getOption('auto_cast_dto') == 1) {
            $className = sprintf($this->_getConfiguration()->getOption('dto_format'), get_class($this));
        }

        if (is_null($className)) {
            $className = 'stdClass';
        }

        if (!class_exists($className)) {
            throw new Exception('A classe "' . $className . '" nao existe');
        }


        $p = $this->_getDialect()->getPointer();
        $this->_getDialect()->moveFirst();

        $dataholder = $this->_dataholder;

        $nova = array();

        while ($this->fetch()) {
            $obj = $this->toObject($format, $className, $considerDataSelect);
            $nova[] = $obj;
        }


        $this->_getDialect()->setPointer($p);
        $this->_dataholder = $dataholder;

        return $nova;
    }

    /**
     * "Escapa" uma string para insercao/consulta no banco de dados
     *
     * @param string $str String a ser "escapada"
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return string Nova string com o escape correto
     */
    public function escape($str) {
        return $this->_getConnection()->escape($str);
    }
    
    /**
     * Adiciona um validator à classe
     * 
     * @param Lumine_Validator_AbstractValidator $validator
     * @author Hugo Ferreira da Silva
     */
    public function addValidator(Lumine_Validator_AbstractValidator $validator){
    	$this->_validators[] = $validator;
    }
    
    /**
	 * Remove um validator
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Validator_AbstractValidator $validator
     */
    public function removeValidator(Lumine_Validator_AbstractValidator $validator){
    	$idx = array_search($validator, $this->_validators);
    	if( $idx !== false ){
    		$this->_validator = array_values(
    			array_splice($this->_validators, $idx, 1)
    		);
    	}
    }
    
    /**
     * Limpa a lista de validators
     * 
     * @author Hugo Ferreira da Silva
     */
    public function clearValidators(){
    	$this->_validators = array();
    }
    
    /**
     * Retorna uma lista dos validators
     * 
     * @return array
     * @author Hugo Ferreira da Silva
     */
    public function listValidators(){
    	return $this->_validators;
    }

    /**
     * Efetua uma validacao
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return mixed True em caso de sucesso, array em caso de erros.
     */
    public function validate() {
        return Lumine_Validator::validate($this);
    }

    /**
     * Reinicia as propriedades do objeto
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return void
     */
    public function reset() {
        $dialect = $this->_getConfiguration()->getProperty('dialect');
        $class_dialect = 'Lumine_Dialect_' . $dialect;

        $this->clearValidators();
        
        $this->_join_list = array($this);
        $this->_from = array($this);
        // $this->_bridge         = new $class_dialect( $this );
        // alias da tabela
        $this->_alias = '';

        // partes da consulta
        $this->_data = array();
        $this->_where = array();
        $this->_having = array();
        $this->_order = array();
        $this->_group = array();
        $this->_join = array();
        $this->_updateExpressions = array();
        $this->_limit = null;
        $this->_offset = null;

        // modo do resultado
        $this->_fetch_mode = self::FETCH_ASSOC;

        // armazena os valores das variaveis
        $this->_dataholder = array();
        $this->_original_dataholder = array();
        $this->_multiInsertList = array();

        $this->_formatters = array();
        $this->_classDefinition = array();
        $this->_classMethods = array();

        // re-une as classes (caso forem extendidas)
        $this->_joinSubClasses();

        $this->_cleanFields();
    }

    /**
     * Remove objetos do banco com o determinado nome de link
     *
     * @param string $linkName Nome do link para remover os objetos do banco
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return void
     */
    public function removeAll($linkname) {
        try {
            $field = $this->metadata()->getField($linkname);
            $schema = $this->_getConfiguration()->getOption("schema_name");
            if (!empty($schema)) {
                $schema .= '.';
            }

            switch ($field['type']) {
                case Lumine_Metadata::MANY_TO_MANY:
                    // $val = Lumine_Parser::parseEntityValues($this,"{".$field['linkOn']."} = ?", $this->$field['linkOn']);
                    // $val = Lumine_Parser::parseSQLValues($this, $val);

                    $field_def = $this->metadata()->getField($field['linkOn']);

                    $val = $field['column'] . "=" . Lumine_Parser::getParsedValue($this, $this->$field['linkOn'], $field_def['type']);
                    $sql = "DELETE FROM " . $schema . $field['table'] . " WHERE ";
                    $sql .= $val;

                    $this->_execute($sql);
                    return $this->_getDialect()->affected_rows();
                    break;

                case Lumine_Metadata::ONE_TO_MANY:
                    $list = $this->fetchLink($linkname);
                    $total = count($list);

                    if (is_array($list)) {
                        foreach ($list as $item) {
                            $item->delete();
                        }
                    }
                    unset($list);
                    return $total;
                    break;
            }
        } catch (Exception $e) {
            Lumine_Log::warning('Link nao encontrado: ' . $linkname);
        }
    }

    /**
     * Remove elementos de relacionamentos N-M
     * Ao contratrio do metodo removeAll, pode-se especificar quais as chaves
     * dos elementos que se deseja remover, ou passar um array contendo
     * objetos Lumine_Base para remocao, exemplo:
     * <code>
     * // recupera pessoa com codigo 1
     * $pessoa = Pessoa::staticGet(1);
     * //remove os modulos 1 e 3 vinculados a esta pessoa
     * $pessoa->remove('modulos', array(Modulo::staticGet(1), Modulo::staticGet(3)));
     * // pode ser escrito tambem como
     * $pessoa->remove('modulos', array(1,3));
     * </code>
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param string $linkname Nome do link
     * @param array $items Codigos / objetos lumine para serem removidos
     * @see Lumine_Base::removeAll
     * @return int numero de linhas removidas
     */
    public function remove($linkname, $items) {
        // se os itens nao forem array
        if (!is_array($items)) {
            // transforma em array
            $items = array($items);
        }
        // numero de itens removidos
        $deleted = 0;

        try {
            // pega as informacoes do campo
            $field = $this->metadata()->getField($linkname);
            // pega o nome do schema
            $schema = $this->_getConfiguration()->getOption('schema_name');
            if ($schema != '') {
                $schema .= '.';
            }

            switch ($field['type']) {

                case Lumine_Metadata::MANY_TO_MANY:

                    // pega as informacoes do campo
                    $field_def = $this->metadata()->getField($field['linkOn']);
                    // pega o valor deste campo
                    $this_val = Lumine_Parser::getParsedValue($this, $this->$field['linkOn'], $field_def['type']);
                    // se for nulo
                    if (is_null($this_val) || ($this_val == 'NULL' && !in_array($field_def['type'], array('varchar', 'text')))) {
                        // envia um alerta e da um break
                        Lumine_Log::warning('Nenhum valor encontrado na chave para a classe ' . get_class($this));
                        break;
                    }

                    // instancia a classe de referencia
                    $ref = new ReflectionClass($field['class']);
                    $obj = $ref->newInstance();

                    // pega os dados do campo da entidade
                    $rel = $obj->metadata()->getRelation(get_class($this));
                    $field_obj = $obj->metadata()->getField($rel['linkOn']);

                    // se as tabelas de uniao nao sao iguais
                    if ($rel['table'] != $field['table']) {
                        Lumine_Log::warning('As tabelas de uniao nao sao iguais entre ' . get_class($obj) . ' e ' . get_class($this));
                        return;
                    }


                    // para cada item do array
                    foreach ($items as $item) {

                        // se o elemento for instancia de lumine base
                        if ($item instanceof Lumine_Base) {
                            // pega o valor do campo de referencia
                            $itemVal = $item->$field_obj['name'];
                            // se nao for
                        } else {
                            // indica que o valor e o proprio item
                            $itemVal = $item;
                        }

                        // faz a clausula com o valor desta entidade
                        $this_val = $field['column'] . "=" . Lumine_Parser::getParsedValue($this, $this->$field['linkOn'], $field_def['type']);
                        // faz a clausula com o valor da entidade alvo
                        $to_val = $rel['column'] . "=" . Lumine_Parser::getParsedValue($obj, $itemVal, $field_obj['type']);

                        // monta a consutla
                        $sql = "DELETE FROM " . $schema . $field['table'] . " WHERE ";
                        $sql .= $this_val . ' AND ' . $to_val;

                        // executa o SQL
                        $this->_execute($sql);
                        $deleted += $this->_getDialect()->affected_rows();
                    }
                    break;

                default:
                    Lumine_Log::warning('Este metodo eh para relacionamentos N-M');
            }
        } catch (Exception $e) {
            // informa no log que deu problema
            Lumine_Log::warning('Link nao encontrado: ' . $linkname);
        }

        return $deleted;
    }

    /**
     * Adiciona um formatador a um campo
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param String $member Nome do membro da classe
     * @param mixed $formatter Funcao / array de classe e metodo para formatar o valor
     * @return Lumine_Base O proprio objeto
     */
    public function addFormatter($member, $formatter) {
        if (!isset($this->_formatters[$member])) {
            $this->_formatters[$member] = array();
        }
        $this->_formatters[$member][] = $formatter;
        return $this;
    }

    /**
     * Remove um formatador
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param String $member Nome do membro da classe
     * @param mixed $formatter Funcao / array de classe e metodo para ser removido dos formatadores
     * @return Lumine_Base O proprio objeto
     */
    public function removeFormatter($member, $formatter) {
        if (!isset($this->_formatters[$member])) {
            foreach ($this->_formatters[$member] as $idx => $item) {
                if ($item === $formatter) {
                    unset($this->_formatters[$member][$idx]);
                    continue;
                }
            }
        }

        return $this;
    }

    /**
     * Recupera o valor formatado do campo
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param String $membro Nome do membro da classe
     * @return mixed Valor formatado
     */
    public function formattedValue($member) {
        $oldvalue = $this->$member;
        $newvalue = $oldvalue;


        if (isset($this->_formatters[$member])) {
            $this->dispatchEvent(new Lumine_FormatEvent(Lumine_Event::PRE_FORMAT, $this, $member, $oldvalue));

            foreach ($this->_formatters[$member] as $formatter) {
                $newvalue = call_user_func_array($formatter, array($newvalue));
            }

            $this->dispatchEvent(new Lumine_FormatEvent(Lumine_Event::POS_FORMAT, $this, $member, $oldvalue, $newvalue));
        }


        return $newvalue;
    }

    /**
     * Altera o valor de um campo
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param string $field nome do campo
     * @param mixed  $val   valor do campo
     * @return void
     */
    public function setFieldValue($field, $val) {
        if ($this->_checkMemberExistence($field)) {
            $this->$field = $val;
        }

        if ($this->_hasSetMethod($field)) {
            $this->_executeSetMethod($field, $val);
        }

        $this->_dataholder[$field] = $val;
    }

    /**
     * Recupera o valor de um determinado campo
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param string $key nome do campo
     * @return mixed Valor do campo
     */
    public function fieldValue($key) {
        try {
            $value = null;

            if ($this->_hasGetMethod($key)) {
                return $this->_executeGetMethod($key);
            }

            if (!$this->_checkMemberExistence($key)) {
                if (!isset($this->_dataholder[$key])) {
                    return null;
                }

                if (gettype($this->_dataholder[$key]) == 'NULL') {
                    return null;
                }

                $value = $this->_dataholder[$key];
            } else {
                $value = $this->$key;
            }

            $res = $this->metadata()->getField($key);

            if (!empty($res['options']['format']) && !is_null($value)) {
                switch ($res['type']) {
                    case 'int':
                    case 'integer':
                    case 'float':
                    case 'double':
                        return sprintf($res['options']['format'], $value);
                        break;

                    case 'date':
                    case 'datetime':
                        if (!empty($value)) {
                            return strftime($res['options']['format'], strtotime($value));
                        }
                        break;
                }
            }

            if (!empty($res['options']['formatter'])) {
                return call_user_func_array($res['options']['formatter'], array($value));
            }

            return $value;
        } catch (Exception $e) {
            // Lumine_Log::warning( 'Campo nao encontrado: '.$key);
            // se encontrar, retorna o que encontrou
            if (!$this->_checkMemberExistence($key)) {
                if (isset($this->_dataholder[$key])) {
                    return $this->_dataholder[$key];
                }
            } else {
                return $this->$key;
            }
        }
    }

    /**
     * Remove todas as expressoes de atualizacao
     * 
     * @author Hugo Ferreira da Silva 
     * @return void
     */
    public function clearUpdateExpression() {
        $this->_updateExpressions = array();
    }

    /**
     * Remove uma expressao para atualizacao.
     * 
     * @param string $field
     * @author Hugo Ferreira da Silva
     * @return void
     */
    public function removeUpdateExpression($field) {
        unset($this->_updateExpressions[$field]);
    }

    /**
     * Adiciona uma expressao para atualizacao.
     * 
     * <p>Por padrao, quando o update vai ser efetuado, Lumine
     * verifica os campos e simplesmente atribui o valor 
     * ao campo, exemplo:</p>
     * 
     * <code>
     * $obj = new Pessoa();
     * $obj->get(1);
     * $obj->nome = 'Hugo';
     * $obj->save();
     * </code>
     * 
     * <p>Gerara a seguinte SQL:</p>
     * <code>
     * UPDATE pessoa SET nome = 'Hugo' WHERE codpessoa = 1;
     * </code>
     * 
     * <p>Caso voce precise efetuar um update executando uma funcao 
     * do banco, utilize este metodo, por exemplo:</p>
     * 
     * <code>
     * $obj = new Pessoa();
     * $obj->get(1);
     * $obj->addUpdateExpression('nome', 'replace(nome, "hugo", ?)', 'Hugo Ferreira da Silva');
     * $obj->save();
     * </code>
     * 
     * <p>Executara uma SQL semelhante a abaixo:</p>
     * <code>
     * UPDATE pessoa SET nome = replace(nome, "hugo", 'Hugo Ferreira da Silva') WHERE codpessoa = 1;
     * </code>
     * 
     * @param unknown_type $field
     * @param unknown_type $expression
     * @param unknown_type $args
     * @author Hugo Ferreira da Silva
     * @return void
     */
    public function addUpdateExpression($field, $expression, $args = null) {
        $this->_updateExpressions[$field]['expression'] = $expression;
        $this->_updateExpressions[$field]['args'] = $args;
    }

    //////////////////////////////////////////////////////////////////
    // metodos depreciados, existem por questoes de compatibilidade //
    //////////////////////////////////////////////////////////////////
    /**
     * @deprecated
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param string $groupStr
     * @return Lumine_Base
     * @see Lumine_Base::group()
     */
    public function groupBy($groupStr) {
        Lumine_Log::debug("Depreciado, use group");
        return $this->group($groupStr);
    }

    /**
     * @see Lumine_Base::order()
     * @deprecated
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $orderStr
     * @return Lumine_Base
     */
    public function orderBy($orderStr) {
        Lumine_Log::debug("Depreciado, use order");
        return $this->order($orderStr);
    }

    /**
     * @see Lumine_Base::where()
     * @deprecated
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $whereStr
     * @return Lumine_Base
     */
    public function whereAdd($whereStr) {
        Lumine_Log::debug("Depreciado, use where");
        $args = func_get_args();
        return call_user_func_array(array($this, 'where'), $args);
    }

    /**
     * @deprecated
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param Lumine_Base $obj
     * @param string $type
     * @param string $alias
     * @param string $linkName
     * @param string $linkTo
     * @return Lumine_Base
     * @see Lumine_Base::join()
     */
    public function joinAdd(Lumine_Base $obj, $type = 'INNER', $alias = '', $linkName = null, $linkTo = null) {
        Lumine_Log::debug("Depreciado, use join");
        return $this->join($obj, $type, $alias, $linkName, $linkTo);
    }

    /**
     * @see Lumine_Base::select()
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param string $data
     * @return Lumine_Base
     * @deprecated
     */
    public function selectAdd($data) {
        Lumine_Log::debug("Depreciado, use select");
        return $this->select($data);
    }

    //////////////////////////////////////////////////////////////////////
    // FUNCOES AUXILIARES
    //////////////////////////////////////////////////////////////////////

    /**
     * Executa uma query definida pelo usuario
     * @author Hugo Ferreira da Silva
     * @param string $sql Comando SQL a ser executado
     * @link http://www.hufersil.com.br/
     * @throws Lumine_SQLException
     * @return int Numero de registros encontrados / afetados
     */
    public function query($sql) {
        try {
            $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_QUERY, $this, $sql));
            $rs = $this->_execute($sql);
            $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_QUERY, $this, $sql));

            return $this->numrows();
        } catch (Lumine_SQLException $lse) {
            throw new Lumine_SQLException($lse->connection, $lse->sql, $lse->getMessage());
        }
    }

    /**
     * Adiciona dados a uma lista de multi-insert
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return void
     */
    public function addMultiInsertItem() {
        $sql = $this->_getSQL(self::SQL_INSERT);
        $sql = preg_replace('@^INSERT\s*INTO.*?\(.+?\)\s*VALUES\s*\((.+?)\)$@', '($1)', $sql);

        $this->_multiInsertList[] = $sql;
    }

    /**
     * Efetua um comando de multi-insercao
     * Ex: INSERT INTO tabela (campo1, campo2, campo3) VALUES (...,...,...), (...,...,...), (...,...,...)
     * @param boolean $ignoreAutoIncrement nao inclui campos auto-incrementaveis no insert
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return boolean
     */
    public function multiInsert($ignoreAutoIncrement = true) {

        $sql = $this->_getMultiInsertSQL($ignoreAutoIncrement);

        if ($sql == false) {
            return false;
        }

        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_MULTI_INSERT, $this, $sql));         // dispara o pre-evento
        $this->_execute($sql);                // executa a insercao
        $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_MULTI_INSERT, $this, $sql));         // dispara o pos evento

        return true;                   // retorna true
    }


    //----------------------------------------------------------------------//
    // Metodos protegidos                                                   //
    //----------------------------------------------------------------------//	

    /**
     * Recupera a "ponte" (dialeto) para este objeto
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return Lumine_Dialect_IDialect
     */
    protected function _getDialect() {
        $obj = Lumine_Dialect_Factory::get($this);
        return $obj;
    }

    /**
     * Inicializacao da classe, chamada no construtor
     * Aqui serao adicionadas as chamadas para adicionar as propriedades da classe
     * para mapeamento.
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     */
    protected function _initialize() {
        
    }

    /**
     * 
     * Efetua um comando de multi-insercao
     * Ex: INSERT INTO tabela (campo1, campo2, campo3) VALUES (...,...,...), (...,...,...), (...,...,...)
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param boolean $ignoreAutoIncrement nao inclui campos auto-incrementaveis no insert
     * @return string
     */
    protected function _getMultiInsertSQL($ignoreAutoIncrement = true) {
        if (empty($this->_multiInsertList)) {             // se nao ha itens na lista
            Lumine_Log::warning('nao ha itens para inserir com o MULTI-INSERT');    // envia um alerta no log
            return false;                  // retorna falso
        }

        Lumine_Log::warning('Iniciando multi-insert');           // log informa que iniciou

        $schema = $this->_getConfiguration()->getOption('schema_name');       // tenta pega o nome do schema

        if (!empty($schema)) {                 // se foi informado o schema
            $schema .= '.';                  // adiciona um ponto como separador
        }

        $columns = array();                  // lista das colunas da tabela

        foreach ($this->metadata()->getFields() as $name => $prop) {           // para cada item da definicao
            if (!empty($prop['options']['autoincrement']) && $ignoreAutoIncrement == true) {   // se for auto-inc. e for para igonrar
                continue;                  // pula este campo
            }
            $columns[] = $prop['column'];              // adiciona o campo na lista de insercao
        }

        $sql = "INSERT INTO " . $schema . $this->metadata()->getTablename();         // monta a consulta
        $sql .= '(' . implode(', ', $columns) . ')';           // adiciona os nomes dos campos
        $sql .= " VALUES ";
        $sql .= implode(', ' . PHP_EOL, $this->_multiInsertList);         // adiciona os valores

        return $sql;
    }

    /**
     * Recupera a lista de entidades relacionadas com a classe atual
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return array Lista de elementos Lumine_Base relacionados a classe atual
     */
    protected function _getJoinList() {
        return $this->_join_list;
    }

    /**
     * Retorna a respresentacao de uniao de classes em string
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return array Lista contendo strings dos relacionamentos relacionados a classe
     */
    protected function _getStrJoinList() {
        return $this->_join;
    }

    /**
     * Monta clausulas where a partir das propriedades da classe 
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return string String contendo as condicoes montadas a partir dos campos
     */
    protected function _makeWhereFromFields() {
        $where = array();
        foreach ($this->metadata()->getFields() as $name => $def) {
            $val = $this->fieldValue($name);

            if (gettype($val) == 'NULL') {
                continue;
            }

            $str = sprintf('{%s.%s} = ?', $this->metadata()->getClassname(), $name);
            $where[] = Lumine_Parser::parsePart($this, $str, array($val));
        }

        $str_where = implode(' AND ' . PHP_EOL . "\t", $where);
        $str_where = $str_where;

        return $str_where;
    }

    /**
     * Prepara uma SQL para efetuar uma consutla (SELECT)
     *
     * <code>
     * $total = $obj->count();
     * $total_distinct = $obj->count('distinct nome');
     * </code>
     * @param boolean $forCount Define sera uma consulta para contagem ou nao
     * @param string $what String contendo logica para contagem
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return int Numero de registros encontrados
     */
    protected function _prepareSQL($forCount = false, $what = '*') {
        $sql = "SELECT ";
        if ($forCount == false) {
            if (empty($this->_data)) {
                reset($this->_join_list);

                foreach ($this->_join_list as $ent) {
                    $this->selectAs($ent);
                }
            }

            $sql .= Lumine_Parser::parseSQLValues($this, implode(', ', $this->_data));
        }

        if ($forCount == true && !empty($what)) {
            $sql .= ' count(' . Lumine_Parser::parseSQLValues($this, $what) . ') as "lumine_count" ';
        }

        $sql .= PHP_EOL . " FROM ";
        $list = array();

        reset($this->_from);

        foreach ($this->_from as $obj) {
            $list[] = Lumine_Parser::parseFromValue($obj);
        }
        $sql .= implode(', ', $list);

        if (count($this->_join_list) > 1) {
            $sql .= Lumine_Parser::parseJoinValues($this, $this->_join_list);
        }

        $where = $this->_makeWhereFromFields();

        if (!empty($this->_where)) {
            reset($this->_where);

            // para cada condicao em where
            foreach ($this->_where as $i => $item) {
                // tiramos espacos em branco
                $item = trim($item);
                // se iniciar com OR ou AND
                if (preg_match('@^\b(or|and)\b@i', $item)) {
                    // somente adicionamos na clausula
                    $where .= ' ' . $item;
                    // do contrario
                } else {
                    // o padrao eh AND
                    $where .= empty($where) ? $item : ' AND ' . $item;
                }
                $where .= PHP_EOL . "\t";
            }
        }

        if (!empty($where)) {
            $sql .= PHP_EOL . " WHERE " . Lumine_Parser::parseSQLValues($this, $where);
        }

        if (!empty($this->_group)) {
            $sql .= PHP_EOL . " GROUP BY " . Lumine_Parser::parseSQLValues($this, implode(', ', $this->_group));
        }

        if (!empty($this->_having)) {
            $sql .= PHP_EOL . " HAVING " . Lumine_Parser::parseSQLValues($this, implode(' AND ', $this->_having));
        }

        if (!empty($this->_order)) {
            $sql .= PHP_EOL . " ORDER BY " . Lumine_Parser::parseSQLValues($this, implode(', ', $this->_order));
        }

        $sql .= PHP_EOL . $this->_getConnection()->setLimit($this->_offset, $this->_limit);

        return $sql;
    }

    /**
     * Prepara um SQL para insercao (INSERT)
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param mixed $opt
     * @return string String pronta para efetuar um INSERT
     */
    protected function _insertSQL($opt = null) {
        $columns = array();
        $values = array();
        $schema = $this->_getConnection()->getOption('schema_name');
        $empty_as_null = $this->_getConfiguration()->getOption('empty_as_null');

        if (!empty($schema)) {
            $schema .= '.';
        }

        foreach ($this->metadata()->getFields() as $name => $def) {
            if ($this->_hasGetMethod($name)) {
                $val = $this->_executeGetMethod($name);

                // se estiver nulo e for auto-increment
                if (is_null($val) && !empty($def['options']['autoincrement'])) {
                    // passa para o proximo campo
                    continue;
                }

                // aqui permitimos entrar mesmo se for autoincrement
                // desde que tenha valor
                if (is_null($val) && isset($def['options']['default'])) {
                    $val = Lumine_Parser::getParsedValue($this, $def['options']['default'], $def['type'], false, true);
                } else {
                    $valor = Lumine_Parser::truncateValue($def, $val);
                    $val = Lumine_Parser::getParsedValue($this, $val, $def['type']);
                }

                $values[] = $val;
                $columns[] = $def['column'];

                continue;
            }

            // se o membro existir
            if ($this->_checkMemberExistence($name)) {
                // pega o valor
                $val = $this->fieldValue($name);

                // se estiver nulo e nao for auto-increment
                if (is_null($val) && empty($def['options']['autoincrement'])) {
                    // se tiver um valor padrao
                    if (isset($def['options']['default'])) {
                        // pega o valor padrao
                        $val = Lumine_Parser::getParsedValue($this, $def['options']['default'], $def['type'], false, true);
                        // se nao grava como null 
                    } else {
                        $val = 'NULL';
                    }

                    // coloca a coluna
                    $columns[] = $def['column'];
                    // coloca o valor
                    $values[] = $val;

                    // se estiver nulo e for autoincrement
                } else if (is_null($val) && !empty($def['options']['autoincrement'])) {
                    // nao faz nada
                    continue;

                    // se tiver valor
                } else {
                    // coloca pra inserir
                    $valor = Lumine_Parser::truncateValue($def, $this->$name);
                    $val = Lumine_Parser::getParsedValue($this, $valor, $def['type']);
                    $columns[] = $def['column'];
                    $values[] = $val;
                }

                continue;
            }

            // se nao for membro da classe e estiver no data-holder
            if (array_key_exists($name, $this->_dataholder)) {

                $val = $this->_dataholder[$name];

                if (is_null($val) && !empty($def['options']['autoincrement'])) {
                    continue;
                }

                $val = $this->getStrictValue($name, $val);
                $columns[] = $def['column'];

                if (!($val instanceof Lumine_Base)) {
                    if ($val === '' && !empty($empty_as_null)) {
                        $values[] = 'NULL';
                    } else if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $valor = Lumine_Parser::truncateValue($def, $this->_dataholder[$name]);
                        $values[] = Lumine_Parser::getParsedValue($this, $valor, $def['type']);
                    }
                } else {
                    //print_r($def);
                    $valor = Lumine_Parser::truncateValue($def, $val->$def['linkOn']);
                    $values[] = Lumine_Parser::getParsedValue($this, $valor, $def['type']);
                }
                continue;
            }

            if (array_key_exists('default', $def['options']) && is_null($this->$name)) {

                if (substr($def['options']['default'], 0, strlen(Lumine::DEFAULT_VALUE_FUNCTION_IDENTIFIER)) != Lumine::DEFAULT_VALUE_FUNCTION_IDENTIFIER) {
                    $this->$name = $def['options']['default'];
                }

                $columns[] = $def['column'];
                $values[] = Lumine_Parser::getParsedValue($this, $def['options']['default'], $def['type'], false, true);
                continue;
            }

            if (!empty($def['options']['autoincrement'])) {
                $sequence_type = empty($def['option']['sequence_type']) ? '' : $def['option']['sequence_type'];
                // se nao estiver definida na entidade, tenta pegar a padrao para todo o banco
                $st = $this->_getConnection()->getOption('sequence_type');
                if (!empty($st)) {
                    $sequence_type = $st;
                }

                switch ($sequence_type) {
                    case Lumine_Sequence::COUNT_TABLE:
                        break;

                    case Lumine_Sequence::SEQUENCE:
                        break;

                    case Lumine_Sequence::NATURAL:
                    default:
                        // se for natural do banco
                        // nao faz nada, nem insere na lista de insercao
                        // o banco que se vire em pegar o padrao
                        break;
                }

                continue;
            }
        }

        if (empty($columns)) {
            Lumine_Log::warning('Sem valores para inserir');
            return false;
        }

        $sql = "INSERT INTO " . $schema . $this->metadata()->getTablename() . "(";
        $sql .= implode(', ', $columns) . ") VALUES (";
        $sql .= implode(', ', $values) . ")";

        return $sql;
    }

    /**
     * Prepara um SQL para atualizacao (UPDATE)
     *
     * @param boolean $whereAddOnly Prepara o SQL somente com os parametros definidos com where
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return SQL montada para atualizacao
     */
    protected function _updateSQL($whereAddOnly = false) {

        $fields = array();
        $values = array();
        $where = array();

        $old = $this->alias();
        $this->alias('');

        $a = $this->alias();

        if (!empty($a)) {
            $a .= '.';
        }

        foreach ($this->metadata()->getFields() as $name => $def) {
            // se setou uma expressa (_updateExpression)
            if (array_key_exists($name, $this->_updateExpressions)) {

                $exp = $this->_updateExpressions[$name];

                // guardamos o valor anterior
                $bkp = $this->$name;
                // setamos o valor que vais ser feito o binding
                $this->$name = $exp['args'];

                $fields[] = $a . $def['column'];
                $values[] = str_replace('?', Lumine_Parser::getParsedValue($this, $exp['args'], $def['type']), $exp['expression']);

                // voltamos o valor anterior
                $this->$name = $bkp;

                continue;
            }

            // se o membro existe na classe mas nao esta setado
            if ($this->_checkMemberExistence($name) && !isset($this->$name)) {
                // se o valor for igual do dataset
                if (!array_key_exists($name, $this->_dataholder)) {
                    // passa para o proximo
                    continue;
                }
            }

            $valor = $this->fieldValue($name);

            // se este campo existir no DataHolder original e o valor for o mesmo
            if (array_key_exists($name, $this->_original_dataholder) && $this->_original_dataholder[$name] == $valor) {
                // nao coloca na lista de atualizacao
                continue;
            }

            $fields[] = $a . $def['column'];
            // $values[] = Lumine_Parser::getParsedValue($this, $this->_dataholder[ $name ], $def['type']);
            $val = $this->getStrictValue($name, $valor);
            $columns[] = $def['column'];

            if (!($val instanceof Lumine_Base)) {
                if ($val === '' && !empty($empty_as_null)) {
                    $values[] = 'NULL';
                } else if (is_null($val)) {
                    $values[] = 'NULL';
                } else {
                    $valor = Lumine_Parser::truncateValue($def, $valor);
                    $values[] = Lumine_Parser::getParsedValue($this, $valor, $def['type']);
                }
            } else {
                $valor = Lumine_Parser::truncateValue($def, $val->$def['linkOn']);
                $values[] = Lumine_Parser::getParsedValue($this, $valor, $def['type']);
            }
        }


        if (empty($values)) {
            $this->alias($old);
            Lumine_Log::warning('nao foram encontradas alteracoes para realizar o update');
            return false;
        }

        $where_str = '';

        if ($whereAddOnly == true) {

            // para cada condicao em where
            foreach ($this->_where as $i => $item) {
                // tiramos espacos em branco
                $item = trim($item);
                // se iniciar com OR ou AND
                if (preg_match('@^\b(or|and)\b@i', $item)) {
                    // somente adicionamos na clausula
                    $where_str .= ' ' . $item;
                    // do contrario
                } else {
                    // o padrao eh AND
                    $where_str .= empty($where_str) ? $item : ' AND ' . $item;
                }
            }

            $where_str = Lumine_Parser::parseSQLValues($this, $where_str);
        } else {
            $pks = $this->metadata()->getPrimaryKeys();

            foreach ($pks as $id => $def) {
                $name = $def['name'];
                $value = $this->fieldValue($name);

                if (!empty($name)) {
                    $where[] = $a . $def['column'] . ' = ' . Lumine_Parser::getParsedValue($this, $value, $def['type']);
                }
            }

            $where_str = implode(' AND ', $where);
        }

        if (empty($where_str)) {
            $this->alias($old);
            throw new Lumine_Exception('nao e possivel atualizar sem definicao de chaves ou argumentos WHERE', Lumine_Exception::ERROR);
        }

        $table = $this->metadata()->getTablename();
        $schema = $this->_getConfiguration()->getOption('schema_name');
        if (!empty($schema)) {
            $table = $schema . '.' . $table;
        }

        $sql = "UPDATE " . $table . " " . $this->alias() . " SET ";
        $valores = array();

        for ($i = 0; $i < count($fields); $i++) {
            $valores[] = $fields[$i] . ' = ' . $values[$i];
        }

        $sql .= implode(', ', $valores);
        $sql .= " WHERE " . $where_str;

        $this->alias($old);

        return $sql;
    }

    /**
     * Prepara um SQL para DELETE
     *
     * @param boolean $whereAddOnly Prepara o SQL somente com os parametros definidos com where
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @throws Lumine_Exception
     * @return string SQL preparado para DELETE
     */
    protected function _deleteSQL($whereAddOnly = false) {
        $fields = array();
        $values = array();
        $where = array();
        $a = $this->alias();
        $this->alias('');

        $where_str = '';

        if ($whereAddOnly == true) {
            // para cada condicao em where
            foreach ($this->_where as $i => $item) {
                // tiramos espacos em branco
                $item = trim($item);
                // se iniciar com OR ou AND
                if (preg_match('@^\b(or|and)\b@i', $item)) {
                    // somente adicionamos na clausula
                    $where_str .= ' ' . $item;
                    // do contrario
                } else {
                    // o padrao eh AND
                    $where_str .= empty($where_str) ? $item : ' AND ' . $item;
                }
            }
            $where_str = Lumine_Parser::parseSQLValues($this, $where_str);
        } else {
            $pks = $this->metadata()->getPrimaryKeys();

            foreach ($pks as $id => $def) {
                $name = $def['name'];
                $valor = $this->fieldValue($name);

                if ($this->$name !== null) {
                    $where[] = $def['column'] . ' = ' . Lumine_Parser::getParsedValue($this, $valor, $def['type']);
                }
            }

            $where_str = implode(' AND ', $where);
        }

        $this->alias($a);

        if (empty($where_str)) {
            throw new Lumine_Exception('nao e possivel remover sem definicao de chaves ou argumentos WHERE', Lumine_Exception::ERROR);
        }

        $table = $this->metadata()->getTablename();
        $schema = $this->_getConfiguration()->getOption('schema_name');
        if (!empty($schema)) {
            $table = $schema . '.' . $table;
        }

        $sql = "DELETE FROM " . $table . " ";
        $sql .= " WHERE " . $where_str;

        return $sql;
    }

    /**
     * Salva os objetos determinantes para a insercao ou atualizacao do atual (Classe extendida)
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return void
     */
    protected function savePendingObjects() {
        // faremos uma iteracao nos membros da classe,
        // procurando itens que sejam chaves estrangeiras
        // Menos MTM e OTM

        foreach ($this->metadata()->getFields() as $name => $prop) {
            // para funciona corretamente, tem que ser
            // - chave estrangeira;
            // - chave primaria;
            // - esta classe deve estender a outra.

            if (!empty($prop['options']['primary']) && !empty($prop['options']['foreign']) && !empty($prop['options']['class'])) {
                Lumine_Log::debug('Classe pai: "' . $prop['options']['class'] . '"');
                // verifica se esta classe extende a outra
                $class = get_parent_class($this);

                if (strtolower($class) == strtolower($prop['options']['class'])) {
                    Lumine_Log::debug('Instanciando classe: "' . $prop['options']['class'] . '"');
                    // instancia o objeto
                    $obj = new $prop['options']['class'];
                    // verifica se o objeto que esta chamando tem o valor do objeto pai
                    $chave = $this->fieldValue($name);

                    // se tiver um valor
                    if (!is_null($chave)) {

                        // da um GET primeiro
                        $total = $obj->get($chave);

                        // pega os valores e coloca na classe
                        $list = $this->toArray();
                        foreach ($list as $chave => $valor) {
                            $obj->setFieldValue($chave, $valor);
                        }
                        // se nao encontrou
                        if ($total == 0) {
                            // insere
                            $obj->insert();
                        } else { // se achou
                            // atualiza
                            $obj->save();
                        }

                        // coloca o valor no campo apropriado da classe chamadora
                        $this->setFieldvalue($name, $obj->fieldValue($prop['options']['linkOn']));
                    } else { // se nao tiver um valor
                        Lumine_Log::debug('Valor nao encontrado para: "' . $obj->metadata()->getClassname() . '" com o nome de campo ' . $name . '-> ' . $chave);

                        $list = $this->toArray();
                        foreach ($list as $chave => $valor) {
                            $obj->setFieldValue($chave, $valor);
                        }
                        $obj->insert();
                        $this->setFieldValue($name, $obj->fieldValue($prop['options']['linkOn']));
                    }
                }
            }
        }
    }

    /**
     * Salva os objetos vinculados a este que dependem deste 
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return void
     */
    protected function saveDependentObjects() {
        $schema = $this->_getConfiguration()->getOption('schema_name');
        if (!empty($schema)) {
            $schema .= '.';
        }

        foreach ($this->metadata()->getRelations() as $name => $def) {
            switch ($def['type']) {
                case Lumine_Metadata::ONE_TO_MANY:
                    $list = $this->fieldValue($name);

                    if (!empty($list) && is_array($list)) {
                        foreach ($list as $val) {

                            if ($val instanceof Lumine_Base) {
                                $relname = $this->metadata()->getClassname();

                                try {
                                    $field = $val->metadata()->getRelation($relname, Lumine_Metadata::MANY_TO_ONE);
                                    $val->setFieldValue($field['name'], $this->$field['linkOn']);
                                    $val->save();
                                } catch (Lumine_Exception $e) {
                                    Lumine_log::warning('nao foi possivel encontrar o campo ' . $relname . ' em ' . $val->metadata()->getClassname());
                                }
                            }
                        }
                    }

                    break;

                case Lumine_Metadata::MANY_TO_MANY:
                    $list = $this->$name;

                    if (!empty($list) && is_array($list)) {
                        foreach ($list as $val) {
                            // se for uma instancia de Lumine_Base
                            if ($val instanceof Lumine_Base) {
                                // pega o valor da chave primaria
                                $f1 = $this->metadata()->getField($def['linkOn']);
                                $v1 = $this->fieldValue($def['linkOn']);
                                // salva o objeto
                                $val->save();

                                // valor do outro objeto
                                $rel = $val->metadata()->getRelation($this->metadata()->getClassname(), Lumine_Metadata::MANY_TO_MANY);

                                $f2 = $val->metadata()->getField($rel['linkOn']);
                                $v2 = $val->fieldValue($f2['name']);

                                // se ambos nao forem nulos
                                if (!is_null($v1) && !is_null($v2)) {
                                    // verifica se ja existe
                                    $sv1 = Lumine_Parser::getParsedValue($this, $v1, $f1['type']);
                                    $sv2 = Lumine_Parser::getParsedValue($val, $v2, $f2['type']);

                                    $sql = "SELECT * FROM " . $schema . $def['table'] . " WHERE ";
                                    $sql .= $def['column'] . '=' . $sv1;
                                    $sql .= ' AND ';
                                    $sql .= $rel['column'] . '=' . $sv2;

                                    $ref = new ReflectionClass(get_class($this));
                                    $ponte = $ref->newInstance();

                                    Lumine_Log::debug('Verificando existencia da referencia do objeto no banco: ' . $sql);
                                    $ponte->query($sql);

                                    // se nao existir
                                    if ($ponte->numrows() == 0) {
                                        // insert
                                        $sql = "INSERT INTO " . $schema . $def['table'] . "(%s, %s) VALUES (%s, %s)";
                                        $sql = sprintf($sql, $def['column'], $rel['column'], $sv1, $sv2);

                                        $ponte->query($sql);
                                    }

                                    $ponte->destroy();
                                }
                            } else {
                                // pega o valor do campo desta classe
                                $campo = $this->metadata()->getField($def['linkOn']);

                                $valor_pk = $this->fieldValue($campo['name']);

                                // se este objeto tem um valor no campo indicado
                                if (!is_null($valor_pk)) {
                                    // primeiro vemos se este valor ja nao existe
                                    $sql = "SELECT * FROM " . $schema . $def['table'] . " WHERE ";

                                    // pega o valor do campo desta entidade
                                    $valor_objeto = Lumine_Parser::getParsedValue($this, $valor_pk, $campo['type']);

                                    // instanciamos a classe estrangeira
                                    $this->_getConfiguration()->import($def['class']);

                                    $obj = new $def['class'];
                                    // pega o relacionamento com esta entidade
                                    $rel = $obj->metadata()->getRelation($this->metadata()->getClassname(), Lumine_Metadata::MANY_TO_MANY);
                                    $rel_def = $obj->metadata()->getField($rel['linkOn']);

                                    // ajusta o valor
                                    $valor_estrangeiro = Lumine_Parser::getParsedValue($obj, $val, $rel_def['type']);

                                    // termina a SQL
                                    $sql .= $def['column'] . '=' . $valor_objeto;
                                    $sql .= " AND ";
                                    $sql .= $rel['column'] . '=' . $valor_estrangeiro;

                                    $obj->query($sql);
                                    $res = $obj->numrows();

                                    // se nao encontrou
                                    if ($res == 0) {
                                        // insere
                                        $sql = "INSERT INTO %s (%s,%s) VALUES (%s,%s)";
                                        $sql = sprintf($sql, $schema . $def['table'], $def['column'], $rel['column'], $valor_objeto, $valor_estrangeiro);

                                        Lumine_Log::debug("Inserindo valor Many-To-Many: " . $sql);
                                        $obj->query($sql);
                                    }

                                    $obj->destroy();
                                } else {
                                    Lumine_Log::warning('A o campo "' . $pks[0]['name'] . ' da classe "' . $this->metadata()->getClassname() . '" nao possui um valor');
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Carrega os objetos que sao "preguicosos" (LAZY)
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @param mixed $except
     * @return void
     */
    protected function loadLazy($except = null) {
        foreach ($this->metadata()->getFields() as $name => $def) {
            if ($except != null && !empty($def['options']['class']) && $def['options']['class'] == $except) {
                continue;
            }

            if (!empty($def['options']['lazy']) && !empty($def['options']['class'])) {
                $this->setFieldValue($name, $this->fetchLink($name));
            }
        }

        foreach ($this->metadata()->getRelations() as $name => $def) {
            if ($except != null && !empty($def['class']) && $def['class'] == $except) {
                continue;
            }
            if (!empty($def['lazy']) && !empty($def['class'])) {
                $this->setFieldValue($name, $this->fetchLink($name));
            }
        }
    }

    //----------------------------------------------------------------------//
    // Metodos privados                                                     //
    //----------------------------------------------------------------------//
    /**
     * set implicitdo, para campos que pertencem a outras entidades
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public function __set($key, $val) {
        if (isset($this->_dataholder)) {
            $this->_dataholder[$key] = $val;
        }
    }

    /**
     * get implicito
     * 
     * para pegar valores de campos de outras entidades ou do resultado de uma select
     * que contenha uniao
     *  
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        $cfg = $this->_getConfiguration();
        if (!empty($cfg)) {
            if ($cfg->getOption('use_formatter_as_default') == true) {
                return $this->formattedValue($key);
            } else {
                return $this->fieldValue($key);
            }
        }
    }

    /**
     * Sobrecarga de metodo
     * 
     * <p>Utilizado para chamar plugins</p>
     * 
     * @author Hugo Ferreira da silva
     * @link http://www.hufersil.com.br
     * @param string $method Nome do metodo
     * @param args $args argumentos passados pelo usuario
     * @return mixed retorno do metodo do plugin
     */
    public function __call($method, $args) {
        return Lumine::runPlugin($method, $this, $args);
    }

    /**
     * recupera o valor ja formatado para o banco
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $key
     * @param mixed $val
     * @return mixed
     */
    private function getStrictValue($key, $val) {
        try {
            $res = $this->metadata()->getField($key);

            if (($val instanceof Lumine_Base) || gettype($val) == 'NULL') {
                //$this->_dataholder[ $key ] = $val;
                return $val;
            }

            switch ($res['type']) {
                case 'int':
                case 'integer':
                case 'boolean':
                case 'bool':
                    $val = sprintf('%d', $val);

                    break;

                case 'float':
                case 'double':
                    $val = sprintf('%f', $val);

                    break;

                case 'datetime':
                    $val = Lumine_Util::FormatDateTime($val);
                    break;

                case 'date':
                    if (preg_match('@^(\d{2})/(\d{2})/(\d{4})$@', $val, $reg)) {
                        if (checkdate($reg[2], $reg[1], $reg[3])) {
                            $val = "$reg[3]-$reg[2]-$reg[1]";
                        } else {
                            $val = "$reg[3]-$reg[1]-$reg[2]";
                        }
                    } else if (preg_match('@^(\d{4})-(\d{2})-(\d{2})$@', $val, $reg)) {
                        $val = $val;
                    } else if (is_numeric($val)) {
                        $val = date('Y-m-d', $val);
                    } else {
                        $val = date('Y-m-d', strtotime($val));
                    }
                    break;
            }

            // $this->_dataholder[ $key ] = $val;
            return $val;
        } catch (Exception $e) {
            // $this->_dataholder[ $key ] = $val;
            return $val;
        }
    }

    /**
     * checa se existe um determinado membro na classe atual
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $memberName nome do campo desejado
     * @return boolean
     */
    private function _checkMemberExistence($memberName) {
        if (empty($this->_classDefinition)) {
            $this->_classDefinition = get_class_vars($this->metadata()->getClassname());
        }

        return array_key_exists($memberName, $this->_classDefinition);
    }

    /**
     * Verifica a existencia de um determinado metodo na classe
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $methodName Nome do metodo
     * @return boolean
     */
    private function _checkMethodExistence($methodName) {
        if (empty($this->_classMethods)) {
            $this->_classMethods = get_class_methods($this->metadata()->getClassname());
        }
        return in_array($methodName, $this->_classMethods);
    }

    /**
     * checa se um metodo GET existe para um determinado campo
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $field nome do campo
     * @return boolean
     */
    private function _hasGetMethod($field) {
        $method = 'get' . ucfirst($field);
        return $this->_checkMethodExistence($method);
    }

    /**
     * verifica se um campo tem um metodo set
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $field nome do campo
     * @return boolean
     */
    private function _hasSetMethod($field) {
        $method = 'set' . ucfirst($field);
        return $this->_checkMethodExistence($method);
    }

    /**
     * executa o metodo get se existir de um determinado campo
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $field nome do campo
     * @return mixed
     */
    private function _executeGetMethod($field) {
        $method = 'get' . ucfirst($field);
        return $this->$method();
    }

    /**
     * executa o metodo set se existir de um determinado campo
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $field nome do campo
     * @return mixed
     */
    private function _executeSetMethod($field, $val) {
        $method = 'set' . ucfirst($field);
        return $this->$method($val);
    }

    /**
     * limpa os campos do objeto
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return void
     */
    private function _cleanFields() {
        // limpa os campos definidos pelo usuario
        foreach ($this->metadata()->getFields() as $name => $dummy) {
            $this->$name = null;
        }
        foreach ($this->metadata()->getRelations(FALSE) as $name => $dummy) {
            $this->$name = null;
        }
    }

    /**
     * Eftua a conexao com o banco de dados.
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @return void
     */
    protected function connect() {
        $this->_getConnection()->connect();
    }

    /**
     * Executa uma SQL no banco
     *
     * @param string $sql SQL a ser executada
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/lumine
     * @throws Lumine_SQLException
     * @return boolean True em caso de sucesso, false em caso de falhas.
     */
    protected function _execute($sql) {
        try {
            $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::PRE_EXECUTE, $this, $sql));
            $this->connect();
            $result = $this->_getDialect()->execute($sql);
            $this->dispatchEvent(new Lumine_Events_SQLEvent(Lumine_Event::POS_EXECUTE, $this, $sql));

            return $result;
        } catch (Lumine_SQLException $lse) {
            throw new Lumine_SQLException($lse->connection, $lse->sql, $lse->getMessage());
        }
    }

    /**
     * recupera um campo da classe pai
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $key
     * @return array configuracoes do campo
     * @throws Exception
     */
    protected function _getFromParent($key) {
        try {
            $super = $this->_getParentClass();

            if (is_null($super)) {
                throw new Exception('Super-Classe nao encontrada para ' . $this->metadata()->getClassname());
            }

            $field = $super->metadata()->getField($key);
            return $field;
        } catch (Exception $e) {
            throw new Exception('Campo nao encontrado');
        }
    }

    /**
     * Recupera a classe pai, caso essa classe herde outra
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return Lumine_Base
     */
    protected function _getParentClass() {
        $super = get_parent_class($this);
        if ($super == 'Lumine_Base') {
            return null;
        }

        $instance = new $super;
        return $instance;
    }

    /**
     * 
     * une classes para realizar consultas
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return void
     */
    protected function _joinSubClasses() {
        // lista de classes antecessoras a esta
        $classes = array();
        // pesquisa as classes "pais"
        for ($class = get_class($this); $class = get_parent_class($class); $classes[] = $class) {
            // se for Lumine_Base
            if (strtolower($class) == 'lumine_base') {
                // para a iteracao
                break;
            } else {
                $classes[] = $class;
            }
        }
        // se nao encontrou nenhuma
        if (empty($classes)) {
            // sai da rotina
            return;
        }

        // lista de objetos
        $lista_objetos = array($this);

        // cria um objeto para cada classe encontrada
        foreach ($classes as $classname) {
            $obj = new $classname;
            $lista_objetos[] = $obj;
        }

        // percorre a lista de tras pra frente
        for ($i = count($lista_objetos) - 1; $i >= 0; $i--) {
            // se nao for o primeiro objeto, 
            if (isset($lista_objetos[$i - 1])) {
                // une com o objeto anterior
                $lista_objetos[$i - 1]->join($lista_objetos[$i]);
            }
        }
    }

    /**
     * remove um objeto da lista de uniao
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param Lumine_Base $obj
     * @return void
     */
    protected function _removeFromJoin(Lumine_Base $obj, Lumine_Base $source=null) {
        $new = array();

        if (!empty($this->_join_list)) {
            foreach ($this->_join_list as $item) {
                if ($item != $obj) {
                    $new[] = $item;
                }
            }

            $this->_join_list = $new;
        }
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////// IMPLEMENTACOES DE INTERFACES ////////////////////
    ////////////////////////////////////////////////////////////////////
    /**
     * Implementacao de iterator, para usar com foreach
     * @return void
     */
    public function rewind() {
        Lumine_Log::debug('Reiniciando ponteiro do iterator');
        $this->_getDialect()->moveFirst();
        $this->_iteratorPosition = 0;
    }

    /**
     * Indica se a posicao atual eh valida
     * Implementacao de iterator, para usar com foreach
     * @return boolean
     */
    public function valid() {
        Lumine_Log::debug('Checando indice ' . $this->_iteratorPosition . ' para iterator');
        return $this->_iteratorPosition < $this->numrows() && $this->numrows() > 0;
    }

    /**
     * Retorna o array da posicao atual
     * Implementacao de iterator, para usar com foreach
     * @return Lumine_Base
     */
    public function current() {
        Lumine_Log::debug('Retornando valor do indice ' . $this->_iteratorPosition . ' para iterator');
        $this->fetch_row($this->_iteratorPosition);
        return $this;
    }

    /**
     * Move o ponteiro para o proximo registro
     * @return void
     */
    public function next() {
        Lumine_Log::debug('Movendo para o indice ' . ($this->_iteratorPosition + 1) . ' para iterator');
        ++$this->_iteratorPosition;
    }

    /**
     * Retorna o indice do registro atual
     * @return int;
     */
    public function key() {
        Lumine_Log::debug('Retornando o indice ' . ($this->_iteratorPosition + 1) . ' para iterator');
        return $this->_iteratorPosition;
    }

}

