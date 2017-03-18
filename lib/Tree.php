<?php
################################################################################
#  Lumine - Database Mapping for PHP
#  Copyright (C) 2005  Hugo Ferreira da Silva
#  
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#  
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>
################################################################################
/**
 * Classe que representa uma arvore de dados
 * 
 * @author Hugo Ferreira da Silva
 * @package Lumine_Tree
 * @link http://www.hufersil.com.br/lumine
 *
 */

// Carrega as dependencias
Lumine::load('Tree_Node','Tree_NodeList','Tree_Event');

/**
 * Classe que representa uma arvore de dados
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 *
 */
class Lumine_Tree extends Lumine_EventListener {
	/**
	 * Constante para setar o valor como nulo
	 * @var int
	 */
	const DELETE_SET_NULL = 0x1;
	/**
	 * Constante para remover em cascata
	 * @var int
	 */
	const DELETE_CASCADE  = 0x2;
	/**
	 * Objeto relacionado a arvore
	 * @var Lumine_Base
	 */
	private $obj;
	/**
	 * No Raiz
	 * @var Lumine_Tree_Node
	 */
	private $root;
	
	/**
	 * Construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Base $pObj
	 * @param string $pFieldId Nome do campo identificador do objeto
	 * @param string $pFieldParentId Nome do campo que contem o valor de identificacao para o registro pai
	 * @return Lumine_Tree
	 */
	public function __construct(Lumine_Base $pObj, $pFieldId = null, $pFieldParentId = null){
		// tipos de eventos suportados
		$this->_event_types = array(
			Lumine_Tree_Event::PRE_NODE_ADD,
			Lumine_Tree_Event::PRE_NODE_REMOVE,
			Lumine_Tree_Event::PRE_NODE_SAVE,
			Lumine_Tree_Event::PRE_NODE_DELETE,
			Lumine_Tree_Event::POS_NODE_ADD,
			Lumine_Tree_Event::POS_NODE_REMOVE,
			Lumine_Tree_Event::POS_NODE_SAVE,
			Lumine_Tree_Event::POS_NODE_DELETE
		);
		
		$this->obj = $pObj;
		
		// se nao informou nenhum dos dois campos
		if( is_null($pFieldId) && is_null($pFieldParentId) ){
			$res = self::getTreeFields($pObj);
			
			$pFieldId = $res['fieldId'];
			$pFieldParentId = $res['fieldParentId'];
		}
		
		$this->fieldId = $pFieldId;
		$this->fieldParentId = $pFieldParentId;
		
		// ja cria o no raiz
		$this->setRootNode( new Lumine_Tree_Node($pObj, $pFieldId, $pFieldParentId) );
		
		// atribui os ouvintes
		$this->setEventListeners( $this->getRootNode() );
	}
	
	/**
	 * Carrega a arvore por completo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node retorna o node raiz
	 */
	public function load(){
		$this->getRootNode()->getChildNodes(true);
		return $this->getRootNode();
	}
	
	/**
	 * Set o no raiz da arvore
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node
	 * @return void
	 */
	public function setRootNode(Lumine_Tree_Node $node){
		$this->root = $node;
		$this->root->setParentNode( null );
		
		$node->setTree( $this );
	}
	
	/**
	 * Recupera o no raiz da arvore
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function getRootNode(){
		if( is_null($this->root) ){
			$this->setRootNode( $this->createNode() );
		}
		
		return $this->root;
	}
	
	/**
	 * Cria um node
	 * 
	 * Utiliza-se o objeto inicial como base para criar os nos filhos
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param array|object $data Dados iniciar para preencher o node
	 * @return Lumine_Tree_Node Node recem criado
	 */
	public function createNode($data = null){
		$ref = new ReflectionClass( $this->obj->metadata()->getClassname() );
		$inst = $ref->newInstance();
		$inst->populateFrom( $data );
		
		// cria o no
		$node = new Lumine_Tree_Node( $inst, $this->fieldId, $this->fieldParentId );
		
		// registra os ouvintes
		$this->setEventListeners($node);
		
		return $node;
	}
	
	/**
	 * Persiste os valores
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return void
	 */
	public function save(){
		$this->getRootNode()->save();
	}
	
	/**
	 * Remove um arvore
	 * 
	 * Pode-se escolher por remover a arvore inteira ou
	 * setar o valor dos filhos para o no anterior.
	 * 
	 * Caso o no anterior nao exista, os filhos ficam orfaos,
	 * ou seja, o parentId fica nulo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param int $deleteMode Modo de remocao
	 * @return void
	 */
	public function delete( $deleteMode = self::DELETE_CASCADE ){
		$this->getRootNode()->delete( $deleteMode );
	}
	
	/**
	 * Tenta recuperar dinamicamente os campos de identificacao
	 * 
	 * Para trabalhar com uma estrutura de arvore,
	 * e necessario que a tabela tenha:
	 * - Uma unica chave primaria
	 * - Uma chave estrangeira (FK) que referencie a chave primaria.
	 * 
	 * Nestas condicoes, Lumine consegue identificar quais sao os campos
	 * que compoe a arvore a partir de um dado objeto
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Base $pObj
	 * @return array Contendo os campos identificadores
	 */
	public static function getTreeFields(Lumine_Base $pObj){
		$pFieldID = null;
		$pFieldParentId = null;
		
		// pega as definicoes do objeto
		$def = $pObj->metadata()->getFields();
		// chave primaria
		$pk = null;
		
		// para cada definicao
		foreach( $def as $name => $item ){
			// se for chave primaria
			if( !empty($item['options']['primary']) ){
				// armazenamos seus dados
				$pk = $item;
				$pk['name'] = $name;
				continue;
			}
			
			// se for chave estrangeira
			if( !empty($item['options']['foreign']) ){
				// se o campo de linkagem for igual a pk e o nome da classe for o nome do objeto
				if( $item['options']['linkOn'] == $pk['name'] && $item['options']['class'] == $pObj->metadata()->getClassname() ){
					// encontramos os campos!
					$pFieldId = $pk['name'];
					$pFieldParentId = $name;
					break;
				}
			}
		}
		
		// se continuar nulo
		if( is_null($pFieldId) && is_null($pFieldParentId) ){
			// dispara excecao
			new Lumine_Exception('Os campos indicativos de arvore devem ser informados!');
		}
		
		$data['fieldId'] = $pFieldId;
		$data['fieldParentId'] = $pFieldParentId;
		
		return $data;
	}
	
	/**
	 * Redispara um evento
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Event $evt Evento a ser redisparado
	 * @return void
	 */
	public function redispatchEvent(Lumine_Tree_Event $evt){
		$this->dispatchEvent( $evt );
	}
	
	/**
	 * Atribui os ouvintes de evento
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node
	 * @return void
	 */
	protected function setEventListeners(Lumine_Tree_Node $node){
		$node->addEventListener( Lumine_Tree_Event::PRE_NODE_ADD, array($this, 'redispatchEvent') );
		$node->addEventListener( Lumine_Tree_Event::PRE_NODE_REMOVE, array($this, 'redispatchEvent') );
		$node->addEventListener( Lumine_Tree_Event::PRE_NODE_SAVE, array($this, 'redispatchEvent') );
		$node->addEventListener( Lumine_Tree_Event::PRE_NODE_DELETE, array($this, 'redispatchEvent') );
		$node->addEventListener( Lumine_Tree_Event::POS_NODE_ADD, array($this, 'redispatchEvent') );
		$node->addEventListener( Lumine_Tree_Event::POS_NODE_REMOVE, array($this, 'redispatchEvent') );
		$node->addEventListener( Lumine_Tree_Event::POS_NODE_SAVE, array($this, 'redispatchEvent') );
		$node->addEventListener( Lumine_Tree_Event::POS_NODE_DELETE, array($this, 'redispatchEvent') );
	}
}