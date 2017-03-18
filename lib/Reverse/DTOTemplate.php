<?php
/**
 * Classe que gera o DTO
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
class Lumine_Reverse_DTOTemplate {

	/**
	 * Definicao dos campos
	 * @var array
	 */
	private $definition;
	/**
	 * nome da classe
	 * @var string
	 */
	private $classname;
	/**
	 * formato da classe
	 * @var string
	 */
	private $format;
	/**
	 * indica se eh para usar camelcase
	 * @var boolean
	 */
	private $camelCase = true;
    /**
     * relacionamentos do tipo um-para-muitos
     *
     * @var array
     */
    private $one_to_many  = array();
    /**
     * relacionamentos muitos-para-muitos
     *
     * @var array
     */
    private $many_to_many = array();
	/**
	 * pacote a ser colocado em explicity type
	 * @var string
	 */
    private $package = '';

    /**
     * Construtor da classe
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param string $classname
     * @param array $definition
     * @param string $format
     * @return DTOTemplate
     */
	function __construct($classname, $definition,$format='%sDTO'){
		$this->format = $format;
		$this->classname = $classname;
		$this->definition = $definition;
	}

	/**
	 * Adiciona um item 1-M
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param array $def
	 * @return void
	 */
	public function addOneToMany($def) {
        $this->one_to_many[] = $def;
    }

    /**
     * adiciona um item N-M
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @param array $def
     * @return void
     */
    public function addManyToMany($def) {
        $this->many_to_many[] = $def;
    }

    /**
     * Recupera o pacote que sera colocado em explicty type
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br
     * @return string
     */
    public function getPackage(){
    	return $this->package;
    }

    /**
     * Altera o pacote que sera colocado em explicty type
     *
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br
     * @param string $value
     * @return void
     */
    public function setPackage($value){
    	$this->package = $value;
    }

    /**
     * Pega o conteudo que sera colocado no arquivo
     * @author Hugo Ferreira da Silva
     * @link http://www.hufersil.com.br/
     * @return string
     */
	public function getContent(){
		$class = '<?php'.PHP_EOL;
		$class .= "class ". $this->getClassname() ." {".PHP_EOL.PHP_EOL;

		$pacote = $this->getPackage();
		if(!empty($pacote)){
			$pacote .= '.';
		}

		$class .= "\tpublic \$_explicitType = '" . $pacote . $this->getClassname() . "';" . PHP_EOL.PHP_EOL;

		foreach($this->definition as $item){
			$class .= "\tpublic $" . $this->CamelCase($item[0]) .";".PHP_EOL;
		}

		foreach($this->one_to_many as $item) {
	        $class .= "\tpublic $" . $this->CamelCase($item['name']).' = array();' . PHP_EOL;
        }

        foreach($this->many_to_many as $item) {
	        $class .= "\tpublic $" . $this->CamelCase($item['name']).' = array();' . PHP_EOL;
		}

		$class .= "}";

		return $class;
	}

	/**
	 * indica se eh para usar camel case
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param boolean $flag
	 * @return void
	 */
	public function setCamelCase($flag){
		$this->camelCase = $flag;
	}

	/**
	 * Recupera para saber se eh para usar camel case
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return boolean
	 */
	public function getCamelCase(){
		return $this->camelCase;
	}

	/**
	 * Recupera o nome da classe
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @return string
	 */
	public function getClassname(){
		return sprintf($this->format, ucfirst($this->CamelCase($this->classname)));
	}

	private $createdNames = array();
	/**
	 * Retorna o nome de um membro no estilo CamelCase
	 * @author Hugo Ferreira da Silva
	 * @link http://www.hufersil.com.br/
	 * @param string $name
	 * @return string
	 */
	private function CamelCase( $name ) {
        if( $this->getCamelCase() == true ) {
	        $name = Lumine_Util::camelCase($name);
        }

        if( isset($this->createdNames[$name]) ) {
	        if( count($this->createdNames) > 1 ){
	        	$name .= $this->createdNames[$name]++;
	        }
        } else {
        	$this->createdNames[$name] = 1;
        }
        return $name;
    }
}


?>