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
 * Classe abstrata para Add-ons
 * 
 * Utilizada para padronizar classes que serao utilizadas na engenharia
 * reversa ou em algum outro momento, a criterio do usuario.
 * 
 * @author Hugo Ferreira da silva
 * @package Lumine
 * @link http://www.hufersil.com.br
 *
 */

/**
 * Classe abstrata para Add-ons
 * 
 * Utilizada para padronizar classes que serao utilizadas na engenharia
 * reversa ou em algum outro momento, a criterio do usuario.
 * 
 * @author Hugo Ferreira da silva
 * @link http://www.hufersil.com.br
 *
 */
abstract class Lumine_AddOn {
	
	/**
	 * Titulo do add-on
	 * @var string
	 */
	protected $title = 'Lumine Add-On';
	
	/**
	 * Executa o add-on criado
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @param string $configurationFile Caminho absoluto do arquivo de configuracao
	 * @param array $params Parametros adicionais para executar o add-on
	 * @return void
	 */
	public function execute($configurationFile, array $params){
		
	}
	
	/**
	 * Exibe a tela de configuracao dentro do painel de engenharia reversa 
	 * 
	 * Caso nao for implementado, deve retornar false para nao
	 * ser exibido na tela de engenharia reversa
	 * 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @return void
	 */
	public function displayConfigScreen(){
		
	}
	
	/**
	 * Exibir ou na tela de engenharia reversa
	 * 
	 * Indica se tela de configuracoes deste add-on devera
	 * ser exibida na tela de engenharia reversa
	 * 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @return boolean
	 */
	public function showInReverseScreen(){
		return true;
	}
	
	/**
	 * Recupera o titulo a ser exibido na engenharia reversa
	 * 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @return string
	 */
	public function getTitle(){
		return $this->title;
	}
	
}