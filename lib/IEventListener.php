<?php
/**
 * Interface para implementacao de EventListener
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Interface para implementacao de EventListener
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
interface Lumine_IEventListener
{
	/**
	 * Adiciona um novo ouvinte a instancia
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $evt nome do evento
	 * @param array|string $callback callback para quando o evento for disparado
	 * @return void
	 */
	function addEventListener($evt, $callback);
	/**
	 * Remove um determinado ouvinte 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $evt nome do ouvinte
	 * @param array|string $callback callback cadastrado
	 * @return void
	 */
	function removeEventListener($evt, $callback);
	/**
	 * Remove todos os ouvintes de um determinado evento
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $evt nome do evento
	 * @return void
	 */
	function removeAllListeners($evt);
	/**
	 * Dispara um evento
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Event $evt Objeto de evento
	 * @return void
	 */
	function dispatchEvent(Lumine_Event $evt );
	/**
	 * Destroi o objeto
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return void
	 */
	function __destruct();

}


?>