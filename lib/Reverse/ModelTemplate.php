<?php
/**
 * Classe que gera as Models
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */

/**
 * Classe que gera o DTO 
 * 
 * @package Lumine
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br
 */
class Lumine_Reverse_ModelTemplate {
	
	/**
	 * nome da classe
	 * @var string
	 */
	private $classname;
	
	/**
	 * nome da model
	 * @var string
	 */
	private $modelname;
	
	/**
	 * formato de nome da model
	 * @var string
	 */
	private $format;
	
	/**
	 * Caminho onde serao gravadas as models
	 * @var string
	 */
	private $modelsPath = '';
	
	/**
	 * Data atual
	 * @var string
	 */
	private $date = '';
	
	/**
	 * Altera a pasta onde sera gravada a model
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @param string $value
	 * @return void
	 */
	public function setModelsPath($value){
		$this->modelsPath = $value;
	}
	
	/**
	 * Recupera a pasta onde sera gravada a model
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return string
	 */
	public function getModelsPath(){
		return $this->modelsPath;
	}
	
	/**
	 * Recupera o nome da classe
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return string
	 */
	public function getClassname(){
		return $this->classname;
	}
	
	/**
	 * Recupera o nome completo do arquivo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return string
	 */
	public function getFullFileName(){
		$filename = $this->modelsPath . '/' . sprintf($this->format, $this->classname) . '.php';
		
		return $filename;
	}
	
	/**
	 * Recupera o nome do arquivo
	 * 
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br
	 * @return string
	 */
	public function getFileName(){
		$filename = sprintf($this->format, $this->classname) . '.php';
		
		return $filename;
	}
    
    /**
     * Construtor da classe
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $classname
     * @param string $format
     * @return Lumine_Reverse_ModelTemplate
     */
	function __construct($classname, $format='%s'){
		$this->format = $format;
		$this->classname = $classname;
	}
	
    /**
     * Cria / atualiza o arquivo gerado
     * 
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return string 
     */
	public function getContent(){
		$this->modelname = sprintf($this->format, $this->classname);
		$this->date = date('Y-m-d H:i:s');
		
		$templateFile = LUMINE_INCLUDE_PATH . '/lib/Templates/Model.tpl';
		$tpl = file_get_contents($templateFile);
		
		$start = "### START AUTOCODE";
		$end = "### END AUTOCODE";
		
		$originalFile = $this->getFullFileName();
		
		$class = '';
		$tpl = preg_replace('@\{(\w+)\}@e','$this->$1',$tpl);
		
		if(file_exists($originalFile)){
			
			$content = file_get_contents($originalFile);
			$autoCodeOriginal = substr($content, strpos($content,$start)+strlen($start), strpos($content,$end)+strlen($end) - (strpos($content,$start)+strlen($start)));
			
			$autoCodeGenerated = substr($tpl, strpos($tpl,$start)+strlen($start), strpos($tpl,$end)+strlen($end) - (strpos($tpl,$start)+strlen($start)));
			
			$class = str_replace($autoCodeOriginal, $autoCodeGenerated, $content);
			
		} else {
			$class = $tpl;
		}
		
		
		return $class;
	}
	


}


