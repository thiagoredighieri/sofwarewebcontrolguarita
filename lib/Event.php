<?php
/**
 * Classe de Eventos, usada no fluxo de eventos/ouvintes
 * @author Hugo Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine
 */

/**
 * Classe de Eventos, usada no fluxo de eventos/ouvintes
 * @author Hugo Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine
 */
class Lumine_Event {


	/**
	 * Disparado antes de efetuar um Select
	 * @var string
	 */
	const PRE_SELECT = 'preSelect';
	/**
	 * Disparado apos efetuar um Select
	 * @var string
	 */
	const POS_SELECT = 'posSelect';
	/**
	 * Disparado antes de inserir varios elementos
	 * @var string
	 */
	const PRE_MULTI_INSERT = 'onPreMultiInsert';
	/**
	 * Disparado apos inserir varios elementos
	 * @var string
	 */
	const POS_MULTI_INSERT = 'onPosMultiInsert';
	/**
	 * Disparado antes de recuperar um objeto
	 * @var string
	 */
	const PRE_GET = 'preGet';
	/**
	 * Disparado apos recuperar um objeto
	 * @var string
	 */
	const POS_GET = 'posGet';
	/**
	 * Disparado antes de inserir um objeto
	 * @var string
	 */
	const PRE_INSERT = 'preInsert';
	/**
	 * Disparado apos inserir um objeto
	 * @var string
	 */
	const POS_INSERT = 'posInsert';
	/**
	 * Disparado antes de atualizar um objeto
	 * @var string
	 */
	const PRE_UPDATE= 'preUpdate';
	/**
	 * Disparado apos atualizar um objeto
	 * @var string
	 */
	const POS_UPDATE = 'posUpdate';
	/**
	 * Disparado antes de salvar um objeto
	 * @var string
	 */
	const PRE_SAVE = 'preSave';
	/**
	 * Disparado apos salvar um objeto
	 * @var string
	 */
	const POS_SAVE = 'posSave';
	/**
	 * Disparado antes de deletar um objeto
	 * @var string
	 */
	const PRE_DELETE = 'preDelete';
	/**
	 * Disparado apos deletar um objeto
	 * @var string
	 */
	const POS_DELETE = 'posDelete';
	/**
	 * Disparado antes de executar uma query literal
	 * @var string
	 */
	const PRE_QUERY = 'preQuery';
	/**
	 * Disparado apos executar uma query literal
	 * @var string
	 */
	const POS_QUERY = 'posQuery';
	/**
	 * Disparado antes de efetuar uma consulta com find
	 * @var string
	 */
	const PRE_FIND = 'preFind';
	/**
	 * Disparado apos efetuar uma consulta com find
	 * @var string
	 */
	const POS_FIND = 'posFind';
	/**
	 * Disparado antes de ir para o proximo registro
	 * @var string
	 */
	const PRE_FETCH = 'preFetch';
	/**
	 * Disparado apos de ir para o proximo registro
	 * @var string
	 */
	const POS_FETCH = 'posFetch';
	/**
	 * Disparado antes de formatar o valor de um campo
	 * @var string
	 */
	const PRE_FORMAT = 'preFormat';
	/**
	 * Disparado apos formatar o valor de um campo
	 * @var string
	 */
	const POS_FORMAT = 'posFormat';
	/**
	 * Disparado antes de executar uma consulta
	 * @var string
	 */
	const PRE_EXECUTE = 'preExecute';
	/**
	 * Disparado apos executar uma consulta
	 * @var string
	 */
	const POS_EXECUTE = 'posExecute';
	/**
	 * Disparado quando criar um novo objeto
	 * @var string
	 */
	const CREATE_OBJECT = 'onCreateObject';
	/**
	 * Disparado quando houver erro de execucao de SQL
	 * @var string
	 */
	const EXECUTE_ERROR = 'onExecuteError';
	/**
	 * Disparado quando houver erro na conexao
	 * @var string
	 */
	const CONNECTION_ERROR = 'onConnectionError';
	/**
	 * Disparado antes da conexao ser efetuada
	 * @var string
	 */
	const PRE_CONNECT = 'preConnect';
	/**
	 * Disparado apos a conexao ser efetuada
	 * @var string
	 */
	const POS_CONNECT = 'posConnect';
	/**
	 * Disparado antes do fechamento da conexao
	 * @var string
	 */
	const PRE_CLOSE = 'preClose';
	/**
	 * Disparado apos o fechamento da conexao
	 * @var string
	 */
	const POS_CLOSE = 'posClose';
	/**
	 * disparado antes de criar a sql de update
	 * @var string
	 */
	const PRE_UPDATE_SQL = 'preUpdateSql';
	/**
	 * disparado depois de criar a sql de update
	 * @var string
	 */
	const POS_UPDATE_SQL = 'posUpdateSql';
	/**
	 * disparado antes de criar a sql de insert
	 * @var string
	 */
	const PRE_INSERT_SQL = 'preInsertSql';
	/**
	 * disparado depois de criar a sql de insert
	 * @var string
	 */
	const POS_INSERT_SQL = 'posInsertSql';
	/**
	 * disparado antes de criar a sql de delete
	 * @var string
	 */
	const PRE_DELETE_SQL = 'preDeleteSql';
	/**
	 * disparado depois de criar a sql de delete
	 * @var string
	 */
	const POS_DELETE_SQL = 'posDeleteSql';

	/**
	 * Tipo de evento disparado
	 * @var string
	 */
	public $type;
	/**
	 * permite que o evento continue para o proximo listener
	 *
	 * @var boolean
	 */
	private $propagate = true;

	/**
	 * Construtor
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/lumine
	 * @param $type
	 * @return Lumine_Event
	 */
	function __construct($type){
		$this->type = $type;
	}

	/**
	 * Indicacao para que o evento nao continue se propagando
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	public function stopPropagation(){
		$this->setPropagate(false);
	}

	/**
	 * Recupera o valor da propagacao
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return boolean
	 */
	public function getPropagate()
	{
	    return $this->propagate;
	}

	/**
	 * Altera o valor da propagacao
	 *
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param boolean $propagate
	 * @return boolean
	 */
	public function setPropagate($propagate)
	{
	    $this->propagate = $propagate;
	}
}

