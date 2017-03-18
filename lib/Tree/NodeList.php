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
 * Classe que representa uma lista de nos em uma estrutura de arvore
 * 
 * @author Hugo Ferreira da Silva
 * @package Lumine_Tree
 * @link http://www.hufersil.com.br/lumine
 *
 */

/**
 * Classe que representa uma lista de nos em uma estrutura de arvore
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 *
 */
class Lumine_Tree_NodeList extends Lumine_EventListener {
	/**
	 * Armazena os nos 
	 * @var array
	 */
	private $nodeList;
	/**
	 * Ponteiro para o no atual da lista
	 * @var int
	 */
	private $currNodePointer = -1;
	
	/**
	 * Construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param array $nodeList
	 * @return Lumine_Tree_NodeList
	 */
	public function __construct(array $nodeList = array()){
		$this->nodeList = array();
		
		foreach($nodeList as $item){
			$this->add( $item );
		}
	}
	
	/**
	 * Adiciona um no na lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node
	 * @return void
	 */
	public function add(Lumine_Tree_Node $node){
		if( !in_array($node,$this->nodeList) ){
			$this->nodeList[] = $node;
			$node->setNodeList( $this );
		}
	}
	
	/**
	 * Remove um no da lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node
	 * @return void
	 */
	public function remove(Lumine_Tree_Node $node){
		$newList = array();
		foreach($this->nodeList as $key => $currNode){
			if( $node != $currNode ){ 
				$newList[] = $currNode;
			} else {
				$node->setNodeList( null );
			}
		}
		
		$this->nodeList = $newList;
	}
	
	/**
	 * Retorna o proximo no do mesmo nivel
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function nextSibling(){
		$node = null;
		$this->currNodePointer++;
		if( $this->currNodePointer < $this->length() ){
			$node = $this->item($this->currNodePointer);
		}
		
		
		return $node;
	}
	
	/**
	 * Retorna o no anterior do mesmo nivel
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function prevSibling(){
		$node = null;
		$this->currNodePointer--;
		if( $this->$currNodePointer > -1 ){
			$node = $this->item($this->currNodePointer);
		}
		
		return $node;
	}
	
	/**
	 * Recupera um item de uma posicao especifica
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param int $idx Posicao do elemento na lista
	 * @return Lumine_Tree_Node
	 */
	public function item($idx){
		$node = null;
		if( array_key_exists($idx, $this->nodeList) ){
			$node = $this->nodeList[ $idx ];
		}
		
		return $node;
	}
	
	/**
	 * Recupera o elemento atual da lisat
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node O elemento atual na lista
	 */
	public function current(){
		$node = null;
		if( count($this->nodeList) > 0 ){
			$node = $this->nodeList[ $this->currNodePointer ];
		}
		
		return $node;
	}
	
	/**
	 * Reinicia o ponteiro
	 * 
	 * Reinicia o ponteiro e retorna o primeiro elemento da lista.
	 * Assim e possivel refazer a iteracao utilizando o nextSibling
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node O primeiro elemento da lista
	 */
	public function reset(){
		$this->currNodePointer = -1;
		return $this->first();
	}
	
	/**
	 * Envia o ponteiro para o ultimo elemento
	 * 
	 * Move o ponteiro para o ultimo elemento.
	 * Assim e possivel refazer a iteracao utilizando prevSibling.
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node O ultimo elemento da lista
	 */
	public function end(){
		$this->currNodePointer = $this->length();
		return $this->last();
	}
	
	/**
	 * Recupera o primeiro elemento da lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function first(){
		return $this->item( 0 );
	}
	
	/**
	 * Recupera o ultimo elemento da lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function last(){
		return $this->item( $this->length() - 1 );
	}
	
	/**
	 * Recupera o numero de elementos que a lista contem
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return int
	 */
	public function length(){
		return count($this->nodeList);
	}
	
	/**
	 * Recupera a posicao de um elemento dentro da lista
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param Lumine_Tree_Node $node
	 * @return int|boolean Inteiro quando encontrar a posicao, false se nao encontrar
	 */
	public function findPosition(Lumine_Tree_Node $node){
		return array_search($node, $this->nodeList);
	}
}