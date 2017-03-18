<?php


abstract class Lumine_Templates_AbstractTemplate {
	
	/**
	*
	* @var Lumine_Reverse_ClassTemplate
	*/
	private $classTemplate;
	
	/**
	 * Altera a referencia do template
	 *
	 * @param Lumine_Reverse_ClassTemplate $classTemplate
	 * @author Hugo Ferreira da Silva
	 */
	public function setClassTemplate(Lumine_Reverse_ClassTemplate $classTemplate){
		$this->classTemplate = $classTemplate;
	}
	
	/**
	 * Recupera a referencia do template
	 *
	 *
	 * @return Lumine_Reverse_ClassTemplate
	 */
	public function getClassTemplate(){
		return $this->classTemplate;
	}
	
	/**
	 * Recupera o conteudo gerado
	 * 
	 * @author Hugo Ferreira da Silva
	 * @return string
	 */
	abstract public function getContents();
	
}