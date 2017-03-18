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
 * Interface para implementacao de plugins
 * 
 * @author Hugo Ferreira da silva
 * @link http://www.hufersil.com.br
 *
 */
interface ILumine_Plugin   {
	
	/**
	 * Retorna os metodos da classe
	 * 
	 * <p>Este metodo deve retornar os nomes dos metodos
	 * que poderao ser usados dentro do Lumine_Base,
	 * assim o usuario pode indicar quais metodos poderao
	 * ser escondidos do usuario no plugin.</p>
	 * 
	 * @author Hugo Ferreira da silva
	 * @link http://www.hufersil.com.br
	 * @return array nomes dos metodos da classe
	 */
	public function getMethodList();
	
}