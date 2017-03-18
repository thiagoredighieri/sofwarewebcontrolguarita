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
class Lumine_Tree_Event extends Lumine_Event {
	
	const PRE_NODE_ADD    = 'preNodeAdd';
	const PRE_NODE_REMOVE = 'preNodeRemove';
	const PRE_NODE_DELETE = 'preNodeDelete';
	const PRE_NODE_SAVE   = 'preNodeSave';
	const POS_NODE_ADD    = 'posNodeAdd';
	const POS_NODE_REMOVE = 'posNodeRemove';
	const POS_NODE_DELETE = 'posNodeDelete';
	const POS_NODE_SAVE   = 'posNodeSave';
	
	/**
	 * Node enviado no evento
	 * @var Lumine_Tree_Node
	 */
	private $node;
	
	/**
	 * Tree enviada no evento
	 * @var Lumine_Tree
	 */
	private $tree;
	
	/**
	 * Construtor
	 * 
	 * @author Hugo Ferreira da Silva
	 * @param string $type
	 * @param Lumine_Tree_Node $node
	 * @param Lumine_Tree $tree
	 * @return Lumine_Tree_Event
	 */
	public function __construct($type, $node = null, $tree = null){
		parent::__construct($type);
		$this->node = $node;
		$this->tree = $tree;
	}
	
	/**
	 * Recupera a arvore usada no evento
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree
	 */
	public function getTree(){
		return $this->tree;
	}
	
	/**
	 * Recupera o node usado no evento
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return Lumine_Tree_Node
	 */
	public function getNode(){
		return $this->node;
	}
	
}