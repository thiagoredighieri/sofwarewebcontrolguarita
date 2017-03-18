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
 * Classe que representa um no de uma estrutura de arvore
 * 
 * @author Hugo Ferreira da Silva
 * @package Lumine_Tree
 * @link http://www.hufersil.com.br/lumine
 *
 */

/**
 * Classe que representa um no de uma estrutura de arvore
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 *
 */
class Lumine_Tree_Node extends Lumine_EventListener {
	
	/**
	 * Objeto relacionado ao no
	 * @var Lumine_Base
	 */
	private $obj;
	/**
	 * Nome do membro que servira como identificador para o objeto
	 * Geralmente usa-se uma chave primaria auto-incrementavel
	 * 
	 * @var string
	 */
	private $fieldId;
	/**
	 * Nome do membro que contem o valor do codigo do registro pai
	 * 
	 * @var string
	 */
	private $fieldParentId;
	/**
	 * Nos filhos do no atual
	 * @var Lumine_Tree_NodeList
	 */
	private $childNodes = null;
	
	/**
	 * NodeList ao qual este no pertence
	 * @var Lumine_Tree_NodeList
	 */
	private $nodeList = null;
	
	/**
	 * No pai
	 * @var Lumine_Tree_Node
	 */
	private $parentNode = null;
	
	/**
	 * Arvore deste no
	 * @var Lumine_Tree
	 */
	private $tree;
	
	/**
	 * Construtor
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Base $pObj
	 * @param string $pFieldId Nome do campo identificador do objeto
	 * @param string $pFieldParentId Nome do campo que contem o valor de identificacao para o registro pai
	 * @return Lumine_Tree_Node
	 */
	public function __construct(Lumine_Base $pObj, $pFieldId = null, $pFieldParentId = null){
		$this->obj = $pObj;
		
		// se nao informou nenhum dos dois campos
		if( is_null($pFieldId) && is_null($pFieldParentId) ){
			$res = Lumine_Tree::getTreeFields($pObj);
			
			$pFieldId = $res['fieldId'];
			$pFieldParentId = $res['fieldParentId'];
		}
		
		$this->fieldId = $pFieldId;
		$this->fieldParentId = $pFieldParentId;
		
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
	}
	
	/**
	 * Altera a arvore da qual o no pertence
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree $tree
	 * @return void
	 */
	public function setTree(Lumine_Tree $tree){
		$this->tree = $tree;
		
		if( !is_null($this->childNodes) ){
			for($i=0; $i<$this->childNodes->length(); $i++){
				$this->childNodes->item($i)->setTree( $tree );
			}
		}
	}
	
	/**
	 * Altera a lista de nodes ao qual este pertence
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_NodeList $nodeList
	 * @return void
	 */
	public function setNodeList($nodeList){
		if( !is_null($nodeList) && !($nodeList instanceof Lumine_Tree_NodeList)){
			throw new Exception('O argumento deve ser null ou do tipo Lumine_Tree_NodeList');
		}
		$this->nodeList = $nodeList;
	}
	
	/**
	 * Recupera a lista de nodes ao qual este no pertence
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_NodeList
	 */
	public function getNodeList(){
		return $this->nodeList;
	}
	
	/**
	 * Retorna a arvore que o elemento pertence
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree $tree
	 * @return Lumine_Tree
	 */
	public function getTree(){
		return $this->tree;
	}
	
	/**
	 * Recupera o nome do campo identificador
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	public function getFieldId(){
		return $this->fieldId;
	}
	
	/**
	 * Recupera o nome do campo identificador do no pai
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	public function getFieldParentId(){
		return $this->fieldParentId;
	}
	
	/**
	 * Verifica se o no atual e o raiz ou nao
	 * 
	 * O no raiz nao possui codigo de registro pai, logo
	 * seu conteudo e nulo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return boolean
	 */
	public function isRoot(){
		$valor = $this->getParentCode();
		return is_null( $valor );
	}
	
	/**
	 * Recupera o proximo irmao na lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function nextSibling(){
		$node = null;
		$idx = $this->getNodeList()->findPosition( $this );
		
		if( $idx !== false ){
			$idx++;
			$node = $this->getNodeList()->item($idx);
		}
		
		return $node;
	}
	
	/**
	 * Recupera o irmao anterior na lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function prevSibling(){
		$node = null;
		$idx = $this->getNodeList()->findPosition( $this );
		
		if( $idx !== false ){
			$idx--;
			$node = $this->getNodeList()->item($idx);
		}
		
		return $node;
	}
	
	/**
	 * Recupera o ultimo irmao na lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function last(){
		return $this->getNodeList()->last();
	}
	
	/**
	 * Recupera o primeiro irmao na lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function first(){
		return $this->getNodeList()->first();
	}
	
	/**
	 * Recupera o no raiz de uma arvore
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_TreeNode
	 */
	public function getRootNode(){
		$node = null;
		
		// se este ja for o no raiz
		if( $this->isRoot() ){
			// retorna este mesmo objeto
			$node = $this;
		} else {
		
			
			$parent = $this->getParentNode();
			
			while( !$parent->isRoot() ){
				$parent = $parent->getParentNode();
				
			}
			
			$node = $parent;
		}
		
		return $node;
	}
	
	/**
	 * Adiciona um no filho
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node No a ser adicionado
	 * @return void
	 */
	public function appendChild(Lumine_Tree_Node $node){
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::PRE_NODE_ADD, $node));
		
		$node->setParentNode( $this );
		
		$this->getChildNodes()->add($node);
		if( !is_null($this->getTree()) ){
			$this->setTree( $this->getTree() );
		}
		
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::POS_NODE_ADD, $node));
	}
	
	/**
	 * Remove um no filho
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node No a ser removido
	 * @return void
	 */
	public function removeChild(Lumine_Tree_Node $node){
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::PRE_NODE_REMOVE, $node));
		
		$this->getChildNodes()->remove($node);
		
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::POS_NODE_REMOVE, $node));
	}
	
	/**
	 * Recupera o pai de um no
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function getParentNode(){
		$code = $this->getParentCode();
		
		if( !is_null($code) && is_null($this->parentNode) ){
			$ref = new ReflectionClass( $this->obj->metadata()->getClassname() );
			$inst = $ref->newInstance();
			$inst->get( $this->fieldId, $code );
			
			$this->parentNode = new Lumine_Tree_Node($inst, $this->fieldId, $this->fieldParentId);
		}
		
		return $this->parentNode;
	}
	
	/**
	 * Altera o node pai deste node
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node
	 * @return void
	 */
	public function setParentNode( $node ){
		if( !is_null($node) && !($node instanceof Lumine_Tree_Node) ) {
			throw new Exception('$node deve ser do tipo Lumine_Tree_Node');
		}
		$this->parentNode = $node;
	}
	
	/**
	 * Recupera os nos filhos de uma arvore
	 * 
	 * Efetua a consulta uma unica vez.
	 * Depois que os nos filhos estao populados, sempre
	 * utiliza os nos encontrados
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param boolean $loadSubChilds Indica se os filhos deverao carregar seus sub-filhos
	 * @return Lumine_Tree_NodeList
	 */
	public function getChildNodes( $loadSubChilds = false ){
		// se ainda nao buscou os nos filhos
		if( is_null($this->childNodes) ){
			
			// cria um node list
			$this->childNodes = new Lumine_Tree_NodeList();
			
			// pega o codigo deste objeto
			$code = $this->obj->{$this->fieldId};
			
			// se tiver um codigo
			if( !is_null($code) ){
				// inicia a reflexao
				$ref = new ReflectionClass( $this->obj->metadata()->getClassname() );
				// recupera uma nova instancia
				$inst = $ref->newInstance();
				// consulta os filhos
				$inst->where('{'.$this->fieldParentId.'} = ?', $code)
					->find();
					
				// para cada filho encontrado
				while( $inst->fetch() ){
					// cria uma nova instancia
					$temp = $ref->newInstanceArgs();
					// popula com os dados encontrados
					$temp->populateFrom( $inst->toArray() );
					// cria um no
					$node = new Lumine_Tree_Node($temp, $this->fieldId, $this->fieldParentId);
					// informa que este objeto eh o parentNode
					$node->parentNode = $this;
					if( !is_null($this->getTree()) ){
						// informa a arvore
						$node->setTree( $this->getTree() );
					}
					
					if( $loadSubChilds ){
						$node->getChildNodes( $loadSubChilds );
					}
					
					// coloca na lista
					$this->childNodes->add($node);
				}
				
				// destroi o objetode consulta
				$inst->destroy();
			}
		}
		
		// retorna os nos filhos
		return $this->childNodes;
	}
	
	/**
	 * Remove um no e todos os seus dependentes
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param int $deleteMode Modo de remover
	 * @return void
	 */
	public function delete( $deleteMode = Lumine_Tree::DELETE_SET_NULL ){
		
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::PRE_NODE_DELETE, $this));
		
		$parent = $this->getParentNode();
		
		while( $child = $this->getChildNodes()->nextSibling() ){
			
			switch($deleteMode){
				case Lumine_Tree::DELETE_SET_NULL:
					if( !is_null($parentNode) ){
						$child->{$this->fieldParentId} = $parentNode->{$this->fieldId};
					} else {
						$child->{$this->fieldParentId} = null;
					}
					$child->getObj()->save();
				break;
				
				case Lumine_Tree::DELETE_CASCADE:
					$child->delete( $deleteMode );
				break;
			}
			
		}
		
		$this->obj->delete();
		$this->childNodes = null;
		$this->destroy();
		
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::POS_NODE_DELETE, $this));
	}
	
	/**
	 * Salva um no e toda sua arvore
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return void
	 */
	public function save(){
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::PRE_NODE_SAVE, $this));
		
		$this->obj->save();
		$id = $this->obj->{$this->fieldId};
		
		$this->getChildNodes()->reset();
		while( $child = $this->getChildNodes()->nextSibling() ){
			$child->{$this->fieldParentId} = $id;
			$child->save();
		}
		
		$this->dispatchEvent(new Lumine_Tree_Event(Lumine_Tree_Event::POS_NODE_SAVE, $this));
	}
	
	/**
	 * Destroi o no
	 * 
	 * Tambem destroi os nos filhos
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return void
	 */
	public function destroy(){
		if( !is_null($this->childNodes) ){
			$this->childNodes->reset();
			while( $child = $this->childNodes->nextSibling() ){
				$child->destroy();
			}
		}
		
		$this->parentNode = null;
		$this->childNodes = null;
		$this->obj->destroy();
		$this->obj = null;
	}
	
	/**
	 * Get implicito
	 * 
	 * Redireciona para o objeto Lumine_Base
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param string $key Nome do membro
	 * @return mixed
	 */
	public function __get( $key ){
		return $this->obj->{$key};
	}
	
	/**
	 * Set implicito
	 * 
	 * Redireciona para o objeto Lumine_Base
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param string $key Nome do membro
	 * @param mixed $value Valor do membro
	 * @return void
	 */
	public function __set( $key, $value ){
		$this->obj->{$key} = $value;
	}
	
	/**
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Base
	 */
	public function getObj(){
		return $this->obj;
	}
	
	/**
	 * Retorna a representacao do node como array
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param boolean $withChilds Indica se tambem e para pegar os nos filhos ou nao
	 * @return array
	 */
	public function toArray( $withChilds = true ){
		$node = $this->obj->toArray();
		$node['childNodes'] = array();
		
		if( $withChilds ){
			$list = $this->getChildNodes();
			while( $child = $list->nextSibling() ){
				$node['childNodes'][] = $child->toArray( $withChilds );
			}
		}
		
		return $node;
	}
	
	/**
	 * Retorna a representacao do node como objeto
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param boolean $withChilds Indica se tambem e para pegar os nos filhos ou nao
	 * @return array
	 */
	public function toObject( $withChilds = true ){
		$node = $this->obj->toObject();
		$node->childNodes = array();
		
		if( $withChilds ){
			$list = $this->getChildNodes();
			$list->reset();
			while( $child = $list->nextSibling() ){
				$node->childNodes[] = $child->toObject( $withChilds );
			}
		}
		
		return $node;
	}
	
	/**
	 * Retorna uma representacao XML do node
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param boolean $withChilds Indica se tambem deve incluir os nodes filhos
	 * @return string
	 */
	public function toXML( $withChilds = true ){
		$result[0] = $this->toArray( $withChilds );
		return Lumine_Util::array2xml( $result );
	}
	
	/**
	 * Retorna uma representacao JSON do node
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param boolean $withChilds Indica se tambem deve incluir os nodes filhos
	 * @return string
	 */
	public function toJSON( $withChilds = true ){
		$result = $this->toArray( $withChilds );
		return json_encode( $result );
	}
	
	/**
	 * Recupera o valor do codigo do no pai
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return mixed
	 */
	protected function getParentCode(){
		return $this->obj->{$this->fieldParentId};
	}
}