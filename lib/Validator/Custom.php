<?php

/**
 * Permite o usuario usar um algoritimo proprio para validação
 * 
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine_Validator
 */
class Lumine_Validator_Custom extends Lumine_Validator_AbstractValidator {

	/**
	 * Metodo a ser executado
	 * @var mixed
	 */
	protected $callback;

	/**
	 * Construtor
	 * 
	 * @param string $field
	 * @param string $errorMessage
	 * @param mixed $callback
	 * @author Hugo Ferreira da Silva
	 * @see Lumine_Validator_AbstractValidator::__construct()
	 */
	public function __construct($field, $errorMessage, $callback){
		parent::__construct($field, $errorMessage);
		$this->callback = $callback;
	}

	/**
	 * @see Lumine_Validator_AbstractValidator::execute()
	 */
	public function execute(Lumine_Base $obj){
		$value = $this->getFieldValue($obj);

		$result = true;
		
		// se for um array
		if(is_array($this->callback)){
			$result = call_user_func_array($this->callback,array($obj, $this->getField(), $value));
		}

		if(is_string($this->callback)){
			$function = new ReflectionFunction( $this->callback );
			$result = $function->invoke( $obj, $this->getField(), $value );
				
			unset($function);
		}

		return $result;
	}

}