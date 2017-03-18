<?php
/**
 * Interface para Geracao de Relatorios
 * @package Lumine_Report
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

/**
 * Interface para Geracao de Relatorios
 * @package Lumine_Report
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
interface Lumine_Report_IReport {
	/**
	 * Gera o relatorio
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	public function run();
	/**
	 * Faz a analise a partir de um modelo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param mixed $tpl
	 * @return void
	 */
	public function parseFromModel( $tpl );
	/**
	 * Envia a saida do relatorio 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	public function output();
	
}


?>