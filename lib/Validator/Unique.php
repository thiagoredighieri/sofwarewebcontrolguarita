<?php

/**
 * Verifica se já existe ou não outro registro com o mesmo valor de campo
 * no banco de dados.
 * 
 *
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine
 * @package Lumine_Validator
 */
class Lumine_Validator_Unique extends Lumine_Validator_AbstractValidator {

	/**
	 * @see Lumine_Validator_AbstractValidator::execute()
	 */
	public function execute(Lumine_Base $obj){
		$value = $this->getFieldValue($obj);
		
		$reflection = new ReflectionClass( $obj->metadata()->getClassname() );
		
		/* @var $objeto Lumine_Base */
		$objeto = $reflection->newInstance();
		$objeto->{$this->getField()} = $obj->{$this->getField()};
		$objeto->find();
		
		$pks = $objeto->metadata()->getPrimaryKeys();
		$result = true;
		
		while ($objeto->fetch()) {
			foreach( $pks as $def ) {
				if( $objeto->$def['name'] != $obj->$def['name']) {
					$result = false;
					break;
				}
			}
			
			if(!$result){
				break;
			}
		}
		
		$objeto->destroy();
		unset($objeto, $reflection);
		
		return $result;
	}

}