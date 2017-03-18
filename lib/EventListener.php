<?php
/**
 * Implementacao de EventListeners
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

// carrega o arquivo da interface desta classe
Lumine::load('IEventListener');

/**
 * Implementacao de EventListeners
 *
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_EventListener implements Lumine_IEventListener
{
	/**
	 * ouvintes registrados
	 * @var array
	 */
	private $_listeners     = array();
	/**
	 * tipos de eventos suportados
	 * @var array
	 */
	protected $_event_types   = array();

	/**
	 * @see Lumine_IEventListener::addEventListener()
	 */
	public function addEventListener($evt, $callback)
	{
		if( ! in_array($evt, $this->_event_types))
		{
			throw new Lumine_Exception('Tipo de evento nao suportado', Lumine_Exception::ERROR);
		}
		if( ! isset($this->_listeners[ $evt ]) )
		{
			$this->_listeners[ $evt ] = array();
		}

		$this->_listeners[ $evt ][] = $callback;
	}
	/**
	 * @see Lumine_IEventListener::removeEventListener()
	 */
	public function removeEventListener($evt, $callback)
	{
		if( ! in_array($evt, $this->_event_types))
		{
			throw new Lumine_Exception('Tipo de evento nao suportado', Lumine_Exception::ERROR);
		}
		if( ! isset($this->_listeners[ $evt ]) )
		{
			$this->_listeners[ $evt ] = array();
		}

		// $this->_listeners[ $evt ][] = $callback;
	}
	/**
	 * @see Lumine_IEventListener::removeAllListeners()
	 */
	public function removeAllListeners($evt)
	{
		if( ! in_array($evt, $this->_event_types))
		{
			throw new Lumine_Exception('Tipo de evento nao suportado', Lumine_Exception::ERROR);
		}
		$this->_listeners[ $evt ] = array();
	}
	/**
	 * @see Lumine_IEventListener::dispatchEvent()
	 */
	public function dispatchEvent(Lumine_Event $evt )
	{
		if( isset($this->_listeners[ $evt->type ]) )
		{
			foreach($this->_listeners[ $evt->type ] as $id => $callback)
			{
				call_user_func_array($callback, array($evt));

				// se pediu para parar a propagacao
				if( ! $evt->getPropagate() ) {
					// nao executa mais os listeners
					break;
				}
			}
		}
	}
	/**
	 * @see Lumine_IEventListener::__destruct()
	 */
	function __destruct()
	{
	    $this->_listeners = array();
	    $this->_event_types = array();
	}

}


?>