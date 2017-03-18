<?php

/**
 * Verifica se o valor é um e-mail válido
 * 
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine_Validator
 */
class Lumine_Validator_Email extends Lumine_Validator_AbstractValidator {

	/**
	 * @see Lumine_Validator_AbstractValidator::execute()
	 */
	public function execute(Lumine_Base $obj){
		$value = $this->getFieldValue($obj);

		return Lumine_Util::validateEmail($value);
	}

}