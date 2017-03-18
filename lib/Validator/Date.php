<?php

/**
 * Verifica se um valor é uma data válida
 * 
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine_Validator
 */
class Lumine_Validator_Date extends Lumine_Validator_AbstractValidator {
	/**
	 * Data minima
	 * @var string
	 */
	protected $minDate;
	/**
	 * Data maxima
	 * @var string
	 */
	protected $maxDate;

	/**
	 * Construtor
	 * 
	 * @param string $field
	 * @param string $errorMessage
	 * @param string $minDate
	 * @param string $maxDate
	 * @author Hugo Ferreira da Silva
	 */
	public function __construct($field, $errorMessage, $minDate = null, $maxDate = null){
		parent::__construct($field, $errorMessage);
		$this->minDate = $minDate;
		$this->maxDate = $maxDate;
	}
	
	/**
	 * @see Lumine_Validator_AbstractValidator::execute()
	 */
	public function execute(Lumine_Base $obj){
		$value = $this->getFieldValue($obj);
		
		if( ! preg_match('@^((\d{2}\/\d{2}\/\d{4})|(\d{4}-\d{2}-\d{2}))$@', $value, $reg)  ) {
			return false;
		
			// se digitou no formato com barras
		} else if( !empty($reg[2]) ) {
			list($dia,$mes,$ano) = explode('/', $reg[2]);
				
			// se nao for formato brasileiro e norte-americano
			if( !checkdate($mes,$dia,$ano) && !checkdate($dia,$mes,$ano) ) {
				return false;
			}
			
			// se digitou no formato ISO
		} else if( !empty($reg[3]) ) {
			list($ano,$mes,$dia) = explode('-', $reg[3]);
				
			// se for uma data valida
			if( !checkdate($mes,$dia,$ano) ) {
				return false;
			}
		}
		
		if(!is_null($this->minDate) || !is_null($this->maxDate)){
			$f = '%Y-%m-%d';
			$resultTime = strtotime(Lumine_Util::FormatDate($value, $f));
			
			if(!is_null($this->minDate) && $resultTime < strtotime(Lumine_Util::FormatDate($this->minDate, $f))){
				return false;
			}
			
			if(!is_null($this->maxDate) && $resultTime > strtotime(Lumine_Util::FormatDate($this->maxDate, $f))){
				return false;
			}
		}
		
		return true;
	}

}