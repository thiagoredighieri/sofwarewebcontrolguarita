<?php
/**
 * Classe abstrata para validacao
 * 
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 * @package Lumine
 */

/**
 * Classe abstrata para validacao
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
abstract class Lumine_Validator
{
	/**
	 * Indica que e para validar uma string
	 * @var string
	 */
	const REQUIRED_STRING = 'requiredString';
	/**
	 * Indica que e para validar um numero
	 * @var string
	 */
	const REQUIRED_NUMBER = 'requiredNumber';
	/**
	 * Valida o campo como sendo um email
	 * @var string
	 */
	const REQUIRED_EMAIL = 'requiredEmail';
	/**
	 * Valida o valor como sendo um CPF
	 * @var string
	 */
	const REQUIRED_CPF = 'requiredCpf';
	/**
	 * Valida o valor como sendo um CNPJ
	 * @var string
	 */
	const REQUIRED_CNPJ = 'requiredCnpj';
	/**
	 * Indica que o campo deve ser unico na tabela, nao pode haver outros registros com o mesmo valor
	 * @var string
	 */
	const REQUIRED_UNIQUE = 'requiredUnique';
	/**
	 * Validacao para o tamanho de uma string 
	 * @var string
	 */
	const REQUIRED_LENGTH = 'requiredLength';
	/**
	 * utiliza um callback (funcao) como validador
	 * @var string
	 */
	const REQUIRED_FUNCTION = 'requiredFunction';
	/**
	 * valida se o valor esta em formato de data
	 * @var string
	 */
	const REQUIRED_DATE = 'requiredDate';
	/**
	 * valida se o valor esta em formato de data/hora
	 * @var string
	 */
	const REQUIRED_DATE_TIME = 'requiredDateTime';

	/**
	 * Efetua a validacao
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj Objeto a ser validado
	 * @return array Lista de erros encontrados
	 */
	public static function validate(Lumine_Base $obj)
	{
		############################################################################
		## Aqui vamos checar todos os tipos padrao de validacao
		## e armazenar os resultados em um array
		## para que o objeto passe na validacao, todos os retornos devem ser TRUE
		## para isto, utilizaremos a interface de reflexao
		############################################################################
		// aqui armazenamos o resultado das validacoes
		$erros = array();
		
		$lista = $obj->listValidators();
		
		/* @var $item Lumine_Validator_AbstractValidator */
		foreach($lista as $item){
			if(array_key_exists($item->getField(), $erros)){
				continue;
			}
			
			$result = $item->execute($obj);
			if( !$result ){
				$erros[$item->getField()] = $item->getErrorMessage();
			}
		}
		
		return $erros;
	}

}


