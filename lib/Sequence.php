<?php
/**
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

Lumine::load('Sequence_Exception');

/**
 * Classe para poder gerenciar sequencias (auto_increments)
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Sequence extends Lumine_EventListener
{

	/**
	 * utilizar o do banco de dados
	 * @var int
	 */
	const NATURAL                 = 1;
	/**
	 * por criacao de uma tabela de sequencia
	 * @var int
	 */
	const SEQUENCE                = 2;
	/**
	 * por contagem de registros da tabela
	 * @var int
	 */
	const COUNT_TABLE             = 3;

	/**
	 * Objeto de referencia
	 * @var Lumine_Base
	 */
	private $obj                  = null;
	
	/**
	 * Objeto de sequencia
	 * @var Lumine_Sequence_ISequence
	 */
	private $seq_obj              = null;
	
	/**
	 * Construtor 
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return Lumine_Sequence
	 */
	public function __construct(Lumine_Base $obj)
	{
		$this->obj = $obj;
	}
	
	/**
	 * Recupera uma sequencia para um campo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $field
	 * @return string
	 */
	public function getSequence( $field )
	{
		$st = null;
		$con_st = $this->obj->_getConnection()->getOption('sequence_type');
		
		if( empty($field['sequence_type']))
		{
			$st = $con_st;
		} else {
			$st = $field['sequence_type'];
		}
		
		$dialect = $this->obj->_getConfiguration()->getProperty('dialect');
		
		switch($st)
		{
			case self::SEQUENCE:
				$class = $dialect."_Sequence";
				Lumine::load('Sequence_'.$class);
				$this->seq_obj = new $class( $obj, $field );
			break;
			
			case self::COUNT_TABLE:
				$class = $dialect."_Count";
				Lumine::load('Sequence_'.$class);
				$this->seq_obj = new $class( $obj, $field );
			break;
			
			case self::NATURAL:
			default:
				$class = $dialect."_Natural";
				Lumine::load('Sequence_'.$class);
				$this->seq_obj = new $class( $obj, $field );
		}
		
		$this->seq_obj->createSequence();
		return $this->seq_obj;
	}
	
	
}

?>