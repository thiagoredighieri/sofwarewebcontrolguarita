<?php
/**
 * Interface para geracao de formularios
 * @package Lumine_Form
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

/**
 * Interface para geracao de formularios
 * @package Lumine_Form
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
interface Lumine_Form_IForm
{
	/**
	 * Recupera o topo do formulario
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	public function getTop();
	/**
	 * Cria o formulario
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $action Url para onde sera enviado o formulario
	 * @return string
	 */
	public function createForm($action = null);
	/**
	 * Monta um campo de formulario para o campo, de acordo com o seu tipo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $nome Nome do campo
	 * @return string
	 */
	public function getInputFor($nome);
	/**
	 * Monta um calendario para um determinado campo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name Nome do campo
	 * @return string
	 */
	public function getCalendarFor($name);
	/**
	 * Recupera o rodape do formluario
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string 
	 */
	public function getFooter();
	/**
	 * Exibe uma lista de registros encontrados para a entidade
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param int    $offset
	 * @param int    $limit
	 * @param string $fieldSort
	 * @param string $order
	 * @return string
	 */
	public function showList($offset, $limit, $fieldSort = null, $order = null);
	/**
	 * Manipula uma acao vinda do formulario de controle
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $actionName  Nome da acao
	 * @param array  $values      Valores enviados
	 * @return boolean
	 */
	public function handleAction($actionName, array $values);
	/**
	 * Recupera o template para uma entidade
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Configuration $cfg Configuracao ativa
	 * @param string $className Nome da classe
	 * @return string
	 */
	public function getControlTemplate(Lumine_Configuration $cfg, $className);
	
	/**
	 * Copia os demais arquivos para a pasta de destino
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return void
	 */
	public function copyFiles($destination);
}



?>