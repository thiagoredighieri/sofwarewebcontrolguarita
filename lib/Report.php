<?php
/**
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Classe para geracao de relatorios
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */
class Lumine_Report extends Lumine_EventListener
{

	/**
	 * tipos de eventos disparados
	 * @var array
	 */
	protected $_event_types  = array(
		'onPreCreate','onCreateFinish'
	);
	
	/**
	 * Formatos de arquivos disponiveis
	 * @var array
	 */
	protected $_formats = array('HTML','PDF');
	/**
	 * colunas a serem geradas
	 * @var array
	 */
	protected $columns = array();
	/**
	 * formato escolhido
	 * @var string
	 */
	protected $format;
	/**
	 * Objeto de referencia de onde serao extraidos os dados
	 * @var Lumine_Base
	 */
	protected $obj;

	/**
	 * Construtor da classe
	 * @param Lumine_Base $obj Objeto a ser gerado o relat�rio
	 * @param string $format Formato final do arquivo
	 */
	function __construct( Lumine_Base $obj, $format = null )
	{
		$this->obj = $obj;
		
		if( !is_null($format) )
		{
			$this->setFormat( $format );
		}
	}
	
	/**
	 * adiciona uma coluna
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param $prop
	 * @return unknown_type
	 */
	function addColumn( $prop )
	{
		if( !isset($prop['name']) || !isset($prop['header']) || !isset($prop['width']) )
		{
			throw new Exception('Formato de coluna invalida. Voce deve informar as propriedades "name","header" e "width"');
		}
		
		$this->columns[] = $prop;
	}
	
	/**
	 * remove uma coluna do objeto de geracao do relatorio
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $prop
	 * @return void
	 */
	function removeColumn( $prop )
	{
		$nova = array();
		foreach( $this->columns as $column )
		{
			if( $column != $prop )
			{
				$nova[] = $column;
			}
		}
		$this->columns = $nova;
	}
	
	/**
	 * altera as colunas
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $arrayColumns
	 * @return void
	 */
	function setColumns( $arrayColumns )
	{
		$old = $this->columns;
		try {
			$this->columns = array();
			foreach( $arrayColumns as $column )
			{
				$this->addColumn( $column );
			}
		} catch(Exception $e ) {
			Lumine_Log::warning('Formato de coluna invalido, restaurando anterior...');
			$this->columns = $old;
		}
	}

	/**
	 * altera o formato
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $fm
	 * @return void
	 */
	public function setFormat( $fm )
	{
		if( !in_array($fm, $this->_formats) )
		{
			throw new Exception('Formato nao suportado: '.$fm);
		}
		$this->format = $fm;
	}
	
	/**
	 * Pega o formato
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	public function getFormat()
	{
		return $this->format;
	}

	
	/**
	 * Inicia uma geracao em PDF
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return Lumine_IReport
	 */
	public static function PDF( Lumine_Base $obj )
	{
		Lumine::load('Report_PDF');
		$obj = new Lumine_Report_PDF( $obj, 'PDF' );
		return $obj;
	}

}
?>