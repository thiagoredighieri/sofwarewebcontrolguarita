<?php

/**
 * Classe abstrata para regras de validacao
 * 
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 *
 */
abstract class Lumine_Validator_AbstractValidator {

	/**
	 * Campo a ser validado
	 * @var string
	 */
	protected $field;

	/**
	 * Mensagem em caso de erro
	 * @var string
	 */
	protected $errorMessage;

	/**
	 * Construtor
	 * 
	 * @param string $field Campo a ser validado
	 * @param string $errorMessage Mensagem em caso de erro
	 * @author Hugo Ferreira da Silva
	 */
	public function __construct($field, $errorMessage){
		$this->field = $field;
		$this->errorMessage = $errorMessage;
	}
	
	/**
	 * Recupera o nome do campo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	public function getField() {
		return $this->field;
	}
	
	/**
	 * Recupera os metadados do campo
	 * 
	 * @param Lumine_Base $target
	 * @author Hugo Ferreira da Silva
	 */
	public function getFieldMetadata(Lumine_Base $target){
		$result = null;
		try {
			$result = $target->metadata()->getField($this->getField());
		} catch(Exception $e){}
		
		return $result;
	}
	
	/**
	 * Recupera o valor da propriedade
	 * 
	 * @param Lumine_Base $target
	 * @author Hugo Ferreira da Silva
	 * @return mixed
	 */
	public function getFieldValue(Lumine_Base $target){
		return $target->{$this->getField()};
	}
	
	/**
	 * Recupera a mensagem em caso de erros
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	public function getErrorMessage(){
		return $this->errorMessage;
	}

	/**
	 * Metodo que executara a validacao do item.
	 * 
	 * <p>Este metodo sempre retornara boolean. A classe que executa
	 * as regras de validacao vinculadas ao objeto eh quem devera
	 * retornar o array com as mensagens de erro.</p>
	 * 
	 * @param Lumine_Base $target
	 * @author Hugo Ferreira da Silva
	 * @return boolean
	 */
	abstract public function execute(Lumine_Base $target);
}