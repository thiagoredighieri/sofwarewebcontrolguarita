<?php

/**
 * Valida se o valor é uma string valida.
 * 
 *
 * @author Hugo Ferreira da Silva
 *
 */
class Lumine_Validator_String extends Lumine_Validator_AbstractValidator {
	/**
	 * Tamanho minimo
	 * @var int
	 */
	protected $minLength;
	/**
	 * Tamanho maximo
	 * @var int
	 */
	protected $maxLength;
	
	/**
	 * Construtor
	 * 
	 * @param string $field
	 * @param string $errorMessage
	 * @param int $minLength
	 * @param int $maxLength
	 * @author Hugo Ferreira da Silva
	 */
	public function __construct($field, $errorMessage, $minLength = null, $maxLength = null){
		parent::__construct($field, $errorMessage);
		$this->minLength = $minLength;
		$this->maxLength = $maxLength;
	}
	
	/**
	 * 
	 * @see Lumine_Validator_AbstractValidator::execute()
	 */
	public function execute(Lumine_Base $obj){
		$value = $this->getFieldValue($obj);
	
		if(!is_scalar($value)){
			return false;
		}
	
		if(!is_string($value)){
			return false;
		}
	
		if(empty($value)){
			return false;
		}
		
		$meta = $this->getFieldMetadata($obj);
		
		if(!is_null($meta) && is_null($this->maxLength) && !empty($meta['length'])){
			$this->maxLength = $meta['length'];
		}
	
		if(!is_null($this->minLength) && strlen($value) < $this->minLength){
			return false;
		}
	
		if(!is_null($this->maxLength) && strlen($value) > $this->maxLength){
			return false;
		}
	
		return true;
	}
	
}