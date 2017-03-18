<?php
/**
 * Classe para recuperar um dialeto
 * 
 * @package Dialect
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

/**
 * Classe para recuperar um dialeto
 * 
 * @package Dialect
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Dialect_Factory extends Lumine_EventListener {

	private static $createdItems = array();
	
	/**
	 * Recupera o dialeto para o objeto
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param Lumine_Base $obj
	 * @return Lumine_Dialect_IDialect
	 */
	public static function get(Lumine_Base $obj){
		$dialect = $obj->_getConfiguration()->getProperty('dialect');
		$id = $obj->_getObjectPart('_objectID');
		
		if(!array_key_exists($dialect, self::$createdItems)){
			Lumine::load('Lumine_Dialect_'.$dialect);
			$ref = new ReflectionClass('Lumine_Dialect_'.$dialect);
			self::$createdItems[ $dialect ] = $ref->newInstance();;
		}
		
		self::$createdItems[ $dialect ]->setConnection($obj->_getConnection());
		self::$createdItems[ $dialect ]->setObjectId($id);
		self::$createdItems[ $dialect ]->setTablename($obj->metadata()->getTablename());
		
		return self::$createdItems[ $dialect ];
	}
	
	/**
	 * Retorna um dialeto pelo nome
	 * @author Hugo Ferreira da Silva
	 * @param string $dialectName
	 * @return Lumine_Dialect_IDialect
	 */
	public static function getByName($dialectName){
		
		if( isset(self::$createdItems[$dialectName]) ){
			return self::$createdItems[$dialectName];
		} else {
			Lumine::load('Lumine_Dialect_'.$dialectName);
			$ref = new ReflectionClass('Lumine_Dialect_'.$dialectName);
			self::$createdItems[ $dialectName ] = $ref->newInstance();
		}
		
		return self::$createdItems[ $dialectName ];
	}
	
}