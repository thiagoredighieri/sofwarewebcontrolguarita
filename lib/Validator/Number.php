<?php

/**
 * Verifica se o valor é um numero válido
 * 
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine_Validator
 */
class Lumine_Validator_Number extends Lumine_Validator_AbstractValidator {
	/**
	 * Valor minimo
	 * @var float
	 */
	protected $min;
	/**
	 * Valor maximo
	 * @var float
	 */
	protected $max;

	/**
	 * Construtor
	 * 
	 * @param string $field
	 * @param string $errorMessage
	 * @param float $min
	 * @param float $max
	 * @author Hugo Ferreira da Silva
	 */
	public function __construct($field, $errorMessage, $min = null, $max = null){
		parent::__construct($field, $errorMessage);
		$this->min = $min;
		$this->max = $max;
	}

	/**
	 * @see Lumine_Validator_AbstractValidator::execute()
	 */
	public function execute(Lumine_Base $obj){
		$value = $this->getFieldValue($obj);

		if(!is_scalar($value)){
			return false;
		}

		if(!is_numeric($value)){
			return false;
		}

		if(!is_null($this->min) && $value < $this->min){
			return false;
		}

		if(!is_null($this->max) && $value > $this->max){
			return false;
		}

		return true;
	}

}